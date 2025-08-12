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
    $period = $this->input->get('period');      // jan-jun | jul-dec | full
    $year   = $this->input->get('year') ?: date('Y');

    // build a date range from period+year
    if ($period === 'jan-jun') {
        $start = "$year-01-01"; $end = "$year-06-30";
    } elseif ($period === 'jul-dec') {
        $start = "$year-07-01"; $end = "$year-12-31";
    } else {
        $start = "$year-01-01"; $end = "$year-12-31";
    }

    // 👉 pull from finalized payroll (see Model below)
    $data['payroll_data']    = $this->Thirteenth_model->get_13th_from_payroll($start, $end);
    $data['selected_period'] = $period ?: 'full';
    $data['year']            = $year;

    $this->load->view('thirteenth_month', $data);
}


}
