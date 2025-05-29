<?php
class Settings extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('url');
		$this->load->helper('url', 'form');
		$this->load->library('form_validation');
		$this->load->model('PayrollModel');
		$this->load->model('SettingsModel');
		$this->load->library('user_agent');


		if ($this->session->userdata('logged_in') !== TRUE) {
			redirect('login');
		}
	}
}
