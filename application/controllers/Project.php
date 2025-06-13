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
    $data['settingsID'] = $this->session->userdata('settingsID'); // get from session
    $this->Project_model->insert($data);
    redirect('Project/project_view');
}

public function update()
{
    $data = $this->input->post();
    $data['settingsID'] = $this->session->userdata('settingsID'); // update with current user
    $this->Project_model->update($data);
    redirect('Project/project_view');
}

    public function delete($id) {
        $this->Project_model->delete($id);
        redirect('project/project_view');
    }
}
