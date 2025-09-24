<?php
class Project extends CI_Controller
{
    public function __construct() {
        parent::__construct();
         $this->load->model('PayrollModel');
        $this->load->model('Project_model');
        $this->load->library('AuditLogger');
    }
private function currentRole()
{
    return $this->session->userdata('position') ?: $this->session->userdata('level');
}

private function requireAdmin()
{
    if ($this->currentRole() !== 'Admin') {
        $this->session->set_flashdata('error', 'Unauthorized action.');
        redirect('project/project_view');
        exit;
    }
}

public function project_view()
{
    $settingsID = $this->session->userdata('settingsID');

    $data['projects'] = $this->Project_model->getAll($settingsID);
    $data['attendance_periods'] = $this->Project_model->get_attendance_batches($settingsID);

    $data['canEditProjects'] = ($this->currentRole() === 'Admin');

    $this->load->view('project_view', $data);
}



public function store()
{
    $this->requireAdmin();           
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
    $this->requireAdmin();           
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
    $this->requireAdmin();         
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


    $data['attendance_logs'] = $this->Project_model->getAttendanceLogs($settingsID, $projectID);

    $this->load->view('attendance_list_view', $data);
}



public function attendance($settingsID)
{
        date_default_timezone_set('Asia/Manila'); 

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
  case 'bi-month':
case 'bi-monthly':
case 'bimonth':
    // Bi-month = 15 days * 8 hours = 120 hours
    $hourly_rate = $rate / 120;
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
$__ins_count = is_array($batch) ? count($batch) : 0;
if ($__ins_count === 0) {
    // AUDIT: attempt produced no new rows (duplicates or no data)
    $this->auditlogger->log(
        'other', 'payroll_summary', null, null, null, null,
        'Generate payroll summary produced 0 new rows | projectID='.$projectID.' | period='.$start.'..'.$end
    );
}

    if (!empty($batch)) {
// AUDIT: summarize rows inserted to payroll_summary
$this->auditlogger->log(
    'other',
    'payroll_summary',
    null,
    null,
    null,
    null,
    'Generated payroll summary | projectID='.$projectID.' | period='.$start.'..'.$end.' | rows='.$__ins_count
);

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
    $this->load->model('Project_model');
    $this->load->model('SettingsModel');
    $this->load->model('PayrollModel');

    $batch_id  = (string)$this->input->get('batch_id');
    $parts     = explode('|', $batch_id);
    if (count($parts) !== 3) {
        $this->session->set_flashdata('error', 'Invalid batch id.');
        redirect('project/project_view');
        return;
    }

    list($projectID, $start, $end) = $parts;
    $settingsID = $this->session->userdata('settingsID');

    if (empty($projectID) || empty($start) || empty($end)) {
        $this->session->set_flashdata('error', 'Missing project or date range.');
        redirect('project/project_view');
        return;
    }

    $data['project']         = $this->Project_model->getProjectDetails($projectID);
    $data['start']           = $start;
    $data['end']             = $end;
    $data['signatories']     = $this->SettingsModel->get_signatories($settingsID);
    $data['show_signatories']= true;

    $payroll = $this->Project_model->getPayrollData($settingsID, $projectID, $start, $end);

    $this->db->select('personnelID, date, status, work_duration, overtime_hours');
    $this->db->from('attendance');
    $this->db->where('projectID', $projectID);
    $this->db->where('date >=', $start);
    $this->db->where('date <=', $end);
    $this->db->where('settingsID', $settingsID);
    $daily_logs = $this->db->get()->result();

    $logs = [];
    $dateList = [];
    foreach ($daily_logs as $log) {
        $pid  = (int)$log->personnelID;
        $date = date('Y-m-d', strtotime($log->date));
        $logs[$pid][$date] = [
            'status'          => (string)($log->status ?? ''),
            'hours'           => (float)($log->work_duration ?? 0),
            'overtime_hours'  => (float)($log->overtime_hours ?? 0),
        ];
        $dateList[$date] = true;
    }
    ksort($dateList);
    $data['dates'] = array_keys($dateList);
    $data['logs']  = $logs;

    $normalize = function($s){
        return preg_replace('/[^a-z]/', '', strtolower(trim((string)$s)));
    };

    $hourlyFrom = function($rateType, $rateAmount){
        $rt = strtolower((string)$rateType);
        $rate = (float)$rateAmount;
        switch ($rt) {
            case 'hour':     return $rate;
            case 'day':      return $rate / 8;
            case 'month':    return $rate / 240;
            case 'bi-month':
            case 'bi-monthly':
            case 'bimonth':  return $rate / 120;
            default:         return 0.0;
        }
    };

    $batchInsert = [];
    foreach ($payroll as &$row) {
        $pid = (int)$row->personnelID;

        $gov = $this->PayrollModel->getGovDeduction($pid, $start, $end, $settingsID);
        $row->gov_sss        = $gov['SSS']        ?? 0;
        $row->gov_pagibig    = $gov['PAGIBIG']    ?? 0;
        $row->gov_philhealth = $gov['PHILHEALTH'] ?? 0;

        $row->reg_hours_per_day = [];
        $row->present_days      = 0;
        $row->total_reg_hours   = 0.0;
        $row->total_ot_hours    = 0.0;

        foreach ($data['dates'] as $d) {
            $day = $logs[$pid][$d] ?? null;
            if (!$day) { $row->reg_hours_per_day[$d] = '-'; continue; }

            $statusRaw = $day['status'] ?? '';
            $status    = $normalize($statusRaw);

            $valid = [
                'present','regularho','regularholiday','legalholiday',
                'specialholiday','specialnonworkingholiday','specialnonworking',
                'specialnon','specialno','holiday'
            ];

            if (in_array($status, $valid, true)) {
                $row->reg_hours_per_day[$d] = [
                    'hours'          => (float)$day['hours'],
                    'overtime_hours' => (float)$day['overtime_hours'],
                    'status'         => $statusRaw
                ];
                if ($status === 'present' || $status === 'regularho') {
                    $row->total_reg_hours += (float)$day['hours'];
                    $row->total_ot_hours  += (float)$day['overtime_hours'];
                    $row->present_days++;
                } else {
                    $row->total_ot_hours += (float)$day['overtime_hours'];
                }
            } elseif ($status === 'dayoff') {
                $row->reg_hours_per_day[$d] = 'Day Off';
            } else {
                $hours = (float)($day['hours'] ?? 0);
                $ot    = (float)($day['overtime_hours'] ?? 0);
                if ($hours > 0 || $ot > 0) {
                    $row->reg_hours_per_day[$d] = [
                        'hours'          => $hours,
                        'overtime_hours' => $ot,
                        'status'         => $statusRaw
                    ];
                } else {
                    $row->reg_hours_per_day[$d] = '-';
                }
                if ($ot > 0) $row->total_ot_hours += $ot;
            }
        }

        $hourly      = $hourlyFrom($row->rateType, $row->rateAmount);
        $reg_pay     = $row->total_reg_hours * $hourly;
        $ot_pay      = $row->total_ot_hours  * $hourly;
        $gross       = round($reg_pay + $ot_pay, 2);
        $row->gross  = $gross;

        $row->total_deduction =
            (float)$row->sss +
            (float)$row->philhealth +
            (float)$row->pagibig +
            (float)$row->ca_cashadvance +
            (float)$row->loan +
            (float)$row->other_deduction;

        $row->take_home = $gross - $row->total_deduction;

        $exists = $this->db->get_where('payroll_summary', [
            'personnelID' => $pid,
            'projectID'   => $projectID,
            'start_date'  => $start,
            'end_date'    => $end,
        ])->row();

        if (!$exists) {
            $batchInsert[] = [
                'personnelID'        => $pid,
                'projectID'          => $projectID,
                'settingsID'         => $settingsID,
                'start_date'         => $start,
                'end_date'           => $end,
                'reg_hours'          => $row->total_reg_hours,
                'ot_hours'           => $row->total_ot_hours,
                'reg_pay'            => round($reg_pay, 2),
                'ot_pay'             => round($ot_pay, 2),
                'gross_pay'          => $gross,
                'ca_deduction'       => (float)$row->ca_cashadvance,
                'sss_deduction'      => (float)$row->sss,
                'pagibig_deduction'  => (float)$row->pagibig,
                'philhealth_deduction'=> (float)$row->philhealth,
                'loan_deduction'     => (float)$row->loan,
                'other_deduction'    => (float)$row->other_deduction,
                'total_deduction'    => $row->total_deduction,
                'net_pay'            => $row->take_home,
                'created_at'         => date('Y-m-d H:i:s'),
            ];
        }
    }
    unset($row);

    if (!empty($batchInsert)) {
        $this->db->insert_batch('payroll_summary', $batchInsert);
    }

    usort($payroll, function ($a, $b) {
        $ln = strcmp($a->last_name, $b->last_name);
        return $ln === 0 ? strcmp($a->first_name, $b->first_name) : $ln;
    });

    $data['attendance_data']  = $payroll;
    $data['personnel_loans']  = $this->Project_model->getPersonnelLoans($settingsID);

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
        // AUDIT: attempted export with no data
        $this->auditlogger->log(
            'export', 'attendance', null, null,
            null, null,
            'Export attempted: attendance CSV has no rows | settingsID='.$settingsID.' | projectID='.$projectID
        );

        $this->session->set_flashdata('error', 'No attendance logs found.');
        redirect('project/attendance_list/' . $settingsID . '?pid=' . $projectID);
        return;
    }

    $filename = 'AttendanceLogs_' . date('YmdHis') . '.csv';

    // AUDIT: successful export (record row count + filters)
    $this->auditlogger->log(
        'export', 'attendance', null, null,
        null, null,
        'Exported attendance CSV | rows='.count($logs).' | settingsID='.$settingsID.' | projectID='.$projectID
    );

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

public function api_get_batch_total_live()
{
    $projectID  = $this->input->get('projectID', true);
    $start_date = $this->input->get('start_date', true);
    $end_date   = $this->input->get('end_date', true);
    $settingsID = $this->session->userdata('settingsID'); // or however you store it

    if (!$projectID || !$start_date || !$end_date || !$settingsID) {
        return $this->output->set_status_header(400)->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'Missing parameters']));
    }

    $this->load->model('Project_model');
    $gross = $this->Project_model->compute_batch_gross_live($settingsID, $projectID, $start_date, $end_date);

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode(['gross_total' => $gross]));
}

public function api_get_all_batch_totals_live()
{
    $settingsID = $this->session->userdata('settingsID');
    if (!$settingsID) {
        return $this->output->set_status_header(401)->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'Not authorized']));
    }

    $this->load->model('Project_model');
    $rows = $this->Project_model->get_all_summary_batches_live($settingsID);

    return $this->output->set_content_type('application/json')
        ->set_output(json_encode(['batches' => $rows]));
}


}
