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
		$this->load->model('StudentModel');
		$this->load->model('SettingsModel');
		$this->load->library('user_agent');


		if ($this->session->userdata('logged_in') !== TRUE) {
			redirect('login');
		}
	}

	public function uploadletterhead()
	{
		$config['upload_path'] = './upload/banners/';
		$config['allowed_types'] = 'jpg|gif|png';
		$config['max_size'] = 15000;
		//$config['max_width'] = 1500;
		//$config['max_height'] = 1500;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('nonoy')) {
			$msg = array('error' => $this->upload->display_errors());

			$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Error uploading the file.</b></div>');
		} else {
			$data = array('image_metadata' => $this->upload->data());
			//get data from the form
			$username = $this->session->userdata('username');
			//$filename=$this->input->post('nonoy');
			$filename = $this->upload->data('file_name');

			$que = $this->db->query("update o_srms_settings set letterhead_web='$filename'");
			$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Uploaded Succesfully!</b></div>');
			//$this->load->view('loginFormImage');
			redirect('Settings/loginFormBanner');
		}
	}


	public function Sections()
	{
		$this->load->model('SettingsModel');

		// Get unique year levels and courses for dropdowns
		$result['yearLevels'] = $this->SettingsModel->get_year_levels1(); // Fetch distinct year levels
		$result['courses'] = $this->SettingsModel->get_courseTable1(); // Fetch distinct courses
		$result['desc'] = $this->SettingsModel->get_course(); // Fetch distinct courses

		// Get filter values from URL
		$selectedYearLevel = $this->input->get('yearLevel');
		$selectedCourse = $this->input->get('course');

		// Query the sections table with filters
		$this->db->select('*');
		$this->db->from('sections');

		if ($selectedYearLevel) {
			$this->db->where('YearLevel', $selectedYearLevel);
		}
		if ($selectedCourse) {
			$this->db->where('Course', $selectedCourse);
		}

		$this->db->order_by('Section', 'ASC');
		$query = $this->db->get();
		$result['data'] = $query->result(); // Store filtered sections

		// Load the view with filtered data
		$this->load->view('settings_sections', $result);


		// Check if the form was submitted
		if ($this->input->post('submit')) {
			// Get data from the form
			$section = $this->input->post('Section');
			$course = $this->input->post('Course');
			$yearLevel = $this->input->post('YearLevel');

			// Set the timezone
			date_default_timezone_set('Asia/Manila');
			$now = date('H:i:s A');
			$date = date('Y-m-d');
			$encoder = $this->session->userdata('username');

			// Description for the log entry
			$description = 'Encoded a section: ' . $section;

			// Check if the section already exists
			$this->db->where('Section', $section);
			$this->db->where('Course', $course);
			$this->db->where('YearLevel', $yearLevel);
			$query = $this->db->get('sections');

			if ($query->num_rows() > 0) {
				$this->session->set_flashdata('danger', 'Duplicate entry.');
			} else {
				// Insert the new section with additional data
				$data = array(
					'Section' => $section,
					'Course' => $course,
					'YearLevel' => $yearLevel
				);
				$this->db->insert('sections', $data);

				// Insert the action trail log
				$logData = array(
					'atDesc' => $description,
					'atDate' => $date,
					'atTime' => $now,
					'atRes' => $encoder
				);
				$this->db->insert('atrail', $logData);

				$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Section added successfully.</b></div>');
			}

			redirect('Settings/Sections');
		}
	}





	//delete Section
	public function deleteSection()
	{
		$id = $this->input->get('id');
		$username = $this->session->userdata('username');
		date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
		$now = date('H:i:s A');
		$date = date("Y-m-d");
		$query = $this->db->query("delete from sections where sectionID='" . $id . "'");
		$query = $this->db->query("insert into atrail values('','Deleted a Section','$date','$now','$username','$id')");
		$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Section deleted successfully.</b></div>');
		redirect('Settings/Sections');
	}
	//delete Course
	public function deleteCourse()
	{
		// Get the course ID from GET parameters
		$id = $this->input->get('id');
		$username = $this->session->userdata('username');

		// Set timezone and get current date and time
		date_default_timezone_set('Asia/Manila'); // Adjust to your timezone
		$now = date('H:i:s A');
		$date = date("Y-m-d");

		// Delete the course record
		$query = $this->db->query("DELETE FROM course_table WHERE courseid='" . $id . "'");

		// Log the deletion action in the trail
		$query = $this->db->query("INSERT INTO atrail VALUES('', 'Deleted a Course', '$date', '$now', '$username', '$id')");

		// Set a success message and redirect
		$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Course deleted successfully.</b></div>');
		redirect('Settings/Department');
	}

	public function SectionsList()
	{
		$course = $this->input->get('Course');

		$this->load->model('SettingsModel');
		$data['sections'] = $this->SettingsModel->getSectionsByCourse($course);
		$data['course'] = $course;

		$this->load->view('sections_view', $data); // Adjust the path if needed
	}

	public function addSection()
	{
		$data = [
			'Course' => $this->input->post('Course'),
			'Section' => $this->input->post('Section'),
			'YearLevel' => $this->input->post('YearLevel')
		];

		$this->SettingsModel->insertSection($data);
		$this->session->set_flashdata('msg', '<div class="alert alert-success">Section added successfully.</div>');

		redirect('Settings/SectionsList?Course=' . $data['Course']);
	}



	function Department()
	{
		$result['data'] = $this->SettingsModel->getDepartmentList();
		$result['yearLevels'] = $this->SettingsModel->get_year_levels();
		$result['course'] = $this->SettingsModel->course();
		$this->load->view('settings_department', $result);

		if ($this->input->post('submit')) {
			// Get data from the form
			$CourseCode = $this->input->post('CourseCode');
			$CourseDescription = $this->input->post('CourseDescription');
			$Major = $this->input->post('Major');
			$Duration = $this->input->post('Duration');
			$recogNo = $this->input->post('recogNo');
			$SeriesYear = $this->input->post('SeriesYear');
			$ProgramHead = $this->input->post('ProgramHead');

			date_default_timezone_set('Asia/Manila'); // Set timezone
			$now = date('H:i:s A');
			$date = date("Y-m-d");
			$Encoder = $this->session->userdata('username');

			$description = 'Encoded a Course ' . $CourseDescription;

			// Check if record already exists
			$que = $this->db->query("SELECT * FROM course_table WHERE CourseDescription = '$CourseDescription' AND Major = '$Major'");
			$row = $que->num_rows();

			if ($row > 0) {
				// Flash message for duplicate entry
				$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Duplicate entry. The record already exists.</b></div>');
				redirect('Settings/Department');
			} else {
				// Save record and track changes
				$this->db->query("INSERT INTO course_table VALUES('', '$CourseCode', '$CourseDescription', '$Major', '$Duration', '$recogNo', '$SeriesYear', '$ProgramHead')");
				$this->db->query("INSERT INTO atrail VALUES('', '$description', '$date', '$now', '$Encoder', '')");

				// Flash success message
				$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>One record added successfully.</b></div>');
				redirect('Settings/Department');
			}
		}
	}



	public function displaysubByCourse()
	{

		$result['data'] = $this->SettingsModel->display_course();
		$result['yearLevels'] = $this->SettingsModel->get_year_levels();
		$result['course'] = $this->SettingsModel->course();
		$this->load->view('settings_department_Subject', $result);
	}



	public function schoolInfo()
	{
		$this->load->model('SettingsModel');

		if ($this->input->post('submit')) {
			$data = array(
				'SchoolName'            => $this->input->post('SchoolName'),
				'SchoolAddress'         => $this->input->post('SchoolAddress'),
				'SchoolHead'            => $this->input->post('SchoolHead'),
				'sHeadPosition'         => $this->input->post('sHeadPosition'),
				'Registrar'             => $this->input->post('Registrar'),
				'registrarPosition'     => $this->input->post('registrarPosition'),
				'clerk'                 => $this->input->post('clerk'),
				'clerkPosition'         => $this->input->post('clerkPosition'),
				'administrative'        => $this->input->post('administrative'),
				'administrativePosition' => $this->input->post('administrativePosition'),
				'admissionOfficer'      => $this->input->post('admissionOfficer'),
				'accountant'            => $this->input->post('accountant'),
				'cashier'               => $this->input->post('cashier'),
				'cashierPosition'       => $this->input->post('cashierPosition'),
				'PropertyCustodian'     => $this->input->post('PropertyCustodian'),
				'slogan'                => $this->input->post('slogan'),
				'active_sem'                => $this->input->post('active_sem'),
				'active_sy'                => $this->input->post('active_sy'),
				'allow_signup'                => $this->input->post('allow_signup')
			);

			$this->db->update('o_srms_settings', $data);

			// Log the update in atrail
			$trail = array(
				'atDesc'    => 'Updated the School Info',
				'atDate'      => date('Y-m-d'),
				'atTime'      => date('h:i:s A'),
				'atRes'  => $this->session->userdata('username'),
				'atSNo'    => ''
			);
			$this->db->insert('atrail', $trail);

			$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Updated successfully.</b></div>');
			redirect('Settings/schoolInfo');
		} else {
			$result['data'] = $this->SettingsModel->getSchoolInfo();
			$this->load->view('settings_school_info', $result);
		}
	}



	public function loginFormBanner()
	{
		$this->load->view('settings_login_image');
	}
	public function uploadloginFormImage()
	{
		$config['upload_path'] = './upload/banners/';
		$config['allowed_types'] = 'jpg|gif|png';
		$config['max_size'] = 15000;
		//$config['max_width'] = 1500;
		//$config['max_height'] = 1500;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('nonoy')) {
			$msg = array('error' => $this->upload->display_errors());

			$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Error uploading the file.</b></div>');
		} else {
			$data = array('image_metadata' => $this->upload->data());
			//get data from the form
			$username = $this->session->userdata('username');
			//$filename=$this->input->post('nonoy');
			$filename = $this->upload->data('file_name');

			$que = $this->db->query("update o_srms_settings set loginFormImage='$filename'");
			$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Uploaded Succesfully!</b></div>');
			//$this->load->view('loginFormImage');
			redirect('Settings/loginFormBanner');
		}
	}


	public function uploadloginLogo()
	{
		$config['upload_path'] = './upload/banners/';
		$config['allowed_types'] = 'jpg|gif|png';
		$config['max_size'] = 15000;
		//$config['max_width'] = 1500;
		//$config['max_height'] = 1500;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('nonoy')) {
			$msg = array('error' => $this->upload->display_errors());

			$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center"><b>Error uploading the file.</b></div>');
		} else {
			$data = array('image_metadata' => $this->upload->data());
			//get data from the form
			$username = $this->session->userdata('username');
			//$filename=$this->input->post('nonoy');
			$filename = $this->upload->data('file_name');

			$que = $this->db->query("update o_srms_settings set login_form_image='$filename'");
			$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Uploaded Succesfully!</b></div>');
			//$this->load->view('loginFormImage');
			redirect('Settings/loginFormBanner');
		}
	}





	function ethnicity()
	{
		$result['data'] = $this->SettingsModel->get_ethnicity();
		$this->load->view('settings_ethnicity', $result);
	}




	public function Addethnicity()
	{
		if ($this->input->post('save')) {
			$data = array(
				'ethnicity' => $this->input->post('ethnicity')
			);
			$this->load->model('SettingsModel');
			$this->SettingsModel->insertethnicity($data);


			redirect('Settings/ethnicity');
		}
		$this->load->view('settings_Addethnicity');
	}






	public function updateCourse()
	{
		// Get the course ID from GET parameters
		$courseid = $this->input->get('courseid');

		// Fetch course data using the provided course ID
		$result['data'] = $this->SettingsModel->getcoursebyId($courseid);

		// Load the view with the course data
		$this->load->view('updatecourse', $result);

		// Check if the form has been submitted
		if ($this->input->post('update')) {

			// Get the updated course data from POST request
			$CourseCode = $this->input->post('CourseCode');
			$CourseDescription = $this->input->post('CourseDescription');
			$Major = $this->input->post('Major');
			$Duration = $this->input->post('Duration');
			$recogNo = $this->input->post('recogNo');
			$SeriesYear = $this->input->post('SeriesYear');
			$ProgramHead = $this->input->post('ProgramHead');

			// Prepare the data array for updating
			$data = [
				'CourseCode' => $CourseCode,
				'CourseDescription' => $CourseDescription,
				'Major' => $Major,
				'Duration' => $Duration,
				'recogNo' => $recogNo,
				'SeriesYear' => $SeriesYear,
				'ProgramHead' => $ProgramHead
			];

			// Update the course in the database
			$this->SettingsModel->updateCourse($courseid, $data);

			// Set a success message and redirect
			$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Course updated successfully.</b></div>');
			redirect('Settings/Department');
		}
	}


	public function updateSection()
	{
		// Get the section ID from GET parameters
		$sectionID = $this->input->get('sectionID');

		// Fetch section and department data
		$result['data'] = $this->SettingsModel->getsectionbyId($sectionID);
		$result['data1'] = $this->SettingsModel->getDepartmentList();

		// Check if the form has been submitted
		if ($this->input->post('update')) {
			// Get the updated section data from POST request
			$section = $this->input->post('Section');

			// Prepare the data array for updating
			$data = ['Section' => $section];

			// Update the section in the database
			$this->SettingsModel->updateSection($sectionID, $data);

			// Set a success message and redirect
			$this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Section updated successfully.</b></div>');
			redirect('Settings/Sections');
		} else {
			// Load the view with the section data
			$this->load->view('updateSection', $result);
		}
	}














	public function updateethnicity()
	{
		$id = $this->input->get('id');
		$result['data'] = $this->SettingsModel->getethnicitybyId($id);
		$this->load->view('updateethnicity', $result);

		if ($this->input->post('update')) {

			$ethnicity = $this->input->post('ethnicity');

			$this->SettingsModel->updateethnicity($id, $ethnicity);
			$this->session->set_flashdata('author', 'Record updated successfully');
			redirect('Settings/ethnicity');
		}
	}


	public function Deleteethnicity()
	{
		$id = $this->input->get('id');
		if ($id) {
			$this->SettingsModel->Delete_ethnicity($id);
			$this->session->set_flashdata('ethnicity', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('ethnicity', 'Error deleting record');
		}

		redirect('Settings/ethnicity');
	}





	function religion()
	{
		$result['data'] = $this->SettingsModel->get_religion();
		$this->load->view('settings_religion', $result);
	}




	public function Addreligion()
	{
		if ($this->input->post('save')) {
			$data = array(
				'religion' => $this->input->post('religion')
			);
			$this->load->model('SettingsModel');
			$this->SettingsModel->insertreligion($data);


			redirect('Settings/religion');
		}
		$this->load->view('settings_Addreligion');
	}


	public function updatereligion()
	{
		$id = $this->input->get('id');
		$result['data'] = $this->SettingsModel->getreligionbyId($id);
		$this->load->view('updateReligion', $result);

		if ($this->input->post('update')) {

			$religion = $this->input->post('religion');

			$this->SettingsModel->updatereligion($id, $religion);
			$this->session->set_flashdata('author', 'Record updated successfully');
			redirect('Settings/religion');
		}
	}


	public function Deletereligion()
	{
		$id = $this->input->get('id');
		if ($id) {
			$this->SettingsModel->Delete_religion($id);
			$this->session->set_flashdata('religion', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('religion', 'Error deleting record');
		}

		redirect('Settings/religion');
	}

	// for prevschool

	function prevschool()
	{
		$result['data'] = $this->SettingsModel->get_prevschool();
		$this->load->view('settings_prevschool', $result);
	}




	public function Addprevschool()
	{
		if ($this->input->post('save')) {
			$data = array(
				'School' => $this->input->post('School'),
				'Address' => $this->input->post('Address')
			);
			$this->load->model('SettingsModel');
			$this->SettingsModel->insertprevschool($data);


			redirect('Settings/prevschool');
		}
		$this->load->view('settings_Addprevschool');
	}


	public function updateprevschool()
	{
		$schoolID = $this->input->get('schoolID');
		$result['data'] = $this->SettingsModel->getprevschoolbyId($schoolID);
		$this->load->view('updateprevschool', $result);

		if ($this->input->post('update')) {

			$School = $this->input->post('School');
			$Address = $this->input->post('Address');


			$this->SettingsModel->updateprevschool($schoolID, $School, $Address);
			$this->session->set_flashdata('author', 'Record updated successfully');
			redirect('Settings/prevschool');
		}
	}


	public function Deleteprevschool()
	{
		$schoolID = $this->input->get('schoolID');
		if ($schoolID) {
			$this->SettingsModel->Delete_prevschool($schoolID);
			$this->session->set_flashdata('prevschool', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('prevschool', 'Error deleting record');
		}

		redirect('Settings/prevschool');
	}

	public function brand()
	{
		$result['data1'] = $this->SettingsModel->getSectionList();
		$result['staff'] = $this->SettingsModel->get_staff();
		$result['data'] = $this->SettingsModel->get_brand();


		if ($this->input->post('save')) {
			$data = array(
				'brand' => $this->input->post('brand')
			);
			$this->load->model('SettingsModel');
			$this->SettingsModel->insertBrand($data);


			redirect('Settings/brand');
		}
		$this->load->view('ls_brand', $result);
	}



	public function updateBrand()
	{
		$brandID = $this->input->post('brandID');
		if ($this->input->post('update')) {
			$brand = $this->input->post('brand');

			// Update track and strand in the database
			$this->SettingsModel->update_brand($brandID, $brand);

			$this->session->set_flashdata('success', 'Record updated successfully');
			redirect('Settings/brand');
		} else {
			$result['data'] = $this->SettingsModel->get_brandbyID($brandID);
			$this->load->view('ls_brand', $result);
		}
	}



	public function DeleteBrand()
	{
		$brandID = $this->input->get('brandID');
		if ($brandID) {
			$this->SettingsModel->Delete_brand($brandID);
			$this->session->set_flashdata('brand', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('brand', 'Error deleting record');
		}

		redirect('Settings/brand');
	}




	public function category()
	{
		$result['data'] = $this->SettingsModel->get_category();


		if ($this->input->post('save')) {
			$data = array(
				'Category' => $this->input->post('Category'),
				'Sub_category' => $this->input->post('Sub_category')
			);
			$this->load->model('SettingsModel');
			$this->SettingsModel->insertCategory($data);


			redirect('Settings/category');
		}
		$this->load->view('ls_category', $result);
	}



	public function updateCategory()
	{
		$CatNo = $this->input->post('CatNo');
		if ($this->input->post('update')) {
			$Category = $this->input->post('Category');
			$Sub_category = $this->input->post('Sub_category');


			// Update track and strand in the database
			$this->SettingsModel->update_category($CatNo, $Category, $Sub_category);

			$this->session->set_flashdata('success', 'Record updated successfully');
			redirect('Settings/category');
		} else {
			$result['data'] = $this->SettingsModel->get_categorybyID($CatNo);
			$this->load->view('ls_brand', $result);
		}
	}



	public function DeleteCategory()
	{
		$CatNo = $this->input->get('CatNo');
		if ($CatNo) {
			$this->SettingsModel->Delete_category($CatNo);
			$this->session->set_flashdata('category', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('category', 'Error deleting record');
		}

		redirect('Settings/category');
	}

	public function office()
	{
		$result['data'] = $this->SettingsModel->get_office();


		if ($this->input->post('save')) {
			$data = array(
				'office' => $this->input->post('office')
			);
			$this->SettingsModel->insertOffice($data);


			redirect('Settings/office');
		}
		$this->load->view('ls_office', $result);
	}



	public function updateOffice()
	{
		$officeID = $this->input->post('officeID');
		if ($this->input->post('update')) {
			$office = $this->input->post('office');

			// Update track and strand in the database
			$this->SettingsModel->update_office($officeID, $office);

			$this->session->set_flashdata('success', 'Record updated successfully');
			redirect('Settings/office');
		} else {
			$result['data'] = $this->SettingsModel->get_officebyID($officeID);
			$this->load->view('ls_office', $result);
		}
	}



	public function DeleteOffice()
	{
		$officeID = $this->input->get('officeID');
		if ($officeID) {
			$this->SettingsModel->Delete_office($officeID);
			$this->session->set_flashdata('office', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('office', 'Error deleting record');
		}

		redirect('Settings/office');
	}


	public function Subjects()
	{
		$this->load->model('SettingsModel');
		$this->load->library('session');

		// Get filter values from URL
		$selectedYearLevel = $this->input->get('yearLevel');
		$selectedCourse = $this->input->get('Course');
		$selectedMajor = $this->input->get('Major');
		$selectedSemester = $this->input->get('Semester');

		// Get unique year levels, courses, and semesters for filters
		$result['yearLevels'] = $this->SettingsModel->get_year_levels();
		$result['course'] = $this->SettingsModel->course();
		$result['semesters'] = $this->SettingsModel->get_semesters();

		// Filter subjects based on selected values
		$this->db->select('*');
		$this->db->from('subjects');

		if ($selectedYearLevel) {
			$this->db->where('yearLevel', $selectedYearLevel);
		}
		if ($selectedCourse) {
			$this->db->where('course', $selectedCourse);
		}
		if ($selectedMajor) {
			$this->db->where('major', $selectedMajor);
		}
		if ($selectedSemester) {
			$this->db->where('semester', $selectedSemester);
		}

		$query = $this->db->get();
		$result['data'] = $query->result();

		// Retain selected course/major for form rendering
		$result['selectedCourse'] = $selectedCourse;
		$result['selectedMajor'] = $selectedMajor;

		// Handle form submission
		if ($this->input->post('save')) {
			$data = array(
				'Course'       => $this->input->post('Course'),
				'Major'        => $this->input->post('Major'),
				'SubjectCode'  => $this->input->post('SubjectCode'),
				'description'  => $this->input->post('description'),
				'lecunit'      => $this->input->post('lecunit'),
				'labunit'      => $this->input->post('labunit'),
				'prereq'       => $this->input->post('prereq'),
				'totalUnits'   => $this->input->post('lecunit') + $this->input->post('labunit'),
				'YearLevel'    => $this->input->post('YearLevel'),
				'Semester'     => $this->input->post('Semester') ?? '',
				'SemEffective' => $this->input->post('Semester') ?? '',
				'SYEffective'  => $this->input->post('SYEffective') ?? '',
				'Effectivity'  => $this->input->post('Effectivity')
			);

			$this->SettingsModel->insertsubjects($data);
			$this->session->set_flashdata('success', 'Subject added successfully!');
			redirect('Settings/Subjects?Course=' . urlencode($selectedCourse) . '&Major=' . urlencode($selectedMajor));
		}

		$this->load->view('subjects', $result);
	}


	public function updatesubjects()
	{
		$subjectid = $this->input->post('subjectid');
		if ($this->input->post('update')) {
			// Collecting the form data
			$Course = $this->input->post('Course');
			$Major = $this->input->post('Major');
			$SubjectCode = $this->input->post('SubjectCode');
			$description = $this->input->post('description');
			$lecunit = $this->input->post('lecunit');
			$labunit = $this->input->post('labunit');
			$prereq = $this->input->post('prereq');
			$totalUnits = $this->input->post('totalUnits');
			$YearLevel = $this->input->post('YearLevel');
			$Semester = $this->input->post('Semester');
			$SYEffective = $this->input->post('SYEffective');
			$Effectivity = $this->input->post('Effectivity');

			// Updating subject data
			$this->SettingsModel->update_subject(
				$subjectid,
				$SubjectCode,
				$description,
				$YearLevel,
				$Course,
				$Semester,
				$Major,
				$lecunit,
				$labunit,
				$prereq,
				$totalUnits,
				$Semester,
				$SYEffective,
				$Effectivity
			);

			// Set flash message and redirect
			$this->session->set_flashdata('success', 'Record updated successfully');
			redirect('Settings/Subjects');
		} else {
			// Retrieve the existing subject data to display in the form
			$result['data'] = $this->SettingsModel->get_subjectbyId($subjectid);
			$this->load->view('subjects', $result);
		}
	}

	public function Deletesubject()
	{
		$id = $this->input->get('id');
		if ($id) {
			$this->SettingsModel->Delete_subjects($id);
			$this->session->set_flashdata('success', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('success', 'Error deleting subject.');
		}

		redirect('Settings/Subjects');
	}
	public function ClassProgram()
	{
		$course = $this->input->get('course');
		$sy = $this->session->userdata('sy');
		$sem = $this->session->userdata('semester');

		$result['yearLevels'] = $this->SettingsModel->get_Yearlevels();
		$result['data'] = $this->SettingsModel->get_classProgram($sy, $sem, $course);
		$result['staff'] = $this->SettingsModel->get_staff();
		$result['sub3'] = $this->SettingsModel->GetSection();
		$result['sub5'] = $this->SettingsModel->GetSection1();
		$result['courses'] = $this->SettingsModel->get_courseTable();
		$result['sec'] = $this->SettingsModel->GetSection();
		$result['sub4'] = $this->SettingsModel->GetSub4();
		$result['selectedCourse'] = $course;  // 👈 Pass the selected course

		$this->load->view('ClassProgram', $result);
	}

	public function getSubjectsByYearLevel()
	{
		$yearLevel = $this->input->post('yearLevel');
		$subjects = $this->SettingsModel->getSubjectsByYearLevel($yearLevel);
		echo json_encode($subjects);
	}


	public function classSched()
	{
		$this->load->model('SettingsModel');
		$result['courses'] = $this->SettingsModel->get_courses();

		$this->load->view('ClassSched', $result);
	}





	public function classprogramform()
	{
		$this->load->model('SettingsModel');
		$loggedInYearLevel = $this->session->userdata('yearLevel');

		$result['yearLevels'] = $this->SettingsModel->get_Yearlevels();
		$result['courses'] = $this->SettingsModel->get_courseTable(); // Added courses
		$result['staff'] = $this->SettingsModel->get_staff();
		$result['sub3'] = $this->SettingsModel->GetSub3();
		$result['sec'] = $this->SettingsModel->GetSection();

		if ($loggedInYearLevel) {
			$result['data'] = $this->SettingsModel->get_subjects_by_yearlevel2($loggedInYearLevel);
		} else {
			$result['data'] = [];
		}

		$this->load->view('classprogramForm', $result);
	}



	public function insertClassform()
	{
		// Get all POST inputs
		$formData = $this->input->post();

		// Get actual column names from semsubjects table
		$fields = $this->db->list_fields('semsubjects');

		// Keep only keys in $formData that exist in semsubjects table
		$dataToInsert = array_intersect_key($formData, array_flip($fields));

		// Check for duplicate based on YearLevel, SubjectCode, Section, SY
		if ($this->SettingsModel->checkClassExists(
			$dataToInsert['YearLevel'],
			$dataToInsert['SubjectCode'],
			$dataToInsert['Section'],
			$dataToInsert['SY']
		)) {
			$this->session->set_flashdata('error', "Class Program already exists for Subject: {$dataToInsert['SubjectCode']}, Section: {$dataToInsert['Section']}.");
			redirect($this->agent->referrer());
			return;
		}

		// Insert data
		$this->db->insert('semsubjects', $dataToInsert);

		$this->session->set_flashdata('success', 'Class Program successfully saved.');
		redirect($this->agent->referrer());
	}



	public function updateClassform()
	{
		// Get all POST inputs
		$formData = $this->input->post();

		// Get the subject ID from the form
		$subjectId = $formData['subjectid'];
		unset($formData['subjectid']); // Remove from data to be updated

		// Get actual column names from semsubjects table
		$fields = $this->db->list_fields('semsubjects');

		// Keep only keys in $formData that exist in semsubjects table
		$dataToUpdate = array_intersect_key($formData, array_flip($fields));

		// Optional: Check for duplicate based on YearLevel, SubjectCode, Section, SY
		if ($this->SettingsModel->checkClassExists(
			$dataToUpdate['YearLevel'],
			$dataToUpdate['SubjectCode'],
			$dataToUpdate['Section'],
			$dataToUpdate['SY'],
			$subjectId // so we can exclude this current record when checking
		)) {
			$this->session->set_flashdata('error', "Class Program already exists for Subject: {$dataToUpdate['SubjectCode']}, Section: {$dataToUpdate['Section']}.");
			redirect($this->agent->referrer());
			return;
		}

		// Perform the update
		$this->db->where('subjectid', $subjectId);
		$this->db->update('semsubjects', $dataToUpdate);

		$this->session->set_flashdata('success', 'Class Program successfully updated.');
		redirect($this->agent->referrer());
	}



	public function getClassProgramById()
	{
		$subjectid = $this->input->post('subjectid');
		$data = $this->SettingsModel->getClassProgramById($subjectid);

		echo json_encode($data);
	}


	public function getDescriptionBySubjectCode()
	{
		$subjectCode = $this->input->post('subjectCode');
		$description = $this->SettingsModel->getDescriptionBySubjectCode($subjectCode);
		echo json_encode(['description' => $description]);
	}



	public function insertClass()
	{
		$subjects = $this->input->post('subjects');
		$response = ['success' => true, 'messages' => []];

		foreach ($subjects as $subject) {
			$yearLevel = $subject['yearLevel'];
			$course = $subject['course']; // Ensure course is being passed
			$subjectCode = $subject['subjectCode'];
			$section = $subject['section'];
			$schoolYear = $subject['sy']; // Make sure 'sy' is being passed from the AJAX request

			if ($this->SettingsModel->checkClassExists($yearLevel, $subjectCode, $section, $schoolYear)) {
				$response['success'] = false;
				$response['messages'][] = "Record already exists for Subject: $subjectCode, Section: $section.";
				continue;
			}

			// Prepare the data array to insert
			$data = [
				'YearLevel'   => $yearLevel,
				'Course'      => $course, // Include course
				'SubjectCode' => $subjectCode,
				'Description' => $subject['description'],
				'IDNumber'    => $subject['adviser'],
				'Section'     => $section,
				'SchedTime'   => $subject['daysOfClass'],
				'SY'          => $schoolYear  // Ensure SY is saved
			];

			// Insert the data into the database
			$this->SettingsModel->insertclass($data);
		}

		if ($response['success']) {
			$this->session->set_flashdata('success', 'Class program created successfully.');
		} else {
			$this->session->set_flashdata('error', implode('<br>', $response['messages']));
		}

		// Send JSON response to client
		echo json_encode($response);
		exit();
	}


	public function get_subjects_by_yearlevel1()
	{
		$this->load->model('SettingsModel');

		$selectedYearLevel = $this->input->post('year_level');

		// Get subjects for the selected Year Level
		$subjects = $this->SettingsModel->get_subjects_by_yearlevel1($selectedYearLevel);

		// Return the data as JSON
		echo json_encode($subjects);
	}


	public function getSectionsByCourseAndYearLevel()
	{
		$yearLevel = $this->input->post('yearLevel');
		$course = $this->input->post('course');

		// Fetch sections based on Year Level and Course
		$sections = $this->SettingsModel->get_sections_by_course_and_yearlevel($yearLevel, $course);

		echo json_encode($sections); // Return JSON response
	}


	public function get_subjects_by_course_and_yearlevel()
	{
		$this->load->model('SettingsModel');

		$yearLevel = $this->input->post('year_level');
		$course = $this->input->post('course');

		// Get subjects for the selected course and year level
		$subjects = $this->SettingsModel->get_subjects_by_course_and_yearlevel($yearLevel, $course);

		// Return the data as JSON
		echo json_encode($subjects);
	}


	public function updateClassProgram()
	{
		// Check if the form is submitted
		if ($this->input->post('update')) {
			$subjectid = $this->input->post('subjectid');
			$SubjectCode = $this->input->post('SubjectCode');
			$Description = $this->input->post('Description');
			$Section = $this->input->post('Section');
			$YearLevel = $this->input->post('YearLevel');
			$Course = $this->input->post('Course');
			$SchedTime = $this->input->post('SchedTime');
			$SY = $this->input->post('SY');
			$SubjectStatus = $this->input->post('SubjectStatus');
			$IDNumber = $this->input->post('IDNumber');

			// Update track and strand in the database
			$this->SettingsModel->update_class($subjectid, $SubjectCode, $Description, $Section, $SchedTime, $IDNumber, $SY, $Course, $YearLevel, $SubjectStatus);

			$this->session->set_flashdata('success', 'Record updated successfully');
			redirect('Settings/ClassProgram');
		} else {
			// Fetch data if the form is not submitted yet
			$subjectid = $this->input->get('subjectid');
			$result['data'] = $this->SettingsModel->get_classbyId($subjectid);
			$result['staff'] = $this->SettingsModel->get_staff();
			$result['sub3'] = $this->SettingsModel->GetSection();
			$result['courses'] = $this->SettingsModel->get_courseTable(); // Added courses
			$this->load->view('ClassProgramUpdate', $result);
		}
	}


	public function DeleteClass()
	{
		$subjectid = $this->input->get('subjectid');
		if ($subjectid) {
			$this->SettingsModel->Delete_class($subjectid);
			$this->session->set_flashdata('success', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('error', 'Error deleting record');
		}

		redirect($this->agent->referrer());
	}


	public function getSectionsByYearLevel()
	{
		$yearLevel = $this->input->post('yearLevel');
		$sections = $this->SettingsModel->get_sections_by_yearlevel($yearLevel);

		echo json_encode($sections); // Return JSON response
	}
}
