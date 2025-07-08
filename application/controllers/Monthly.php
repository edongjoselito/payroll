<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monthly extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Monthly_model');
    }

    public function index()
    {
        $month = $this->input->get('month') ?? date('Y-m');

        $data['month'] = $month;
        $data['attendance'] = $this->Monthly_model->getMonthlyAttendance($month);
        $data['salaries'] = $this->Monthly_model->calculateMonthlySalary($month);

        $this->load->view('monthly_attendance_view', $data);
    }


public function officeAttendance()
{
    $date = $this->input->get('date') ?? date('Y-m-d');
    $data['date'] = $date;

    $data['personnel'] = $this->Monthly_model->getOfficePersonnel();
    $data['existing'] = $this->Monthly_model->getAttendanceByDate($date);
    $data['present_personnel'] = $this->Monthly_model->getPresentAttendanceByDate($date);

    $this->load->view('office_attendance_view', $data);
}

public function saveOfficeAttendance()
{
    $this->load->model('Monthly_model');
    $attendance = $this->input->post('attendance');
   $date = $this->input->post('attendance_date');

    $settingsID = $this->session->userdata('settingsID');

    foreach ($attendance as $personnelID => $row) {
        $status = $row['status'];
        $duration = $row['duration'];

        // Check if already exists
        $exists = $this->db->get_where('personnelattendance', [
            'personnelID' => $personnelID,
            'attendance_date' => $date,
            'settingsID' => $settingsID
        ])->row();

        if ($exists) {
            $this->db->where('attendanceID', $exists->attendanceID)->update('personnelattendance', [
                'attendance_status' => $status,
                'workDuration' => $duration
            ]);
        } else {
            $this->db->insert('personnelattendance', [
                'personnelID' => $personnelID,
                'settingsID' => $settingsID,
                'attendance_date' => $date,
                'attendance_status' => $status,
                'workDuration' => $duration
            ]);
        }
    }

    $this->session->set_flashdata('success', 'Attendance saved successfully!');
    redirect('Monthly/officeAttendance?date=' . $date);
}
public function presentPersonnel()
{
    $this->load->model('Monthly_model');

    $date = $this->input->get('date') ?? date('Y-m-d');
    $data['date'] = $date;

    $data['present_personnel'] = $this->Monthly_model->getPresentPersonnelByDate($date);

    $this->load->view('present_personnel_view', $data);
}

}
