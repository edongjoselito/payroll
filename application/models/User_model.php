<?php
class User_model extends CI_Model {

    private $table = 'o_users';

    public function get_all_users($settingsID) {
        $this->db->where('settingsID', $settingsID);
        return $this->db->get($this->table)->result();
    }

    public function insert_user($data) {
        return $this->db->insert($this->table, $data);
    }

    public function get_user($username) {
        return $this->db->get_where($this->table, ['username' => $username])->row();
    }

    public function update_user($username, $data) {
        $this->db->where('username', $username);
        return $this->db->update($this->table, $data);
    }

    public function delete_user($username) {
        $this->db->where('username', $username);
        return $this->db->delete($this->table);
    }
}
