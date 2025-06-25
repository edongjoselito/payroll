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

    // Generate payroll as usual
    $payroll_data = $this->Payroll_model->generate_payroll($dateFrom, $dateTo, $cutoff);

    // Auto-deduct cash advances due this cutoff
    $this->load->model('Cashadvance_model');
    $due_advances = $this->Cashadvance_model->get_due_cash_advances($cutoff, $settingsID);

    foreach ($due_advances as $advance) {
        // Mark as deducted
        $this->Cashadvance_model->mark_cash_advance_deducted($advance->id);

        // Optional: Save as payroll deduction record
        $this->db->insert('payroll_deductions', [
            'personnelID' => $advance->personnelID,
            'amount' => $advance->amount,
            'description' => 'Cash Advance',
            'cutoff' => $cutoff,
            'deducted_on' => date('Y-m-d'),
            'settingsID' => $settingsID
        ]);
    }

    // Load results view
    $this->load->view('payroll/result', ['payroll' => $payroll_data]);
}


    public function save() {
        $payroll = $this->input->post('payroll'); // assume it's an array
        $this->Payroll_model->save_payroll($payroll);
        $this->session->set_flashdata('success', 'Payroll saved successfully!');
        redirect('Payroll');
    }
}
