<?php
class Page extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('url');
		$this->load->helper('url', 'form');
		$this->load->library('form_validation');
		$this->load->model('PayrollModel');
		$this->load->library('user_agent');

		if ($this->session->userdata('logged_in') !== TRUE) {
			redirect('login');
		}
	}


	function index()
	{
		//Allowing access to admin only
		if ($this->session->userdata('level') === 'Admin') {

			$this->load->view('dashboard_admin');
		} else {
			echo "Access Denied";
		}
	}


	function admin()
	{
		$this->load->view('dashboard_admin');
	}
}
