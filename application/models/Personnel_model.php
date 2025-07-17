<?php
class Personnel_model extends CI_Model
{
    protected $table = 'personnel';

    public function getAll($settingsID) {
        $this->db->where('settingsID', $settingsID);
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


    // -------------CASH ADVANCE
public function get_all_personnel($settingsID)
{
    return $this->db
        ->where('settingsID', $settingsID)
        ->order_by('last_name', 'ASC')
        ->get('personnel')
        ->result();
}

// --------------END----------
public function get_by_id($id)
{
    return $this->db->get_where('personnel', ['personnelID' => $id])->row();
}

public function getNameByID($personnelID) {
    $this->db->select('last_name, first_name');
    $this->db->from('personnel');
    $this->db->where('personnelID', $personnelID);
    $row = $this->db->get()->row();
    return $row ? $row->last_name . ', ' . $row->first_name : '';
}

public function getActiveWithDateEmployed()
{
    $this->db->where('date_employed IS NOT NULL');
    $this->db->where('(date_terminated IS NULL OR date_terminated = "" OR date_terminated = "0000-00-00")');
    return $this->db->get('personnel')->result();
}



}

