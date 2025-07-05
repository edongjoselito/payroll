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

    // public function generate() {
    //     $cutoff = $this->input->post('cutoff');
    //     $dateFrom = $this->input->post('date_from');
    //     $dateTo = $this->input->post('date_to');

    //     $payroll_data = $this->Payroll_model->generate_payroll($dateFrom, $dateTo, $cutoff);
    //     $this->load->view('payroll/result', ['payroll' => $payroll_data]);
    // }
public function generate() {
    $cutoff = $this->input->post('cutoff');
    $dateFrom = $this->input->post('date_from');
    $dateTo = $this->input->post('date_to');
    $settingsID = $this->session->userdata('settingsID');

    // Generate payroll
    $payroll_data = $this->Payroll_model->generate_payroll($dateFrom, $dateTo, $cutoff);

    // Load Other Deductions (material type)
    $this->load->model('OtherDeduction_model');
    $other_deductions = $this->OtherDeduction_model->get_deductions_by_date_range($dateFrom, $dateTo, $settingsID);

    // Group other deductions by personnelID
    $groupedDeductions = [];
    foreach ($other_deductions as $deduction) {
        $pid = (int) $deduction->personnelID;
        if (!isset($groupedDeductions[$pid])) {
            $groupedDeductions[$pid] = 0;
        }
        $groupedDeductions[$pid] += $deduction->amount;
    }

    // Load Personnel Loan deductions
    $this->load->model('Loan_model');

    // ✅ Load total cash advance per personnel between dateFrom and dateTo
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

    // Inject deductions into payroll rows
    foreach ($payroll_data as &$row) {
        $pid = (int) $row->personnelID;

        // Other Deduction
        $row->other_deduction = $groupedDeductions[$pid] ?? 0;

        // Personnel Loan Deduction
        $loan = $this->Loan_model->get_personnel_loan($pid, $settingsID);
        $row->loan_deduction = $loan->monthly_deduction ?? 0;

        // ✅ Cash Advance Amount for display
        $row->cash_advance = $cashAdvanceMap[$pid] ?? 0;

        // Save loan deduction to payroll_deductions table if any
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

    // Auto-deduct Cash Advances (mark as deducted and log)
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

    // Render payroll result
    $this->load->view('payroll/result', ['payroll' => $payroll_data]);
}




    public function save() {
        $payroll = $this->input->post('payroll'); // assume it's an array
        $this->Payroll_model->save_payroll($payroll);
        $this->session->set_flashdata('success', 'Payroll saved successfully!');
        redirect('Payroll');
    }
}
