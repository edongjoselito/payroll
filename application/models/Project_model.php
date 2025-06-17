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
    $result[$row->personnelID] = $row; // store full object with status AND workDuration
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


public function getAttendanceLogs($settingsID, $projectID)
{
    $this->db->select('pa.*, p.first_name, p.last_name');
    $this->db->from('personnelattendance AS pa');
    $this->db->join('personnel AS p', 'pa.personnelID = p.personnelID');
    $this->db->where('pa.settingsID', $settingsID);
    $this->db->where('pa.projectID', $projectID);
    $this->db->order_by('pa.attendance_date', 'DESC');
    return $this->db->get()->result();
}


   public function get_all_rates()
    {
        return $this->db->get('rate')->result();
    }

    public function get_rate($rateID)
    {
        return $this->db->get_where('rate', ['rateID' => $rateID])->row();
    }

    public function insert_rate($data)
    {
        return $this->db->insert('rate', $data);
    }

    public function update_rate($rateID, $data)
    {
        $this->db->where('rateID', $rateID);
        return $this->db->update('rate', $data);
    }

    public function delete_rate($rateID)
    {
        return $this->db->delete('rate', ['rateID' => $rateID]);
    }



    public function getAttendanceByDateRange($settingsID, $projectID, $start, $end)
{
    $this->db->select('pa.*, p.first_name, p.last_name, p.rateType');
    $this->db->from('personnelattendance pa');
    $this->db->join('personnel p', 'pa.personnelID = p.personnelID');
    $this->db->where('pa.settingsID', $settingsID);
    $this->db->where('pa.projectID', $projectID);
    $this->db->where('pa.attendance_date >=', $start);
    $this->db->where('pa.attendance_date <=', $end);
    $this->db->order_by('pa.attendance_date', 'ASC');

    return $this->db->get()->result();
}

public function getPayrollData($settingsID, $projectID, $start, $end)
{
    $this->db->select('
        p.personnelID, 
        p.first_name, 
        p.last_name, 
        p.position, 
        p.rateType, 
        r.rateAmount
    ');
    $this->db->from('personnel p');
    $this->db->join('personnelattendance pa', 'pa.personnelID = p.personnelID', 'left');
    $this->db->join('rate r', 'r.rateType = p.rateType', 'left'); // Join rate table by rateType
    $this->db->where('pa.settingsID', $settingsID);
    $this->db->where('pa.projectID', $projectID);
    $this->db->where('pa.attendance_date >=', $start);
    $this->db->where('pa.attendance_date <=', $end);
    $this->db->group_by('p.personnelID');
    $this->db->order_by('p.last_name', 'ASC');

    return $this->db->get()->result();
}


}
