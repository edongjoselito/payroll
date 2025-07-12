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
    $this->db->select('a.*, p.first_name, p.last_name');
    $this->db->from('attendance AS a');
    $this->db->join('personnel AS p', 'a.personnelID = p.personnelID', 'left');
    $this->db->where('a.settingsID', $settingsID);
    $this->db->where('a.projectID', $projectID);
    $this->db->order_by('a.date', 'DESC');
    
    return $this->db->get()->result();
}
public function getAttendanceLogsByDate($settingsID, $projectID, $date = null)
{
    $this->db->select('a.*, p.first_name, p.last_name');
    $this->db->from('attendance a');
    $this->db->join('personnel p', 'a.personnelID = p.personnelID', 'left');
    $this->db->where('a.settingsID', $settingsID);
    $this->db->where('a.projectID', $projectID);
    if ($date) {
        $this->db->where('a.date', $date);
    }
    $this->db->order_by('p.first_name', 'ASC');
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



// public function getPayrollData($settingsID, $projectID, $start, $end, $rateType = null)
// {
//     // Step 1: Get assigned personnel with static deductions
//     $this->db->select('p.personnelID, p.first_name, p.last_name, p.position, p.rateType, p.rateAmount,
//                       p.sss_deduct, p.pagibig_deduct, p.philhealth_deduct');
//     $this->db->from('project_personnel_assignment a');
//     $this->db->join('personnel p', 'p.personnelID = a.personnelID');
//     $this->db->where('a.settingsID', $settingsID);
//     $this->db->where('a.projectID', $projectID);

//     if (!empty($rateType)) {
//         $this->db->where('p.rateType', $rateType);
//     }

//     $this->db->order_by('p.last_name', 'ASC');
//     $assignedPersonnel = $this->db->get()->result();

//     // Step 2: Get attendance logs
//     $this->db->select('personnelID, attendance_date, attendance_status, workDuration');
//     $this->db->from('personnelattendance');
//     $this->db->where('settingsID', $settingsID);
//     $this->db->where('projectID', $projectID);
//     $this->db->where('attendance_date >=', $start);
//     $this->db->where('attendance_date <=', $end);
//     $logs = $this->db->get()->result();

//     $logMap = [];
//     foreach ($logs as $log) {
//         $logMap[$log->personnelID][$log->attendance_date] = $log;
//     }

//     // Step 3: Get Cash Advance from `cashadvance` table
//     $this->db->select('personnelID, SUM(amount) as total_ca');
//     $this->db->from('cashadvance');
//     $this->db->where('settingsID', $settingsID);
//     $this->db->where('date >=', $start);
//     $this->db->where('date <=', $end);
//     $this->db->group_by('personnelID');
//     $cashAdvances = $this->db->get()->result();

//     $caMap = [];
//     foreach ($cashAdvances as $ca) {
//         $caMap[$ca->personnelID] = $ca->total_ca;
//     }

//     // Step 4: Merge attendance + deduction info
//     foreach ($assignedPersonnel as &$p) {
//         $p->logs = $logMap[$p->personnelID] ?? [];

//         $p->cash_advance = $caMap[$p->personnelID] ?? 0;
//         $p->sss = $p->sss_deduct ?? 0;
//         $p->pagibig = $p->pagibig_deduct ?? 0;
//         $p->philhealth = $p->philhealth_deduct ?? 0;
//     }

//     return $assignedPersonnel;
// }
public function getPayrollData($settingsID, $projectID, $start, $end, $rateType = null)
{
    // Step 1: Get assigned personnel with static deductions
    $this->db->select('p.personnelID, p.first_name, p.last_name, p.position, p.rateType, p.rateAmount,
                      p.sss_deduct, p.pagibig_deduct, p.philhealth_deduct');
    $this->db->from('project_personnel_assignment a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.settingsID', $settingsID);
    $this->db->where('a.projectID', $projectID);

    // if (!empty($rateType)) {
    //     $this->db->where('p.rateType', $rateType);
    // }

    $this->db->order_by('p.last_name', 'ASC');
    $assignedPersonnel = $this->db->get()->result();

    // Step 2: Get attendance logs
  $this->db->select('a.personnelID, a.date AS attendance_date, a.status AS attendance_status, 
                  COALESCE(w.total_hours, 0) AS workDuration');
$this->db->from('attendance a');
$this->db->join('work_hours w', 
    'a.personnelID = w.personnelID 
     AND a.projectID = w.projectID 
     AND a.date BETWEEN w.from AND w.to', 
     'left');
$this->db->where('a.settingsID', $settingsID);
$this->db->where('a.projectID', $projectID);
$this->db->where('a.date >=', $start);
$this->db->where('a.date <=', $end);

    $logs = $this->db->get()->result();

    $logMap = [];
    foreach ($logs as $log) {
        $logMap[$log->personnelID][$log->attendance_date] = $log;
    }

    // Step 3-A: Cash Advance only (description contains 'Cash Advance')
    $this->db->select('personnelID, SUM(amount) as total_ca');
    $this->db->from('cashadvance');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('date >=', $start);
    $this->db->where('date <=', $end);
    $this->db->where("LOWER(description) LIKE '%cash advance%'");

    $this->db->group_by('personnelID');
    $cashAdvances = $this->db->get()->result();

    $caMap = [];
    foreach ($cashAdvances as $ca) {
       $caMap[trim($ca->personnelID)] = $ca->total_ca;

    }

    // Step 3-B: Other Deductions (excluding 'Cash Advance')
    $this->db->select('personnelID, SUM(amount) as total_other');
    $this->db->from('cashadvance');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('date >=', $start);
    $this->db->where('date <=', $end);
    $this->db->not_like('description', 'Cash Advance');
    $this->db->group_by('personnelID');
    $otherDeductions = $this->db->get()->result();

    $otherMap = [];
    foreach ($otherDeductions as $other) {
        $otherMap[$other->personnelID] = $other->total_other;
    }

    // Step 4: Get loan deductions (monthly)
    $this->db->select('personnelID, SUM(monthly_deduction) as total_loan');
    $this->db->from('personnelloans');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('status', 1); // only active
    $this->db->group_by('personnelID');
    $loanRows = $this->db->get()->result();

    $loanMap = [];
    foreach ($loanRows as $loan) {
        $loanMap[$loan->personnelID] = $loan->total_loan;
    }

    // Step 5: Merge all data to personnel
    foreach ($assignedPersonnel as &$p) {
        $p->logs = $logMap[$p->personnelID] ?? [];
       $pid = trim($p->personnelID);
$p->ca_cashadvance = $caMap[$pid] ?? 0;

        $p->other_deduction = $otherMap[$p->personnelID] ?? 0;
        $p->loan = $loanMap[$p->personnelID] ?? 0;
        $p->sss = $p->sss_deduct ?? 0;
        $p->pagibig = $p->pagibig_deduct ?? 0;
        $p->philhealth = $p->philhealth_deduct ?? 0;
    }

    return $assignedPersonnel;
}


// Pa display sa personnel loan in payroll_report
public function getPersonnelLoans($settingsID)
{
    $this->db->select('pl.personnelID, SUM(pl.amount) AS loan_deduction');
    $this->db->from('personnelloans pl');
    $this->db->join('personnel p', 'pl.personnelID = p.personnelID');
    $this->db->where('p.settingsID', $settingsID);
    $this->db->group_by('pl.personnelID');
    
    $query = $this->db->get();
    $result = [];
    foreach ($query->result() as $row) {
        $result[$row->personnelID] = $row->loan_deduction;
    }
    return $result;
}

public function getProjectBySettingsID($settingsID) {
    return $this->db
        ->where('settingsID', $settingsID)
        ->get('project')
        ->result();
}


// ---------------------------------------------

public function save_batch_attendance_range($data)
{
    if (!empty($data)) {
        // Optional: delete existing records within that range to prevent duplicates
        foreach ($data as $row) {
            $this->db->where([
                'personnelID'     => $row['personnelID'],
                'projectID'       => $row['projectID'],
                'settingsID'      => $row['settingsID'],
                'attendance_date' => $row['attendance_date']
            ]);
            $this->db->delete('personnelattendance');
        }

        // Now insert new batch
        $this->db->insert_batch('personnelattendance', $data);
    }
}
public function save_or_update_weekly_total_duration($settingsID, $projectID, $personnelID, $start, $end, $duration)
{
    // This is only useful if you're tracking summary per week in another table
    // Otherwise, just keep it in the attendance form and don't store it

    $data = [
        'settingsID' => $settingsID,
        'projectID' => $projectID,
        'personnelID' => $personnelID,
        'week_start' => $start,
        'week_end' => $end,
        'totalDuration' => $duration
    ];

    // example table: weekly_attendance_summary
    $this->db->where([
        'settingsID' => $settingsID,
        'projectID' => $projectID,
        'personnelID' => $personnelID,
        'week_start' => $start,
        'week_end' => $end
    ]);

    $exists = $this->db->get('weekly_attendance_summary')->row();

    if ($exists) {
        $this->db->where('id', $exists->id);
        $this->db->update('weekly_attendance_summary', $data);
    } else {
        $this->db->insert('weekly_attendance_summary', $data);
    }
}
public function getAttendanceByRange($settingsID, $projectID, $start, $end)
{
    $this->db->where('settingsID', $settingsID);
    $this->db->where('projectID', $projectID);
    $this->db->where('attendance_date >=', $start);
    $this->db->where('attendance_date <=', $end);
    $query = $this->db->get('personnelattendance');

    $result = [];
    foreach ($query->result() as $row) {
        $key = $row->personnelID . '_' . $row->attendance_date;
        $result[$key] = $row;
    }
    return $result;
}
public function getWeeklyDurations($settingsID, $projectID, $start, $end)
{
    $this->db->where('settingsID', $settingsID);
    $this->db->where('projectID', $projectID);
    $this->db->where('week_start', $start);
    $this->db->where('week_end', $end);
    $query = $this->db->get('weekly_attendance_summary');

    $result = [];
    foreach ($query->result() as $row) {
        $result[$row->personnelID] = $row->totalDuration;
    }
    return $result;
}
public function getPersonnelRates($settingsID, $projectID)
{
    $this->db->select('p.personnelID, p.rateType, p.rateAmount');
    $this->db->from('personnel p');
    $this->db->where('p.settingsID', $settingsID);
    return $this->db->get()->result();
}
public function getUniquePersonnel($settingsID)
{
    $this->db->distinct();
    $this->db->select('personnelID, first_name, last_name');
    $this->db->from('personnel');
    $this->db->where('settingsID', $settingsID);
    return $this->db->get()->result();
}
public function getPersonnelWithAttendance($settingsID, $projectID, $start, $end)
{
   $this->db->distinct(); 
$this->db->select('p.personnelID, p.first_name, p.last_name');

    $this->db->from('personnel p');
    $this->db->join('advisory_attendance a', 'a.personnelID = p.personnelID');
    $this->db->where('p.settingsID', $settingsID);
    $this->db->where('a.projectID', $projectID);
    $this->db->where('a.attendance_date >=', $start);
    $this->db->where('a.attendance_date <=', $end);
    return $this->db->get()->result();
}


// ----------------------------------NEW FOR GENERATE PAYROLL SIDEBAR-----------


public function get_all_projects()
{
    return $this->db->get('project')->result();
}
public function get_project_by_id($projectID)
{
    return $this->db->get_where('project', ['projectID' => $projectID])->row();
}
public function get_project_details($projectID)
{
    return $this->db->get_where('project', ['projectID' => $projectID])->row();
}

public function get_attendance_batches($settingsID)
{
    $this->db->select('projectID, MIN(date) as start, MAX(date) as end');
    $this->db->from('attendance');
    $this->db->where('settingsID', $settingsID);
    $this->db->group_by('projectID');
    $this->db->order_by('start', 'desc');
    $batches = $this->db->get()->result();

    foreach ($batches as &$batch) {
        $project = $this->db->get_where('project', ['projectID' => $batch->projectID])->row();
        $batch->projectTitle = $project->projectTitle ?? 'Untitled Project';
    }

    return $batches;
}



}
