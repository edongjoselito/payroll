<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payroll extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Library that does the SOA logic
        $this->load->library('PayrollService');

        // Session for settingsID
        $this->load->library('session');
    }

    /**
     * Landing page for "Payroll SOA"
     * URL:  http://localhost/payroll/index.php/Payroll
     */
    public function index()
    {
        // Simple form to choose cutoff & date range
        $this->load->view('payroll/soa_form');
    }

    /**
     * Handles the form submit from soa_form, calls PayrollService,
     * and shows the Statement of Account table.
     *
     * POST: cutoff, date_from, date_to
     */
    public function generate()
    {
        $cutoff   = $this->input->post('cutoff');
        $dateFrom = $this->input->post('date_from');
        $dateTo   = $this->input->post('date_to');

        // settingsID from session (same as system-wide)
        $settingsID = (int) $this->session->userdata('settingsID');

        if (!$cutoff || !$dateFrom || !$dateTo) {
            $this->session->set_flashdata('error', '<div class="alert alert-danger">Please fill in all fields.</div>');
            return redirect('Payroll');
        }

        if (!$settingsID) {
            $this->session->set_flashdata('error', '<div class="alert alert-danger">No active settingsID in session.</div>');
            return redirect('Payroll');
        }

        // Use the shared SOA service
        $rows = $this->payrollservice->generatePayroll($dateFrom, $dateTo, $cutoff, $settingsID);

        $this->load->view('payroll/result', [
            'payroll'  => $rows,
            'cutoff'   => $cutoff,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
        ]);
    }
}
