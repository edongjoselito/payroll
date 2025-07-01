<?php
class Company_model extends CI_Model {

    public function get_company_info() {
        return $this->db->get('company')->row();
    }

    public function update_company_info($data) {
        unset($data['submit']);
        return $this->db->update('company', $data);
    }
}
