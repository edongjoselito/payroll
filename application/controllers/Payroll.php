<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Payroll_model');
        $this->load->model('Attendance_model');
        $this->load->model('Loan_model');
    }

    public function index() {
        // Load view for generating payroll (e.g., form to select cutoff, personnel)
        $data['personnel'] = $this->Payroll_model->get_all_personnel();
        $this->load->view('payroll/generate', $data);
    }
public function generate()
{
    $cutoff = $this->input->post('cutoff');
    $dateFrom = $this->input->post('date_from');
    $dateTo = $this->input->post('date_to');
    $settingsID = $this->session->userdata('settingsID');

    // Generate payroll
    $payroll_data = $this->Payroll_model->generate_payroll($dateFrom, $dateTo, $cutoff);

    // Load Other Deductions (e.g. materials/tools)
    $this->load->model('OtherDeduction_model');
    $other_deductions = $this->OtherDeduction_model->get_deductions_by_date_range($dateFrom, $dateTo, $settingsID);

    // Group other deductions by personnelID
    $groupedDeductions = [];
    foreach ($other_deductions as $deduction) {
        $pid = (int)$deduction->personnelID;
        if (!isset($groupedDeductions[$pid])) {
            $groupedDeductions[$pid] = 0;
        }
        $groupedDeductions[$pid] += $deduction->amount;
    }

    // Load Cash Advance and map totals
    $this->load->model('Cashadvance_model');
    $cash_advance_query = $this->db->select('personnelID, SUM(amount) as total_amount')
        ->from('cashadvance')
        ->where('settingsID', $settingsID)
        ->where('date >=', $dateFrom)
        ->where('date <=', $dateTo)
        ->group_by('personnelID')
        ->get()->result();

    $cashAdvanceMap = [];
    foreach ($cash_advance_query as $ca) {
        $cashAdvanceMap[(int)$ca->personnelID] = $ca->total_amount;
    }

    // ✅ Load Government Deductions per personnel
    $govt_deductions_query = $this->db->select('personnelID, description, amount')
        ->from('government_deductions')
        ->where('settingsID', $settingsID)
        ->where("(
            (deduct_from IS NULL OR deduct_from <= '$dateTo') AND 
            (deduct_to IS NULL OR deduct_to >= '$dateFrom')
        )")
        ->get()->result();

    $govtMap = [];
    foreach ($govt_deductions_query as $deduct) {
        $pid = (int)$deduct->personnelID;
        if (!isset($govtMap[$pid])) $govtMap[$pid] = [];
        $govtMap[$pid][$deduct->description] = $deduct->amount;
    }

    // Process each payroll row
    foreach ($payroll_data as &$row) {
        $pid = (int)$row->personnelID;

        // Other Deduction
        $row->other_deduction = $groupedDeductions[$pid] ?? 0;

        // Personnel Loan Deduction
        $loan = $this->Loan_model->get_personnel_loan($pid, $settingsID);
        $row->loan_deduction = $loan->monthly_deduction ?? 0;

        // Cash Advance
        $row->cash_advance = $cashAdvanceMap[$pid] ?? 0;

        // ✅ Government Deductions Breakdown
        $row->govt_sss = $govtMap[$pid]['SSS'] ?? 0;
        $row->govt_pagibig = $govtMap[$pid]['Pag-IBIG'] ?? 0;
        $row->govt_phic = $govtMap[$pid]['PhilHealth'] ?? 0;

        // ✅ Total of Gov't
        $row->govt_total_deduction = $row->govt_sss + $row->govt_pagibig + $row->govt_phic;

        // Save loan deduction into payroll_deductions
        if (!empty($row->loan_deduction)) {
            $this->db->insert('payroll_deductions', [
                'personnelID' => $pid,
                'amount' => $row->loan_deduction,
                'description' => 'Personnel Loan',
                'cutoff' => $cutoff,
                'deducted_on' => date('Y-m-d'),
                'settingsID' => $settingsID
            ]);
        }
    }

    // Auto-deduct due Cash Advances and save
    $due_advances = $this->Cashadvance_model->get_due_cash_advances($cutoff, $settingsID);
    foreach ($due_advances as $advance) {
        $this->Cashadvance_model->mark_cash_advance_deducted($advance->id);
        $this->db->insert('payroll_deductions', [
            'personnelID' => $advance->personnelID,
            'amount' => $advance->amount,
            'description' => 'Cash Advance',
            'cutoff' => $cutoff,
            'deducted_on' => date('Y-m-d'),
            'settingsID' => $settingsID
        ]);
    }

    // Load view with result
    $this->load->view('payroll/result', ['payroll' => $payroll_data]);
}




    public function save() {
        $payroll = $this->input->post('payroll'); // assume it's an array
        $this->Payroll_model->save_payroll($payroll);
        $this->session->set_flashdata('success', 'Payroll saved successfully!');
        redirect('Payroll');
    }



    // ----------NEW SIDEBAR

        public function generate_form()
{
    // Load your project list (for dropdown selection if needed)
    $this->load->model('Project_model');
    $data['projects'] = $this->Project_model->get_all_projects();

    $this->load->view('payroll/sidebar_generate_form', $data);
}



}
