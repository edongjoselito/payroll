<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index() {
        $settingsID = $this->session->userdata('settingsID');
        $data['users'] = $this->User_model->get_all_users($settingsID);
        $this->load->view('user_manage', $data);
    }

    public function add() {
        $fName = $this->input->post('fName');
        $mName = $this->input->post('mName');
        $lName = $this->input->post('lName');

        $data = [
            'username'    => $this->input->post('username'),
            'password' => sha1($this->input->post('password')),

            'position'    => $this->input->post('position'),
            'fName'       => $fName,
            'mName'       => $mName,
            'lName'       => $lName,
            'name'        => $fName . ' ' . $mName . ' ' . $lName,
            'email'       => $this->input->post('email'),
            'IDNumber'    => $this->input->post('IDNumber'),
            'settingsID'  => $this->session->userdata('settingsID'),
            'dateCreated' => date('Y-m-d'),
            'acctStat'    => 'Active'
        ];

        $this->User_model->insert_user($data);
        redirect('User');
    }

    public function edit($username) {
        $fName = $this->input->post('fName');
        $mName = $this->input->post('mName');
        $lName = $this->input->post('lName');

        $data = [
            'position'   => $this->input->post('position'),
            'fName'      => $fName,
            'mName'      => $mName,
            'lName'      => $lName,
            'name'       => $fName . ' ' . $mName . ' ' . $lName,
            'email'      => $this->input->post('email'),
            'acctStat'   => $this->input->post('acctStat')
        ];

        if (!empty($this->input->post('password'))) {
            $data['password'] = sha1($this->input->post('password'));

        }

        $this->User_model->update_user($username, $data);
        redirect('User');
    }

    public function delete($username) {
        $this->User_model->delete_user($username);
        redirect('User');
    }
}
