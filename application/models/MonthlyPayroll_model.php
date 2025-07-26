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
        $this->db->where_in('salaryType', $allowedTypes); // Make sure the field name matches
    }
    $this->db->order_by('last_name, first_name');
    return $this->db->get()->result();
}



public function save_payroll_monthly($personnelID, $month, $details)
{
    // $details is an associative array: [ '01' => ['status'=>'Present', ...], ...]
    $data = [
        'personnelID'    => $personnelID,
        'payroll_month'  => $month,
        'details_json'   => json_encode($details),
        'date_generated' => date('Y-m-d H:i:s')
    ];
    // Upsert logic: update if exists, else insert
    $this->db->where('personnelID', $personnelID);
    $this->db->where('payroll_month', $month);
    $query = $this->db->get('payroll_attendance_monthly');
    if ($query->num_rows() > 0) {
        $this->db->where('personnelID', $personnelID);
        $this->db->where('payroll_month', $month);
        $this->db->update('payroll_attendance_monthly', $data);
    } else {
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
    $this->db->select('DISTINCT(payroll_month)', FALSE);
    $this->db->from('payroll_attendance_monthly');
    $this->db->order_by('payroll_month', 'DESC');
    $query = $this->db->get();
    $result = $query->result_array();
    $months = [];
    foreach ($result as $row) {
        $months[] = $row['payroll_month'];
    }
    return $months;
}
public function get_monthly_payroll_records($month)
{
    // Get all personnel
  $this->db->from('personnel');
$this->db->where_in('rateType', ['Month', 'Bi-Month']);
$this->db->order_by('last_name, first_name');
$personnel = $this->db->get()->result();


    // Get all payroll monthly records for the selected month
    $rows = $this->db->get_where('payroll_attendance_monthly', ['payroll_month' => $month])->result();

    // Build all days in the selected month
    $year = (int)substr($month, 0, 4);
    $monthNum = (int)substr($month, 5, 2);
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
    $dates = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dates[] = sprintf('%04d-%02d-%02d', $year, $monthNum, $d);
    }

    // Map personnelID => [date => ['status'=>..., 'regular_hours'=>..., 'overtime_hours'=>...]]
    $attendance = [];
    foreach ($rows as $row) {
        $details = json_decode($row->details_json, true); // array: ['2025-07-01' => [...], ...]
        if (!$details) $details = [];
        $attendance[$row->personnelID] = $details;
    }

    return [
        'personnel' => $personnel,
        'dates'     => $dates,
        'attendance'=> $attendance,
    ];
}

// Get one record for a person/month
public function get_payroll_record($personnelID, $month)
{
    $this->db->where('personnelID', $personnelID);
    $this->db->where('payroll_month', $month);
    return $this->db->get('payroll_attendance_monthly')->row();
}

// Update the details_json for a person/month
public function update_payroll_details($personnelID, $month, $details)
{
    $this->db->where('personnelID', $personnelID);
    $this->db->where('payroll_month', $month);
    $this->db->update('payroll_attendance_monthly', [
        'details_json' => json_encode($details),
        'date_generated' => date('Y-m-d H:i:s')
    ]);
}


}
