<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Material_model extends CI_Model {

   public function get_material_loans($settingsID) {
    $this->db->select("
        ca.*, 
        CONCAT(
            p.first_name, ' ',
            LEFT(p.middle_name, 1), '. ',
            p.last_name,
            IF(p.name_ext IS NOT NULL AND p.name_ext != '', CONCAT(' ', p.name_ext), '')
        ) AS fullname
    ");
    $this->db->from('cashadvance ca');
    $this->db->join('personnel p', 'p.personnelID = ca.personnelID');
    $this->db->where('ca.settingsID', $settingsID);
    $this->db->where('ca.type', 'material');
    $this->db->order_by('ca.date', 'DESC');
    return $this->db->get()->result();
}


    public function get_personnel($settingsID) {
        $this->db->where('settingsID', $settingsID);
        return $this->db->get('personnel')->result();
    }

    public function save_material_loan($data) {
        $record = [
            'personnelID' => $data['personnelID'],
            'description' => $data['description'],
            'amount'      => $data['amount'],
            'date'        => $data['date'],
            'type'        => 'material',
            'settingsID'  => $this->session->userdata('settingsID'),
        ];

        if (!empty($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('cashadvance', $record);
        } else {
            $this->db->insert('cashadvance', $record);
        }
    }

    public function delete_material_loan($id) {
        $this->db->where('id', $id);
        $this->db->delete('cashadvance');
    }
public function update_material($data) {
    if (!isset($data['id'])) {
        return false;
    }

    $updateData = [
        'personnelID' => $data['personnelID'],
        'description' => $data['description'],
        'amount' => $data['amount'],
        'date' => $data['date'],
        'type' => 'material' // ensure it stays classified as 'material'
    ];

    $this->db->where('id', $data['id']);
    return $this->db->update('cashadvance', $updateData);
}

public function insert_material($data) {
    $data['type'] = 'material'; // Ensure 'material' type is tagged
    return $this->db->insert('cashadvance', $data);
}


    
}
