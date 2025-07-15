<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Overtime_model extends CI_Model {

    public function generateOvertimeOnly($sy) {
        $this->db->select('*');
        $this->db->from('work_hours');
        $this->db->where('total_hours >', 8); // Overtime is any work beyond 8 hours
        $query = $this->db->get();
        $rows = $query->result();

        foreach ($rows as $row) {
            $exists = $this->db->get_where('attendance', [
                'personnelID' => $row->personnelID,
                'date' => $row->from,
                'status' => 'Overtime'
            ])->num_rows();

            if ($exists == 0) {
                $this->db->insert('attendance', [
                    'personnelID' => $row->personnelID,
                    'projectID' => $row->projectID,
                    'date' => $row->from,
                    'status' => 'Overtime',
                    'work_duration' => $row->total_hours - 8,
                    'holiday_hours' => 0.00,
                    'settingsID' => $row->settingsID
                ]);
            }
        }

        return count($rows) > 0;
    }
public function get_personnel_by_project($projectID) {
    $this->db->distinct();
    $this->db->select('p.*');
    $this->db->from('work_hours w');
    $this->db->join('personnel p', 'p.personnelID = w.personnelID');
    $this->db->where('w.projectID', $projectID);
    return $this->db->get()->result();
}


}
?>
