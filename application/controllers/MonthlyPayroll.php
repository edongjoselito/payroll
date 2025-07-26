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
    $month = $this->input->post('payroll_month');
    if (!$month) {
        $this->session->set_flashdata('error', 'Please select a valid month.');
        redirect('WeeklyAttendance/records');
        return;
    }

    $settingsID = $this->session->userdata('settingsID');

    // Get only Bi-Month and Monthly personnel
    $personnel = $this->MonthlyPayroll_model->get_all_personnel($settingsID, ['Bi-Month', 'Monthly']);

    // Build days
    $year = (int)substr($month, 0, 4);
    $monthNum = (int)substr($month, 5, 2);
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
    $dates = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dates[] = sprintf('%04d-%02d-%02d', $year, $monthNum, $d);
    }

    $saved_months = $this->MonthlyPayroll_model->get_saved_months();

    $data = [
        'personnel' => $personnel,
        'dates' => $dates,
        'month' => $month,
        'saved_months' => $saved_months,
    ];

    $this->load->view('monthly_payroll_input', $data);
}



    // Save attendance/payroll data
   public function save()
{
    $month = $this->input->post('payroll_month');
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
                $details[$day] = [
                    'status' => $status,
                    'reg'    => isset($regular_hours[$personnelID][$date]) ? (float)$regular_hours[$personnelID][$date] : 0,
                    'ot'     => isset($overtime_hours[$personnelID][$date]) ? (float)$overtime_hours[$personnelID][$date] : 0,
                ];
            }
        }
        // Save (insert or update)
        $this->MonthlyPayroll_model->save_payroll_monthly($personnelID, $month, $details);
    }

    $this->session->set_flashdata('msg', 'Monthly payroll saved successfully!');
    $this->session->set_userdata('payroll_month', $month);
redirect('MonthlyPayroll/view_record');

}
public function view_record()
{
    $month = $this->input->post('payroll_month');
    if (!$month) {
        $this->session->set_flashdata('msg', 'Please select a month.');
        redirect('MonthlyPayroll');
    }

    // Get all payroll records for the selected month
    $records = $this->MonthlyPayroll_model->get_monthly_payroll_records($month);

    // Pass for display (you can use a modal, or a separate view)
    $data['month'] = $month;
    $data['records'] = $records;
    $data['saved_months'] = $this->MonthlyPayroll_model->get_saved_months(); // so dropdown still works in the view

    // Show on the same page (replace the grid with view, or display both as you wish)
   $this->load->view('monthly_payroll_records', $data);
}
public function update_attendance()
{
    $personnelID   = $this->input->post('personnelID');
    $payroll_month = $this->input->post('payroll_month');
    $day           = $this->input->post('day'); // '01', '02', etc.
    $status        = $this->input->post('status');
    $reg           = (float)$this->input->post('reg');
    $ot            = (float)$this->input->post('ot');

    // Get existing details_json
    $row = $this->MonthlyPayroll_model->get_payroll_record($personnelID, $payroll_month);
    if ($row) {
        $details = json_decode($row->details_json, true);
        if (!$details) $details = [];
        $details[$day] = [
            'status' => $status,
            'reg'    => $reg,
            'ot'     => $ot
        ];
        $this->MonthlyPayroll_model->update_payroll_details($personnelID, $payroll_month, $details);
        $this->session->set_flashdata('msg', 'Attendance updated!');
    } else {
        $this->session->set_flashdata('msg', 'Record not found.');
    }

    redirect('MonthlyPayroll/view_record?month=' . $payroll_month);

}

}
