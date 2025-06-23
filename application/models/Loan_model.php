<?php
class Loan_model extends CI_Model {

    public function get_loans() {
        return $this->db->get('loans')->result();
    }

   public function insert_loan($data) {
    $insert = [
        'personnelID' => $data['personnelID'],
        'loan_description' => $data['loan_description'],
        'loan_type' => $data['loan_type'],
        'loan_amount' => $data['loan_amount'],
        'salary_basis' => $data['salary_basis']
    ];
    return $this->db->insert('loans', $insert);
}


    public function update_loan($loan_id, $data) {
    $this->db->where('loan_id', $loan_id);
    return $this->db->update('loans', $data);
}

public function is_duplicate($personnelID, $description, $type, $exclude_id = null) {
    $this->db->where([
        'personnelID' => $personnelID,
        'loan_description' => $description,
        'loan_type' => $type
    ]);
    if ($exclude_id) {
        $this->db->where('loan_id !=', $exclude_id);
    }
    return $this->db->get('loans')->num_rows() > 0;
}

 public function get_all_personnel() {
    return $this->db->get('personnel')->result();
}

public function get_loans_with_personnel() {
    $this->db->select('loans.*, CONCAT(p.first_name, " ", p.middle_name, " ", p.last_name) as full_name');
    $this->db->from('loans');
    $this->db->join('personnel p', 'p.personnelID = loans.personnelID', 'left');
    return $this->db->get()->result();
}
public function delete_loan($loan_id) {
    return $this->db->delete('loans', ['loan_id' => $loan_id]);
    
}
// ------------------------------------Cash Advance Model------------------------

     public function get_cash_advances()
{
    $this->db->select('c.*, CONCAT(p.first_name, " ", p.middle_name, " ", p.last_name) as full_name');
    $this->db->from('cash_advance c');
    $this->db->join('personnel p', 'p.personnelID = c.personnelID');
    $this->db->order_by('c.date_requested', 'DESC');
    return $this->db->get()->result();
}

public function update_cash_advance_status($id, $status)
{
    return $this->db->where('id', $id)->update('cash_advance', ['status' => $status]);
}

public function delete_cash_advance($id)
{
    return $this->db->where('id', $id)->delete('cash_advance');
}

public function update_cash_advance($id, $data)
{
    return $this->db->where('id', $id)->update('cash_advance', $data);
}



// -------------------------------------End Advance Model------------------------

//     ----------------------Supply Loan-------------------
public function get_supply_loans()
{
    $this->db->select('s.*, CONCAT(p.first_name, " ", p.middle_name, " ", p.last_name) as full_name');
    $this->db->from('supply_loans s');
    $this->db->join('personnel p', 'p.personnelID = s.personnelID');
    $this->db->order_by('s.date_purchased', 'DESC');
    return $this->db->get()->result();
}

public function insert_supply_loan($data)
{
    return $this->db->insert('supply_loans', $data);
}

public function update_supply_loan_status($id, $status)
{
    return $this->db->where('id', $id)->update('supply_loans', ['status' => $status]);
}

          public function delete_supply_loan($id)
{
    return $this->db->where('id', $id)->delete('supply_loans');
}

public function update_supply_loan($id, $data)
{
    return $this->db->where('id', $id)->update('supply_loans', $data);
}



//     -------------------- End Supply Loan----------------
}



