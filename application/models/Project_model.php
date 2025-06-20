<?php
class Project_model extends CI_Model
{
    private $table = 'project';

public function getAll($settingsID) {
    $this->db->where('settingsID', $settingsID);
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

public function getProject($settingsID, $projectID) {
    return $this->db
        ->where('settingsID', $settingsID)
        ->where('projectID', $projectID)
        ->get('project')
        ->row(); // Use row() since you expect one record
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



public function getProjectDetails($settingsID, $projectID)
{
    return $this->db
        ->where('settingsID', $settingsID)
        ->where('projectID', $projectID)
        ->get('project')
        ->row();  // single result
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


public function get_all_personnel($settingsID, $projectID)
{
    // Get personnel already assigned to this specific project and settings
    $subQuery = $this->db->select('personnelID')
                         ->from('project_personnel_assignment')
                         ->where('settingsID', $settingsID)
                         ->where('projectID', $projectID)
                         ->get_compiled_select();

    // Main query: get all personnel not in the subquery
    $this->db->where('settingsID', $settingsID);
    $this->db->where("personnelID NOT IN ($subQuery)", null, false);

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



public function getPayrollData($settingsID, $projectID, $start, $end, $rateType = null)
{
    $this->db->select('p.personnelID, p.first_name, p.last_name, p.position, p.rateType, p.rateAmount');
    $this->db->from('project_personnel_assignment a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.settingsID', $settingsID);
    $this->db->where('a.projectID', $projectID);

    if (!empty($rateType)) {
        $this->db->where('p.rateType', $rateType);
    }

    $this->db->order_by('p.last_name', 'ASC');
    $assignedPersonnel = $this->db->get()->result();

    // Step 2: Get all attendance logs within date range
    $this->db->select('personnelID, attendance_date, attendance_status, workDuration');
    $this->db->from('personnelattendance');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('projectID', $projectID);
    $this->db->where('attendance_date >=', $start);
    $this->db->where('attendance_date <=', $end);
    $logs = $this->db->get()->result();

    // Step 3: Group attendance by personnelID and date
    $logMap = [];
    foreach ($logs as $log) {
        $logMap[$log->personnelID][$log->attendance_date] = $log;
    }

    // Step 4: Attach attendance to each assigned personnel
    foreach ($assignedPersonnel as &$p) {
        $p->logs = $logMap[$p->personnelID] ?? [];
    }

    return $assignedPersonnel;
}


}
