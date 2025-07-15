<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Generatepayroll_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }
public function getProjectsBySettingsID($settingsID)
{
    return $this->db
        ->select('projectID, projectTitle')
        ->from('project')
        ->where('settingsID', $settingsID)
        ->get()
        ->result();
}
public function get_saved_payroll_batches($settingsID)
{
    return $this->db
        ->select('payroll_summary.projectID, payroll_summary.start_date, payroll_summary.end_date, project.projectTitle')
        ->from('payroll_summary')
        ->join('project', 'payroll_summary.projectID = project.projectID')
        ->where('payroll_summary.settingsID', $settingsID)
        ->group_by(['payroll_summary.projectID', 'payroll_summary.start_date', 'payroll_summary.end_date'])
        ->order_by('payroll_summary.start_date', 'DESC')
        ->get()
        ->result();
}

}
