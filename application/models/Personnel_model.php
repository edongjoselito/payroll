<?php
class Personnel_model extends CI_Model
{
    // Set table property
    protected $table = 'personnel';

    public function getAll() {
        return $this->db->get($this->table)->result();
    }

    public function insert($data) {
        $this->db->insert($this->table, $data);
    }

    public function update($data) {
        $this->db->where('personnelID', $data['personnelID']);
        $this->db->update($this->table, $data);
    }

    public function delete($personnelID) {
        $this->db->delete($this->table, ['personnelID' => $personnelID]);
    }
}
