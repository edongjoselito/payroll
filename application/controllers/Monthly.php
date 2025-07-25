<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monthly extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Monthly_model');
        $this->load->library('user_agent');

    }

    public function index() {
        $settingsID = $this->session->userdata('settingsID');
        $month = $this->input->get('month') ?? date('Y-m');

        $data['title'] = "Monthly Attendance";
        $data['month'] = $month;
        $data['personnel'] = $this->Monthly_model->get_personnel($settingsID);

        $this->load->view('monthly_attendance_view', $data);
    }

    public function save() {
        $this->Monthly_model->save_monthly_attendance($this->input->post());
        $this->session->set_flashdata('success', 'Attendance saved.');
        redirect('Monthly');
    }
    public function records()
{
    $data['title'] = "View Monthly Attendance";
    $month = $this->input->get('month') ?? date('Y-m');
    $settingsID = $this->session->userdata('settingsID');

    $data['month'] = $month;
    $data['daysInMonth'] = date('t', strtotime("$month-01"));
    $data['personnel'] = $this->Monthly_model->get_all_personnel($settingsID);
    $data['records'] = $this->Monthly_model->get_monthly_records($month, $settingsID);

    $this->load->view('monthly_attendance_records', $data);
}
 // 🔁 Update single record
    public function update()
    {
        $personnelID = $this->input->post('personnelID');
        $date = $this->input->post('date');
        $status = $this->input->post('status');
        $reg = floatval($this->input->post('reg') ?? 0);
        $holiday = floatval($this->input->post('holiday') ?? 0);
        $ot = floatval($this->input->post('ot') ?? 0);

        $result = $this->Monthly_model->updateAttendance($personnelID, $date, [
            'status' => $status,
            'reg' => $reg,
            'holiday' => $holiday,
            'ot' => $ot
        ]);

        if ($result) {
            $this->session->set_flashdata('update_success', 'Attendance updated successfully.');
        } else {
            $this->session->set_flashdata('update_success', 'Failed to update attendance.');
        }

        redirect($this->agent->referrer());
    }

    // ❌ Delete all entries in selected month
    public function delete()
    {
        $month = $this->input->post('month');
        $deleted = $this->Monthly_model->deleteMonth($month);

        if ($deleted) {
            $this->session->set_flashdata('update_success', 'Attendance for ' . date('F Y', strtotime($month)) . ' deleted.');
        } else {
            $this->session->set_flashdata('update_success', 'No records found to delete.');
        }

        redirect($this->agent->referrer());
    }
}
