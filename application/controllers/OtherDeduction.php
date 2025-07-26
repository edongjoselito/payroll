<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OtherDeduction extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('OtherDeduction_model');
        $this->load->helper('url');
        $this->load->library('session');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index() {
        $settingsID = $this->session->userdata('settingsID');
        $data['material_loans'] = $this->OtherDeduction_model->get_other_deductions($settingsID);
        $data['personnel_list'] = $this->OtherDeduction_model->get_personnel($settingsID);

        $this->load->view('materials_loan_view', $data); // You can rename this view if desired
    }

    public function save() {
        $this->OtherDeduction_model->save_other_deduction($this->input->post());
        $this->session->set_flashdata('success', 'Other deduction saved successfully!');
        redirect('OtherDeduction');
    }

    public function delete($id) {
        $this->OtherDeduction_model->delete_other_deduction($id);
        $this->session->set_flashdata('success', 'Other deduction deleted successfully!');
        redirect('OtherDeduction');
    }
 public function summary()
{
    $settingsID = $this->session->userdata('settingsID');
    $this->load->model('OtherDeduction_model');
    $data['summary'] = $this->OtherDeduction_model->get_all_deductions($settingsID);
    $this->load->view('deduction_summary_view', $data); // updated filename
}
public function loan_summary()
{
    $settingsID = $this->session->userdata('settingsID');
    $this->load->model('OtherDeduction_model');
    $data['summary'] = $this->OtherDeduction_model->get_loan_summary($settingsID);
    $this->load->view('LoanSummary', $data);
}
public function attendance_summary()
{
    $settingsID = $this->session->userdata('settingsID');
    $this->load->model('OtherDeduction_model');
    $data['summary'] = $this->OtherDeduction_model->get_attendance_summary($settingsID);
    $this->load->view('AttendanceSummary', $data);
}
public function filter_attendance_summary()
{
    $type = $this->input->get('filter_type');
    $now = date('Y-m-d');
    $data['summary'] = $this->OtherDeduction_model->get_attendance_summary_filtered($type, $now);
    $this->load->view('AttendanceSummary', $data);
}


}
