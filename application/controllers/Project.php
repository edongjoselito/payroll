<?php
class Project extends CI_Controller
{
    public function __construct() {
        parent::__construct();
         $this->load->model('PayrollModel');
        $this->load->model('Project_model');
    }

public function project_view()
{
    $settingsID = $this->session->userdata('settingsID');

    $data['projects'] = $this->Project_model->getAll($settingsID);
    $data['attendance_periods'] = $this->Project_model->get_attendance_batches($settingsID); 

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
   $data['project'] = $this->Project_model->getProjectDetails($projectID);


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
    $settingsID        = $this->input->post('settingsID');
    $projectID         = $this->input->post('projectID');
    $attendance_date   = $this->input->post('attendance_date');
    $attendance_status = $this->input->post('attendance_status');
    $work_duration     = $this->input->post('work_duration');

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
            $batchData[] = [
                'settingsID'        => $settingsID,
                'projectID'         => $projectID,
                'personnelID'       => $personnelID,
                'attendance_date'   => $date,
                'attendance_status' => isset($dates[$date]) ? 'Present' : 'Absent'
            ];
        }
    }

    $this->Project_model->save_batch_attendance_range($batchData);

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
$data['project'] = $this->Project_model->getProjectDetails($projectID);



    $payroll = $this->Project_model->getPayrollData($settingsID, $projectID, $start, $end);

    // Group attendance logs (AM/PM/holidays)
$this->db->select('personnelID, date, status, work_duration, overtime_hours');

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
   $pid = (string)$log->personnelID;
// ✅ force to int
    $date = date('Y-m-d', strtotime($log->date));
    $logs[$pid][$date] = [
        'status' => $log->status,
        'hours' => floatval($log->work_duration ?? 0),
        'overtime_hours' => floatval($log->overtime_hours ?? 0)

    ];
    
    $dateList[$date] = true;
}
    ksort($dateList);
    $data['dates'] = array_keys($dateList);
    $data['logs'] = $logs;

    // Prepare batch for saving
    $batch = [];

   foreach ($payroll as &$row) {
    $pid = (int)trim($row->personnelID); // ✅ cast again
    
      $govDeduction = $this->PayrollModel->getGovDeduction($row->personnelID, $start, $end, $settingsID);


$row->gov_sss = $govDeduction['SSS'] ?? 0;
$row->gov_pagibig = $govDeduction['PAGIBIG'] ?? 0;
$row->gov_philhealth = $govDeduction['PHILHEALTH'] ?? 0;



        $row->reg_hours_per_day = [];
        $row->present_days = 0;
        $row->total_reg_hours = 0;
        $row->total_ot_hours = 0;

       foreach ($data['dates'] as $date) {
        $day_log = $logs[$pid][$date] ?? null;
            $status = strtolower(trim($day_log['status'] ?? ''));
            
if ($status === 'present' || $status === 'regular ho') {
    $reg = floatval($day_log['hours']);
    $ot  = floatval($day_log['overtime_hours'] ?? 0);

   $row->reg_hours_per_day[$date] = [
    'hours' => $reg,
    'overtime_hours' => $ot, // ✅ Corrected
    'status' => $day_log['status'] ?? ''
];



                $row->total_reg_hours += $reg;
                $row->total_ot_hours  += $ot;
                
                $row->present_days++;
         } elseif ($status === 'day off') {
    $row->reg_hours_per_day[$date] = 'Day Off';
} else {
    // ✅ Always capture hours and overtime even for Absent
    $reg = floatval($day_log['hours'] ?? 0); // Usually 0
    $ot  = floatval($day_log['overtime_hours'] ?? 0); // Might be > 0

    $row->reg_hours_per_day[$date] = [
        'hours' => $reg,
        'overtime_hours' => $ot,
        'status' => $day_log['status'] ?? ''
    ];

    // ⛔ DO NOT increment present_days or reg_total
    // ✅ But allow OT hours to accumulate
    if ($ot > 0) {
        $row->total_ot_hours += $ot;
    }
}

            
        }

       // Compute gross pay
$rate = floatval($row->rateAmount);
$gross = 0;
$reg_pay = 0;
$ot_pay = 0;

switch (strtolower($row->rateType)) {
    case 'hour':
        $reg_pay = $row->total_reg_hours * $rate;
        $ot_pay = $row->total_ot_hours * $rate;
        break;
    case 'day':
        $hourly_rate = $rate / 8;
        $reg_pay = $row->total_reg_hours * $hourly_rate;
        $ot_pay = $row->total_ot_hours * $hourly_rate;
        break;
    case 'month':
        $hourly_rate = $rate / 240;
        $reg_pay = $row->total_reg_hours * $hourly_rate;
        $ot_pay = $row->total_ot_hours * $hourly_rate;
        break;
}

$gross = round($reg_pay + $ot_pay, 2);
$row->gross = $gross;

$row->total_deduction =
    floatval($row->sss) +
    floatval($row->philhealth) +
    floatval($row->pagibig) +
    floatval($row->ca_cashadvance) +
    floatval($row->loan) +
    floatval($row->other_deduction);

$row->take_home = $gross - $row->total_deduction;

// Save to payroll_summary (skip if duplicate exists)
$exists = $this->db->get_where('payroll_summary', [
    'personnelID' => $pid,
    'projectID' => $projectID,
    'start_date' => $start,
    'end_date' => $end,
])->row();

if (!$exists) {
    $batch[] = [
        'personnelID' => $pid,
        'projectID' => $projectID,
        'settingsID' => $settingsID,
        'start_date' => $start,
        'end_date' => $end,
        'reg_hours' => $row->total_reg_hours,
        'ot_hours' => $row->total_ot_hours,
        'reg_pay' => round($reg_pay, 2),
        'ot_pay' => round($ot_pay, 2),
        'gross_pay' => $gross,
        'ca_deduction' => $row->ca_cashadvance,
        'sss_deduction' => $row->sss,
        'pagibig_deduction' => $row->pagibig,
        'philhealth_deduction' => $row->philhealth,
        'loan_deduction' => $row->loan,
        'other_deduction' => $row->other_deduction,
        'total_deduction' => $row->total_deduction,
        'net_pay' => $row->take_home,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

    }

    if (!empty($batch)) {
        $this->db->insert_batch('payroll_summary', $batch);
    }

usort($payroll, function ($a, $b) {
    $lastNameCompare = strcmp($a->last_name, $b->last_name);
    if ($lastNameCompare === 0) {
        return strcmp($a->first_name, $b->first_name);
    }
    return $lastNameCompare;
});

$data['attendance_data'] = $payroll;

    $data['personnel_loans'] = $this->Project_model->getPersonnelLoans($settingsID);
    $data['show_signatories'] = true;

    $this->load->view('payroll_report_view', $data);
}
public function view_payroll_batch()
{
    $this->load->model('SettingsModel');

    $batch_id = $this->input->get('batch_id');
    list($projectID, $start, $end) = explode('|', $batch_id);
    $settingsID = $this->session->userdata('settingsID');

    $data['project'] = $this->Project_model->getProjectDetails($projectID);
    $data['start'] = $start;
    $data['end'] = $end;
    $data['signatories'] = $this->SettingsModel->get_signatories($settingsID);
    $data['show_signatories'] = true;

    // ✅ Get saved payroll summary
    $data['attendance_data'] = $this->Project_model->getSavedPayrollData($projectID, $start, $end, $settingsID);
    usort($data['attendance_data'], function ($a, $b) {
        $nameA = strtolower($a->last_name . ', ' . $a->first_name);
        $nameB = strtolower($b->last_name . ', ' . $b->first_name);
        return strcmp($nameA, $nameB);
    });

    // ✅ Get attendance logs per personnel/date
    $this->db->select('personnelID, date, status, work_duration, overtime_hours');
    $this->db->from('attendance');
    $this->db->where('projectID', $projectID);
    $this->db->where('DATE(date) >=', $start);
    $this->db->where('DATE(date) <=', $end);
    $this->db->where('settingsID', $settingsID);
    $logs_result = $this->db->get()->result();

    $logs = [];
    $dates = [];

    foreach ($logs_result as $log) {
        $pid = (int)$log->personnelID;
        $date = $log->date;
        $logs[$pid][$date] = [
            'status' => $log->status,
            'hours' => floatval($log->work_duration ?? 0),
            'overtime_hours' => floatval($log->overtime_hours ?? 0)
        ];
        $dates[$date] = true;
    }

    ksort($dates);
    $data['dates'] = array_keys($dates);

    // ✅ Prepare data per person per date
    foreach ($data['attendance_data'] as &$row) {
        $pid = (string)$row->personnelID;

        $row->reg_hours_per_day = [];

        // ✅ Inject holiday totals (if already saved in summary)
        $row->holiday_hours = floatval($row->holiday_hours ?? 0);
        $row->holiday_pay = floatval($row->holiday_pay ?? 0);

        // ✅ Government deductions
        $govDeduction = $this->PayrollModel->getGovDeduction($pid, $start, $end, $settingsID);
        $row->gov_sss = $govDeduction['SSS'] ?? 0;
        $row->gov_pagibig = $govDeduction['PAGIBIG'] ?? 0;
        $row->gov_philhealth = $govDeduction['PHILHEALTH'] ?? 0;

        // ✅ Accepted statuses including holidays
        $validStatuses = [
            'present',
            'regularho',
            'regularholiday',
            'legalholiday',
            'specialholiday',
            'specialnonworkingholiday',
            'specialnon',
            'holiday'
        ];

       foreach ($data['dates'] as $d) {
    if (isset($logs[$pid][$d])) {
        $status = strtolower(trim($logs[$pid][$d]['status']));
        $normalizedStatus = preg_replace('/[^a-z]/', '', $status);

        $validStatuses = [
            'present',
            'regularho',
            'regularholiday',
            'legalholiday',
            'specialholiday',
            'specialnonworkingholiday',
            'specialnonworking',
            'specialnon',
            'specialno',
            'holiday'
        ];

        if (in_array($normalizedStatus, $validStatuses)) {
            $row->reg_hours_per_day[$d] = [
                'hours' => $logs[$pid][$d]['hours'],
                'overtime_hours' => $logs[$pid][$d]['overtime_hours'],
                'status' => $status
            ];
        } elseif ($normalizedStatus === 'dayoff') {
            $row->reg_hours_per_day[$d] = 'Day Off';
        } else {
            // ✅ Absent with OT hours fallback
            $hours = $logs[$pid][$d]['hours'] ?? 0;
            $ot = $logs[$pid][$d]['overtime_hours'] ?? 0;

            if ($ot > 0 || $hours > 0) {
                $row->reg_hours_per_day[$d] = [
                    'hours' => $hours,
                    'overtime_hours' => $ot,
                    'status' => $status
                ];
            } else {
                $row->reg_hours_per_day[$d] = '-';
            }
        }
    } else {
        $row->reg_hours_per_day[$d] = '-';
    }
}
    }

    // ✅ Load payroll view (already working)
    $this->load->view('payroll_report_view', $data);
}





// -------END----------------

public function delete_attendance_group()
{
    $projectID  = $this->input->post('projectID');
    $settingsID = $this->input->post('settingsID');
    $date       = $this->input->post('date');

    // Delete attendance logs
    $this->db->where('projectID', $projectID);
    $this->db->where('settingsID', $settingsID);
    $this->db->where('date', $date);
    $this->db->delete('attendance');

   

    // Redirect back to attendance list
    redirect("project/attendance_list/{$settingsID}?pid={$projectID}");
}

// Project.php
public function print_attendance()
{
    $settingsID = $this->input->get('settingsID');
    $projectID = $this->input->get('projectID');
    $date = $this->input->get('date');

    if ($date) {
        $data['attendance_logs'] = $this->Project_model->getAttendanceLogsByDate($settingsID, $projectID, $date);
    } else {
        $data['attendance_logs'] = $this->Project_model->getAttendanceLogs($settingsID, $projectID);
    }

   $data['project'] = $this->Project_model->getProjectDetails($projectID);

    $this->load->view('print_attendance_view', $data);
}
public function export_attendance_csv($settingsID)
{
    $projectID = $this->input->get('pid');
    $logs = $this->Project_model->getAttendanceLogs($settingsID, $projectID);

    if (empty($logs)) {
        $this->session->set_flashdata('error', 'No attendance logs found.');
        redirect('project/attendance_list/' . $settingsID . '?pid=' . $projectID);
        return;
    }

    $filename = 'AttendanceLogs_' . date('YmdHis') . '.csv';

    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Pragma: no-cache');
    header("Expires: 0");

    $output = fopen('php://output', 'w');

    // Column headers
    fputcsv($output, ['Personnel Name', 'Date', 'Status', 'Work Duration (hrs)', 'Project']);

    foreach ($logs as $log) {
        fputcsv($output, [
            ucwords($log->first_name . ' ' . $log->last_name),
            $log->date,
            ucfirst($log->status),
            $log->work_duration,
            $log->projectTitle ?? ''
        ]);
    }

    fclose($output);
    exit;
}



// =======================
// PAYROLL SUMMARY CONTROLLER
// =======================

public function view_payroll_summary_batches()
{
    $this->load->model('Project_model');

    $settingsID = $this->session->userdata('settingsID');
    $projectID = $this->input->get('project_id');

    // 👇 Fetch projects only for the logged-in company (for the modal dropdown)
    $data['projects'] = $this->Project_model->get_projects_by_settings($settingsID);

    // Get either all summary batches or specific project
    if ($projectID) {
        $batches = $this->Project_model->get_summary_batches_by_project($projectID, $settingsID);
    } else {
        $batches = $this->Project_model->get_all_summary_batches($settingsID);
    }

    $summary = [];
    $grandTotalGross = 0;

    foreach ($batches as $batch) {
        $gross = $this->Project_model->get_total_grosspay_latest_only(
            $batch->projectID,
            $batch->start_date,
            $batch->end_date,
            $settingsID
        );

        $summary[] = [
            'projectID' => $batch->projectID,
            'projectTitle' => $batch->projectTitle,
            'projectLocation' => $batch->projectLocation,
            'start_date' => $batch->start_date,
            'end_date' => $batch->end_date,
            'gross_total' => $gross
        ];

        $grandTotalGross += $gross;
    }

    $data['batch_summaries'] = $summary;
    $data['grand_total_gross'] = $grandTotalGross;

    $this->load->view('payroll_summary_batches', $data);
}




public function delete_summary_batch()
{
    $projectID = $this->input->post('projectID');
    $start_date = $this->input->post('start_date');
    $end_date = $this->input->post('end_date');
    $settingsID = $this->session->userdata('settingsID');

    $this->load->model('Project_model'); // ✅ Corrected
    $result = $this->Project_model->delete_summary_batch($projectID, $start_date, $end_date, $settingsID);

    if ($result) {
        $this->session->set_flashdata('success', 'Payroll batch deleted successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete payroll batch.');
    }

    redirect('project/view_payroll_summary_batches'); // ✅ Ensure this matches your routing
}



}
