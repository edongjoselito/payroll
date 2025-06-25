<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Borrow_model extends CI_Model {

    public function get_personnel() {
        return $this->db->get('personnel')->result();
    }

   public function get_cash_advances() {
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
    $this->db->where('ca.description', 'Cash Advance');
    return $this->db->get()->result();
}


    public function insert_cash_advance($data) {
        $insert = [
            'personnelID' => $data['personnelID'],
            'description' => 'Cash Advance',
            'amount' => $data['amount'],
            'date' => $data['date']
        ];
        $this->db->insert('cashadvance', $insert);
    }

    public function update_cash_advance($data) {
        $update = [
            'amount' => $data['amount'],
            'date' => $data['date']
        ];
        $this->db->where('id', $data['id'])->update('cashadvance', $update);
    }

    public function delete_cash_advance($id) {
        $this->db->where('id', $id)->delete('cashadvance');
    }

public function get_materials() {
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
    $this->db->where('ca.description !=', 'Cash Advance');
    return $this->db->get()->result();
}



    public function insert_material($data) {
        $insert = [
            'personnelID' => $data['personnelID'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'date' => $data['date']
        ];
        $this->db->insert('cashadvance', $insert);
    }

    public function update_material($data) {
        $update = [
            'description' => $data['description'],
            'amount' => $data['amount'],
            'date' => $data['date']
        ];
        $this->db->where('id', $data['id'])->update('cashadvance', $update);
    }

    public function delete_material($id) {
        $this->db->where('id', $id)->delete('cashadvance');
    }
}
