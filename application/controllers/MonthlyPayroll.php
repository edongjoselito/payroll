<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MonthlyPayroll extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MonthlyPayroll_model');
        $this->load->model('SettingsModel');

        $this->load->helper(['form', 'url']);
        $this->load->library('AuditLogger');

        $this->load->library('session');
          if (!in_array($this->session->userdata('level'), ['Admin','Payroll User'], true)) {
        $this->session->set_flashdata('error', 'Unauthorized access.');
        redirect('login');
        return;
    }
    }

   public function index()
{
    $this->load->model('MonthlyPayroll_model');
    $data['saved_months'] = $this->MonthlyPayroll_model->get_saved_months();
    $this->load->view('monthly_payroll_input', $data);
}

public function generate()
{
    if ($this->input->get('saved') === 'true') {
        $this->session->keep_flashdata('msg'); 
    }

    $from = $this->input->post('from_date');
    $to   = $this->input->post('to_date');

    if (!$from || !$to || strtotime($from) > strtotime($to)) {
        $this->session->set_flashdata('error', 'Please select a valid date range.');
        redirect('WeeklyAttendance');
        return;
    }

    $month = date('Y-m', strtotime($from));

    $settingsID = $this->session->userdata('settingsID');
    $personnel = $this->MonthlyPayroll_model->get_all_personnel($settingsID, ['Bi-Month', 'Month']);
    $personnelIDs = array_column($personnel, 'personnelID');

    if (empty($personnelIDs)) {
        $this->session->set_flashdata('error', 'No personnel found for this payroll period.');
        redirect('WeeklyAttendance');
        return;
    }

    $dates = [];
    $current = strtotime($from);
    $end = strtotime($to);
    while ($current <= $end) {
        $dates[] = date('d', $current);
        $current = strtotime('+1 day', $current);
    }

    $existingRows = $this->db->where('payroll_month', $month)
                             ->where('settingsID', $settingsID)
                             ->where_in('personnelID', $personnelIDs)
                             ->get('payroll_attendance_monthly')
                             ->result();

    $overlapCount = 0;
    foreach ($existingRows as $row) {
        $existingDetails = json_decode($row->details_json, true);
        if (!is_array($existingDetails)) continue;

        foreach ($dates as $day) {
            if (array_key_exists(ltrim($day, '0'), $existingDetails) || array_key_exists($day, $existingDetails)) {
                $overlapCount++;
                break;
            }
        }
    }

    if ($overlapCount >= count($personnel)) {
        $this->session->set_flashdata('duplicate_msg',
            'Monthly payroll for <strong>' . date('F Y', strtotime($month . '-01')) . '</strong> covering this range has already been generated for all personnel.');
        redirect('WeeklyAttendance');
        return;
    }

    $fullDates = [];
    $current = strtotime($from);
    while ($current <= strtotime($to)) {
        $fullDates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }

    $saved_months = $this->MonthlyPayroll_model->get_saved_months();

    $data = [
        'personnel' => $personnel,
        'dates' => $fullDates,
        'month' => $month,
        'from' => $from,
        'to' => $to,
        'saved_months' => $saved_months,
    ];

    $this->load->view('monthly_payroll_input', $data);
}





public function save()
{
    $month = $this->input->post('payroll_month');
    $from = $this->input->post('from_date');
    $to = $this->input->post('to_date');

    $personnelIDs = $this->input->post('personnelID');
    $attendance_status = $this->input->post('attendance_status');
    $regular_hours = $this->input->post('regular_hours');
    $overtime_hours = $this->input->post('overtime_hours');

    foreach ($personnelIDs as $personnelID) {
        $details = [];

        if (isset($attendance_status[$personnelID])) {
            foreach ($attendance_status[$personnelID] as $date => $status) {
                $day = date('d', strtotime($date)); 
               $details[$date] = [
    'status' => $status,
    'reg'    => isset($regular_hours[$personnelID][$date]) ? (float)$regular_hours[$personnelID][$date] : 0,
    'ot'     => isset($overtime_hours[$personnelID][$date]) ? (float)$overtime_hours[$personnelID][$date] : 0,
];
            }
        }

        $details['_range'] = [
            'from' => $from,
            'to' => $to,
        ];

        $this->MonthlyPayroll_model->save_payroll_monthly($personnelID, $month, $details);
    }

    $this->session->set_flashdata('msg', '✅ Monthly payroll saved successfully!');
    $this->session->set_userdata('payroll_month', $month);

    $redirect_url = $_SERVER['HTTP_REFERER'];
    $redirect_url .= (strpos($redirect_url, '?') === false) ? '?saved=true' : '&saved=true';

    redirect($redirect_url);
    // AUDIT: summarize monthly save
$this->auditlogger->log(
    'other',
    'payroll_attendance_monthly',
    null,
    null,
    null,
    null,
    'Saved monthly payroll | month='.$month.' | period='.$from.'..'.$to.' | employees='.count((array)$personnelIDs)
);

}

public function view_record()
{
    $month = $this->input->post('payroll_month');
   $from = $this->input->post('from_date');
$to   = $this->input->post('to_date');

if (!$month) {
    $month = $this->session->userdata('payroll_month');
} else {
    $this->session->set_userdata('payroll_month', $month);
}

if (!$from) {
    $from = $this->session->userdata('payroll_from');
}
if (!$to) {
    $to = $this->session->userdata('payroll_to');
}

if (!$from || !$to) {
    $this->session->set_flashdata('msg', 'Please select a valid date range.');
    redirect('MonthlyPayroll');
}


    $this->session->set_userdata('payroll_from', $from);
    $this->session->set_userdata('payroll_to', $to);

    $records = $this->MonthlyPayroll_model->get_monthly_payroll_records($month, $from, $to);

    $data['month'] = $month;
    $data['from'] = $from;
    $data['to'] = $to;
    $data['records'] = $records;
    $data['saved_months'] = $this->MonthlyPayroll_model->get_saved_months();

    $this->load->view('monthly_payroll_records', $data);
}


public function update_attendance()
{
    $personnelID   = $this->input->post('personnelID');
    $payroll_month = $this->input->post('payroll_month'); 
    $dayParam      = $this->input->post('day');  
    $dateParam     = $this->input->post('date'); 
    $status        = $this->input->post('status');
    $reg           = (float)$this->input->post('reg');
    $ot            = (float)$this->input->post('ot');

    if ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
        $fullDate = $dateParam;
        $dayKey   = date('d', strtotime($fullDate)); 
    } else {
        $ym = $payroll_month ?: date('Y-m');
        $dayKey = str_pad($dayParam ?: '01', 2, '0', STR_PAD_LEFT);
        $fullDate = $ym . '-' . $dayKey; 
    }

    $row = $this->MonthlyPayroll_model->get_payroll_record($personnelID, $payroll_month);
    if ($row) {
        $details = json_decode($row->details_json, true);
        if (!is_array($details)) $details = [];
    } else {
        $details = [];
    }

    $payload = ['status' => $status, 'reg' => $reg, 'ot' => $ot];

    $details[$fullDate] = $payload; 
    $details[ltrim($dayKey, '0')] = $payload; 
    $details[$dayKey] = $payload;     

    $this->MonthlyPayroll_model->update_payroll_details($personnelID, $payroll_month, $details);
    $this->session->set_flashdata('msg', 'Attendance updated!');

    redirect($_SERVER['HTTP_REFERER']);
}

public function view_formatted()
{
    $from = $this->input->get('start'); // YYYY-MM-DD
    $to   = $this->input->get('end');   // YYYY-MM-DD

    if (!$from || !$to) {
        $this->session->set_flashdata('msg', 'Start and End date are required.');
        redirect('MonthlyPayroll');
        return;
    }

    $month = date('Y-m', strtotime($from));
    $data  = $this->MonthlyPayroll_model->get_monthly_payroll_records($month, $from, $to);

    $data['start'] = $from;
    $data['end']   = $to;

    $projectID = $this->input->get('project_id');
    $data['project'] = null; 

    $settingsID = $this->session->userdata('settingsID');
    $data['signatories']      = $this->SettingsModel->get_signatories($settingsID);
    $data['show_signatories'] = true;

    $data['is_summary'] = false;

    if (!isset($data['personnel']) || !isset($data['dates']) || !isset($data['attendance'])) {
        $this->session->set_flashdata('msg', 'Missing data for view.');
        redirect('MonthlyPayroll');
        return;
    }

    $attendance_data = [];
    foreach ($data['personnel'] as $p) {
        $pid = $p->personnelID;
        $p->reg_hours_per_day = [];
        $total_hours = 0; $total_ot = 0; $total_days = 0;

        foreach ($data['dates'] as $d) {
            $attn = $data['attendance'][$pid][$d] ?? ['status' => '', 'reg' => 0, 'ot' => 0];
            $status = $attn['status']; $reg = $attn['reg']; $ot = $attn['ot'];

            $p->reg_hours_per_day[$d] = [
                'status' => $status,
                'hours' => $reg,
                'overtime_hours' => $ot,
                'holiday_hours' => 0
            ];

            $total_hours += $reg;
            $total_ot    += $ot;
            if ($reg > 0) $total_days += $reg / 8;
        }

        $rateTypeNorm = strtolower(trim($p->rateType ?? ''));
        $rateAmount   = (float)($p->rateAmount ?? 0);
        $perHour      = ($rateTypeNorm === 'hour') ? $rateAmount : ($rateAmount / 8.0);

        $amount_reg = $total_hours * $perHour;
        $amount_ot  = $total_ot    * $perHour * 1.25;

        $monthKey = date('Y-m', strtotime($from));
        $cashadvance    = (float)$this->MonthlyPayroll_model->get_cash_advance($pid, $monthKey);
        $gov            = (array)$this->MonthlyPayroll_model->get_government_deductions($pid, $monthKey);
        $loan           = (float)$this->MonthlyPayroll_model->get_loan_deduction($pid, $monthKey);
        $sss = (float)($gov['sss'] ?? 0);
        $pagibig = (float)($gov['pagibig'] ?? 0);
        $philhealth = (float)($gov['philhealth'] ?? 0);

        $total_deductions = $cashadvance + $sss + $pagibig + $philhealth + $loan;

        $p->rate_per_hour   = number_format($perHour, 2);
        $p->amount_reg      = number_format($amount_reg, 2);
        $p->amount_ot       = number_format($amount_ot, 2);
        $p->gross           = number_format($amount_reg + $amount_ot, 2);
        $p->total_days      = number_format($total_days, 2);
        $p->cashadvance     = number_format($cashadvance, 2);
        $p->sss             = number_format($sss, 2);
        $p->pagibig         = number_format($pagibig, 2);
        $p->philhealth      = number_format($philhealth, 2);
        $p->loan            = number_format($loan, 2);
        $p->total_deduction = number_format($total_deductions, 2);
        $p->takehome        = number_format(($amount_reg + $amount_ot) - $total_deductions, 2);

        $attendance_data[] = $p;
    }

    $data['attendance_data'] = $attendance_data;

    $this->load->view('monthly_payroll_view', $data);
}

public function generate_bimonth()
{
    $from      = $this->input->post('start'); // YYYY-MM-DD
    $to        = $this->input->post('end');   // YYYY-MM-DD
    $projectID = $this->input->post('project_id'); // optional

    if (!$from || !$to || strtotime($from) > strtotime($to)) {
        $this->session->set_flashdata('msg','Start and End date are required.');
        redirect('MonthlyPayroll');
        return;
    }

    $month   = date('Y-m', strtotime($from));
    $records = $this->MonthlyPayroll_model->get_monthly_payroll_records($month, $from, $to);

    $personnel = isset($records['personnel']) ? $records['personnel'] : [];
    $dates     = isset($records['dates']) ? $records['dates'] : [];
    $attIndex  = isset($records['attendance']) ? $records['attendance'] : [];

    $attendance_data = [];
    $linesForDB      = [];

    foreach ($personnel as $p) {
        $pid = $p->personnelID;

        $total_hours = 0.0;
        $total_ot    = 0.0;
        $total_days  = 0.0;

        $per_day = [];
        $reg_hours_per_day = [];

        foreach ($dates as $d) {
            $attn = isset($attIndex[$pid][$d]) ? $attIndex[$pid][$d] : ['status'=>'', 'reg'=>0, 'ot'=>0];

            $reg = (float)($attn['reg'] ?? 0);
            $ot  = (float)($attn['ot']  ?? 0);

            $per_day[$d] = [
                'status' => $attn['status'] ?? '',
                'reg'    => $reg,
                'ot'     => $ot,
                'hol'    => 0,
            ];

            $reg_hours_per_day[$d] = [
                'status'         => $attn['status'] ?? '',
                'hours'          => $reg,
                'overtime_hours' => $ot,
                'holiday_hours'  => 0,
            ];

            $total_hours += $reg;
            $total_ot    += $ot;
            if ($reg > 0) $total_days += $reg / 8.0;
        }

        $rateAmount = (float)($p->rateAmount ?? 0);
        $rateType   = strtolower(trim($p->rateType ?? 'day'));
        $perHour    = ($rateType === 'hour') ? $rateAmount : $rateAmount / 8.0;

        $amount_reg = $total_hours * $perHour;
        $amount_ot  = $total_ot    * $perHour * 1.25;
        $gross      = $amount_reg + $amount_ot;

        $monthKey   = date('Y-m', strtotime($from));
        $gov        = (array)$this->MonthlyPayroll_model->get_government_deductions($pid, $monthKey);
        $cash       = (float)$this->MonthlyPayroll_model->get_cash_advance($pid, $monthKey);
        $loan       = (float)$this->MonthlyPayroll_model->get_loan_deduction($pid, $monthKey);

        $sss        = (float)($gov['sss']        ?? 0);
        $pag        = (float)($gov['pagibig']    ?? 0);
        $ph         = (float)($gov['philhealth'] ?? 0);

        $total_ded  = $cash + $sss + $pag + $ph + $loan;
        $net        = $gross - $total_ded;

        $linesForDB[] = [
            'personnelID' => $pid,
            'amounts_json'=> [
                'personnelID' => $pid,
                'last_name'   => $p->last_name  ?? '',
                'first_name'  => $p->first_name ?? '',
                'position'    => $p->position   ?? '',
                'rateType'    => $p->rateType   ?? '',
                'rateAmount'  => $rateAmount,
                'perHour'     => $perHour,
                'hours_reg'   => $total_hours,
                'hours_ot'    => $total_ot,
                'days'        => $total_days,
                'amount_reg'  => round($amount_reg, 2),
                'amount_ot'   => round($amount_ot, 2),
                'gross'       => round($gross, 2),
                'cash_advance'=> round($cash, 2),
                'sss'         => round($sss, 2),
                'pagibig'     => round($pag, 2),
                'philhealth'  => round($ph, 2),
                'loan'        => round($loan, 2),
                'total_deduction'=> round($total_ded, 2),
                'net'         => round($net, 2),
                'per_day'     => $per_day,
            ],
        ];

        $row = (object)(array)$p; 
        $row->personnelID       = $pid;
        $row->reg_hours_per_day = $reg_hours_per_day;
        $row->rate_per_hour     = number_format($perHour, 2);
        $row->amount_reg        = number_format($amount_reg, 2);
        $row->amount_ot         = number_format($amount_ot, 2);
        $row->gross             = number_format($gross, 2);
        $row->total_days        = number_format($total_days, 2);
        $row->cashadvance       = number_format($cash, 2);
        $row->sss               = number_format($sss, 2);
        $row->pagibig           = number_format($pag, 2);
        $row->philhealth        = number_format($ph, 2);
        $row->loan              = number_format($loan, 2);
        $row->total_deduction   = number_format($total_ded, 2);
        $row->takehome          = number_format($net, 2);

        $attendance_data[] = $row;
    }

    $sum_gross = 0; $sum_ded = 0; $sum_net = 0;
    foreach ($linesForDB as $l) {
        $sum_gross += (float)$l['amounts_json']['gross'];
        $sum_ded   += (float)$l['amounts_json']['total_deduction'];
        $sum_net   += (float)$l['amounts_json']['net'];
    }
    $totals = [
        'count_lines' => count($linesForDB),
        'sum_gross'   => $sum_gross,
        'sum_ded'     => $sum_ded,
        'sum_net'     => $sum_net,
    ];

    $this->load->model('BimonthPayroll_model');
    $settingsID = $this->session->userdata('settingsID');
    $userID     = $this->session->userdata('user_id');

    $batch_id = $this->BimonthPayroll_model->create_batch(
        $settingsID, $projectID, $from, $to, $month, $totals, $userID
    );
    $this->BimonthPayroll_model->insert_lines($batch_id, $linesForDB);
// AUDIT: summarize bi-month generation
$this->auditlogger->log(
    'other',
    'payroll_bimonth_batches',
    'id',
    $batch_id,
    null,
    null,
    'Generated bi-month payroll | projectID='.($projectID?:'ALL').
    ' | period='.$from.'..'.$to.
    ' | lines='.count((array)$linesForDB).
    ' | gross='.number_format((float)$sum_gross, 2).
    ' | net='.number_format((float)$sum_net, 2)
);

    $data = [
        'start'            => $from,
        'end'              => $to,
        'dates'            => $dates,  
        'project'          => null,  
        'signatories'      => $this->SettingsModel->get_signatories($settingsID),
        'show_signatories' => true,
        'is_summary'       => false,
        'attendance_data'  => $attendance_data,
        'saved_batch_id'   => $batch_id,
    ];

    $this->session->set_flashdata('msg', '✅ Bi-Month payroll saved.');
    $this->load->view('monthly_payroll_view', $data);
}


public function list_bimonth_batches()
{
    $this->load->model('BimonthPayroll_model');
    $settingsID = $this->session->userdata('settingsID');

    $projectID = $this->input->get('project_id'); 
    $data['batches'] = $this->BimonthPayroll_model->list_batches($settingsID, $projectID);

    $data['show_recompute'] = false;

    $this->load->view('bimonth_batches_list', $data);
}

public function open_bimonth_batch($batch_id)
{
    $this->load->model('BimonthPayroll_model');

    [$batch, $lines] = $this->BimonthPayroll_model->get_batch($batch_id);
    if (!$batch) { show_error('Batch not found', 404); return; }

    $dates = [];
    $cur = strtotime($batch->start_date);
    $end = strtotime($batch->end_date);
    while ($cur <= $end) {
        $dates[] = date('Y-m-d', $cur);
        $cur = strtotime('+1 day', $cur);
    }

    $attendance_data = [];
    foreach ($lines as $l) {
        $row = json_decode($l->amounts_json, true) ?: [];

        $pid        = (int)($row['personnelID'] ?? 0);
        $last_name  = (string)($row['last_name']  ?? ($row['name'] ?? ''));
        $first_name = (string)($row['first_name'] ?? '');
        $position   = (string)($row['position']   ?? '');
        $rateType   = (string)($row['rateType']   ?? '');
        $rateAmount = (float) ($row['rateAmount'] ?? 0);
        $perHour    = (float) ($row['perHour']    ?? 0);

        $amount_reg = (float)($row['amount_reg'] ?? 0);
        $amount_ot  = (float)($row['amount_ot']  ?? 0);
        $gross      = (float)($row['gross']      ?? ($amount_reg+$amount_ot));
        $days       = (float)($row['days']       ?? 0);

        $cash       = (float)($row['cash_advance'] ?? 0);
        $sss        = (float)($row['sss']          ?? 0);
        $pagibig    = (float)($row['pagibig']      ?? 0);
        $philhealth = (float)($row['philhealth']   ?? 0);
        $loan       = (float)($row['loan']         ?? 0);
        $total_ded  = (float)($row['total_deduction'] ?? ($cash+$sss+$pagibig+$philhealth+$loan));
        $net        = (float)($row['net'] ?? ($gross - $total_ded));

        $reg_hours_per_day = [];
        $per_day = isset($row['per_day']) && is_array($row['per_day']) ? $row['per_day'] : [];
        foreach ($dates as $d) {
            $cell = $per_day[$d] ?? ['status'=>'', 'reg'=>0, 'ot'=>0, 'hol'=>0];
            $reg_hours_per_day[$d] = [
                'status'         => $cell['status'] ?? '',
                'hours'          => (float)($cell['reg'] ?? 0),
                'overtime_hours' => (float)($cell['ot']  ?? 0),
                'holiday_hours'  => (float)($cell['hol'] ?? 0),
            ];
        }

        $p = new stdClass();
        $p->personnelID      = $pid;
        $p->last_name        = $last_name;
        $p->first_name       = $first_name;
        $p->position         = $position;
        $p->rateType         = $rateType;
        $p->rateAmount       = $rateAmount;

        $p->reg_hours_per_day = $reg_hours_per_day;
        $p->rate_per_hour     = number_format($perHour, 2);
        $p->amount_reg        = number_format($amount_reg, 2);
        $p->amount_ot         = number_format($amount_ot, 2);
        $p->gross             = number_format($gross, 2);
        $p->total_days        = number_format($days, 2);

        $p->cashadvance       = number_format($cash, 2);
        $p->sss               = number_format($sss, 2);
        $p->pagibig           = number_format($pagibig, 2);
        $p->philhealth        = number_format($philhealth, 2);
        $p->loan              = number_format($loan, 2);
        $p->total_deduction   = number_format($total_ded, 2);
        $p->takehome          = number_format($net, 2);

        $attendance_data[] = $p;
    }

    $data = [
        'start'            => $batch->start_date,
        'end'              => $batch->end_date,
        'dates'            => $dates,          
        'project'          => null,   
        'signatories'      => [],
        'show_signatories' => true,
        'is_summary'       => false,
        'attendance_data'  => $attendance_data,
    ];

    $this->load->view('monthly_payroll_view', $data);
}



}
