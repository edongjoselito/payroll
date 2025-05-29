<?php
class CalendarModel extends CI_Model
{
    public function getEvents($username)
    {
        $this->db->group_start();
        $this->db->where('username', $username);
        $this->db->or_where('status', 'public');
        $this->db->group_end();

        $query = $this->db->get('calendar_events');
        return $query->result();
    }




    public function addEvent($data)
    {
        return $this->db->insert('calendar_events', $data);
    }

    public function deleteEvent($id, $username)
    {
        $this->db->where('id', $id);
        $this->db->where('username', $username);
        return $this->db->delete('calendar_events');
    }



    public function updateEvent($id, $data, $username)
    {
        $this->db->where('id', $id);
        $this->db->where('username', $username);
        return $this->db->update('calendar_events', $data);
    }
}
