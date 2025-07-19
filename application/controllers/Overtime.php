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

    // 🛑 Check for existing batch before loading the form
    $batch_id = $start . '_' . $end;

  $project = $this->Project_model->get_project_by_id($projectID);
if ($this->Overtime_model->isBatchAlreadyGenerated($projectID, $batch_id)) {
    echo json_encode([
        'status' => 'duplicate',
        'message' => 'Overtime already exists for this project and date range.',
        'projectID' => $projectID,
        'projectTitle' => $project->projectTitle ?? 'N/A',
        'start' => $start,
        'end' => $end
    ]);
    return;
}


    $data['project'] = $this->Project_model->get_project_by_id($projectID);
    $data['personnel'] = $this->Overtime_model->get_personnel_by_project($projectID);
    $data['start'] = $start;
    $data['end'] = $end;

    $this->load->view('generated_overtime_form', $data);
}

   public function save_overtime() {
    $post = $this->input->post();
    $projectID = $post['projectID'];
    $batch_id = $post['batch_id']; // Make sure this exists in your form data

    // 🛑 Prevent duplicate batch save
    if ($this->Overtime_model->isBatchAlreadyGenerated($projectID, $batch_id)) {
        $this->session->set_flashdata('error', '❌ This batch has already been generated for the selected project.');
        redirect('Overtime');
        return;
    }

    // ✅ Proceed if unique
    $this->Overtime_model->save_overtime($post);
    $this->session->set_flashdata('success', 'Overtime saved successfully.');
    $this->session->set_flashdata('open_modal', 'viewModal');
    redirect('Overtime');
}


public function view_saved_overtime()
{
    $projectID = $this->input->post('projectID');
    $start = $this->input->post('start');
    $end = $this->input->post('end');

    if (!$projectID || !$start || !$end) {
        show_error("Missing required POST data.", 500);
        return;
    }

    $data['start'] = $start;
    $data['end'] = $end;
    $data['projectID'] = $projectID; // used to repopulate dropdowns if needed
    $data['records'] = $this->Overtime_model->getSavedOvertime($projectID, $start, $end);

    if ($this->input->is_ajax_request()) {
        $this->load->view('saved_overtime_display', $data); // ✅ AJAX view only
    } else {
        // ✅ Full layout request → preload projects + flash content
        $settingsID = $this->session->userdata('settingsID'); 
        $data['projects'] = $this->Project_model->getAll($settingsID);

        // Store preload flag and content to session
        $this->session->set_flashdata('saved_overtime_block', $this->load->view('saved_overtime_display', $data, true));
        $this->session->set_flashdata('open_modal', 'viewModal');

        redirect('Overtime');
    }
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

    $data['start'] = $start;
    $data['end'] = $end;
    $data['records'] = $this->Overtime_model->getSavedOvertime($projectID, $start, $end);

    $this->load->view('saved_overtime_display', $data);
}



 

public function get_dates_by_project()
{
    $projectID = $this->input->post('projectID');
    $batches = $this->Overtime_model->get_saved_dates($projectID);

    foreach ($batches as $batch) {
        // ✅ Check if batch_id is formatted as start_end
        if (!empty($batch->batch_id) && strpos($batch->batch_id, '_') !== false) {
            list($start, $end) = explode('_', $batch->batch_id);
            
            // ✅ Format dates safely
            if (strtotime($start) && strtotime($end)) {
                echo '<option value="' . $start . '|' . $end . '">' .
                     date('F d', strtotime($start)) . ' - ' . date('d, Y', strtotime($end)) .
                     '</option>';
            }
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
