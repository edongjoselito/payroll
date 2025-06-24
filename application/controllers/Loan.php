<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Loan_model');
    }

   public function personnel_loan() {
    $settingsID = $this->session->userdata('settingsID');
    $data['personnel'] = $this->Loan_model->get_personnel_by_settings($settingsID);
    $data['assigned_loans'] = $this->Loan_model->get_assigned_loans($settingsID); // required
    $this->load->view('personnel_loan', $data);
}


    public function edit_loan($id) {
        // Load edit form here
    }

    public function delete_loan($id) {
        $this->db->where('personnelID', $id)->delete('personnel');
        $this->session->set_flashdata('success', 'Personnel loan deleted successfully.');
        redirect('Loan/personnel_loan');
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


    public function update_loan_entry() {
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
    $personnelID = $this->input->post('personnelID');
    $loanID = $this->input->post('loan_id');
    $loanAmount = $this->input->post('loan_amount');
    $settingsID = $this->session->userdata('settingsID');

    // Check for duplicate (already assigned)
    $exists = $this->db->get_where('personnelloans', [
        'personnelID' => $personnelID,
        'loan_id' => $loanID,
        'status' => 1
    ])->row();

    if ($exists) {
        $this->session->set_flashdata('error', 'Loan already assigned to this personnel.');
    } else {
        $data = [
            'personnelID' => $personnelID,
            'loan_id'     => $loanID, // Correct column name
            'amount'      => $loanAmount, // Use `amount`, not `loan_amount`
            'settingsID'  => $settingsID,
            'created_at'  => date('Y-m-d H:i:s'),
            'status'      => 1
        ];
        $this->db->insert('personnelloans', $data);
        $this->session->set_flashdata('success', 'Loan successfully assigned.');
    }

    redirect('Loan/personnel_loan');
}



}
