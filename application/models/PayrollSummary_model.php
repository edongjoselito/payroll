<?php
class PayrollSummary_model extends CI_Model
{
    public function save($data)
    {
        $this->db->insert('payroll_summary', $data);
        return $this->db->insert_id();
    }

    public function exists($personnelID, $projectID, $start, $end)
    {
        $this->db->where('personnelID', $personnelID);
        $this->db->where('projectID', $projectID);
        $this->db->where('start_date', $start);
        $this->db->where('end_date', $end);
        return $this->db->count_all_results('payroll_summary') > 0;
    }
}
