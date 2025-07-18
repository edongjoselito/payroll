<?php
class Overtime_model extends CI_Model {

   public function get_personnel_by_project($projectID) {
    $this->db->select('p.personnelID, p.first_name, p.last_name');
    $this->db->from('project_personnel_assignment pa');
    $this->db->join('personnel p', 'pa.personnelID = p.personnelID');
    $this->db->where('pa.projectID', $projectID);
    return $this->db->get()->result();
}


   public function save_overtime($post) {
    $projectID = $post['projectID'];
    $start = $post['start'];
    $end = $post['end'];
    $hours = $post['hours'];

    foreach ($hours as $personnelID => $dailyHours) {
        foreach ($dailyHours as $date => $value) {
            if ($value !== null && $value !== '') {
                $this->db->insert('overtime', [
                    'personnelID' => $personnelID,
                    'projectID' => $projectID,
                    'date' => $date,
                    'hours' => $value
                ]);
            }
        }
    }
}
public function get_saved_dates($projectID)
{
    $this->db->select('date');
    $this->db->from('overtime');
    $this->db->where('projectID', $projectID);
    $this->db->group_by('date');
    $this->db->order_by('date', 'DESC');
    $query = $this->db->get();
    return $query->result(); 
}




public function get_saved_overtime($projectID, $date)
{
    $this->db->select('o.*, p.first_name, p.last_name');
    $this->db->from('overtime o');
    $this->db->join('personnel p', 'p.personnelID = o.personnelID');
    $this->db->where('o.projectID', $projectID);
    $this->db->where('o.date', $date);
    return $this->db->get()->result();
}

public function get_saved_overtime_batch($projectID, $start, $end)
{
    $this->db->select('o.*, p.first_name, p.last_name');
    $this->db->from('overtime o');
    $this->db->join('personnel p', 'p.personnelID = o.personnelID');
    $this->db->where('o.projectID', $projectID);
    $this->db->where('o.date >=', $start);
    $this->db->where('o.date <=', $end);
    $this->db->order_by('o.date', 'ASC');
    return $this->db->get()->result();
}
public function getSavedOvertime($projectID, $start, $end)
{
    return $this->db->select('o.*, p.first_name, p.last_name')
        ->from('overtime o')
        ->join('personnel p', 'p.personnelID = o.personnelID', 'left')
        ->where('o.projectID', $projectID)
        ->where('o.date >=', $start)
        ->where('o.date <=', $end)
        ->order_by('o.personnelID, o.date')
        ->get()
        ->result();
}




}
