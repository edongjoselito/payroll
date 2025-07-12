<?php
class Project extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('Project_model');
    }

public function project_view() {
    $settingsID = $this->session->userdata('settingsID');
    $data['projects'] = $this->Project_model->getAll($settingsID);
    $this->load->view('project_view', $data);
}


public function store()
{
    $data = $this->input->post();
    $data['settingsID'] = $this->session->userdata('settingsID');

    if ($this->Project_model->insert($data)) {
        $this->session->set_flashdata('success', 'Project successfully added.');
    } else {
        $this->session->set_flashdata('error', 'Failed to add project.');
    }

    redirect('project/project_view');
}

public function update()
{
    $data = $this->input->post();
    $data['settingsID'] = $this->session->userdata('settingsID');

    if ($this->Project_model->update($data)) {
        $this->session->set_flashdata('success', 'Project successfully updated.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update project.');
    }

    redirect('project/project_view');
}

public function delete($id)
{
    if ($this->Project_model->delete($id)) {
        $this->session->set_flashdata('success', 'Project successfully deleted.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete project.');
    }

    redirect('project/project_view');
}


public function attendance_list($settingsID)
{
    date_default_timezone_set('Asia/Manila');
    $projectID = $this->input->get('pid');

    $data['settingsID'] = $settingsID;
    $data['projectID'] = $projectID;
    $data['project'] = $this->Project_model->getProjectDetails($settingsID, $projectID);

    // $data['project'] = $this->Project_model->getProjectBySettingsID($settingsID);
    $data['attendance_logs'] = $this->Project_model->getAttendanceLogs($settingsID, $projectID);

    $this->load->view('attendance_list_view', $data);
}



public function attendance($settingsID)
{
        date_default_timezone_set('Asia/Manila'); // Add this line

    $projectID = $this->input->get('pid');

    $data['settingsID'] = $settingsID;
    $data['projectID'] = $projectID;
    $data['attendance_date'] = date('Y-m-d');
    $data['personnels'] = $this->Project_model->getAssignedPersonnel($settingsID, $projectID);
    $data['project'] = $this->Project_model->getProjectBySettingsID($settingsID);
    $data['attendance_records'] = $this->Project_model->getAttendanceBySettingsID($settingsID, $projectID, date('Y-m-d'));

    $this->load->view('attendance_view', $data);
}


public function save_attendance()
{
    $settingsID         = $this->input->post('settingsID');
    $projectID          = $this->input->post('projectID');
    $attendance_date    = $this->input->post('attendance_date');
    $attendance_status  = $this->input->post('attendance_status');
    $work_duration = $this->input->post('work_duration');


    $batchData = [];

  foreach ($attendance_status as $personnelID => $status) {
    $duration = isset($work_duration[$personnelID]) ? $work_duration[$personnelID] : null;

    $batchData[] = [
        'personnelID'       => $personnelID,
        'settingsID'        => $settingsID,
        'projectID'         => $projectID,
        'attendance_date'   => $attendance_date,
        'attendance_status' => $status,
        'workDuration'      => $duration
    ];
}


    $this->Project_model->save_batch_attendance($attendance_date, $batchData);

    $this->session->set_flashdata('success', 'Attendance saved successfully.');
    redirect('project/attendance/' . $settingsID . '?pid=' . $projectID);
}




public function weekly_attendance_report()
{
    $settingsID = $this->input->post('settingsID');
    $projectID = $this->input->post('projectID');
    $start = $this->input->post('start_date');
    $end = $this->input->post('end_date');

    // generate date range
    $dates = [];
    $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($end))->modify('+1 day'));
    foreach ($period as $date) {
        $dates[] = $date->format('Y-m-d');
    }

    $this->load->model('Project_model');
    $personnels = $this->Project_model->getAssignedPersonnel($settingsID, $projectID);

    $data = compact('settingsID', 'projectID', 'start', 'end', 'dates', 'personnels');
    $this->load->view('attendance_weekly_report_view', $data);
}
public function save_weekly_attendance()
{
    $settingsID = $this->input->post('settingsID');
    $projectID  = $this->input->post('projectID');
    $start      = $this->input->post('start');
    $end        = $this->input->post('end');
    $attendData = $this->input->post('attendance');
    $workHours  = $this->input->post('work_duration');

    $this->load->model('Project_model');

    $batchData = [];

    foreach ($attendData as $personnelID => $dates) {
        foreach ($this->generateDateRange($start, $end) as $date) {
            $status = isset($dates[$date]) ? 'Present' : 'Absent';

            $batchData[] = [
                'settingsID'        => $settingsID,
                'projectID'         => $projectID,
                'personnelID'       => $personnelID,
                'attendance_date'   => $date,
                'attendance_status' => $status
            ];
        }
    }

    // Save individual attendance rows
    $this->Project_model->save_batch_attendance_range($batchData);

    // Save weekly total duration
    foreach ($workHours as $personnelID => $duration) {
        $this->Project_model->save_or_update_weekly_total_duration($settingsID, $projectID, $personnelID, $start, $end, $duration);
    }

    $this->session->set_flashdata('success', 'Weekly attendance saved successfully.');
  redirect("project/weekly_attendance_report_summary?settingsID={$settingsID}&projectID={$projectID}&start={$start}&end={$end}");

}

private function generateDateRange($start, $end) {
    $dates = [];
    $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($end))->modify('+1 day'));
    foreach ($period as $date) {
        $dates[] = $date->format('Y-m-d');
    }
    return $dates;
}
public function weekly_attendance_report_summary()
{
    $settingsID = $this->input->get('settingsID');
    $projectID  = $this->input->get('projectID');
    $start      = $this->input->get('start');
    $end        = $this->input->get('end');

    $this->load->model('Project_model');

    $personnels = $this->Project_model->getAssignedPersonnel($settingsID, $projectID);

    $dates = [];
    $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($end))->modify('+1 day'));
    foreach ($period as $date) {
        $dates[] = $date->format('Y-m-d');
    }

    // Fetch previously saved data
    $attendance = $this->Project_model->getAttendanceByRange($settingsID, $projectID, $start, $end);
    $durations = $this->Project_model->getWeeklyDurations($settingsID, $projectID, $start, $end);

    $data = compact('settingsID', 'projectID', 'start', 'end', 'dates', 'personnels', 'attendance', 'durations');
    $this->load->view('attendance_weekly_report_summary_view', $data);
}



public function assign_personnel($settingsID, $projectID)
{
    $this->load->model('Project_model');

    $data['settingsID'] = $settingsID;
    $data['projectID'] = $projectID;
    
    // ✅ Fix: Pass both parameters
    $data['personnel'] = $this->Project_model->get_all_personnel($settingsID, $projectID);
    $data['assignments'] = $this->Project_model->get_assignments_by_project($projectID);
$project = $this->Project_model->getProject($settingsID, $projectID);
$data['project'] = is_array($project) ? $project[0] : $project;


    $this->load->view('assign_personnel', $data);
}


public function save_assignment()
{
    $this->load->model('Project_model');

    $settingsID = $this->input->post('settingsID');
    $projectID = $this->input->post('projectID');
    $personnelID = $this->input->post('personnelID');

    // Check if already assigned
    $exists = $this->Project_model->check_assignment_exists($settingsID, $projectID, $personnelID);

    if ($exists) {
        $this->session->set_flashdata('error', 'This personnel is already assigned to this project.');
    } else {
        $data = [
            'settingsID' => $settingsID,
            'projectID' => $projectID,
            'personnelID' => $personnelID
        ];
        $this->Project_model->assign_personnel($data);
        $this->session->set_flashdata('success', 'Personnel assigned successfully.');
    }

    redirect('project/assign_personnel/' . $settingsID . '/' . $projectID);
}

public function delete_assignment($ppID, $settingsID, $projectID)
{
    $this->load->model('Project_model');
    $this->Project_model->delete_assignment($ppID);
    $this->session->set_flashdata('success', 'Assignment deleted.');
    redirect("project/assign_personnel/$settingsID/$projectID");
}


public function attendance_range($settingsID)
{
    $projectID = $this->input->get('pid');
    $start = $this->input->get('start');
    $end = $this->input->get('end');

    $this->load->model('Project_model');

    $data['settingsID'] = $settingsID;
    $data['projectID'] = $projectID;
    $data['project'] = $this->Project_model->getProjectBySettingsID($settingsID);
    $data['start'] = $start;
    $data['end'] = $end;

    if ($start && $end) {
        $data['attendance_logs'] = $this->Project_model->getAttendanceByDateRange($settingsID, $projectID, $start, $end);
    } else {
        $data['attendance_logs'] = [];
    }

    $this->load->view('attendance_range_view', $data);
}

public function payroll_report($settingsID = null)
{
    $settingsID = $settingsID ?? $this->session->userdata('settingsID');
    $projectID  = $this->input->get('pid');
    $start      = $this->input->get('start');
    $end        = $this->input->get('end');
    $rateType   = $this->input->get('rateType');

    $this->load->model('Project_model');
    $this->load->model('SettingsModel');
    $this->load->model('OtherDeduction_model');
    $this->load->model('WeeklyAttendance_model');

    if (empty($start) || empty($end)) {
        $this->session->set_flashdata('error', 'Start and end dates are required.');
        redirect('project/project_view');
        return;
    }

    $data['settingsID'] = $settingsID;
    $data['projectID']  = $projectID;
    $data['start']      = $start;
    $data['end']        = $end;
    $data['rateType']   = $rateType;

    $data['signatories'] = $this->SettingsModel->get_signatories($settingsID);
    $data['project'] = $this->Project_model->getProjectDetails($settingsID, $projectID);
    $payroll = $this->Project_model->getPayrollData($settingsID, $projectID, $start, $end, $rateType);

    // Group deductions
    $deductions = $this->OtherDeduction_model->get_deductions_by_date_range($start, $end, $settingsID);
    $groupedDeductions = [];
    foreach ($deductions as $deduction) {
        $pid = trim($deduction->personnelID);
        if (!isset($groupedDeductions[$pid])) {
            $groupedDeductions[$pid] = 0;
        }
        $groupedDeductions[$pid] += $deduction->amount;
    }

    // Daily logs
    $this->db->select('personnelID, date, status, work_duration');
    $this->db->from('attendance');
    $this->db->where('projectID', $projectID);
    $this->db->where('date >=', $start);
    $this->db->where('date <=', $end);
    $this->db->where('settingsID', $settingsID);
    $query = $this->db->get();
    $daily_logs = $query->result();

    $logs = [];
    $dateList = [];
    foreach ($daily_logs as $log) {
        $date = date('Y-m-d', strtotime($log->date));
        $logs[$log->personnelID][$date] = [
            'status' => $log->status,
            'hours'  => floatval($log->work_duration ?? 0)
        ];
        $dateList[$date] = true;
    }
    ksort($dateList);
    $data['dates'] = array_keys($dateList);
    $data['logs'] = $logs;

    foreach ($payroll as &$row) {
        $pid = trim($row->personnelID);
        $row->ca_cashadvance = $row->ca_cashadvance ?? 0;
        $row->other_deduction = $groupedDeductions[$pid] ?? 0;
        $row->total_hours = $this->WeeklyAttendance_model->get_total_work_hours($pid, $projectID, $start, $end);

        // Convert time for display
        $decimal = floatval($row->total_hours);
        $hours = floor($decimal);
        $minutes = round(($decimal - $hours) * 100);
        if ($minutes >= 60) {
            $hours += floor($minutes / 60);
            $minutes = $minutes % 60;
        }
        $decimal_time = $hours + ($minutes / 100);
        $row->total_hours_display = number_format($decimal_time, 2, '.', '');

        // Daily logs
        $row->reg_hours_per_day = [];
        $row->present_days = 0;
        $row->total_reg_hours = 0;
        foreach ($data['dates'] as $date) {
            $day_log = $logs[$pid][$date] ?? null;
            if ($day_log && $day_log['status'] === 'Present') {
                $hours = $day_log['hours'];
                $row->reg_hours_per_day[$date] = $hours;
                $row->total_reg_hours += $hours;
                $row->present_days++;
            } else {
                $row->reg_hours_per_day[$date] = '-';
            }
        }
    }

    $data['attendance_data'] = $payroll;
    $data['personnel_loans'] = $this->Project_model->getPersonnelLoans($settingsID, $projectID);
    $data['show_signatories'] = true;

    $this->load->view('payroll_report_view', $data);
}




public function payroll_summary($settingsID, $projectID)
{
    $start = $this->input->get('start');
    $end   = $this->input->get('end');
    $rateType = $this->input->get('rateType'); // Optional

    // Fallback to current month
    $defaultStart = date('Y-m-01');
    $defaultEnd   = date('Y-m-t');
    $start = $start ?? $defaultStart;
    $end   = $end ?? $defaultEnd;

    $this->load->model('Project_model');
    $this->load->model('SettingsModel');
    $this->load->model('OtherDeduction_model');
    $this->load->model('Report_model'); // Needed for logging

    $data['start'] = $start;
    $data['end'] = $end;
    $data['rateType'] = $rateType;

    $data['project'] = $this->Project_model->getProject($settingsID, $projectID);
    $payroll = $this->Project_model->getPayrollData($settingsID, $projectID, $start, $end, $rateType);

    // Group other deductions by personnel
    $deductions = $this->OtherDeduction_model->get_deductions_by_date_range($start, $end, $settingsID);
    $groupedDeductions = [];

    foreach ($deductions as $deduction) {
        $pid = trim($deduction->personnelID);
        if (!isset($groupedDeductions[$pid])) {
            $groupedDeductions[$pid] = 0;
        }
        $groupedDeductions[$pid] += $deduction->amount;
    }

    $totalNetPay = 0;

    foreach ($payroll as &$row) {
        $pid = trim($row->personnelID);
        $row->other_deduction = $groupedDeductions[$pid] ?? 0;
        $totalNetPay += $row->netpay ?? 0; // Using netpay instead of gross
    }

    // ✅ Save to payroll_logs table
$log_data = [
    'projectID'      => $projectID,
    'project_title'  => $data['project']->project_title ?? '',
    'location'       => $data['project']->location ?? '',
    'period'         => date('F Y', strtotime($start)),
    'date_from'      => $start,
    'date_to'        => $end,
    'payroll_date'   => date('Y-m-d'),
    'total_gross'    => $totalNetPay,
    'date_saved'     => date('Y-m-d H:i:s')
];



    $this->Report_model->insert_payroll_log($log_data);

    $data['attendance_data'] = $payroll;
    $data['signatories'] = $this->SettingsModel->get_signatories($settingsID);
    $data['show_signatories'] = true;
    $data['is_summary'] = true;

    $this->load->view('payroll_report_view', $data);
}





// -------END----------------





}
