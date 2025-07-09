<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WeeklyAttendance extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('WeeklyAttendance_model');
        $this->load->helper(['form', 'url']);
    }

    public function index() {
        $data['projects'] = $this->WeeklyAttendance_model->getProjects();
        $this->load->view('weekly_attendance_input', $data);
    }

    public function generate() {
        $projectID = $this->input->post('project');
        $from = $this->input->post('from');
        $to = $this->input->post('to');

        // ✅ Get data
        $data['projects'] = $this->WeeklyAttendance_model->getProjects();
        $data['employees'] = $this->WeeklyAttendance_model->getEmployeesByProject($projectID); 
        $data['project'] = $this->WeeklyAttendance_model->getProjectById($projectID);
        $data['dates'] = $this->getDateRange($from, $to);
        $data['projectID'] = $projectID;
        $data['from'] = $from;
        $data['to'] = $to;

        $this->load->view('weekly_attendance_input', $data);
    }

    public function save() {
        $post = $this->input->post();
        $this->WeeklyAttendance_model->saveAttendance($post);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Attendance saved successfully. please go to View Attendance to view</div>');
        redirect('WeeklyAttendance');
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


//     DISPLAY SAVED ATTENDANCE
public function records() {
    $data['projects'] = $this->WeeklyAttendance_model->getProjects();

    if ($this->input->post()) {
        $projectID = $this->input->post('project');
        $from = $this->input->post('from');
        $to = $this->input->post('to');

        $data['project'] = $this->WeeklyAttendance_model->getProjectById($projectID);
        $data['dates'] = $this->getDateRange($from, $to);
        $data['attendances'] = $this->WeeklyAttendance_model->getAttendanceRecords($projectID, $from, $to);
        $data['hours'] = $this->WeeklyAttendance_model->getWorkHours($projectID, $from, $to);
    }

    $this->load->view('weekly_attendance_records', $data);
}

}
