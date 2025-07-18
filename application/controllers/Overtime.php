<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Overtime extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Overtime_model');
        $this->load->model('Project_model');
    }

 public function index()
{
    $settingsID = $this->session->userdata('settingsID'); 
    $data['projects'] = $this->Project_model->getAll($settingsID);
    $this->load->view('overtime_view', $data);
}


    public function generate_personnel() {
        $projectID = $this->input->post('projectID');
        $start = $this->input->post('start');
        $end = $this->input->post('end');

       $data['project'] = $this->Project_model->get_project_by_id($projectID);

        $data['personnel'] = $this->Overtime_model->get_personnel_by_project($projectID);
        $data['start'] = $start;
        $data['end'] = $end;

        $this->load->view('generated_overtime_form', $data);
    }

    public function save_overtime() {
        $this->Overtime_model->save_overtime($this->input->post());
        redirect('Overtime');
    }

    public function view_saved_overtime() {
        $projectID = $this->input->post('projectID');
        $date = $this->input->post('date');
        $data['entries'] = $this->Overtime_model->get_saved_overtime($projectID, $date);
        $this->load->view('saved_overtime_display', $data);
    }

 

public function get_dates_by_project()
{
    $projectID = $this->input->post('projectID');
    $dates = $this->Overtime_model->get_saved_dates($projectID);

    if (!empty($dates)) {
        foreach ($dates as $d) {
            echo '<option value="' . $d->date . '">' . date('F d, Y', strtotime($d->date)) . '</option>';
        }
    } else {
        echo '<option disabled>No saved dates found.</option>';
    }
}




}
