<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MonthlyPayroll extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MonthlyPayroll_model');
        $this->load->helper(['form', 'url']);
        $this->load->library('session');
    }

    // Landing page: shows the Generate Payroll button/modal only
   public function index()
{
    $this->load->model('MonthlyPayroll_model');
    $data['saved_months'] = $this->MonthlyPayroll_model->get_saved_months();
    $this->load->view('monthly_payroll_input', $data);
}

    // After selecting month
public function generate()
{
    if ($this->input->get('saved') === 'true') {
        $this->session->keep_flashdata('msg'); // keep the success message alive
    }

    // NEW: Accept date range input
    $from = $this->input->post('from_date');
    $to   = $this->input->post('to_date');

    if (!$from || !$to || strtotime($from) > strtotime($to)) {
        $this->session->set_flashdata('error', 'Please select a valid date range.');
        redirect('WeeklyAttendance');
        return;
    }

    // Use month from the 'from' date for backward compatibility
    $month = date('Y-m', strtotime($from));

    $settingsID = $this->session->userdata('settingsID');
    $personnel = $this->MonthlyPayroll_model->get_all_personnel($settingsID, ['Bi-Month', 'Month']);
    $personnelIDs = array_column($personnel, 'personnelID');

    if (empty($personnelIDs)) {
        $this->session->set_flashdata('error', 'No personnel found for this payroll period.');
        redirect('WeeklyAttendance');
        return;
    }

    // ✅ Build date range to check overlap
    $dates = [];
    $current = strtotime($from);
    $end = strtotime($to);
    while ($current <= $end) {
        $dates[] = date('d', $current); // only days (01 to 31)
        $current = strtotime('+1 day', $current);
    }

    // 🔍 Check if this date range already exists per personnel
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
                break; // only count once per personnel
            }
        }
    }

    if ($overlapCount >= count($personnel)) {
        $this->session->set_flashdata('duplicate_msg',
            'Monthly payroll for <strong>' . date('F Y', strtotime($month . '-01')) . '</strong> covering this range has already been generated for all personnel.');
        redirect('WeeklyAttendance');
        return;
    }

    // ✅ Proceed to generation
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





    // Save attendance/payroll data
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

        // Loop through days (all days posted for this personnel)
        if (isset($attendance_status[$personnelID])) {
            foreach ($attendance_status[$personnelID] as $date => $status) {
                $day = date('d', strtotime($date)); // '01'...'31'
               $details[$date] = [
    'status' => $status,
    'reg'    => isset($regular_hours[$personnelID][$date]) ? (float)$regular_hours[$personnelID][$date] : 0,
    'ot'     => isset($overtime_hours[$personnelID][$date]) ? (float)$overtime_hours[$personnelID][$date] : 0,
];
            }
        }

        // ✅ Save the selected range inside the JSON (as _range key)
        $details['_range'] = [
            'from' => $from,
            'to' => $to,
        ];

        // Save (insert or update)
        $this->MonthlyPayroll_model->save_payroll_monthly($personnelID, $month, $details);
    }

    $this->session->set_flashdata('msg', '✅ Monthly payroll saved successfully!');
    $this->session->set_userdata('payroll_month', $month);

    // Append ?saved=true to the referer so we can detect success later
    $redirect_url = $_SERVER['HTTP_REFERER'];
    $redirect_url .= (strpos($redirect_url, '?') === false) ? '?saved=true' : '&saved=true';

    redirect($redirect_url);
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

// ✅ Use fallback if not posted
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


    // Save range to session (optional)
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
    $payroll_month = $this->input->post('payroll_month'); // YYYY-MM
    $dayParam      = $this->input->post('day');  // '01'..'31' (legacy)
    $dateParam     = $this->input->post('date'); // 'YYYY-MM-DD' (new)
    $status        = $this->input->post('status');
    $reg           = (float)$this->input->post('reg');
    $ot            = (float)$this->input->post('ot');

    // Resolve a full date key (prefer 'date', else derive from month+day)
    if ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
        $fullDate = $dateParam;
        $dayKey   = date('d', strtotime($fullDate)); // '01'..'31'
    } else {
        // fallback: build from month + day (legacy)
        $ym = $payroll_month ?: date('Y-m');
        $dayKey = str_pad($dayParam ?: '01', 2, '0', STR_PAD_LEFT);
        $fullDate = $ym . '-' . $dayKey; // YYYY-MM-DD
    }

    // Fetch existing details
    $row = $this->MonthlyPayroll_model->get_payroll_record($personnelID, $payroll_month);
    if ($row) {
        $details = json_decode($row->details_json, true);
        if (!is_array($details)) $details = [];
    } else {
        $details = [];
    }

    // Write both keys: full-date (new) and day-only (legacy) to avoid breaking anything
    $payload = ['status' => $status, 'reg' => $reg, 'ot' => $ot];

    $details[$fullDate] = $payload; // new canonical key
    $details[ltrim($dayKey, '0')] = $payload; // '1'..'31' legacy loose
    $details[$dayKey] = $payload;             // '01'..'31' legacy strict

    // Save back
    $this->MonthlyPayroll_model->update_payroll_details($personnelID, $payroll_month, $details);
    $this->session->set_flashdata('msg', 'Attendance updated!');

    redirect($_SERVER['HTTP_REFERER']);
}

public function view_formatted()
{
    $from = $this->input->get('start'); // YYYY-MM-DD
    $to = $this->input->get('end');     // YYYY-MM-DD

    if (!$from || !$to) {
        $this->session->set_flashdata('msg', 'Start and End date are required.');
        redirect('MonthlyPayroll');
        return;
    }

    $month = date('Y-m', strtotime($from)); // Extract just '2025-07'
    $data = $this->MonthlyPayroll_model->get_monthly_payroll_records($month, $from, $to);

    $data['start'] = $from;
    $data['end'] = $to;

    $projectID = $this->input->get('project_id');

    $this->load->model('Project_model');
    $this->load->model('SettingsModel');

    $project = $this->Project_model->get_project_by_id($projectID);
    $data['project'] = $project;

    $settingsID = $this->session->userdata('settingsID');
    $data['signatories'] = $this->SettingsModel->get_signatories($settingsID);
    $data['show_signatories'] = true;

    $data['is_summary'] = false;

    // Send to view
    if (!isset($data['personnel']) || !isset($data['dates']) || !isset($data['attendance'])) {
        $this->session->set_flashdata('msg', 'Missing data for view.');
        redirect('MonthlyPayroll');
        return;
    }

    $attendance_data = [];
    $this->load->database();

    foreach ($data['personnel'] as $p) {
        $pid = $p->personnelID;
        $p->reg_hours_per_day = [];
        $total_hours = 0;
        $total_ot = 0;
        $total_days = 0; // ✅ Added for fractional day computation

        foreach ($data['dates'] as $d) {
           $attn = $data['attendance'][$pid][$d] ?? ['status' => '', 'reg' => 0, 'ot' => 0];


            $status = $attn['status'];
            $reg = $attn['reg'];
            $ot  = $attn['ot'];

            $p->reg_hours_per_day[$d] = [
                'status' => $status,
                'hours' => $reg,
                'overtime_hours' => $ot,
                'holiday_hours' => 0
            ];

            $total_hours += $reg;
            $total_ot += $ot;

            // ✅ Add fractional day logic
            if ($reg > 0) {
                $total_days += $reg / 8;
            }
        }

        $rateType = strtolower($p->rateType ?? '');
        $rateAmount = floatval($p->rateAmount ?? 0);

        $actual_hours = $total_hours;
        $actual_ot = $total_ot;

        // Compute based on working days selected (e.g., 15 for bi-month, 30 for full)
        $working_days = count($data['dates']); // Days shown in table
        $total_working_hours = $working_days * 8;
        if ($total_working_hours == 0) $total_working_hours = 1; // avoid division by zero

        if ($rateType === 'month') {
            $hourlyRate = $rateAmount / $total_working_hours;

            $amount_reg = $actual_hours * $hourlyRate;
            $amount_ot = $actual_ot * $hourlyRate * 1.25; // 25% OT bonus

            $p->rate_per_hour = number_format($hourlyRate, 2);
            $p->amount_reg = number_format($amount_reg, 2);
            $p->amount_ot = number_format($amount_ot, 2);
            $p->gross = number_format($amount_reg + $amount_ot, 2);

        } elseif ($rateType === 'bi-month') {
            $monthlyAmount = $rateAmount * 2;
            $hourlyRate = $monthlyAmount / $total_working_hours;

            $amount_reg = $actual_hours * $hourlyRate;
            $amount_ot = $actual_ot * $hourlyRate * 1.25;

            $p->rate_per_hour = number_format($hourlyRate, 2);
            $p->amount_reg = number_format($amount_reg, 2);
            $p->amount_ot = number_format($amount_ot, 2);
            $p->gross = number_format($amount_reg + $amount_ot, 2);

        } else {
            continue;
        }

        // ✅ Add total days to the personnel object
        $p->total_days = number_format($total_days, 2);

        // Load deductions using month part of $from
        $month = date('Y-m', strtotime($from));

        $this->load->model('MonthlyPayroll_model');
        $cashadvance = $this->MonthlyPayroll_model->get_cash_advance($pid, $month);
        $gov_deductions = $this->MonthlyPayroll_model->get_government_deductions($pid, $month);
        $loan = $this->MonthlyPayroll_model->get_loan_deduction($pid, $month);

        $p->cashadvance = number_format($cashadvance, 2);
        $p->sss = number_format($gov_deductions['sss'], 2);
        $p->pagibig = number_format($gov_deductions['pagibig'], 2);
        $p->philhealth = number_format($gov_deductions['philhealth'], 2);
        $p->loan = number_format($loan, 2);

        $total_deductions = $cashadvance + $gov_deductions['sss'] + $gov_deductions['pagibig'] + $gov_deductions['philhealth'] + $loan;
        $p->total_deduction = number_format($total_deductions, 2);
        $p->takehome = number_format(floatval($p->gross) - $total_deductions, 2);

        $attendance_data[] = $p;
    }

    $data['attendance_data'] = $attendance_data;

    $this->load->view('monthly_payroll_view', $data);
}

}
