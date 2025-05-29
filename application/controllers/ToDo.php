<?php
class ToDo extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ToDoModel');

        // Optional: Block access if not logged in
        if (!$this->session->userdata('username')) {
            redirect('login'); // change to your login page
        }
    }

    public function index()
    {
        $username = $this->session->userdata('username');
        $data['todos'] = $this->ToDoModel->get_all($username);
        $this->load->view('ToDoList', $data);
    }

    public function pendingList()
    {
        $username = $this->session->userdata('username');
        $data['todos'] = $this->ToDoModel->pendingList($username);
        $this->load->view('ToDoList', $data);
    }


    public function add()
    {
        $task = $this->input->post('task');
        $due_date = $this->input->post('due_date');
        $username = $this->session->userdata('username');

        if ($task && $username && $due_date) {
            // Save to ToDo table
            $this->ToDoModel->add($task, $username, $due_date);

            // Save to calendar_events table
            $calendarData = [
                'title'     => $task,
                'start'     => $due_date,
                'end'       => $due_date,
                'username'  => $username,
                'status'    => 'Private'
            ];
            $this->db->insert('calendar_events', $calendarData);
        }

        redirect('ToDo');
    }


    public function edit($id)
    {
        $task = $this->input->post('task');
        $due_date = $this->input->post('due_date');

        $this->ToDoModel->update_task($id, [
            'task' => $task,
            'due_date' => $due_date
        ]);

        $this->session->set_flashdata('success', 'Task updated successfully.');
        redirect('ToDo');
    }


    public function mark_done($id)
    {
        $manilaTimeZone = new DateTimeZone('Asia/Manila');
        $currentDateTime = new DateTime('now', $manilaTimeZone);  // Get current date and time in Manila time
        $completed_at = $currentDateTime->format('Y-m-d H:i:s');  // Format the date as MySQL DATETIME format

        // Update the task status to "done" and set the completion date
        $this->ToDoModel->mark_task_done($id, $completed_at);

        // Redirect back to the ToDo list
        redirect('ToDo');
    }



    public function delete($id)
    {
        $username = $this->session->userdata('username');
        $this->ToDoModel->delete($id, $username);
        redirect('ToDo');
    }

    public function mark_undone($id)
    {
        // Reset the 'completed_at' field to NULL
        $this->ToDoModel->mark_task_undone($id);

        // Redirect back to the ToDo list
        redirect('ToDo');
    }
    public function add_comment($id)
    {
        $comment = $this->input->post('comment');

        $this->ToDoModel->update($id, ['comment' => $comment]);

        $this->session->set_flashdata('success', 'Comment updated.');
        redirect('ToDo');
    }
}
