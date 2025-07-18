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
    $start = $this->input->post('start');
    $end = $this->input->post('end');

    $data['start'] = $start;
    $data['end'] = $end;
    $data['records'] = $this->Overtime_model->get_saved_overtime_batch($projectID, $start, $end);

    $this->load->view('saved_overtime_display', $data);
}

public function loadSavedOvertimeView()
{
    $projectID = $this->input->post('projectID');
    $start = $this->input->post('start');
    $end = $this->input->post('end');

    if (!$projectID || !$start || !$end) {
        show_error("Missing data: projectID=$projectID, start=$start, end=$end", 500);
        return;
    }

    // 🔍 DEBUG
    log_message('debug', "🔁 loadSavedOvertimeView: projectID=$projectID, start=$start, end=$end");

    $data['start'] = $start;
    $data['end'] = $end;
    $data['records'] = $this->Overtime_model->getSavedOvertime($projectID, $start, $end);

    $this->load->view('saved_overtime_display', $data);
}


 

public function get_dates_by_project()
{
    $projectID = $this->input->post('projectID');
    $dates = $this->Overtime_model->get_saved_dates($projectID);

    // Group dates into weekly ranges (e.g. 2025-07-01 to 2025-07-05)
    $grouped = [];
    $currentBatch = [];

    foreach ($dates as $i => $d) {
        $currentBatch[] = $d->date;

        // Every 5 days or end of list
        if (count($currentBatch) == 5 || $i == count($dates) - 1) {
            $start = min($currentBatch);
            $end = max($currentBatch);
            echo '<option value="' . $start . '|' . $end . '">' . date('F d', strtotime($start)) . ' - ' . date('d, Y', strtotime($end)) . '</option>';
            $currentBatch = [];
        }
    }
}
public function delete_overtime()
{
    $ids = explode(',', $this->input->post('id'));

    // 🐞 DEBUG OUTPUT
    log_message('debug', 'Deleting overtime entries for IDs: ' . json_encode($ids));

    foreach ($ids as $id) {
        $this->db->delete('overtime', ['id' => $id]);
    }

    echo json_encode(['status' => 'success']);
}





}
