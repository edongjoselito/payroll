<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Company_model');
    }

    public function edit() {
        $data['company'] = $this->Company_model->get_company_info();
        $this->load->view('includes/header');
        $this->load->view('includes/topbar');
        $this->load->view('includes/sidebar');
        $this->load->view('company_edit', $data);
        $this->load->view('includes/footer');
    }

    public function update() {
        $this->Company_model->update_company_info($this->input->post());
        $this->session->set_flashdata('success', 'Company information updated successfully.');
        redirect('Company/edit');
    }
}
