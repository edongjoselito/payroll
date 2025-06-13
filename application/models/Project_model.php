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

    public function getProjectBySettingsID($settingsID) {
    return $this->db->where('settingsID', $settingsID)->get('project')->result();
}
public function getAttendanceBySettingsID($settingsID, $date) {
    $this->db->where('settingsID', $settingsID);
    $this->db->where('attendance_date', $date);
    return $this->db->get('personnelattendance')->result();
}




public function save_batch_attendance($date, $data) {
    foreach ($data as $record) {
        // Check if already exists for this personnel + date
        $this->db->where('personnelID', $record['personnelID']);
        $this->db->where('attendance_date', $date);
        $existing = $this->db->get('personnelattendance')->row();

        if ($existing) {
            $this->db->where('attendanceID', $existing->attendanceID);
            $this->db->update('personnelattendance', $record);
        } else {
            $this->db->insert('personnelattendance', $record);
        }
    }
}


}
