<?php
class Personnel extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Personnel_model');
    }
public function manage() {
    $settingsID = $this->session->userdata('settingsID');
    $data['personnel'] = $this->Personnel_model->getAll($settingsID);
    $this->load->view('personnel_list', $data);
}


public function store() {
    $data = $this->input->post();
    $data['settingsID'] = $this->session->userdata('settingsID');

    if ($this->Personnel_model->insert($data)) {
        $this->session->set_flashdata('success', 'Personnel successfully added.');
    } else {
        $this->session->set_flashdata('error', 'Failed to add personnel.');
    }

    redirect('personnel/manage');
}

public function update() {
    $data = $this->input->post();
    $data['settingsID'] = $this->session->userdata('settingsID');

    if ($this->Personnel_model->update($data)) {
        $this->session->set_flashdata('success', 'Personnel successfully updated.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update personnel.');
    }

    redirect('personnel/manage');
}

public function delete($personnelID) {
    if ($this->Personnel_model->delete($personnelID)) {
        $this->session->set_flashdata('success', 'Personnel successfully deleted.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete personnel.');
    }

    redirect('personnel/manage');
}








}
