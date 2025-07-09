<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WeeklyAttendance extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('WeeklyAttendance_model');
        $this->load->helper(['form', 'url']);
    }

    public function index() {
       $data['projects'] = $this->WeeklyAttendance_model->getProjects($this->session->userdata('settingsID'));

        $this->load->view('weekly_attendance_input', $data);
    }

    public function generate() {
        $projectID = $this->input->post('project');
        $from = $this->input->post('from');
        $to = $this->input->post('to');

      $data['projects'] = $this->WeeklyAttendance_model->getProjects($this->session->userdata('settingsID'));

      $settingsID = $this->session->userdata('settingsID');
$data['employees'] = $this->WeeklyAttendance_model->getEmployeesByProject($projectID, $settingsID);

        $data['project'] = $this->WeeklyAttendance_model->getProjectById($projectID);
        $data['dates'] = $this->getDateRange($from, $to);
        $data['projectID'] = $projectID;
        $data['from'] = $from;
        $data['to'] = $to;

        $this->load->view('weekly_attendance_input', $data);
    }

   public function save() {
    $post = $this->input->post();
    
    $post['settingsID'] = $this->session->userdata('settingsID');

    $this->WeeklyAttendance_model->saveAttendance($post);

    $this->session->set_flashdata('msg', '<div class="alert alert-success">Weekly attendance has been saved successfully. Please go to View Attendance to view it.</div>');
    redirect('WeeklyAttendance');
}


   public function records() {
    $data['projects'] = $this->WeeklyAttendance_model->getProjects($this->session->userdata('settingsID'));

    if ($this->input->post()) {
        $projectID = $this->input->post('project');
        $from = $this->input->post('from');
        $to = $this->input->post('to');

        $data['project'] = $this->WeeklyAttendance_model->getProjectById($projectID);
        $data['dates'] = $this->getDateRange($from, $to);
        $data['attendances'] = $this->WeeklyAttendance_model->getAttendanceRecords($projectID, $from, $to);
        $data['hours'] = $this->WeeklyAttendance_model->getWorkHours($projectID, $from, $to);

        $data['projectID'] = $projectID;
        $data['from'] = $from;
        $data['to'] = $to;
    }

    $this->load->view('weekly_attendance_records', $data);
}


    private function getDateRange($from, $to) {
        $start = new DateTime($from);
        $end = new DateTime($to);
        $dates = [];

        while ($start <= $end) {
            $dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }

        return $dates;
    }






public function deleteAttendance()
{
    $projectID = $this->input->post('projectID');
    $from = $this->input->post('from');
    $to = $this->input->post('to');

    $this->load->model('WeeklyAttendance_model');
    $this->WeeklyAttendance_model->deleteAttendanceByDateRange($projectID, $from, $to);

    $this->session->set_flashdata('msg', 'Attendance for the selected range has been deleted successfully.');
    redirect('WeeklyAttendance');
}




}
