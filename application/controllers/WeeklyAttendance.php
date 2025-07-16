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
       $project = $this->db->get_where('project', ['projectID' => $post['projectID']])->row();

$this->session->set_flashdata('attendance_success', [
    'projectID' => $post['projectID'],
    'projectTitle' => $project->projectTitle ?? 'N/A',
    'from' => $post['from'],
    'to' => $post['to']
]);

    redirect('WeeklyAttendance');
}


public function records()
{
    $settingsID = $this->session->userdata('settingsID');

    $projectFilter = $this->input->get('project');
    $fromFilter = $this->input->get('from');
    $toFilter = $this->input->get('to');

    $data['attendance_periods'] = $this->WeeklyAttendance_model->getSavedBatches($settingsID);
    $data['projects'] = $this->WeeklyAttendance_model->getProjects($settingsID);
    $data['batches'] = [];

    foreach ($data['attendance_periods'] as $batch) {
        $projectID = $batch->projectID;
        $from = $batch->start;
        $to = $batch->end;
        $group_number = $batch->group_number;

        // Only include the filtered batch if filter is applied
        if ($projectFilter && $fromFilter && $toFilter) {
            if (
                $projectID != $projectFilter ||
                $from != $fromFilter ||
                $to != $toFilter
            ) {
                continue;
            }
        }

        $existingDates = $this->WeeklyAttendance_model->getExistingAttendanceDates($projectID, $from, $to, $group_number);
        // if (empty($existingDates)) continue;

        $raw = $this->WeeklyAttendance_model->getAttendanceRecords($projectID, $from, $to, $existingDates, $group_number);

        $attendances = [];
        foreach ($raw as $pid => $personData) {
            $attendances[$pid]['name'] = $personData['name'];
            foreach ($personData['dates'] as $date => $status) {
                $attendances[$pid]['dates'][$date] = $status;
                $attendances[$pid]['hours'][$date] = $personData['hours'][$date] ?? 0;
                $attendances[$pid]['holiday'][$date] = $personData['holiday'][$date] ?? 0;
            }
        }

        $data['batches'][] = [
            'projectID'     => $projectID,
            'group_number'  => $group_number,
            'from'          => $from,
            'to'            => $to,
            'dates'         => $existingDates,
            'attendances'   => $attendances,
            'project'       => $this->WeeklyAttendance_model->getProjectById($projectID)
        ];
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
    redirect('WeeklyAttendance/records');
}


public function updateAttendance()
{
    $personnelID = $this->input->post('personnelID');
    $date = $this->input->post('date');
    $status = $this->input->post('status');
    $hours = $this->input->post('hours');
    $holiday = $this->input->post('holiday');

    // Get current page context
    $projectID = $this->input->post('project');
    $from = $this->input->post('from');
    $to = $this->input->post('to');

    // Update attendance
    $this->db->where('personnelID', $personnelID);
    $this->db->where('date', $date);
    $this->db->update('attendance', [
        'status' => $status,
        'work_duration' => $hours,
        'holiday_hours' => $holiday
        
    ]);
$this->session->set_flashdata('update_success', 'Attendance updated successfully!');


$projectID = $this->input->post('projectID');
$from = $this->input->post('from');
$to = $this->input->post('to');

redirect("WeeklyAttendance/records?projectID={$projectID}&from={$from}&to={$to}");





}




}