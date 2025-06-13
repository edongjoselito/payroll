<?php
class Personnel_model extends CI_Model
{
    // Set table property
    protected $table = 'personnel';

    public function getAll() {
        return $this->db->get($this->table)->result();
    }

public function insert($data)
{
    return $this->db->insert($this->table, $data);
}

public function update($data)
{
    $this->db->where('personnelID', $data['personnelID']);
    return $this->db->update($this->table, $data);
}

public function delete($id)
{
    $this->db->where('personnelID', $id);
    return $this->db->delete($this->table);
}

}
