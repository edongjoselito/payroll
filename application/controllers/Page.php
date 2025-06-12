<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Page extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		// Load necessary models, helpers, or libraries here
		// $this->load->model('Page_model');
		$this->load->model('StudentModel');
		$this->load->model('SettingsModel');
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


	


	// tyrone



		public function superAdmin()
	{
		// Default method when accessing /Page
		$result['data'] = $this->SettingsModel->getSchoolInformation();
		$this->load->view('dashboard_SuperAdmin',  $result); // You should create this view
	}

		public function updateSuperAdmin()
	{
		$settingsID = $this->input->get('settingsID');
		$result['data'] = $this->SettingsModel->getSuperAdminbyId($settingsID);
		$this->load->view('update_Superadmin', $result);

		if ($this->input->post('update')) {
			// File upload for schoolLogo
			$schoolLogo = $this->uploadImage('schoolLogo');

			// File upload for letterHead
			$letterHead = $this->uploadImage('letterHead');

			// Collecting form data
			$data = array(
				'SchoolName' => $this->input->post('SchoolName'),
				'SchoolAddress' => $this->input->post('SchoolAddress'),
				'SchoolHead' => $this->input->post('SchoolHead'),
				'sHeadPosition' => $this->input->post('sHeadPosition'),
			
				'schoolLogo' => $schoolLogo, // Save filename
				'letterHead' => $letterHead, // Save filename
			);

			$this->SettingsModel->updateSuperAdmin($settingsID, $data);
			$this->session->set_flashdata('msg', 'Record updated successfully');
			redirect('Page/superAdmin');
		}
	}

	/**
	 * Uploads an image and returns the filename.
	 */
	private function uploadImage($fieldName)
	{
		$config['upload_path'] = './assets/images/';
		$config['allowed_types'] = 'jpg|jpeg|png|gif';
		$config['max_size'] = 2048; // 2MB
		$config['encrypt_name'] = TRUE; // Encrypt filename for uniqueness

		$this->load->library('upload', $config);

		if ($this->upload->do_upload($fieldName)) {
			$uploadData = $this->upload->data();
			return $uploadData['file_name']; // Return the filename
		} else {
			// If no file was uploaded, keep the existing value
			return $this->input->post($fieldName . '_existing');
		}
	}



	public function userAccounts()
	{
		// Load the user accounts data and pass it to the view
		$result['data'] = $this->StudentModel->userAccounts();
		$this->load->view('user_accounts', $result);

		// Check if the form has been submitted
		if ($this->input->post('submit')) {
			// Sanitize and retrieve data from the form
			$username = $this->input->post('username', TRUE); // TRUE for XSS filtering
			$IDNumber = $this->input->post('IDNumber', TRUE);
			$password = sha1($this->input->post('password')); // Consider using a more secure hashing method
			$acctLevel = $this->input->post('acctLevel', TRUE);
			$fName = $this->input->post('fName', TRUE);
			$mName = $this->input->post('mName', TRUE);
			$lName = $this->input->post('lName', TRUE);
			$completeName = $fName . ' ' . $lName;
			$email = $this->input->post('email', TRUE);
			$dateCreated = date("Y-m-d");

			// Use query builder to check if the username already exists
			$this->db->where('username', $username);
			$query = $this->db->get('o_users');

			if ($query->num_rows() > 0) {
				// Set flash message and redirect if username exists
				$this->session->set_flashdata('danger', '<div class="alert alert-danger text-center"><b>The username is already taken. Please choose a different one.</b></div>');
				redirect('Page/userAccounts');
			} else {
				// Prepare data for insertion
				$data = array(
					'username' => $username,
					'password' => $password,
					'position' => $acctLevel,
					'fName' => $fName,
					'mName' => $mName,
					'lName' => $lName,
					'email' => $email,
					'avatar' => 'avatar.png',
					'acctStat' => 'active',
					'dateCreated' => $dateCreated,
					'IDNumber' => $IDNumber
				);

				// Insert data into the database
				$this->db->insert('o_users', $data);

				$this->session->set_flashdata('success', '<div class="alert alert-success text-center"><b>New account has been created successfully.</b></div>');
				redirect('Page/userAccounts');
			}
		}
	}

}
