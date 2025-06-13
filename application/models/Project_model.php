<?php
class Project_model extends CI_Model
{
    private $table = 'project';

    public function getAll() {
        return $this->db->get($this->table)->result();
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($data) {
        $this->db->where('projectID', $data['projectID']);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        $this->db->where('projectID', $id);
        return $this->db->delete($this->table);
    }




    
    public function getPersonnelBySettingsID($settingsID) {
    return $this->db->where('settingsID', $settingsID)->get('personnel')->result();
}

public function getAttendanceBySettingsID($settingsID, $date) {
    $this->db->where('settingsID', $settingsID);
    $this->db->where('attendance_date', $date);
    return $this->db->get('personnelattendance')->result();
}

public function save_attendance($data) {
    // Check if record exists for personnelID + date
    $this->db->where('personnelID', $data['personnelID']);
    $this->db->where('attendance_date', $data['attendance_date']);
    $existing = $this->db->get('personnelattendance')->row();

    if ($existing) {
        $this->db->where('attendanceID', $existing->attendanceID);
        return $this->db->update('personnelattendance', $data);
    } else {
        return $this->db->insert('personnelattendance', $data);
    }
}

}
