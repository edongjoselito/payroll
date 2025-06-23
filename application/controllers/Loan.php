<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Loan_model');
         $this->load->model('Personnel_model');
        
    }

   public function index() {
    $data['loans'] = $this->Loan_model->get_loans_with_personnel();
    $data['personnel'] = $this->Loan_model->get_all_personnel();
    $this->load->view('loan_view', $data);
}


    public function add() {
    $data = $this->input->post();
    $this->Loan_model->insert_loan($data);
    $this->session->set_flashdata('success', 'Loan added successfully!');
    redirect('Loan');
}




 public function update() {
    $loan_id = $this->input->post('loan_id');
    $personnelID = $this->input->post('personnelID');
    $description = $this->input->post('loan_description');
    $type = $this->input->post('loan_type');

    $this->load->model('Loan_model');

    if ($this->Loan_model->is_duplicate($personnelID, $description, $type, $loan_id)) {
        $this->session->set_flashdata('error', 'Duplicate loan entry detected.');
    } else {
        $data = [
            'personnelID' => $personnelID,
            'loan_description' => $description,
            'loan_type' => $type,
            'loan_amount' => $this->input->post('loan_amount'),
            'salary_basis' => $this->input->post('salary_basis')
        ];

        if ($this->Loan_model->update_loan($loan_id, $data)) {
            $this->session->set_flashdata('success', 'Loan updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update loan.');
        }
    }

    redirect('Loan');
}

// ------------------Cash Advance----------------------------

     public function cash_advance()
{
   
    $this->load->model('Loan_model');
    $this->load->model('Personnel_model'); 

    $data['cash_advances'] = $this->Loan_model->get_cash_advances();


    $data['personnel'] = $this->Personnel_model->get_all_personnel($this->session->userdata('settingsID'));

    $data['title'] = "Cash Advance";

    $this->load->view('cash_advance', $data);
}


public function save_cash_advance()
{
    $this->load->model('Loan_model');

    $data = [
        'personnelID'    => $this->input->post('personnelID'),
        'amount'         => $this->input->post('amount'),
        'date_requested' => $this->input->post('date_requested'),
        'deduct_on'      => $this->input->post('deduct_on'),
        'status'         => 'pending'
    ];

    if ($this->Loan_model->insert_cash_advance($data)) {
        $this->session->set_flashdata('success', 'Cash advance saved successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to save cash advance.');
    }

    redirect('Loan/cash_advance');
}
public function update_cash_advance()
{
    $id = $this->input->post('cash_id');
    $data = [
        'personnelID'     => $this->input->post('personnelID'),
        'amount'          => $this->input->post('amount'),
        'date_requested'  => $this->input->post('date_requested'),
        'deduct_on'       => $this->input->post('deduct_on')
    ];

    $this->Loan_model->update_cash_advance($id, $data);

    $this->session->set_flashdata('success', 'Cash advance updated successfully.');
    redirect('Loan/cash_advance');
}


public function mark_cash_advance_deducted($id)
{
    $this->load->model('Loan_model');

    $undo = $this->input->get('undo');
    $newStatus = $undo ? 'pending' : 'deducted';

    if ($this->Loan_model->update_cash_advance_status($id, $newStatus)) {
        $msg = $undo ? 'Cash advance marked as pending again.' : 'Cash advance marked as deducted.';
        $this->session->set_flashdata('success', $msg);
    } else {
        $this->session->set_flashdata('error', 'Failed to update status.');
    }

    redirect('Loan/cash_advance');
}
public function delete_cash_advance($id)
{
    $this->load->model('Loan_model');

    if ($this->Loan_model->delete_cash_advance($id)) {
        $this->session->set_flashdata('success', 'Cash advance deleted successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete cash advance.');
    }

    redirect('Loan/cash_advance');
}


// -------------------------End Cash Advance ----------------
//     ----------------------Supply Loan-------------------
public function supply_loan()
{
    $this->load->model('Loan_model');
    $data['personnel'] = $this->Loan_model->get_all_personnel();
    $data['supply_loans'] = $this->Loan_model->get_supply_loans_from_loans(); // filtered from 'loans' table
    
    $this->load->view('supply_loan', $data);
}



public function save_supply_loan() {
    $data = $this->input->post();
    $personnel = $this->db->get_where('personnel', ['personnelID' => $data['personnelID']])->row();

    if (!$personnel || $data['amount'] > $personnel->rateAmount * 2) {
        $this->session->set_flashdata('error', 'Personnel not qualified for this loan amount.');
        redirect('Loan/supply_loan');
        return;
    }

    $insert = [
        'personnelID' => $data['personnelID'],
        'item_description' => $data['item_description'],
        'loan_type' => $data['loan_type'],
        'deduction_type' => $data['deduction_type'],
        'amount' => $data['amount'],
        'date_purchased' => $data['date_purchased'],
        'status' => 'pending'
    ];
    $this->db->insert('supply_loans', $insert);
    $this->session->set_flashdata('success', 'Supply loan saved.');
    redirect('Loan/supply_loan');
}

public function mark_supply_loan_deducted($id)
{
    $this->load->model('Loan_model');

    $undo = $this->input->get('undo');
    $newStatus = $undo ? 'pending' : 'deducted';

    if ($this->Loan_model->update_supply_loan_status($id, $newStatus)) {
        $msg = $undo ? 'Supply loan marked as pending again.' : 'Supply loan marked as deducted.';
        $this->session->set_flashdata('success', $msg);
    } else {
        $this->session->set_flashdata('error', 'Failed to update status.');
    }

    redirect('Loan/supply_loan');
}

public function delete_supply_loan($id)
{
    $this->load->model('Loan_model');
    if ($this->Loan_model->delete_supply_loan($id)) {
        $this->session->set_flashdata('success', 'Supply loan deleted successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete supply loan.');
    }
    redirect('Loan/supply_loan');
}
public function update_supply_loan()
{
    $this->load->model('Loan_model');

    $id = $this->input->post('supply_id');
    $data = [
        'personnelID'      => $this->input->post('personnelID'),
        'item_description' => $this->input->post('item_description'),
        'amount'           => $this->input->post('amount'),
        'date_purchased'   => $this->input->post('date_purchased')
    ];

    if ($this->Loan_model->update_supply_loan($id, $data)) {
        $this->session->set_flashdata('success', 'Supply loan updated successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update supply loan.');
    }

    redirect('Loan/supply_loan');
}
public function save_personnel() {
    $data = [
        'first_name' => $this->input->post('first_name'),
        'middle_name' => $this->input->post('middle_name'),
        'last_name' => $this->input->post('last_name'),
        'name_ext' => $this->input->post('name_ext'),
        'contact_number' => $this->input->post('contact_number'),
        'email' => $this->input->post('email'),
        'birthdate' => $this->input->post('birthdate'),
        'gender' => $this->input->post('gender'),
        'civil_status' => $this->input->post('civil_status'),
        'address' => $this->input->post('address'),
        'position' => $this->input->post('position'),
        'rateType' => $this->input->post('rateType'),
        'rateAmount' => $this->input->post('rateAmount'),
        'philhealth_number' => $this->input->post('philhealth_number'),
        'pagibig_number' => $this->input->post('pagibig_number'),
        'sss_number' => $this->input->post('sss_number'),
        'tin_number' => $this->input->post('tin_number'),
         'settingsID' => $this->session->userdata('settingsID')
    ];

    $this->db->insert('personnel', $data); 

    $this->session->set_flashdata('success', 'Personnel added successfully.');
    redirect('Loan/supply_loan');
}



public function delete_loan($loanID) {
    if ($this->Loan_model->delete_loan($loanID)) {
        $this->session->set_flashdata('success', 'Loan deleted.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete loan.');
    }
    redirect('Loan/supply_loan');
}

public function update_loan() {
    $loanID = $this->input->post('loanID');
    $data = [
        'loan_description' => $this->input->post('item_description'),
        'loan_type' => $this->input->post('loan_type'),
        'deduction_type' => $this->input->post('deduction_type'),
        'loan_amount' => $this->input->post('amount'),
        'date_issued' => $this->input->post('date_purchased')
    ];
    if ($this->Loan_model->update_loan($loanID, $data)) {
        $this->session->set_flashdata('success', 'Loan updated.');
    } else {
        $this->session->set_flashdata('error', 'Update failed.');
    }
    redirect('Loan/supply_loan');
}
public function update_personnel($id)
{
    $data = [
        'first_name'   => $this->input->post('first_name'),
        'middle_name'  => $this->input->post('middle_name'),
        'last_name'    => $this->input->post('last_name'),
        'name_ext'     => $this->input->post('name_ext'),
        'rateType'     => $this->input->post('rateType'),
        'rateAmount'   => $this->input->post('rateAmount'),
        'position'     => $this->input->post('position')
    ];

    $this->db->where('personnelID', $id);
    $updated = $this->db->update('personnel', $data);

    if ($updated) {
        $this->session->set_flashdata('success', 'Personnel updated successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update personnel.');
    }

    redirect('Loan/supply_loan');
}
public function delete_personnel($id)
{
    $deleted = $this->db->delete('personnel', ['personnelID' => $id]);

    if ($deleted) {
        $this->session->set_flashdata('success', 'Personnel deleted successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete personnel.');
    }

    redirect('Loan/supply_loan');
}


//     -------------------- End Supply Loan----------------
public function save_personnel_loan()
{
    $this->load->model('Loan_model');
    $data = $this->input->post();

    $personnel = $this->db->get_where('personnel', ['personnelID' => $data['personnelID']])->row();
    $rateAmount = floatval($personnel->rateAmount);

    if ($data['amount'] > $rateAmount * 2) {
        $this->session->set_flashdata('error', 'Loan amount exceeds allowed maximum for this personnel.');
        redirect('Loan/supply_loan');
        return;
    }

    $loanData = [
        'personnelID' => $data['personnelID'],
        'loan_description' => $data['item_description'],
        'loan_type' => $data['loan_type'],
        'deduction_type' => $data['deduction_type'],
        'loan_amount' => $data['amount'],
        'date_issued' => $data['date_purchased'],
        'status' => 'pending',
        'loan_category' => 'supply',
        'salary_basis' => $personnel->rateType
    ];

    $this->Loan_model->insert_loan($loanData);
    $this->session->set_flashdata('success', 'Loan assigned successfully.');
    redirect('Loan/supply_loan');
}
public function approve($id) {
    $this->load->model('Loan_model');
    $this->Loan_model->update_loan_status($id, 'approved');
    $this->session->set_flashdata('success', 'Loan approved successfully.');
    redirect('Loan');
}

public function disapprove($id) {
    $this->load->model('Loan_model');
    $this->Loan_model->update_loan_status($id, 'disapproved');
    $this->session->set_flashdata('error', 'Loan disapproved.');
    redirect('Loan');
} 


    // Approve via AJAX
public function ajax_approve()
{
    $loan_id = $this->input->post('loan_id');
    $success = $this->Loan_model->update_loan_status($loan_id, 'approved');

    echo json_encode(['success' => $success]);
}

// Disapprove via AJAX
public function ajax_disapprove()
{
    $loan_id = $this->input->post('loan_id');
    $success = $this->Loan_model->update_loan_status($loan_id, 'disapproved');

    echo json_encode(['success' => $success]);
}

// Delete via AJAX
public function ajax_delete()
{
    $loan_id = $this->input->post('loan_id');
    $success = $this->Loan_model->delete_loan($loan_id);

    echo json_encode(['success' => $success]);
}

public function save_personnel_from_supply_loan()
{
    $data = [
        'first_name'       => $this->input->post('first_name'),
        'middle_name'      => $this->input->post('middle_name'),
        'last_name'        => $this->input->post('last_name'),
        'name_ext'         => $this->input->post('name_ext'),
        'contact_number'   => $this->input->post('contact_number'),
        'email'            => $this->input->post('email'),
        'birthdate'        => $this->input->post('birthdate'),
        'gender'           => $this->input->post('gender'),
        'civil_status'     => $this->input->post('civil_status'),
        'address'          => $this->input->post('address'),
        'position'         => $this->input->post('position'),
        'rateType'         => $this->input->post('rateType'),
        'rateAmount'       => $this->input->post('rateAmount'),
        'philhealth_number'=> $this->input->post('philhealth_number'),
        'pagibig_number'   => $this->input->post('pagibig_number'),
        'sss_number'       => $this->input->post('sss_number'),
        'tin_number'       => $this->input->post('tin_number')
    ];

    $this->db->insert('personnel', $data);

    $this->session->set_flashdata('success', 'Personnel successfully added.');
    redirect('Loan/supply_loan');
}

}
