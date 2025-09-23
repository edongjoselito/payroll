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
    $period     = $this->input->get('period'); 
    $year       = $this->input->get('year') ?: date('Y');

    $employment = $this->input->get('employment') ?: 'active';

    if ($period === 'jan-jun') {
        $start = "$year-01-01"; $end = "$year-06-30";
    } elseif ($period === 'jul-dec') {
        $start = "$year-07-01"; $end = "$year-12-31";
    } else {
        $period = 'full';
        $start = "$year-01-01"; $end = "$year-12-31";
    }

$showAdmins = ($this->input->get('type') === 'admin');

if ($showAdmins) {
    $data['payroll_data'] = $this->Thirteenth_model->get_13th_for_admins_from_pm($start, $end, $employment);
} else {
    $data['payroll_data'] = $this->Thirteenth_model->get_13th_from_attendance_reg_only($start, $end, $employment);
}
// Always compute 13th from full-year BASIC (YTD), regardless of the visible period
$yStart = $year . '-01-01';
$yEnd   = $year . '-12-31';

if ($showAdmins) {
    $ytdRows = $this->Thirteenth_model->get_13th_for_admins_from_pm($yStart, $yEnd, $employment);
} else {
    $ytdRows = $this->Thirteenth_model->get_13th_from_attendance_reg_only($yStart, $yEnd, $employment);
}

// Map: personnelID => basic_total (YTD)
$ytd_basic_map = [];
foreach ($ytdRows as $r) {
    $pid = (int)($r['personnelID'] ?? 0);
    $ytd_basic_map[$pid] = (float)($r['basic_total'] ?? 0.0);
}
$data['ytd_basic_map'] = $ytd_basic_map;

    $data['selected_period'] = $period;
    $data['year']            = $year;

    $data['employment']      = $employment;

    $this->load->view('thirteenth_month', $data);
}

}
