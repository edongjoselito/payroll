<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Overtime extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Overtime_model');
    }

    public function generate() {
        $SY = $this->session->userdata('sy');
        $result = $this->Overtime_model->generateOvertimeOnly($SY);

        $this->session->set_flashdata('message', $result ? 'Overtime successfully generated!' : 'No new overtime entries.');
        redirect('WeeklyAttendance');
    }
 public function show_overtime_form() {
    $projectID = $this->input->post('projectID');
    $from = $this->input->post('from');
    $to = $this->input->post('to');

    $this->load->model('Overtime_model');
    $personnel = $this->Overtime_model->get_personnel_by_project($projectID, $from, $to);

    $dates = [];
    $start = strtotime($from);
    $end = strtotime($to);
    while ($start <= $end) {
        $dates[] = date('Y-m-d', $start);
        $start = strtotime('+1 day', $start);
    }

    $data = [
        'projectID' => $projectID,
        'from' => $from,
        'to' => $to,
        'dates' => $dates,
        'personnel' => $personnel
    ];

    $this->load->view('overtime_manual_input', $data);
}
public function save_overtime()
{
    $projectID = $this->input->post('projectID');
    $from = $this->input->post('from');
    $to = $this->input->post('to');
    $settingsID = $this->session->userdata('settingsID');
    $overtimeData = $this->input->post('overtime'); // structured as [personnelID][date] => hours

    $successCount = 0;

    foreach ($overtimeData as $personnelID => $dates) {
        foreach ($dates as $date => $hours) {
            $hours = floatval($hours);
            if ($hours > 0) {
                // Check if already exists (based on personnel, date, project)
                $existing = $this->db->get_where('attendance', [
                    'personnelID' => $personnelID,
                    'projectID'   => $projectID,
                    'date'        => $date,
                    'status'      => 'Overtime',
                    'settingsID'  => $settingsID
                ])->row();

                if ($existing) {
                    // Update only work_duration
                    $this->db->where('id', $existing->id);
                    $this->db->update('attendance', [
                        'work_duration' => $hours
                    ]);
                } else {
                    // Insert new overtime record
                    $this->db->insert('attendance', [
                        'personnelID'   => $personnelID,
                        'projectID'     => $projectID,
                        'date'          => $date,
                        'status'        => 'Overtime',
                        'work_duration' => $hours,
                        'holiday_hours' => 0,
                        'settingsID'    => $settingsID
                    ]);
                }

                $successCount++;
            }
        }
    }

    $this->session->set_flashdata('msg', $successCount > 0 ? '✅ Overtime saved successfully!' : '⚠ No valid overtime entries were provided.');
    redirect('WeeklyAttendance');
}


}
?>
