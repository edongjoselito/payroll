<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_model extends CI_Model {

    // =================== Personnel and Loan Entry ======================

    public function get_personnel_by_settings($settingsID)
    {
        return $this->db->where('settingsID', $settingsID)
                        ->get('personnel')
                        ->result();
    }

  public function insert_loan($data)
{
    return $this->db->insert('loans', $data);
}

public function update_loan($loan_id, $data)
{
    $this->db->where('loan_id', $loan_id);
    return $this->db->update('loans', $data);
}


    public function delete_loan($loan_id)
    {
        $this->db->where('loan_id', $loan_id);
        return $this->db->delete('loans');
    }

public function get_loans_by_rate($rateType, $settingsID)
{
    $this->db->where('rateType', $rateType);
    $this->db->where('settingsID', $settingsID);
    $query = $this->db->get('loans_view');
    return $query->result();
}


public function get_loans_by_ratetype($rateType)
{
    $this->db->where('rateType', $rateType);  
    return $this->db->get('loans')->result(); 
}


public function get_all_loans()
{
    return $this->db->get('loans')->result();  
}

public function get_loan_description($loan_id)
{
    $loan = $this->db->get_where('loans', ['loan_id' => $loan_id])->row();
    return $loan ? $loan->loan_description : '';
}




  


    public function get_loan_by_id($loan_id)
    {
        return $this->db->get_where('loans', ['loan_id' => $loan_id])->row();
    }

    // =================== Personnel Loan Assignment ======================

public function insert_personnel_loan($data)
{
    return $this->db->insert('personnelloans', $data);
}



    public function check_existing_personnel_loan($personnelID, $loan_id)
    {
        return $this->db->where('personnelID', $personnelID)
                        ->where('loan_id', $loan_id)
                        ->where('status', 1)
                        ->get('personnelloans')
                        ->num_rows() > 0;
    }

public function get_assigned_loans($settingsID)
{
    $this->db->select('pl.loan_id, pl.personnelID, pl.loan_description, pl.amount, pl.monthly_deduction, pl.date_assigned, p.first_name, p.last_name, p.position, pl.date_assigned, pl.status');
    $this->db->from('personnelloans pl');
    $this->db->join('personnel p', 'p.personnelID = pl.personnelID');
    $this->db->where('pl.settingsID', $settingsID);
    $query = $this->db->get();
    return $query->result();
}





public function assign_personnel_loan($data)
{
    return $this->db->insert('personnelloans', $data);
}



public function update_personnel_loan($settingsID, $personnelID, $loan_id, $data)
{
    $this->db->where('settingsID', $settingsID);
    $this->db->where('personnelID', $personnelID);
    $this->db->where('loan_id', $loan_id);
    return $this->db->update('personnelloans', $data);
}


    public function delete_personnel_loan($id)
    {
        return $this->db->delete('personnelloans', ['loanID' => $id]);
    }
    // =================== Cash Advance ======================

     public function insert_cash_advance($data)
    {
        return $this->db->insert('cashadvance', $data);
    }

    public function update_cash_advance($id, $data)
    {
        return $this->db->where('id', $id)->update('cashadvance', $data);
    }

    public function delete_cash_advance($id)
    {
        return $this->db->delete('cashadvance', ['id' => $id]);
    }

    public function get_cash_advance_within_range($personnelID, $start, $end)
    {
        return $this->db->select_sum('amount')
                        ->where('personnelID', $personnelID)
                        ->where('date >=', $start)
                        ->where('date <=', $end)
                        ->get('cashadvance')
                        ->row()
                        ->amount ?? 0;
    }
}