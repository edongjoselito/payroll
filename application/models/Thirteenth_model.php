<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Thirteenth_model extends CI_Model {

    public function get_13th_month_data($period = null)
    {
        $year = date('Y');

        // Define month range filter
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

        // Monthly/Bi-Monthly
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
        $this->db->order_by('p.last_name', 'ASC');
        $this->db->order_by('p.first_name', 'ASC');
        $monthly_data = $this->db->get()->result();

        // Hour/Day
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
        $this->db->where_in('p.rateType', ['hour', 'day']);
        $this->db->group_by('p.personnelID');
        $this->db->order_by('p.last_name', 'ASC');
        $this->db->order_by('p.first_name', 'ASC');
        $hour_day_data = $this->db->get()->result();

        // Merge both datasets
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

        // Sort merged array by last_name, first_name
        uasort($merged, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $merged;
    }
}
