<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MonthlyPayroll_model extends CI_Model
{
    // Get all personnel (no project filter)
//   public function get_all_personnel()
// {
//     $this->db->from('personnel');
//     // No status column; fetch all personnel
//     $this->db->order_by('last_name, first_name');
//     return $this->db->get()->result();
// }
public function get_all_personnel($settingsID = null, $allowedTypes = ['Bi-Month', 'Monthly'])
{
    $this->db->from('personnel');
    if ($settingsID !== null) {
        $this->db->where('settingsID', $settingsID);
    }
    if (!empty($allowedTypes)) {
      if (!empty($allowedTypes)) {
    $this->db->where_in('rateType', $allowedTypes);  
}

    }
    $this->db->order_by('last_name, first_name');
    return $this->db->get()->result();
}



public function save_payroll_monthly($personnelID, $month, $newDetails)
{
    $settingsID = $this->session->userdata('settingsID');

    $data = [
        'personnelID'    => $personnelID,
        'payroll_month'  => $month,
        'date_generated' => date('Y-m-d H:i:s'),
        'settingsID'     => $settingsID
    ];

    $this->db->where('personnelID', $personnelID);
    $this->db->where('payroll_month', $month);
    $this->db->where('settingsID', $settingsID);
    $existing = $this->db->get('payroll_attendance_monthly')->row();

    if ($existing) {
        $existingDetails = json_decode($existing->details_json, true);
        if (!is_array($existingDetails)) $existingDetails = [];

        foreach ($newDetails as $day => $entry) {
            if ($day === '_range') continue;
            $existingDetails[$day] = $entry;
        }

        $existingDetails['_range'] = $newDetails['_range'] ?? [];

        $data['details_json'] = json_encode($existingDetails);

        $this->db->where('personnelID', $personnelID);
        $this->db->where('payroll_month', $month);
        $this->db->where('settingsID', $settingsID);
        $this->db->update('payroll_attendance_monthly', $data);
    } else {
        $data['details_json'] = json_encode($newDetails);
        $this->db->insert('payroll_attendance_monthly', $data);
    }
}

public function save_attendance($data)
{
    $this->db->where('personnelID', $data['personnelID']);
    $this->db->where('date', $data['date']);
    $exists = $this->db->get('attendance')->row();

    if ($exists) {
        $this->db->where('personnelID', $data['personnelID']);
        $this->db->where('date', $data['date']);
        return $this->db->update('attendance', $data);
    } else {
        return $this->db->insert('attendance', $data);
    }
}

public function get_saved_months()
{
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('DISTINCT(payroll_month)', FALSE);
    $this->db->from('payroll_attendance_monthly');
    $this->db->where('settingsID', $settingsID);
    $this->db->order_by('payroll_month', 'DESC');

    $query = $this->db->get();
    return $query->result();
}



public function get_monthly_payroll_records($month, $filterFrom = null, $filterTo = null)
{
    $settingsID = $this->session->userdata('settingsID');

    $personnel = $this->get_all_personnel($settingsID, ['Bi-Month', 'Month']);

    $seen = [];
    $unique = [];
    foreach ($personnel as $p) {
        if (!isset($seen[$p->personnelID])) {
            $seen[$p->personnelID] = true;
            $unique[] = $p;
        }
    }
    $personnel = $unique;

    $rows = $this->db->from('payroll_attendance_monthly')
                     ->where('payroll_month', $month)
                     ->where('settingsID', $settingsID)
                     ->get()
                     ->result();

    $year = (int)substr($month, 0, 4);
    $monthNum = (int)substr($month, 5, 2);

    $dateSet = [];
    foreach ($rows as $row) {
        $details = json_decode($row->details_json, true);
        if (!is_array($details)) continue;

        foreach ($details as $k => $entry) {
            if ($k === '_range') continue;

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $k)) {
                $date = $k; 
            } else {
                $day  = str_pad($k, 2, '0', STR_PAD_LEFT);
                $date = sprintf('%s-%s-%s', substr($month,0,4), substr($month,5,2), $day);
            }

            if ($filterFrom && $filterTo && ($date < $filterFrom || $date > $filterTo)) continue;
            $dateSet[$date] = true;
        }
    }

    $dates = array_keys($dateSet);
    sort($dates);

    $attendance = [];
    foreach ($rows as $row) {
        $details = json_decode($row->details_json, true) ?: [];
        $personnelID = $row->personnelID;

        foreach ($details as $k => $entry) {
            if ($k === '_range') continue;

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $k)) {
                $date = $k;
            } else {
                $day  = str_pad($k, 2, '0', STR_PAD_LEFT);
                $date = sprintf('%s-%s-%s', substr($month,0,4), substr($month,5,2), $day);
            }

            if ($filterFrom && $filterTo && ($date < $filterFrom || $date > $filterTo)) continue;

            $attendance[$personnelID][$date] = $entry; 
        }
    }

    $start = date('Y-m-01', strtotime($month));
    $end   = date('Y-m-t', strtotime($month));

    foreach ($personnel as &$p) {
        $personnelID = $p->personnelID;

        $cash = $this->db->select_sum('amount')->from('cashadvance')
            ->where('personnelID', $personnelID)->where('type', 'cash')
            ->where("(deduct_from IS NULL OR deduct_from <= '$end')", null, false)
            ->where("(deduct_to IS NULL OR deduct_to >= '$start')",   null, false)
            ->get()->row();
        $p->cash_advance = $cash && $cash->amount !== null ? (float)$cash->amount : 0;

        $other = $this->db->select_sum('amount')->from('cashadvance')
            ->where('personnelID', $personnelID)->where('type', 'Others')
            ->where("(deduct_from IS NULL OR deduct_from <= '$end')", null, false)
            ->where("(deduct_to IS NULL OR deduct_to >= '$start')",   null, false)
            ->get()->row();
        $p->other_deduction = $other && $other->amount !== null ? (float)$other->amount : 0;

        $loan = $this->db->select_sum('monthly_deduction')->from('personnelloans')
            ->where('personnelID', $personnelID)->where('status', 1)->where('is_paid', 0)
            ->where("(deduct_from IS NULL OR deduct_from <= '$end')", null, false)
            ->where("(deduct_to IS NULL OR deduct_to >= '$start')",   null, false)
            ->get()->row();
        $p->loan = $loan && $loan->monthly_deduction !== null ? (float)$loan->monthly_deduction : 0;

        $p->gov_sss = 0; $p->gov_pagibig = 0; $p->gov_philhealth = 0;
        $govt = $this->db->select('description, SUM(amount) AS amount')->from('government_deductions')
            ->where('personnelID', $personnelID)
            ->where("(deduct_from IS NULL OR deduct_from <= '$end')", null, false)
            ->where("(deduct_to IS NULL OR deduct_to >= '$start')",   null, false)
            ->group_by('description')->get()->result();

        foreach ($govt as $g) {
            $desc = strtolower(trim($g->description));
            if ($desc === 'sss')                       $p->gov_sss       = (float)$g->amount;
            elseif ($desc === 'pagibig' || $desc === 'pag-ibig') $p->gov_pagibig   = (float)$g->amount;
            elseif ($desc === 'philhealth' || $desc === 'phic') $p->gov_philhealth = (float)$g->amount;
            else $p->other_deduction += (float)$g->amount;
        }

        $p->total_deduction = $p->cash_advance + $p->loan + $p->gov_sss + $p->gov_pagibig + $p->gov_philhealth + $p->other_deduction;
        $p->ca_cashadvance = $p->cash_advance; $p->sss = $p->gov_sss; $p->pagibig = $p->gov_pagibig; $p->philhealth = $p->gov_philhealth;
    }

    return [
        'personnel'  => $personnel,
        'dates'      => $dates,
        'attendance' => $attendance,
    ];
}






public function get_payroll_record($personnelID, $month)
{
    $this->db->where('personnelID', $personnelID);
    $this->db->where('payroll_month', $month);
    $this->db->where('settingsID', $this->session->userdata('settingsID'));
    return $this->db->get('payroll_attendance_monthly')->row();
}

public function update_payroll_details($personnelID, $month, $details)
{
    $settingsID = $this->session->userdata('settingsID');

    $this->db->where('personnelID', $personnelID);
    $this->db->where('payroll_month', $month);
    $this->db->where('settingsID', $settingsID);
    $this->db->update('payroll_attendance_monthly', [
        'details_json'   => json_encode($details),
        'date_generated' => date('Y-m-d H:i:s')
    ]);

    if ($this->db->affected_rows() === 0) {
        $this->db->insert('payroll_attendance_monthly', [
            'personnelID'    => $personnelID,
            'payroll_month'  => $month,
            'settingsID'     => $settingsID,
            'details_json'   => json_encode($details),
            'date_generated' => date('Y-m-d H:i:s')
        ]);
    }
}
public function get_cash_advance($pid, $month)
{
    $start = date('Y-m-01', strtotime($month));
    $end = date('Y-m-t', strtotime($month));

    $this->db->select_sum('amount');
    $this->db->where('personnelID', $pid);
    $this->db->where("deduct_from <=", $end);
    $this->db->where("deduct_to >=", $start);
    $query = $this->db->get('cashadvance');
    return $query->row()->amount ?? 0;
}


public function get_government_deductions($pid, $month)
{
    $start = date('Y-m-01', strtotime($month));
    $end = date('Y-m-t', strtotime($month));

    $this->db->where('personnelID', $pid);
    $this->db->where("deduct_from <=", $end);
    $this->db->where("deduct_to >=", $start);
    $query = $this->db->get('government_deductions');

    $result = ['sss' => 0, 'pagibig' => 0, 'philhealth' => 0];
    foreach ($query->result() as $row) {
        if (stripos($row->description, 'sss') !== false) {
            $result['sss'] += $row->amount;
        } elseif (stripos($row->description, 'pag-ibig') !== false || stripos($row->description, 'pagibig') !== false) {
            $result['pagibig'] += $row->amount;
        } elseif (stripos($row->description, 'philhealth') !== false || stripos($row->description, 'phic') !== false) {
            $result['philhealth'] += $row->amount;
        }
    }

    return $result;
}


public function get_loan_deduction($pid, $month)
{
    $start = date('Y-m-01', strtotime($month));
    $end = date('Y-m-t', strtotime($month));

    $this->db->select_sum('monthly_deduction');
    $this->db->where('personnelID', $pid);
    $this->db->where('status', 1);
    $this->db->where('is_paid', 0);
    $this->db->where("deduct_from <=", $end);
    $this->db->where("deduct_to >=", $start);
    $query = $this->db->get('personnelloans');
    return $query->row()->monthly_deduction ?? 0;
}


}
