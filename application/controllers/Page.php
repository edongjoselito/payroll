<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Page extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		// Load necessary models, helpers, or libraries here
		// $this->load->model('Page_model');
		// $this->load->helper('url');


		if ($this->session->userdata('logged_in') !== TRUE) {
			redirect('login');
		}
	}

	public function admin()
	{
		// Default method when accessing /Page
		$this->load->view('dashboard_admin'); // You should create this view
	}


}
