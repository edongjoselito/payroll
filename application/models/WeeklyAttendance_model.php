<?php
class WeeklyAttendance_model extends CI_Model {

public function getProjects() {
    $settingsID = $this->session->userdata('settingsID');

    return $this->db
        ->where('settingsID', $settingsID)
        ->get('project')
        ->result();
}


    
    
public function getEmployeesByProject($projectID, $settingsID)
{
    return $this->db
        ->select('p.personnelID, p.first_name, p.last_name')
        ->from('project_personnel_assignment a')
        ->join('personnel p', 'p.personnelID = a.personnelID')
        ->where('a.projectID', $projectID)
        ->where('a.settingsID', $settingsID)
        ->order_by('p.last_name', 'ASC')
        ->get()
        ->result();
}





public function saveAttendance($data) {
    $dates = $data['dates'];
    $projectID = $data['projectID'];
    $from = $data['from'];
    $to = $data['to'];
    $settingsID = $data['settingsID'];

    $statusList = $data['attendance_status'];
    $hourList = $data['attendance_hours'];

    // Used for summarizing into work_hours table
    $weeklyTotal = [];

    foreach ($statusList as $personnelID => $statusDates) {
        foreach ($statusDates as $date => $status) {
            $hours = isset($hourList[$personnelID][$date]) ? floatval($hourList[$personnelID][$date]) : 0.0;

            $work_duration = 0.0;
            $holiday_hours = 0.0;

            if ($status == 'Regular Holiday') {
                if ($hours > 8) {
                    $work_duration = 8;
                    $holiday_hours = $hours - 8;
                } else {
                    $holiday_hours = $hours;
                }
            } else {
                $work_duration = $hours;
            }

            // Track weekly totals
            $weeklyTotal[$personnelID] = ($weeklyTotal[$personnelID] ?? 0) + $work_duration + $holiday_hours;

            // Save attendance per day
            $this->db->replace('attendance', [
                'personnelID'    => $personnelID,
                'projectID'      => $projectID,
                'date'           => $date,
                'status'         => $status,
                'work_duration'  => $work_duration,
                'holiday_hours'  => $holiday_hours,
                'settingsID'     => $settingsID
            ]);
        }
    }

    // Save summarized total to work_hours
    foreach ($weeklyTotal as $personnelID => $total) {
        $this->db->replace('work_hours', [
            'personnelID'   => $personnelID,
            'projectID'     => $projectID,
            'from'          => $from,
            'to'            => $to,
            'total_hours'   => $total,
            'settingsID'    => $settingsID
        ]);
    }
}



    public function getProjectById($id) {
    return $this->db->where('projectID', $id)->get('project')->row();
}

public function attendanceExists($projectID, $from, $to) {
    $settingsID = $this->session->userdata('settingsID');

    $this->db->where('projectID', $projectID);
    $this->db->where('settingsID', $settingsID);
    $this->db->where('date >=', $from);
    $this->db->where('date <=', $to);
    $query = $this->db->get('attendance');

    return $query->num_rows() > 0;
}

public function getExistingAttendanceDates($projectID, $from, $to) {
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('DISTINCT(date) as date');
    $this->db->from('attendance');
    $this->db->where('projectID', $projectID);
    $this->db->where('settingsID', $settingsID);
    $this->db->where('date >=', $from);
    $this->db->where('date <=', $to);
    $this->db->order_by('date', 'ASC');
    
    $query = $this->db->get();

    $dates = [];
    foreach ($query->result() as $row) {
        $dates[] = $row->date;
    }

    return $dates;
}



// DISPLAY SAVED ATTENDANCE

public function getAttendanceRecords($projectID, $from, $to) {
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.*, p.first_name, p.last_name');
    $this->db->from('attendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.projectID', $projectID);
    $this->db->where('a.date >=', $from);
    $this->db->where('a.date <=', $to);
       $this->db->where('p.settingsID', $this->session->userdata('settingsID'));


    $query = $this->db->get();
    $result = [];
  foreach ($query->result() as $row) {
    $result[$row->personnelID]['name'] = $row->last_name . ', ' . $row->first_name;
    $result[$row->personnelID]['dates'][$row->date] = $row->status;
   $result[$row->personnelID]['hours'][$row->date] = $row->work_duration;
$result[$row->personnelID]['holiday'][$row->date] = $row->holiday_hours;

}

    return $result;
}


public function getWorkHours($projectID, $from, $to) {
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.personnelID, 
                       SUM(a.work_duration) as regular_hours, 
                       SUM(a.holiday_hours) as holiday_hours');
    $this->db->from('attendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.projectID', $projectID);
    $this->db->where('a.date >=', $from);
    $this->db->where('a.date <=', $to);
    $this->db->where('p.settingsID', $settingsID);
    $this->db->group_by('a.personnelID');

    $query = $this->db->get();
    $hours = [];

    foreach ($query->result() as $row) {
        $hours[$row->personnelID] = [
            'regular' => $row->regular_hours,
            'holiday' => $row->holiday_hours
        ];
    }

    return $hours;
}


public function updateAttendanceRecord($personnelID, $date, $status, $hours, $holiday) {
    $this->db->where('personnelID', $personnelID);
    $this->db->where('date', $date);
    $this->db->update('work_hours', [
        'attendanceType' => $status,
        'workDuration' => $hours,
        'holidayDuration' => $holiday
    ]);
}





public function deleteAttendanceByDateRange($projectID, $from, $to)
{
    $this->db->where('projectID', $projectID);
    $this->db->where('date >=', $from);
    $this->db->where('date <=', $to);
    $this->db->delete('attendance');

    $this->db->where('projectID', $projectID);
    $this->db->where('`from` >=', $from);
    $this->db->where('`to` <=', $to);
    $this->db->delete('work_hours');
}


public function getSavedBatches($settingsID)
{
    return $this->db->select('projectID, MIN(date) as start, MAX(date) as end')
                    ->from('attendance')
                    ->where('settingsID', $settingsID)
                    ->group_by(['projectID', 'WEEK(date)'])
                    ->order_by('start', 'DESC')
                    ->get()
                    ->result();
}






//----------------------------------------- LINK TO PAYROLL-----------------------------

public function get_total_work_hours($personnelID, $projectID, $from, $to)
{
    $this->db->select_sum('total_hours');
    $this->db->from('work_hours');
    $this->db->where('personnelID', $personnelID);
    $this->db->where('projectID', $projectID);
    $this->db->where('from >=', $from);
    $this->db->where('to <=', $to);

    $query = $this->db->get()->row();

    return $query ? $query->total_hours : 0;
}


// ----------------------------------------END---------------------------------------------

}