<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_model extends CI_Model {

    public function get_personnel_by_settings($settingsID)
    {
        return $this->db->where('settingsID', $settingsID)
                        ->get('personnel')
                        ->result();
    }

public function insert_loan($data) {
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


    public function get_loans_by_rate_type($rateType) {
        $this->db->where('rateType', $rateType);
        return $this->db->get('loans')->result();
    }

  public function get_all_loans($settingsID)
{
    return $this->db
        ->where('status', 1)
        ->where('settingsID', $settingsID)
        ->get('loans')
        ->result();
}


   public function get_loan_by_id($loan_id)
{
    return $this->db->get_where('loans', ['loan_id' => $loan_id])->row();
}

    public function get_loans_by_rate($rateType, $settingsID)
{
    return $this->db->where('rateType', $rateType)
                    ->where('status', 1)
                    ->where('settingsID', $settingsID)  // Ensure multitenant filtering
                    ->get('loans')
                    ->result();
}
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
    $this->db->select('pl.*, p.first_name, p.last_name, p.position, l.loan_description AS loan_name');

    $this->db->from('personnelloans pl');
    $this->db->join('personnel p', 'pl.personnelID = p.personnelID', 'left');
    $this->db->join('loans l', 'pl.loan_id = l.loan_id', 'left');
    $this->db->where('pl.settingsID', $settingsID);
    $this->db->where('pl.status', 1);
    return $this->db->get()->result();
}







// CASH ADVANCED---------------------
// CASH ADVANCE
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

public function update_personnel_loan($loan_id, $data)
{
    $this->db->where('loan_id', $loan_id);
    return $this->db->update('personnelloans', $data);
}


public function delete_personnel_loan($id)
{
    return $this->db->delete('personnelloans', ['loanID' => $id]);
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
