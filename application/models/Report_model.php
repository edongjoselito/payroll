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
public function get_all_logs()
{
    return $this->db
        ->select('
            pl.id,
            pr.projectTitle AS project_title,
            pr.projectLocation AS location,
            pl.period,
            pl.date_from,
            pl.date_to,
            pl.payroll_date,
            pl.total_gross,
            pl.date_saved,
            pl.projectID,
            pl.settingsID
        ')
        ->from('payroll_logs pl')
        ->join('project pr', 'pr.projectID = pl.projectID', 'left')
        ->order_by('pl.date_saved', 'DESC')
        ->get()
        ->result();
}


public function insert_payroll_log($data)
{
    return $this->db->insert('payroll_logs', $data);
}

public function delete_log($id)
{
    return $this->db->where('id', $id)->delete('payroll_logs');
}

}
