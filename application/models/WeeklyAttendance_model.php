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





public function saveAttendance($data)
{
    $settingsID = $this->session->userdata('settingsID');
    $projectID = $data['projectID'];
    $from = $data['from'];
    $to = $data['to'];
    $dates = $data['dates'] ?? [];

    $group_number = date('Ymd', strtotime($from)) . '-' . date('Ymd', strtotime($to));

    $statuses = $data['attendance_status'] ?? [];
    $regularHours = $data['regular_hours'] ?? [];
    $overtimeHours = $data['overtime_hours'] ?? [];

    // -------- Save individual attendance entries --------
    foreach ($statuses as $personnelID => $statusDates) {
        foreach ($statusDates as $date => $status) {
            $reg = $regularHours[$personnelID][$date] ?? 0;
            $ot = $overtimeHours[$personnelID][$date] ?? 0;

            $log = [
                'personnelID'     => $personnelID,
                'projectID'       => $projectID,
                'settingsID'      => $settingsID,
                'date'            => $date,
                'status'          => $status,
                'work_duration'   => is_numeric($reg) ? $reg : 0,
                'overtime_hours'  => is_numeric($ot) ? $ot : 0,
                'holiday_hours'   => 0,
                'group_number'    => $group_number,
            ];

            // Check if existing attendance entry
            $this->db->where([
                'personnelID' => $personnelID,
                'projectID'   => $projectID,
                'settingsID'  => $settingsID,
                'date'        => $date
            ]);
            $existing = $this->db->get('attendance')->row();

            if ($existing) {
                $this->db->where('id', $existing->id)->update('attendance', $log);
            } else {
                $this->db->insert('attendance', $log);
            }
        }
    }

    // -------- Summarize weekly total hours and save to work_hours --------
    $totals = [];

    foreach ($regularHours as $personnelID => $dayHours) {
        foreach ($dayHours as $date => $reg) {
            $reg = is_numeric($reg) ? $reg : 0;
            $ot  = isset($overtimeHours[$personnelID][$date]) ? $overtimeHours[$personnelID][$date] : 0;
            $ot  = is_numeric($ot) ? $ot : 0;

            if (!isset($totals[$personnelID])) {
                $totals[$personnelID] = 0;
            }

            $totals[$personnelID] += $reg + $ot;
        }
    }

    foreach ($totals as $personnelID => $totalHours) {
        $summary = [
            'personnelID' => $personnelID,
            'projectID'   => $projectID,
            'from'        => $from,
            'to'          => $to,
            'total_hours' => $totalHours,
            'settingsID'  => $settingsID
        ];

        $existingWH = $this->db->get_where('work_hours', [
            'personnelID' => $personnelID,
            'projectID'   => $projectID,
            'from'        => $from,
            'to'          => $to,
            'settingsID'  => $settingsID
        ])->row();

        if ($existingWH) {
            $this->db->where('id', $existingWH->id)->update('work_hours', $summary);
        } else {
            $this->db->insert('work_hours', $summary);
        }
    }

    return true;
}

private function getDateRange($from, $to)
{
    $start = new DateTime($from);
    $end = new DateTime($to);
    $dates = [];

    while ($start <= $end) {
        $dates[] = $start->format('Y-m-d');
        $start->modify('+1 day');
    }

    return $dates;
}

public function getProjectById($projectID)
{
    return $this->db
        ->where('projectID', $projectID)
        ->get('project')
        ->row();
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

public function getExistingAttendanceDates($projectID, $from, $to, $group_number)
{
    $this->db->select('date');
    $this->db->from('attendance');
    $this->db->where('projectID', $projectID);
    $this->db->where('group_number', $group_number); // ✅ filter by group_number
    $this->db->where('date >=', $from);
    $this->db->where('date <=', $to);
    $this->db->group_by('date');
    $this->db->order_by('date', 'ASC');
    $query = $this->db->get();
    return array_column($query->result_array(), 'date');
}



// DISPLAY SAVED ATTENDANCE

public function getAttendanceRecords($projectID, $from, $to, $dates, $group_number)
{
    if (empty($dates)) return [];

    // Step 1: Fetch all attendance rows for given project, group, and dates
    $this->db->select('*');
    $this->db->from('attendance');
    $this->db->where('projectID', $projectID);
    $this->db->where('group_number', $group_number);
    $this->db->where_in('date', $dates);
    $query = $this->db->get();

    $results = $query->result();
    $data = [];

    if (empty($results)) return [];

    // Step 2: Collect all unique personnel IDs to fetch names in bulk
    $personnelIDs = array_unique(array_column($results, 'personnelID'));

    // Step 3: Fetch names for all personnel in one query
    $this->db->select('personnelID, first_name, last_name');
    $this->db->from('personnel');
    $this->db->where_in('personnelID', $personnelIDs);
    $personnelQuery = $this->db->get();
    $personnelMap = [];

    foreach ($personnelQuery->result() as $p) {
        $personnelMap[$p->personnelID] = $p->last_name . ', ' . $p->first_name;
    }

    // Step 4: Map attendance records into structured array
    foreach ($results as $row) {
        $pid = $row->personnelID;

        if (!isset($data[$pid]['name'])) {
            $data[$pid]['name'] = $personnelMap[$pid] ?? 'Unknown';
        }

        $data[$pid]['dates'][$row->date] = $row->status;
        $data[$pid]['hours'][$row->date] = $row->work_duration;
      $data[$pid]['holiday'][$row->date] = $row->holiday_hours ?? 0;
$data[$pid]['overtime'][$row->date] = $row->overtime_hours ?? 0;
       

    }

    return $data;
}




public function getWorkHours($projectID, $from, $to) {
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.personnelID, 
                       SUM(a.work_duration) as regular_hours, 
                       SUM(a.overtime_hours) as overtime_hours, 
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
            'overtime' => $row->overtime_hours,
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
    return $this->db->select('projectID, MIN(date) as start, MAX(date) as end, group_number')
        ->from('attendance')
        ->where('settingsID', $settingsID)
        ->group_by(['projectID', 'group_number'])
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
public function getAttendancePeriods()
{
    $this->db->select('projectID, MIN(date) as start, MAX(date) as end, group_number');
    $this->db->from('attendance');
    $this->db->group_by(['projectID', 'group_number']);
    $this->db->order_by('start', 'DESC');
    return $this->db->get()->result();
}

}