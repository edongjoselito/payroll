<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Borrow extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Borrow_model');      // For Cash Advance
        $this->load->model('Material_model');    // For Materials Loan
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
public function insert_cash_advance($data) {
    $insert = [
        'personnelID' => $data['personnelID'],
        'description' => 'Cash Advance',
        'amount' => $data['amount'],
        'date' => $data['date'],
        'settingsID' => $this->session->userdata('settingsID')
    ];
    $this->db->insert('cashadvance', $insert);
}
 public function save_cash_advance() {
    $data = $this->input->post();
    $this->Borrow_model->insert_cash_advance($data);
    $this->session->set_flashdata('success', 'Cash Advance Saved Successfully!');
    redirect('Borrow/cash_advance');
}



    public function update_cash_advance() {
        $this->Borrow_model->update_cash_advance($this->input->post());
        $this->session->set_flashdata('success', 'Cash Advance Updated Successfully!');
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
        $data['material_loans'] = $this->Material_model->get_material_loans($settingsID);
        $data['personnel_list'] = $this->Material_model->get_personnel($settingsID);
        $this->load->view('materials_loan_view', $data);
    }

    public function save_material() {
        $this->Material_model->save_material_loan($this->input->post());
        $this->session->set_flashdata('success', 'Material Loan Saved Successfully!');
        redirect('Borrow/materials_loan');
    }

    public function update_material() {
        $this->Material_model->save_material_loan($this->input->post()); // same as save for edit
        $this->session->set_flashdata('success', 'Material Loan Updated Successfully!');
        redirect('Borrow/materials_loan');
    }

    public function delete_material($id) {
        $this->Material_model->delete_material_loan($id);
        $this->session->set_flashdata('success', 'Material Loan Deleted Successfully!');
        redirect('Borrow/materials_loan');
    }




    
}
