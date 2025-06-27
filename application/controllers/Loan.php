<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Loan_model');
    }

 public function personnel_loan()
{
    $settingsID = $this->session->userdata('settingsID');
    $data['assigned_loans'] = $this->Loan_model->get_assigned_loans($settingsID); // ✅ Must be this
    $data['personnel'] = $this->Loan_model->get_personnel_by_settings($settingsID);

    $this->load->view('personnel_loan', $data);
}



    public function edit_loan($id) {
        // Load edit form here
    }
public function delete_loan($loan_id)
{
    if ($this->Loan_model->delete_loan($loan_id)) {
        $this->session->set_flashdata('success', 'Loan deleted successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete loan.');
    }

    redirect('Loan/loans_view');
}


    public function get_loan_options()
{
    $personnelID = $this->input->post('personnelID');

    $person = $this->db->get_where('personnel', ['personnelID' => $personnelID])->row();

    if (!$person) {
        echo json_encode(['status' => 'error', 'message' => 'Personnel not found.']);
        return;
    }

    // Match rateType → type_rate (e.g., 'Month' = 'monthly')
    $rateMap = ['Hour' => 'hourly', 'Day' => 'daily', 'Month' => 'monthly'];
    $mappedRate = $rateMap[$person->rateType] ?? '';

    $loans = $this->db->get_where('loans', ['type_rate' => $mappedRate])->result();

    echo json_encode([
        'status' => 'success',
        'loans' => $loans,
        'person' => $person
    ]);
}
public function update_personnel()
{
    $personnelID = $this->input->post('personnelID');

    $data = [
        'first_name'        => $this->input->post('first_name'),
        'middle_name'       => $this->input->post('middle_name'),
        'last_name'         => $this->input->post('last_name'),
        'name_ext'          => $this->input->post('name_ext'),
        'birthdate'         => $this->input->post('birthdate'),
        'gender'            => $this->input->post('gender'),
        'civil_status'      => $this->input->post('civil_status'),
        'address'           => $this->input->post('address'),
        'contact_number'    => $this->input->post('contact_number'),
        'email'             => $this->input->post('email'),
        'position'          => $this->input->post('position'),
        'rateType'          => $this->input->post('rateType'),
        'rateAmount'        => $this->input->post('rateAmount'),
        'philhealth_number' => $this->input->post('philhealth_number'),
        'pagibig_number'    => $this->input->post('pagibig_number'),
        'sss_number'        => $this->input->post('sss_number'),
        'tin_number'        => $this->input->post('tin_number'),
    ];

    $this->db->where('personnelID', $personnelID);
    if ($this->db->update('personnel', $data)) {
        $this->session->set_flashdata('success', 'Personnel updated successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update personnel.');
    }

    redirect('Loan/personnel_loan');
}
public function loans_view()
{
    $this->load->model('Loan_model');
    $settingsID = $this->session->userdata('settingsID');
$data['loans'] = $this->Loan_model->get_all_loans($settingsID);

    $this->load->view('loans_view', $data);
}
public function add_loan_entry()
{
    $data = [
        'settingsID'       => $this->session->userdata('settingsID'),
        'loan_description' => $this->input->post('loan_description'),
        'loan_amount'      => $this->input->post('loan_amount'),
        'loan_type'        => $this->input->post('loan_type'),
        'rateType'         => $this->input->post('rateType'),
        'service_charge'   => $this->input->post('service_charge'),
        'status'           => 1,
        'created_at'       => date('Y-m-d H:i:s')
    ];
    $this->Loan_model->insert_loan($data);
    redirect('Loan/loans_view');
}


  public function update_loan_entry()
{
    $loan_id = $this->input->post('loan_id');

    $data = array(
        'loan_description' => $this->input->post('loan_description'),
        'loan_amount' => $this->input->post('loan_amount'),
        'loan_type' => strtolower($this->input->post('loan_type')),
        'rateType' => strtolower($this->input->post('rateType')),
        'service_charge' => $this->input->post('service_charge')
    );

    if ($this->Loan_model->update_loan($loan_id, $data)) {
        $this->session->set_flashdata('success', 'Loan updated successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update loan.');
    }

    redirect('Loan/loans_view');
}


    public function delete_loan_entry($loan_id) {
        if ($this->Loan_model->delete_loan($loan_id)) {
            $this->session->set_flashdata('success', 'Loan deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete loan.');
        }

        redirect('Loan/loans_view');
    }

    public function get_loans_by_ratetype()
{
    $rateType = strtolower($this->input->post('rateType'));
    $settingsID = $this->session->userdata('settingsID'); // multitenant filter

    $loans = $this->Loan_model->get_loans_by_rate($rateType, $settingsID);

    if (!empty($loans)) {
        echo json_encode(['status' => 'success', 'loans' => $loans]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No eligible loans found.']);
    }
}
public function save_personnel_loan()
{
    $personnelID     = $this->input->post('personnelID');
    $loan_id         = $this->input->post('loan_id');
    $amount          = $this->input->post('loan_amount');
    $deduction_type  = $this->input->post('deduction_type');
    $term_months     = $this->input->post('term_months');
    $start_date      = $this->input->post('start_date');
    $end_date        = $this->input->post('end_date');
    $settingsID      = $this->session->userdata('settingsID');
    $created_at      = date('Y-m-d H:i:s');

    // ✅ Validation: Check if deduction_type is empty
    if (empty($deduction_type)) {
        $this->session->set_flashdata('error', 'Deduction type is required.');
        redirect('Loan/personnel_loan');
        return;
    }

    // ✅ Fetch loan_description from loans table
    $loan = $this->Loan_model->get_loan_by_id($loan_id); // You’ll add this method if not already present
    $loan_description = $loan ? $loan->loan_description : null;

    $data = [
        'personnelID'      => $personnelID,
        'loan_id'          => $loan_id,
        'loan_description' => $loan_description, // Now auto-filled!
        'amount'           => $amount,
        'deduction_type'   => $deduction_type,
        'term_months'      => $term_months,
        'start_date'       => $start_date,
        'end_date'         => $end_date,
        'settingsID'       => $settingsID,
        'created_at'       => $created_at,
        'status'           => 1
    ];

    if ($this->Loan_model->insert_personnel_loan($data)) {
        $this->session->set_flashdata('success', 'Loan assigned successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to assign loan.');
    }

    redirect('Loan/personnel_loan');
}





public function update_personnel_loan()
{
    $this->load->model('Loan_model');

    $personnelID = $this->input->post('personnelID');
    $loanID = $this->input->post('loan_id');

    $data = [
        'amount' => $this->input->post('loan_amount'),
        'deduction_type' => $this->input->post('deduction_type'),
        'term_months' => $this->input->post('term_months'),
        'start_date' => $this->input->post('start_date'),
        'end_date' => $this->input->post('end_date'),
    ];

    // Update using composite key (personnelID + loan_id)
    $this->db->where('personnelID', $personnelID);
    $this->db->where('loan_id', $loanID);
    $updated = $this->db->update('personnelloans', $data);

    if ($updated) {
        $this->session->set_flashdata('success', 'Loan updated successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to update loan.');
    }

    redirect('Loan/personnel_loan');
}

public function delete_personnel_loan($loanID)
{
    $this->db->where('loan_id', $loanID); // ✅ Correct column name
    $this->db->delete('personnelloans');

    $this->session->set_flashdata('success', 'Personnel loan deleted successfully.');
    redirect('Loan/personnel_loan');
}


// -----------CASH ADVANCED------------
// public function cashadvance()
// {
//     $settingsID = $this->session->userdata('settingsID');

//     $data['personnel'] = $this->db->where('settingsID', $settingsID)->get('personnel')->result();

//     $this->db->select("ca.*, CONCAT(p.first_name, ' ', COALESCE(p.middle_name, ''), ' ', p.last_name) AS full_name");
//     $this->db->from('cashadvance ca');
//     $this->db->join('personnel p', 'p.personnelID = ca.personnelID');
//     $this->db->where('ca.settingsID', $settingsID);
//     $data['cash_advances'] = $this->db->get()->result();

//     $this->load->view('cashadvance_view', $data);
// }

// public function save_cash_advance()
// {
//     $data = [
//         'personnelID' => $this->input->post('personnelID'),
//         'settingsID' => $this->session->userdata('settingsID'),
//         'amount' => $this->input->post('amount'),
//         'date_requested' => $this->input->post('date_requested'),
//         'deduct_on' => $this->input->post('deduct_on'),
//         'status' => 'pending'
//     ];

//     $this->db->insert('cashadvance', $data);
//     $this->session->set_flashdata('success', 'Cash advance added successfully.');
//     redirect('Loan/cashadvance');
// }

// public function update_cash_advance()
// {
//     $id = $this->input->post('cash_id');
//     $data = [
//         'personnelID' => $this->input->post('personnelID'),
//         'amount' => $this->input->post('amount'),
//         'date_requested' => $this->input->post('date_requested'),
//         'deduct_on' => $this->input->post('deduct_on')
//     ];

//     $this->db->where('id', $id)->update('cashadvance', $data);
//     $this->session->set_flashdata('success', 'Cash advance updated.');
//     redirect('Loan/cashadvance');
// }

// public function delete_cash_advance($id)
// {
//     $this->db->where('id', $id)->delete('cashadvance');
//     $this->session->set_flashdata('success', 'Cash advance deleted.');
//     redirect('Loan/cashadvance');
// }

// public function mark_cash_advance_deducted($id)
// {
//     $undo = $this->input->get('undo');
//     $status = ($undo == 1) ? 'pending' : 'deducted';

//     $this->db->where('id', $id)->update('cashadvance', ['status' => $status]);
//     $msg = $undo ? 'Deduction undone.' : 'Cash advance marked as deducted.';
//     $this->session->set_flashdata('success', $msg);
//     redirect('Loan/cashadvance');
// }

// -----------END CASH ADVANCED-------

}
