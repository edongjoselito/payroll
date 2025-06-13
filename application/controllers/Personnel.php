<?php
class Personnel extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Personnel_model');
    }

    public function manage() {
        $data['personnel'] = $this->Personnel_model->getAll();
        $this->load->view('personnel_list', $data); // use updated view filename
    }

    public function store() {
        $this->Personnel_model->insert($this->input->post());
        redirect('personnel/manage');
    }

    public function update() {
        $this->Personnel_model->update($this->input->post());
        redirect('personnel/manage');
    }

    public function delete($id) {
        $this->Personnel_model->delete($id);
        redirect('personnel/manage');
    }
}
