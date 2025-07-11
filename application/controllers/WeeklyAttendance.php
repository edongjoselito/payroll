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

    $settingsID = $this->session->userdata('settingsID');
    $data['projects'] = $this->WeeklyAttendance_model->getProjects($settingsID);

    // ✅ Get project details early to use project title in flashdata
    $project = $this->WeeklyAttendance_model->getProjectById($projectID);

    // ✅ Check for existing attendance
    if ($this->WeeklyAttendance_model->attendanceExists($projectID, $from, $to)) {
        // Pass params via flashdata to allow view/delete options
        $this->session->set_flashdata('attendance_exists', [
            'projectID'     => $projectID,
            'from'          => $from,
            'to'            => $to,
            'projectTitle'  => $project ? $project->projectTitle : 'N/A'
        ]);
        redirect('WeeklyAttendance');
        return;
    }

    // Normal flow
    $data['employees'] = $this->WeeklyAttendance_model->getEmployeesByProject($projectID, $settingsID);
    $data['project'] = $project;
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


  public function records()
{
    $data['projects'] = $this->WeeklyAttendance_model->getProjects($this->session->userdata('settingsID'));

    // Support GET parameters
    $projectID = $this->input->get('project');
    $from = $this->input->get('from');
    $to = $this->input->get('to');

    // Fallback to POST
    if (!$projectID && $this->input->post()) {
        $projectID = $this->input->post('project');
        $from = $this->input->post('from');
        $to = $this->input->post('to');
    }

    if ($projectID && $from && $to) {
        // Check if attendance data exists
        if (!$this->WeeklyAttendance_model->attendanceExists($projectID, $from, $to)) {
            $this->session->set_flashdata('error', '❌ Attendance has not been generated for the selected date range. Please generate first');
            redirect('WeeklyAttendance/records');
            return;
        }

        // Load project and existing dates
        $existingDates = $this->WeeklyAttendance_model->getExistingAttendanceDates($projectID, $from, $to);

       if (empty($existingDates)) {
    // Get the latest available attendance range for this project
    $latestRange = $this->WeeklyAttendance_model->getLatestAttendanceRange($projectID);
    
    if ($latestRange) {
        // Redirect to latest available range
        $this->session->set_flashdata('error', '⚠ No attendance data for selected range. Showing most recent available record instead.');
        redirect('WeeklyAttendance/records?project=' . $projectID . '&from=' . $latestRange['from'] . '&to=' . $latestRange['to']);
    } else {
        // No data at all for this project
       $this->session->set_flashdata('error', '❌ No attendance has been generated for this project yet.');
redirect('WeeklyAttendance/records?project=' . $projectID . '&from=' . $from . '&to=' . $to);

    }
    return;
}


        // 🔔 Notify via modal if partial data
        $requestedDates = $this->getDateRange($from, $to);
        if (count($requestedDates) !== count($existingDates)) {
            $this->session->set_flashdata('view_error', true);
        }

        $data['project'] = $this->WeeklyAttendance_model->getProjectById($projectID);
        $data['dates'] = $existingDates;
        $data['attendances'] = $this->WeeklyAttendance_model->getAttendanceRecords($projectID, $from, $to, $existingDates);
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


private function convertToMinutes($input)
{
    $input = strval($input);
    $parts = explode('.', $input);
    $hours = (int) $parts[0];
    $minutes = isset($parts[1]) ? (int) str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : 0;

    if ($minutes > 59) {
        $minutes = 59;
    }

    return ($hours * 60) + $minutes;
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
