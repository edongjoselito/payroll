<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OtherDeduction_model extends CI_Model {

    public function get_other_deductions($settingsID) {
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
        $this->db->where('ca.type', 'material'); // still using 'material' as the identifier
        $this->db->order_by('ca.date', 'DESC');
        return $this->db->get()->result();
    }

    public function get_personnel($settingsID) {
        $this->db->where('settingsID', $settingsID);
        return $this->db->get('personnel')->result();
    }

    public function save_other_deduction($data) {
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

    public function delete_other_deduction($id) {
        $this->db->where('id', $id);
        $this->db->delete('cashadvance');
    }

    public function update_other_deduction($data) {
        if (!isset($data['id'])) {
            return false;
        }

        $updateData = [
            'personnelID' => $data['personnelID'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'date' => $data['date'],
            'type' => 'material'
        ];

        $this->db->where('id', $data['id']);
        return $this->db->update('cashadvance', $updateData);
    }

    public function insert_other_deduction($data) {
        $data['type'] = 'material';
        return $this->db->insert('cashadvance', $data);
    }

public function get_deductions_by_date_range($from, $to, $settingsID) {
    return $this->db->where('type', 'material')
                    ->where('settingsID', $settingsID)
                    ->where('date >=', $from)
                    ->where('date <=', $to)
                    ->get('cashadvance')
                    ->result();
}





}
