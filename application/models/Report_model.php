<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model
{
    public function get_payroll_logs()
    {
        $this->db->select('
            pl.project_title,
            pl.location,
            pl.period,
            pl.date_from,
            pl.date_to,
            pl.payroll_date,
            pl.total_gross,
            pl.date_saved
        ');
        $this->db->from('payroll_logs pl');
        return $this->db->get()->result();
    }

    public function insert_payroll_log($data) {
    return $this->db->insert('payroll_logs', $data);
}

}
