<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Report_model');
        $this->load->library('session');
    }

public function payroll_logs()
{
    $this->load->model('Report_model'); // ensure this is loaded
    $data['logs'] = $this->Report_model->get_payroll_logs();
    $this->load->view('payroll_logs_view', $data);
}



    public function save_log() {
        $this->Report_model->insert_log($this->input->post());
        $this->session->set_flashdata('success', 'Payroll log saved successfully!');
        redirect('Report/payroll_logs');
    }
}
