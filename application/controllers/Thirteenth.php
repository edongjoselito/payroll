<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Thirteenth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Thirteenth_model');
    }

   public function index()
{
    $period = $this->input->get('period');
    $data['selected_period'] = $period;
    $data['payroll_data'] = $this->Thirteenth_model->get_13th_month_data($period);
    $this->load->view('thirteenth_month', $data);
}

}
