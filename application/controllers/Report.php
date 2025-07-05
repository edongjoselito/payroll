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
    $this->load->model('Report_model');
    $data['logs'] = $this->Report_model->get_all_logs();
    $this->load->view('payroll_logs_view', $data);
}





   public function save_log() {
    $data = [
        'projectID'     => $this->input->post('projectID'),
        'settingsID'    => $this->session->userdata('settingsID'),
        'project_title' => $this->input->post('project_title'),
        'location'      => $this->input->post('location'),
        'period'        => $this->input->post('period'),
        'date_from'     => $this->input->post('date_from'),
        'date_to'       => $this->input->post('date_to'),
        'payroll_date'  => $this->input->post('payroll_date'),
        'total_gross'   => $this->input->post('total_gross'),
        'date_saved'    => date('Y-m-d H:i:s')
    ];

    $this->Report_model->insert_payroll_log($data);
    $this->session->set_flashdata('success', 'Payroll log saved successfully!');
    redirect('report/payroll_logs');
}


  public function delete_log($id)
{
    $this->load->model('Report_model');
    $this->Report_model->delete_log($id);
    $this->session->set_flashdata('success', 'Payroll log deleted successfully.');
    redirect('report/payroll_logs');
}


}
