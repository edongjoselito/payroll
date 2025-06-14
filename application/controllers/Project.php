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





public function attendance($settingsID)
{
    $projectID = $this->input->get('pid');

    $data['settingsID'] = $settingsID;
    $data['projectID'] = $projectID;
    $data['attendance_date'] = date('Y-m-d');
    $data['personnels'] = $this->Project_model->getAssignedPersonnel($settingsID, $projectID);
    $data['project'] = $this->Project_model->getProjectBySettingsID($settingsID);
    $data['attendance_records'] = $this->Project_model->getAttendanceBySettingsID($settingsID, $projectID, date('Y-m-d'));

    $this->load->view('attendance_view', $data);
}


public function save_attendance()
{
    $settingsID         = $this->input->post('settingsID');
    $projectID          = $this->input->post('projectID');
    $attendance_date    = $this->input->post('attendance_date');
    $attendance_status  = $this->input->post('attendance_status');

    $batchData = [];

    foreach ($attendance_status as $personnelID => $status) {
        $batchData[] = [
            'personnelID'       => $personnelID,
            'settingsID'        => $settingsID,
            'projectID'         => $projectID,
            'attendance_date'   => $attendance_date,
            'attendance_status' => $status
        ];
    }

    $this->Project_model->save_batch_attendance($attendance_date, $batchData);

    $this->session->set_flashdata('success', 'Attendance saved successfully.');
    redirect('project/attendance/' . $settingsID . '?pid=' . $projectID);
}








public function assign_personnel($settingsID, $projectID)
{
    $this->load->model('Project_model');

    $data['settingsID'] = $settingsID;
    $data['projectID'] = $projectID;
    $data['personnel'] = $this->Project_model->get_all_personnel();
    $data['assignments'] = $this->Project_model->get_assignments_by_project($projectID);
    $data['project'] = $this->Project_model->getProjectBySettingsID($settingsID);


    $this->load->view('assign_personnel', $data);
}

public function save_assignment()
{
    $this->load->model('Project_model');

    $settingsID = $this->input->post('settingsID');
    $projectID = $this->input->post('projectID');
    $personnelID = $this->input->post('personnelID');

    // Check if already assigned
    $exists = $this->Project_model->check_assignment_exists($settingsID, $projectID, $personnelID);

    if ($exists) {
        $this->session->set_flashdata('error', 'This personnel is already assigned to this project.');
    } else {
        $data = [
            'settingsID' => $settingsID,
            'projectID' => $projectID,
            'personnelID' => $personnelID
        ];
        $this->Project_model->assign_personnel($data);
        $this->session->set_flashdata('success', 'Personnel assigned successfully.');
    }

    redirect('project/assign_personnel/' . $settingsID . '/' . $projectID);
}

public function delete_assignment($ppID, $settingsID, $projectID)
{
    $this->load->model('Project_model');
    $this->Project_model->delete_assignment($ppID);
    $this->session->set_flashdata('success', 'Assignment deleted.');
    redirect("project/assign_personnel/$settingsID/$projectID");
}


}
