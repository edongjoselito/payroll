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

    public function update_loan($loanID, $data) {
        $this->db->where('loandID', $loanID);
        return $this->db->update('loans', $data);
    }

    public function delete_loan($loanID) {
        $this->db->where('loanID', $loanID);
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


    public function get_loan_by_id($loan_id) {
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
public function get_assigned_loans($settingsID) {
    $this->db->select('pl.*, p.first_name, p.last_name, p.position, l.loan_description');
    $this->db->from('personnelloans pl');
    $this->db->join('personnel p', 'p.personnelID = pl.personnelID');
    $this->db->join('loans l', 'l.loan_id = pl.loan_id');
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

    

}
