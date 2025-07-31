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
         $this->load->model('Personnel_model');


		if ($this->session->userdata('logged_in') !== TRUE) {
			redirect('login');
		}
    // Load both birthday sets once and share globally with views
    if ($this->session->userdata('logged_in')) {
        $today = $this->Personnel_model->getTodayBirthdays();
        $month = $this->Personnel_model->getMonthBirthdays();
        $this->load->vars([
            'today_birthdays' => $today,
            'month_birthdays' => $month,
            'birthday_count' => count($today)
        ]);
    }
	}

public function admin()
{
    $settingsID = $this->session->userdata('settingsID');

    $this->load->model('SettingsModel');
    $data['company'] = $this->SettingsModel->getSuperAdminbyIds($settingsID); // returns object
     $data['project_count'] = $this->SettingsModel->count_projects($settingsID);
    $data['personnel_count'] = $this->SettingsModel->count_personnel($settingsID);
  $this->db->where('settingsID', $settingsID);
    $data['user_count'] = $this->db->count_all_results('o_users');
    $this->load->view('dashboard_admin', $data);
    $this->load->model('Personnel_model');
$data['birthdays'] = $this->Personnel_model->getTodayBirthdays();


}


	


	// tyrone



public function superAdmin()
{
    $schoolData = $this->SettingsModel->getSchoolInformation();

    // Load user data per settingsID
    foreach ($schoolData as &$row) {
        $this->db->where('settingsID', $row->settingsID);
        $query = $this->db->get('o_users');
        $row->hasAdmin = ($query->num_rows() > 0);
    }

    $result['data'] = $schoolData;
    $this->load->view('dashboard_SuperAdmin', $result);
}



// public function seeAdmin()
// {
//     $settingsID = $this->input->get('settingsID');

//     $this->db->where('settingsID', $settingsID);
//     $query = $this->db->get('o_users');

//     if ($query->num_rows() > 0) {
//         $data['admin'] = $query->row();
//         $this->load->view('view_admin_details', $data);
//     } else {
//         show_error('Admin not found for this company.');
//     }
// }


public function viewAdmins()
{
    $settingsID = $this->input->get('settingsID', TRUE);

    if (!$settingsID || !is_numeric($settingsID)) {
        show_error('Invalid or missing settingsID.');
    }

    $this->db->select('u.*, s.SchoolName, s.SchoolAddress');
    $this->db->from('o_users u');
    $this->db->join('o_srms_settings s', 's.settingsID = u.settingsID', 'left');
    $this->db->where('u.position', 'Admin');
    $this->db->where('u.settingsID', $settingsID);
    $admins = $this->db->get()->result();

    $data['admins'] = $admins;
    $data['settingsID'] = $settingsID;
    $this->load->view('admin_list_view', $data);
}




public function addNewSuperAdmin()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Basic upload config
        $config['upload_path'] = './uploads/school/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['encrypt_name'] = TRUE;
        $config['max_size'] = 2048;

        // Load upload library
        $this->load->library('upload');

        $schoolLogo = null;
        $letterHead = null;

        // Upload school logo
        if (!empty($_FILES['schoolLogo']['name'])) {
            $this->upload->initialize($config);
            if ($this->upload->do_upload('schoolLogo')) {
                $schoolLogo = 'uploads/school/' . $this->upload->data('file_name');
            }
        }

        // Upload letter head
        if (!empty($_FILES['letterHead']['name'])) {
            $this->upload->initialize($config);
            if ($this->upload->do_upload('letterHead')) {
                $letterHead = 'uploads/school/' . $this->upload->data('file_name');
            }
        }

        // Save form data
        $data = [
            'SchoolName'    => $this->input->post('SchoolName'),
            'SchoolAddress' => $this->input->post('SchoolAddress'),
            'SchoolHead'    => $this->input->post('SchoolHead'),
            'sHeadPosition' => $this->input->post('sHeadPosition'),
            'schoolLogo'    => $schoolLogo,
            'letterHead'    => $letterHead
        ];

        $this->SettingsModel->insertSuperAdmin($data);
        $this->session->set_flashdata('msg', 'New Super Admin added!');
        redirect('Page/superAdmin');
    } else {
        show_error('Invalid form submission');
    }
}


public function personnel_dashboard() {
    $this->load->view('personnel_dashboard'); // or echo test message
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
       $settingsID = $this->session->userdata('settingsID');

    // Load the user accounts data and pass it to the view
    $result['data'] = $this->StudentModel->userAccounts($settingsID);
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
			$settingsID = $this->input->post('settingsID', TRUE);
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
					'IDNumber' => $IDNumber,
					 'settingsID' => $settingsID
				);

				// Insert data into the database
				$this->db->insert('o_users', $data);

				$this->session->set_flashdata('success', '<div class="alert alert-success text-center"><b>New account has been created successfully.</b></div>');
				redirect('Page/userAccounts');
			}
		}
	}



public function saveAdminFromSuperAdmin()
{
    // Enable error reporting (for debugging)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $this->input->post('username', TRUE);
        $IDNumber = $this->input->post('IDNumber', TRUE);
        $password = sha1($this->input->post('password'));
        $acctLevel = $this->input->post('acctLevel', TRUE);
        $fName = $this->input->post('fName', TRUE);
        $mName = $this->input->post('mName', TRUE);
        $lName = $this->input->post('lName', TRUE);
        $email = $this->input->post('email', TRUE);
        $settingsID = $this->input->post('settingsID', TRUE);
        $dateCreated = date("Y-m-d");

        // Check if username already exists
        $this->db->where('username', $username);
        $query = $this->db->get('o_users');

        if ($query->num_rows() > 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Username already exists.</b></div>');
        } else {
            $data = array(
                'username'     => $username,
                'password'     => $password,
                'position'     => $acctLevel,
                'fName'        => $fName,
                'mName'        => $mName,
                'lName'        => $lName,
                'email'        => $email,
                'avatar'       => 'avatar.png',
                'acctStat'     => 'active',
                'dateCreated'  => $dateCreated,
                'IDNumber'     => $IDNumber,
                'settingsID'   => $settingsID
            );

            $insert = $this->db->insert('o_users', $data);

            if ($insert) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Admin account created successfully.</b></div>');
            } else {
                $error = $this->db->error();
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Database error: ' . $error['message'] . '</b></div>');
            }
        }

        redirect('Page/superAdmin');
    } else {
        show_error('Invalid form submission');
    }
}

// BIRTHDAY----------------
public function birthdays_today() {
    $this->load->model('Personnel_model');
    $data['title'] = "Today's Birthday Celebrants";
    $data['birthdays'] = $this->Personnel_model->getTodayBirthdays();

    $this->load->view('birthdays_today', $data); // load only 1 full template view
}

public function birthdays_month() {
     $this->load->model('Personnel_model');
    $data['title'] = "This Month's Birthday Celebrants";
    $data['birthdays'] = $this->Personnel_model->getMonthBirthdays();
 $data['birthday_count'] = count($data['birthdays']);
    $this->load->view('birthdays_month', $data); // same here
}

}
