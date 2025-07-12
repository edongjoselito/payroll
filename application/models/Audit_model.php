<?php
class Audit_model extends CI_Model
{
    public function insert_audit_log($userID, $action, $description)
    {
        $data = [
            'userID' => $userID,
            'action' => $action,
            'description' => $description
        ];
        return $this->db->insert('audit_attendance_log', $data);
    }

 public function get_all_attendance_logs()
{
    $this->db->select('a.*, u.fName, u.lName');
    $this->db->from('audit_attendance_log a');
    $this->db->join('o_users u', 'u.IDNumber = a.userID', 'left'); // join on IDNumber
    $this->db->order_by('a.date_time', 'DESC');
    return $this->db->get()->result();
}

}
