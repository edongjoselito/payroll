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


   public function delete($id) {
    if ($this->Loan_model->delete_loan($id)) {
        $this->session->set_flashdata('success', 'Loan deleted successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete loan.');
    }
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
    $this->load->model('Personnel_model');

    $data['supply_loans'] = $this->Loan_model->get_supply_loans();
    $data['personnel'] = $this->Personnel_model->get_all_personnel($this->session->userdata('settingsID'));
    $data['title'] = "Supply Loan";

    $this->load->view('supply_loan', $data);
}

public function save_supply_loan()
{
    $this->load->model('Loan_model');

    $data = [
        'personnelID'       => $this->input->post('personnelID'),
        'item_description'  => $this->input->post('item_description'),
        'amount'            => $this->input->post('amount'),
        'date_purchased'    => $this->input->post('date_purchased'),
        'status'            => 'pending'
    ];

    if ($this->Loan_model->insert_supply_loan($data)) {
        $this->session->set_flashdata('success', 'Supply loan saved successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to save supply loan.');
    }

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

//     -------------------- End Supply Loan----------------
}
