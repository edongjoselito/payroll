<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Material extends CI_Controller {  // <--- Must be exactly 'Material'
    public function __construct() {
        parent::__construct();
        $this->load->model('Cashadvance_model'); // shared
        $this->load->model('Personnel_model');
    }

public function index() {
    $settingsID = $this->session->userdata('settingsID');
    $data['materials'] = $this->Cashadvance_model->get_material_items(); // create this method
    $data['materials_loan'] = $this->Cashadvance_model->get_all_material_loans($settingsID);
    $data['personnel'] = $this->Personnel_model->get_all_personnel($settingsID);
    $this->load->view('materialloan_view', $data);
}



    public function save() {
        $data = [
            'personnelID'     => $this->input->post('personnelID'),
            'amount'          => $this->input->post('amount'),
            'date_requested'  => $this->input->post('date_requested'),
            'deduct_on'       => $this->input->post('deduct_on'),
            'remarks'         => $this->input->post('remarks'),
            'loan_type'       => 'material', // distinguishes from cash
            'settingsID'      => $this->session->userdata('settingsID'),
            'status'          => 'pending'
        ];

        if ($this->Cashadvance_model->insert($data)) {
            $this->session->set_flashdata('success', 'Material loan added.');
        } else {
            $this->session->set_flashdata('error', 'Failed to add material loan.');
        }

        redirect('Material');
    }

    public function delete($id) {
        if ($this->Cashadvance_model->delete($id)) {
            $this->session->set_flashdata('success', 'Material loan deleted.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete record.');
        }
        redirect('Material');
    }
}
