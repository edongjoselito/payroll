<?php
class NoteModel extends CI_Model
{

    // Method to insert a new note
    public function insert_note($data)
    {
        $this->db->insert('notes', $data);
    }

    // Method to get all notes for a specific user
    public function get_notes_by_user($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('notes');
        return $query->result();
    }

    // Method to get a note by its ID
    public function get_note_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('notes');
        return $query->row();
    }

    // Method to update a note
    public function update_note($id, $data, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);  // Make sure only the user's own note is updated
        $this->db->update('notes', $data);
    }

    // Method to delete a note
    public function delete_note($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);  // Make sure only the user's own note is deleted
        return $this->db->delete('notes');
    }

    // Method to get the count of notes for a specific user
    public function get_notes_count_by_user($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results('notes');
    }
}
