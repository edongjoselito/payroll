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


public function getAttendanceBySettingsID($settingsID, $projectID, $date)
{
    $this->db->where('settingsID', $settingsID);
    $this->db->where('projectID', $projectID);
    $this->db->where('attendance_date', $date);
    $query = $this->db->get('personnelattendance');
    
    // Index by personnelID for fast lookup
    $result = [];
    foreach ($query->result() as $row) {
        $result[$row->personnelID] = $row->attendance_status;
    }

    return $result;
}





public function save_batch_attendance($date, $data)
{
    foreach ($data as $record) {
        $this->db->where('personnelID', $record['personnelID']);
        $this->db->where('settingsID', $record['settingsID']);
        $this->db->where('projectID', $record['projectID']);
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

public function getAssignedPersonnel($settingsID, $projectID)
{
    $this->db->select('p.personnelID, p.first_name, p.middle_name, p.last_name');
    $this->db->from('project_personnel_assignment AS ppa');
    $this->db->join('personnel AS p', 'ppa.personnelID = p.personnelID');
    $this->db->where('ppa.settingsID', $settingsID);
    $this->db->where('ppa.projectID', $projectID);
    return $this->db->get()->result();
}


   public function get_all_personnel()
    {
        return $this->db->get('personnel')->result();
    }

public function get_assignments_by_project($projectID)
{
    $this->db->select('ppa.ppID, ppa.projectID, ppa.settingsID, ppa.personnelID, 
                       p.first_name, p.middle_name, p.last_name');
    $this->db->from('project_personnel_assignment AS ppa');
    $this->db->join('personnel AS p', 'ppa.personnelID = p.personnelID');
    $this->db->where('ppa.projectID', $projectID);
    return $this->db->get()->result();
}



    public function assign_personnel($data)
    {
        $this->db->insert('project_personnel_assignment', $data);
    }

    public function delete_assignment($ppID)
    {
        $this->db->delete('project_personnel_assignment', ['ppID' => $ppID]);
    }

    
public function check_assignment_exists($settingsID, $projectID, $personnelID)
{
    $this->db->where('settingsID', $settingsID);
    $this->db->where('projectID', $projectID);
    $this->db->where('personnelID', $personnelID);
    $query = $this->db->get('project_personnel_assignment');
    return $query->num_rows() > 0;
}


}
