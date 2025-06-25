<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cashadvance_model extends CI_Model
{
    // === FETCHING ===

    public function get_personnel_by_settings($settingsID)
    {
        return $this->db->where('settingsID', $settingsID)->get('personnel')->result();
    }

    public function get_cash_advances($settingsID)
    {
        return $this->db->select('c.*, p.first_name, p.last_name, p.middle_name, p.position')
                        ->from('cashadvance c')
                        ->join('personnel p', 'p.personnelID = c.personnelID')
                        ->where('c.settingsID', $settingsID)
                        ->get()->result();
    }

   public function get_due_cash_advances($cutoff, $settingsID)
{
    return $this->db
        ->where('deduct_on', $cutoff)
        ->where('status', 'pending')
        ->where('settingsID', $settingsID)
        ->get('cashadvance')
        ->result();
}

    // === INSERT ===

    public function insert_cash_advance($data)
    {
        return $this->db->insert('cashadvance', $data);
    }

    // === UPDATE ===

   public function mark_cash_advance_deducted($id)
{
    return $this->db
        ->where('id', $id)
        ->update('cashadvance', [
            'status' => 'deducted',
            'date_deducted' => date('Y-m-d')
        ]);
}

    // === DELETE ===

    public function delete_cash_advance($id)
    {
        return $this->db->where('id', $id)->delete('cashadvance');
    }

    // === INTERNAL LOGIC ===

    public function save()
    {
        $data = [
            'personnelID' => $this->input->post('personnelID'),
            'settingsID' => $this->session->userdata('settingsID'),
            'amount' => $this->input->post('amount'),
            'date_requested' => $this->input->post('date_requested'),
            'deduct_on' => $this->input->post('deduct_on'),
            'status' => 'pending'
        ];

        if (!$this->input->post('personnelID') || !$this->input->post('amount')) {
            $this->session->set_flashdata('error', 'Required fields missing.');
            redirect('Cashadvance');
        }

        if ($this->insert_cash_advance($data)) {
            $this->session->set_flashdata('success', 'Cash advance successfully recorded.');
        } else {
            $this->session->set_flashdata('error', 'Failed to save cash advance.');
        }

        redirect('Cashadvance');
    }

 public function get_by_type($type, $settingsID) {
    return $this->db
        ->where('loan_type', $type)
        ->where('settingsID', $settingsID)
        ->get('cashadvance')
        ->result();
}


public function insert($data) {
    return $this->db->insert('cashadvance', $data);
}

public function delete($id) {
    return $this->db->where('id', $id)->delete('cashadvance');
}




public function get_material_items() {
    return $this->db->get_where('materials', ['status' => 'active'])->result();
}

}
