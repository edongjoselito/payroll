<?php
class Project extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('Project_model');
    }

    public function project_view() {
        $data['projects'] = $this->Project_model->getAll();
        $this->load->view('project_view', $data);
    }
public function store()
{
    $data = $this->input->post();
    $data['settingsID'] = $this->session->userdata('settingsID');

    if ($this->Project_model->insert($data)) {
        $this->session->set_flashdata('success', 'Project successfully added.');
    } else {
        $this->session->set_flashdata('error', 'Failed to add project.');
    }

    redirect('project/project_view');
}

public function update()
{
    $data = $this->input->post();
    $data['settingsID'] = $this->session->userdata('settingsID');

    if ($this->Project_model->update($data)) {
        $this->session->set_flashdata('success', 'Project successfully updated.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update project.');
    }

    redirect('project/project_view');
}

public function delete($id)
{
    if ($this->Project_model->delete($id)) {
        $this->session->set_flashdata('success', 'Project successfully deleted.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete project.');
    }

    redirect('project/project_view');
}





public function attendance($settingsID) {
    $data['settingsID'] = $settingsID;
    $data['personnels'] = $this->Project_model->getPersonnelBySettingsID($settingsID);
    $data['project'] = $this->Project_model->getProjectBySettingsID($settingsID);
    $data['attendance_date'] = date('Y-m-d');
    $this->load->view('attendance_view', $data);
}

public function save_attendance() {
    $settingsID         = $this->input->post('settingsID');
    $attendance_date    = $this->input->post('attendance_date');
    $attendance_status  = $this->input->post('attendance_status');

    $batchData = [];

    foreach ($attendance_status as $personnelID => $status) {
        $batchData[] = [
            'personnelID'       => $personnelID,
            'settingsID'        => $settingsID,
            'attendance_date'   => $attendance_date,
            'attendance_status' => $status
        ];
    }

    $this->Project_model->save_batch_attendance($attendance_date, $batchData);

    $this->session->set_flashdata('success', 'Attendance saved successfully.');
    redirect('project/attendance/'.$settingsID);
}

}
