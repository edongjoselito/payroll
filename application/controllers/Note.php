<?php
class Note extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('NoteModel');
        $this->load->helper(['form', 'url', 'text']);
        $this->load->library('session');

        // Set the default timezone to Manila (Asia/Manila)
        date_default_timezone_set('Asia/Manila');

        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('login');
        }
    }

    public function index()
    {
        // Fetch notes from the database
        $data['notes'] = $this->NoteModel->get_notes_by_user($this->session->userdata('username'));

        // Pass action as 'index' to the view to handle the display differently
        $data['action'] = 'index';
        $this->load->view('notes_view', $data);
    }

    public function create()
    {
        $data['action'] = 'create';
        $data['note'] = null;
        $this->load->view('notes_view', $data);
    }

    public function store()
    {
        // Get user_id from session
        $user_id = $this->session->userdata('username');

        // Prepare the note data with timestamp in Manila time
        $data = [
            'user_id' => $user_id,
            'title'   => $this->input->post('title'),
            'content' => $this->input->post('content'),
            'created_at' => date('Y-m-d H:i:s') // Current timestamp in Manila Time
        ];

        // Insert the note into the database
        $this->NoteModel->insert_note($data);
        redirect('note');
    }

    public function edit($id)
    {
        // Get the note from the database
        $data['action'] = 'edit';
        $data['note'] = $this->NoteModel->get_note_by_id($id);

        // Ensure the note belongs to the logged-in user
        if ($data['note']->user_id != $this->session->userdata('username')) {
            show_error('Unauthorized access');
        }

        $this->load->view('notes_view', $data);
    }

    public function update($id)
    {
        // Get user_id from session
        $user_id = $this->session->userdata('username');

        // Prepare the updated data with the current timestamp
        $data = [
            'title'   => $this->input->post('title'),
            'content' => $this->input->post('content'),
            'updated_at' => date('Y-m-d H:i:s') // Current timestamp in Manila Time
        ];

        // Update the note
        $this->NoteModel->update_note($id, $data, $user_id);
        redirect('note');
    }

    public function delete($id)
    {
        // Get the user_id from the session
        $user_id = $this->session->userdata('username');

        // Ensure the note belongs to the logged-in user
        if ($this->NoteModel->delete_note($id, $user_id)) {
            redirect('note');
        } else {
            show_error('Unauthorized access');
        }
    }
}
