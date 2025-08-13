<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Thirteenth_model extends CI_Model {

  public function get_13th_from_attendance_reg_only($start, $end, $employment = 'active')
{
    $settingsID = $this->session->userdata('settingsID');

    $rows = $this->db
        ->select("
            p.personnelID,
            p.first_name,
            p.last_name,
            p.position,
            p.rateType,
            p.rateAmount,
            a.date,
            a.status,
            COALESCE(a.work_duration, 0)  AS reg_hours,      -- regular hours logged
            COALESCE(a.overtime_hours, 0) AS ot_hours,       -- ignored for 13th month
            COALESCE(a.holiday_hours, 0)  AS holiday_hours   -- 0 if not used
        ", false)
        ->from('personnel p')
        ->join(
            'attendance a',
            "a.personnelID = p.personnelID
             AND a.settingsID = p.settingsID
             AND a.date >= ".$this->db->escape($start)."
             AND a.date <= ".$this->db->escape($end),
            'left'
        )
        ->where('p.settingsID', $settingsID)

        ->group_start()
            ->where("(CASE 
                        WHEN ".$this->db->escape($employment)." = 'active' THEN 
                            (p.date_terminated IS NULL OR p.date_terminated='0000-00-00' OR p.date_terminated > ".$this->db->escape($end).")
                        WHEN ".$this->db->escape($employment)." = 'inactive' THEN 
                            (p.date_terminated IS NOT NULL AND p.date_terminated <> '0000-00-00' AND p.date_terminated <= ".$this->db->escape($end).")
                        ELSE 1=1
                      END)", null, false)
        ->group_end()

        ->order_by('p.last_name', 'ASC')
        ->order_by('p.first_name', 'ASC')
        ->get()->result_array();

    $agg = [];

    foreach ($rows as $r) {
        $pid = $r['personnelID'];

        if (!isset($agg[$pid])) {
            $agg[$pid] = [
                'personnelID' => $pid,
                'first_name'  => $r['first_name'] ?? '',
                'last_name'   => $r['last_name'] ?? '',
                'position'    => $r['position'] ?? '',
                'basic_total' => 0.0,
            ];
        }

        $rateType   = strtolower((string)$r['rateType']);
        $rateAmount = (float)$r['rateAmount'];

        if ($rateType === 'hour') {
            $base = $rateAmount;
        } elseif ($rateType === 'day') {
            $base = $rateAmount / 8.0;
        } elseif ($rateType === 'month') {
            $base = ($rateAmount / 30.0) / 8.0;
        } elseif ($rateType === 'bi-month' || $rateType === 'bi-monthly' || $rateType === 'bimonth' || $rateType === 'bi-month ') {
            $base = ($rateAmount / 15.0) / 8.0;
        } else {
            $base = 0.0;
        }

        $status       = strtolower(trim(preg_replace('/\s+/', '', (string)($r['status'] ?? ''))));
        $regHours     = max(0.0, (float)$r['reg_hours']);
        $holidayHours = max(0.0, (float)$r['holiday_hours']);

        $isHoliday = (bool)(preg_match('/holiday|regularho|legal|special/i', $status) || $holidayHours > 0);

        if ($isHoliday) {
            $worked = ($holidayHours > 0) ? $holidayHours : $regHours;
            $agg[$pid]['basic_total'] += $worked * $base;
        } else {
            $agg[$pid]['basic_total'] += $regHours * $base;
        }
    }

    foreach ($agg as &$x) {
        $x['basic_total'] = round($x['basic_total'], 2);
    }
    unset($x);

    return array_values($agg);
}


    public function get_13th_from_payroll($start, $end, $employment = 'active')
{
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select("
        pr.personnelID,
        pr.first_name, pr.last_name, pr.position,
        pr.rateType, CAST(pr.rateAmount AS DECIMAL(10,2)) AS rateAmount,
        COALESCE(SUM(ps.reg_pay),0) AS basic_total
    ");
    $this->db->from('personnel pr');
    $this->db->join(
        'payroll_summary ps',
        "ps.personnelID = pr.personnelID
         AND ps.settingsID = pr.settingsID
         AND ps.start_date >= ".$this->db->escape($start)."
         AND ps.end_date   <= ".$this->db->escape($end),
        'left'
    );
    $this->db->where('pr.settingsID', $settingsID);

    $this->db->group_start()
        ->where("(CASE 
                    WHEN ".$this->db->escape($employment)." = 'active' THEN 
                        (pr.date_terminated IS NULL OR pr.date_terminated='0000-00-00' OR pr.date_terminated > ".$this->db->escape($end).")
                    WHEN ".$this->db->escape($employment)." = 'inactive' THEN 
                        (pr.date_terminated IS NOT NULL AND pr.date_terminated <> '0000-00-00' AND pr.date_terminated <= ".$this->db->escape($end).")
                    ELSE 1=1
                  END)", null, false)
    ->group_end();

    $this->db->group_by('pr.personnelID');
    $this->db->order_by('pr.last_name, pr.first_name');

    return $this->db->get()->result_array();
}

    public function get_13th_month_data($period = null)
    {
        $settingsID = $this->session->userdata('settingsID');
        $year = date('Y');

        if ($period == 'jan-jun') {
            $monthFilter = "BETWEEN '{$year}-01' AND '{$year}-06'";
            $dateFilter = "BETWEEN '{$year}-01-01' AND '{$year}-06-30'";
        } elseif ($period == 'jul-dec') {
            $monthFilter = "BETWEEN '{$year}-07' AND '{$year}-12'";
            $dateFilter = "BETWEEN '{$year}-07-01' AND '{$year}-12-31'";
        } else {
            $monthFilter = "LIKE '{$year}%'";
            $dateFilter = "BETWEEN '{$year}-01-01' AND '{$year}-12-31'";
        }

        $this->db->select('
            p.personnelID,
            p.first_name,
            p.last_name,
            p.position,
            p.rateType,
            CAST(p.rateAmount AS DECIMAL(10,2)) as rateAmount,
            pm.details_json
        ');
        $this->db->from('personnel p');
        $this->db->join('payroll_attendance_monthly pm', 'pm.personnelID = p.personnelID');
        $this->db->where("pm.payroll_month $monthFilter");
        $this->db->where('p.settingsID', $settingsID);
        $this->db->order_by('p.last_name', 'ASC');
        $this->db->order_by('p.first_name', 'ASC');
        $monthly_data = $this->db->get()->result();

        $this->db->select('
            p.personnelID,
            p.first_name,
            p.last_name,
            p.position,
            p.rateType,
            CAST(p.rateAmount AS DECIMAL(10,2)) as rateAmount,
            IFNULL(SUM(a.work_duration), 0) AS total_attendance_hours,
            IFNULL(SUM(w.total_hours), 0) AS total_work_hours
        ');
        $this->db->from('personnel p');
        $this->db->join('attendance a', "a.personnelID = p.personnelID AND a.date $dateFilter", 'left');
        $this->db->join('work_hours w', "w.personnelID = p.personnelID AND w.from $dateFilter", 'left');
        $this->db->where_in('LOWER(p.rateType)', ['hour', 'day']);
        $this->db->where('p.settingsID', $settingsID);
        $this->db->group_by('p.personnelID');
        $this->db->order_by('p.last_name', 'ASC');
        $this->db->order_by('p.first_name', 'ASC');
        $hour_day_data = $this->db->get()->result();

        $merged = [];
        foreach ($monthly_data as $row) {
            $details = json_decode($row->details_json, true);
            $regTime = 0;
            if (is_array($details)) {
                foreach ($details as $day => $entry) {
                    if (is_array($entry) && isset($entry['reg'])) {
                        $regTime += floatval($entry['reg']);
                    }
                }
            }
            $merged[$row->personnelID] = [
                'name' => $row->last_name . ', ' . $row->first_name,
                'position' => $row->position,
                'rateAmount' => floatval($row->rateAmount),
                'rateType' => $row->rateType,
                'total_regular_hours' => $regTime,
            ];
        }

        foreach ($hour_day_data as $row) {
            if (!isset($merged[$row->personnelID])) {
                $totalHrs = floatval($row->total_attendance_hours) + floatval($row->total_work_hours);
                $merged[$row->personnelID] = [
                    'name' => $row->last_name . ', ' . $row->first_name,
                    'position' => $row->position,
                    'rateAmount' => floatval($row->rateAmount),
                    'rateType' => $row->rateType,
                    'total_regular_hours' => $totalHrs,
                ];
            }
        }

        uasort($merged, function ($a, $b) { return strcmp($a['name'], $b['name']); });
        return $merged;
    }
}
