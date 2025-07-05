<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Borrow extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Borrow_model');      // For Cash Advance
        $this->load->model('OtherDeduction_model');

        $this->load->helper('url');
        $this->load->library('session');
    }

    // ------------------------------
    // CASH ADVANCE
    // ------------------------------
    public function cash_advance() {
        $settingsID = $this->session->userdata('settingsID');
        $data['cash_advances'] = $this->Borrow_model->get_cash_advances($settingsID);
        $data['personnel'] = $this->Borrow_model->get_personnel($settingsID);
        $this->load->view('cash_advance_view', $data);
    }


public function save_cash_advance()
{
    $this->load->model('Loan_model');

    $data = [
        'personnelID' => $this->input->post('personnelID'),
        'amount' => $this->input->post('amount'),
        'description' => 'Cash Advance',
        'date' => $this->input->post('date'),
        'settingsID' => $this->session->userdata('settingsID')
    ];

    $inserted = $this->Loan_model->insert_cash_advance($data);

    if ($inserted) {
        $this->session->set_flashdata('success', 'Cash advance saved.');
    } else {
        $this->session->set_flashdata('error', 'Failed to save cash advance.');
    }

    redirect('Borrow/cash_advance');

}


// public function save_cash_advance() {
//     $data = $this->input->post();
//     $this->Borrow_model->insert_cash_advance($data);
//     $this->session->set_flashdata('success', 'Cash Advance Saved Successfully!');
//     redirect('Borrow/cash_advance');
// }




public function update_cash_advance($id)
{
    $update = [
        'amount' => $this->input->post('amount'),
        'date' => $this->input->post('date'),
        'deduct_from' => $this->input->post('deduct_from') ?? null,
        'deduct_to' => $this->input->post('deduct_to') ?? null,
    ];

    $this->db->where('id', $id);
    $this->db->update('cashadvance', $update);

    $this->session->set_flashdata('success', 'Cash advance updated successfully!');
    redirect('Borrow/cash_advance');
}



    public function delete_cash_advance($id) {
        $this->Borrow_model->delete_cash_advance($id);
        $this->session->set_flashdata('success', 'Cash Advance Deleted Successfully!');
        redirect('Borrow/cash_advance');
    }

    // ------------------------------
    // MATERIALS LOAN
    // ------------------------------
  public function materials_loan() {
    $settingsID = $this->session->userdata('settingsID');
    
    // ✅ Assign returned result to the correct variable
    $data['material_loans'] = $this->OtherDeduction_model->get_other_deductions($settingsID);
    $data['personnel_list'] = $this->OtherDeduction_model->get_personnel($settingsID);
    
    $this->load->view('materials_loan_view', $data);
}


public function save_material() {
    $this->OtherDeduction_model->save_other_deduction($this->input->post());
    $this->session->set_flashdata('success', 'Other Deduction Saved Successfully!');
    redirect('Borrow/materials_loan');
}

 public function update_material() {
    $this->OtherDeduction_model->save_other_deduction($this->input->post());
    $this->session->set_flashdata('success', 'Other Deduction Updated Successfully!');
    redirect('Borrow/materials_loan');
}

    public function delete_material($id) {
        $this->OtherDeduction_model->delete_other_deduction($id);
        $this->session->set_flashdata('success', 'Other Deduction Deleted Successfully!');
        redirect('Borrow/materials_loan');
    }




    
}
