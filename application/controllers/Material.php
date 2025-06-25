<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Material extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Material_model');
        $this->load->helper('url');
        $this->load->library('session');

        // Optional: Ensure only logged-in users can access
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index() {
        $settingsID = $this->session->userdata('settingsID');
        $data['material_loans'] = $this->Material_model->get_material_loans($settingsID);
        $data['personnel_list'] = $this->Material_model->get_personnel($settingsID);

        $this->load->view('materials_loan_view', $data);
    }

    public function save() {
        $this->Material_model->save_material_loan($this->input->post());
        $this->session->set_flashdata('success', 'Material loan saved successfully!');
        redirect('Material');
    }

    public function delete($id) {
        $this->Material_model->delete_material_loan($id);
        $this->session->set_flashdata('success', 'Material loan deleted successfully!');
        redirect('Material');
    }
}
