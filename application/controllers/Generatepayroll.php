<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Generatepayroll extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Generatepayroll_model');
        $this->load->model('Project_model');
         if (!in_array($this->session->userdata('level'), ['Admin','Payroll User'], true)) {
        $this->session->set_flashdata('error', 'Unauthorized access.');
        redirect('login');
        return;
    }
    }

public function form()
{
    $settingsID = $this->session->userdata('settingsID');

    $data['projects'] = $this->Generatepayroll_model->getProjectsBySettingsID($settingsID);
    $data['attendance_periods'] = $this->Project_model->get_attendance_batches($settingsID);
    $data['batches'] = $this->Generatepayroll_model->get_saved_payroll_batches($settingsID);

    // ✅ Add saved_months for the monthly modal ONLY
    $this->load->model('MonthlyPayroll_model');
    $data['saved_months'] = $this->MonthlyPayroll_model->get_saved_months();

    $this->load->view('sidebar_generate_form', $data);
}



}
