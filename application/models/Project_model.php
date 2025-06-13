<?php
class Project_model extends CI_Model
{
    private $table = 'project';

    public function getAll() {
        return $this->db->get($this->table)->result();
    }
public function insert($data)
{
    return $this->db->insert($this->table, $data);
}

public function update($data)
{
    $this->db->where('projectID', $data['projectID']);
    return $this->db->update($this->table, $data);
}

public function delete($id)
{
    $this->db->where('projectID', $id);
    return $this->db->delete($this->table);
}

}
