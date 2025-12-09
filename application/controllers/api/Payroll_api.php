<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payroll_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session'); // only session here
    }

    public function ping()
    {
        // simple test endpoint, no PayrollService here
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'ok'      => true,
                'message' => 'Payroll_api is alive'
            ]));
    }

    public function generate()
    {
        // Load the service only here (not in __construct)
        $this->load->library('PayrollService');

        // Read JSON or form-data
        $payload = json_decode($this->input->raw_input_stream, true);
        if (!is_array($payload) || empty($payload)) {
            $payload = $this->input->post(NULL, TRUE) ?? [];
        }

        $dateFrom   = $payload['dateFrom'] ?? null;
        $dateTo     = $payload['dateTo'] ?? null;
        $cutoff     = $payload['cutoff'] ?? null;

        // Read from session first
        $settingsID = (int) $this->session->userdata('settingsID');

        // 🔹 For API testing: allow override via body
        if (!$settingsID && !empty($payload['settingsID'])) {
            $settingsID = (int) $payload['settingsID'];

            // 🔹 Make it available to models that rely on session
            $this->session->set_userdata('settingsID', $settingsID);
        }

        if (!$dateFrom || !$dateTo || !$cutoff || !$settingsID) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'ok'    => false,
                    'error' => 'Missing required fields: dateFrom, dateTo, cutoff, or settingsID.'
                ]));
        }

        try {
            $payroll_data = $this->payrollservice
                ->generatePayroll($dateFrom, $dateTo, $cutoff, $settingsID);

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'ok'       => true,
                    'cutoff'   => $cutoff,
                    'dateFrom' => $dateFrom,
                    'dateTo'   => $dateTo,
                    'rows'     => $payroll_data,
                ]));
        } catch (Throwable $e) {
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'ok'    => false,
                    'error' => $e->getMessage(),
                ]));
        }
    }
}
