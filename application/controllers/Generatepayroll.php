<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Generatepayroll extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Generatepayroll_model');
        $this->load->model('Project_model');
    }

    public function form() {
        $data['projects'] = $this->Project_model->get_all_projects();
        $this->load->view('sidebar_generate_form', $data); // No subfolder
    }
}
