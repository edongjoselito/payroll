<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cashadvance extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Cashadvance_model');
    }

    public function index()
    {
        $settingsID = $this->session->userdata('settingsID');
        $data['personnel'] = $this->Cashadvance_model->get_personnel_by_settings($settingsID);
        $data['cash_advances'] = $this->Cashadvance_model->get_cash_advances($settingsID);
        $this->load->view('cashadvance_view', $data);
    }

  public function save()
{
    $data = [
        'personnelID' => $this->input->post('personnelID'),
        'settingsID' => $this->session->userdata('settingsID'),
        'amount' => $this->input->post('amount'),
        'remarks' => $this->input->post('remarks'),
        'date_requested' => $this->input->post('date_requested'),
        'deduct_on' => $this->input->post('deduct_on'),
        'status' => 'pending'
    ];

    if ($this->Cashadvance_model->insert_cash_advance($data)) {
        $this->session->set_flashdata('success', 'Cash advance successfully recorded.');
    } else {
        $this->session->set_flashdata('error', 'Failed to save cash advance.');
    }

    redirect('Cashadvance');
}


    public function delete($id)
    {
        $this->Cashadvance_model->delete_cash_advance($id);
        $this->session->set_flashdata('success', 'Cash advance deleted.');
        redirect('Cashadvance');
    }

    public function update()
{
    $data = [
        'personnelID' => $this->input->post('personnelID'),
        'amount' => $this->input->post('amount'),
        'date_requested' => $this->input->post('date_requested'),
        'deduct_on' => $this->input->post('deduct_on'),
    ];

    $this->db->where('id', $this->input->post('cash_id'))->update('cashadvance', $data);
    $this->session->set_flashdata('success', 'Cash advance updated.');
    redirect('Cashadvance');
}

public function mark_deducted($id)
{
    $undo = $this->input->get('undo');
    $status = $undo ? 'pending' : 'deducted';

    $this->db->where('id', $id)->update('cashadvance', ['status' => $status]);
    $msg = $undo ? 'Deduction undone.' : 'Marked as deducted.';
    $this->session->set_flashdata('success', $msg);
    redirect('Cashadvance');

    if (!$this->input->post('personnelID') || !$this->input->post('amount')) {
    $this->session->set_flashdata('error', 'Required fields missing.');
    redirect('Cashadvance');
}

}
//--------------------- MATERIAL LOAN ----------------
public function get_material_loans($settingsID)
{
    return $this->db->select('c.*, p.first_name, p.last_name, p.middle_name, p.position')
                    ->from('cashadvance c')
                    ->join('personnel p', 'p.personnelID = c.personnelID')
                    ->where('c.settingsID', $settingsID)
                    ->where('c.loan_type', 'material') // filter
                    ->get()->result();
}

// --------------------END MATERIAL LOAN--------------
}
