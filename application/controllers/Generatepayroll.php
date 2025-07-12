<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Generatepayroll extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Generatepayroll_model');
        $this->load->model('Project_model');
    }

   public function form()
{
    $settingsID = $this->session->userdata('settingsID');
   $data['projects'] = $this->Generatepayroll_model->getProjectsBySettingsID($settingsID);
$data['attendance_periods'] = $this->Project_model->get_attendance_batches($settingsID); 
$this->load->view('sidebar_generate_form', $data);

}

}
