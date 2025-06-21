<?php
class StudentModel extends CI_Model
{


	public function getStudentByNumber($studentNumber)
	{
		$this->db->select('StudentNumber, FirstName, MiddleName, LastName, Course');
		return $this->db->get_where('studeprofile', ['StudentNumber' => $studentNumber])->row();
	}

	public function getSubjectsWithGrades($course, $studentNumber, $effectivity = null)
	{
		$this->db->select('s.*, g.Final AS FinalGrade');
		$this->db->from('subjects s');
		$this->db->join('grades g', 's.SubjectCode = g.SubjectCode AND g.StudentNumber = "' . $studentNumber . '"', 'left');
		$this->db->where('s.Course', $course);
		if ($effectivity) {
			$this->db->where('s.Effectivity', $effectivity);
		}
		$this->db->order_by('s.YearLevel, s.Semester');
		return $this->db->get()->result();
	}


	public function getEffectivityOptions()
	{
		$this->db->distinct();
		$this->db->select('Effectivity');
		$this->db->from('subjects');
		$this->db->order_by('Effectivity', 'DESC');
		return $this->db->get()->result();
	}

	public function getEffectivityOptionsByCourse($course)
	{
		$this->db->distinct();
		$this->db->select('Effectivity');
		$this->db->from('subjects');
		$this->db->where('Course', $course);
		$this->db->order_by('Effectivity', 'DESC');
		return $this->db->get()->result();
	}



	public function searchStudents()
	{
		// $this->db->select('*');
		$this->db->select('StudentNumber, FirstName, MiddleName, LastName');
		$this->db->from('studeprofile');
		$this->db->order_by('LastName');
		$query = $this->db->get();
		return $query->result();
	}

	function getProfileAccounting($sem, $sy)
	{
		$this->db->select('sp.StudentNumber, sp.FirstName, sp.MiddleName, sp.LastName, sp.birthDate');
		$this->db->from('studeprofile sp');
		$this->db->join('semesterstude ss', 'sp.StudentNumber = ss.StudentNumber', 'inner');
		$this->db->where('ss.Semester', $sem);
		$this->db->where('ss.SY', $sy);
		$this->db->order_by('sp.LastName', 'ASC');

		$query = $this->db->get();
		return $query->result();
	}

	public function isFlagged($id)
	{
		$this->db->where('StudentNumber', $id);
		$this->db->where('Status', 'unsettled');
		$query = $this->db->get('flagged_students');
		return $query->num_rows() > 0;
	}

	public function getFlagDetails($id)
	{
		$this->db->where('StudentNumber', $id);
		$this->db->where('Status', 'unsettled');
		$query = $this->db->get('flagged_students');
		return $query->row(); // return a single record
	}


	public function get_student_profile($student_number)
	{
		return $this->db->get_where('studeprofile', ['StudentNumber' => $student_number])->row();
	}

	public function get_srms_settings()
	{
		return $this->db->get('o_srms_settings')->row();
	}

	public function get_grades($student_number)
	{
		$this->db->from('grades');
		$this->db->where('StudentNumber', $student_number);
		$this->db->order_by('SY DESC, Semester DESC');
		return $this->db->get()->result();
	}


	function getacountHistory($sem, $sy)
	{
		$this->db->select('studeaccount.StudentNumber, studeaccount.FirstName, studeaccount.MiddleName, studeaccount.LastName, studeaccount.AcctTotal, studeaccount.Discount, studeaccount.TotalPayments, studeaccount.CurrentBalance, paymentsaccounts.PDate, paymentsaccounts.ORNumber, paymentsaccounts.Amount, paymentsaccounts.description');
		$this->db->from('studeaccount');
		$this->db->join('paymentsaccounts', 'studeaccount.StudentNumber = paymentsaccounts.StudentNumber', 'left'); // Use 'left' join to include students with no payments
		$this->db->where('studeaccount.sem', $sem);
		$this->db->where('studeaccount.sy', $sy);
		$this->db->where('paymentsaccounts.sem', $sem);
		$this->db->where('paymentsaccounts.sy', $sy);
		$this->db->order_by('studeaccount.LastName', 'ASC');
		$this->db->order_by('paymentsaccounts.PDate', 'ASC'); // Order payments by date
		$this->db->group_by('paymentsaccounts.ORNumber');

		$query = $this->db->get();
		return $query->result();
	}


	// 	public function getacountHistory($sem, $sy)
	// {
	//     $this->db->distinct();
	//     $this->db->select('StudentNumber');
	//     $this->db->from('studeaccount');
	// 	$this->db->where('sem', $sem); // Ensure 'Semester' is correct
	// 		$this->db->where('sy', $sy); // Ensure 'SY' is correct
	//     $query = $this->db->get();
	//     return $query->result();
	// }



	public function deanList($semester, $sy, $course, $yearLevel, $yearLevelStat)
	{
		// Raw SQL query with parameters
		$sql = "
			SELECT ss.StudentNumber, ss.FName, ss.MName, ss.LName,
				   ROUND(AVG(CAST(CASE 
					   WHEN g.Final = 'INC' THEN 6
					   WHEN NULLIF(g.Final, '') IS NOT NULL THEN g.Final
					   ELSE NULL
				   END AS DECIMAL(3,2))), 1) AS AverageGrade
			FROM grades g
			JOIN semesterstude ss ON g.StudentNumber = ss.StudentNumber
			WHERE g.Semester = ?
			  AND g.SY = ?
			  AND g.Course = ?
			  AND ss.YearLevel = ?
			  AND ss.YearLevelStat = ?
			GROUP BY ss.StudentNumber, ss.FName, ss.MName, ss.LName
			HAVING MAX(CAST(CASE
					   WHEN g.Final = 'INC' THEN 6
					   WHEN NULLIF(g.Final, '') IS NOT NULL THEN g.Final
					   ELSE NULL 
				   END AS DECIMAL(3,2))) <= 2.5
			ORDER BY ss.StudentNumber;
		";

		// Execute the query with bound parameters
		$query = $this->db->query($sql, [$semester, $sy, $course, $yearLevel, $yearLevelStat]);
		return $query->result();
	}

	function totalStudeAccountProfile($sy, $sem)
	{
		$this->db->select('COUNT(DISTINCT sp.StudentNumber) AS StudeCount');
		$this->db->from('studeprofile sp');
		$this->db->join('studeaccount sa', 'sp.StudentNumber = sa.StudentNumber');
		$this->db->where('sa.SY', $sy);
		$this->db->where('sa.Sem', $sem);

		$query = $this->db->get();
		return $query->result(); // Returns a single row with StudeCount
	}


	function totalProfile()
	{
		$query = $this->db->query("SELECT count(StudentNumber) as StudeCount FROM studeprofile");
		return $query->result();
	}


	public function o_srms_settings()
	{
		$this->db->select('*');
		// $this->db->select('StudentNumber, FirstName, MiddleName, LastName');
		$this->db->from('o_srms_settings');
		// $this->db->order_by('LastName');
		$query = $this->db->get();
		return $query->result();
	}

	function getGrades($sem, $sy)
	{
		// Select the required columns from both tables
		$this->db->select('grades.*, studeprofile.*'); // Adjust columns as needed

		// Set the conditions for the grades table
		$this->db->where('grades.Semester', $sem);
		$this->db->where('grades.SY', $sy);

		// Join the studeprofile table on StudentNumber
		$this->db->join('studeprofile', 'grades.StudentNumber = studeprofile.StudentNumber');

		// Perform the query on the grades table
		$query = $this->db->get('grades');

		// Return the result
		return $query->result();
	}


	public function addNewStudent()
	{
		date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
		$data = array(
			'StudentNumber' => $this->input->post('StudentNumber'),
			'FirstName' => $this->input->post('FirstName'),
			'MiddleName' => $this->input->post('MiddleName'),
			'LastName' => $this->input->post('LastName'),
			'nameExtn' => $this->input->post('nameExtn'),
			'Sex' => $this->input->post('Sex'),
			'CivilStatus' => $this->input->post('CivilStatus'),
			'Religion' => $this->input->post('Religion'),
			'Ethnicity' => $this->input->post('Ethnicity'),
			'contactNo' => $this->input->post('contactNo'),
			'birthDate' => $this->input->post('birthDate'),
			'BirthPlace' => $this->input->post('BirthPlace'),
			'Age' => $this->input->post('Age'),
			'Father' => $this->input->post('Father'),
			'FOccupation' => $this->input->post('FOccupation'),
			'Mother' => $this->input->post('Mother'),
			'MOccupation' => $this->input->post('MOccupation'),
			'Guardian' => $this->input->post('Guardian'),
			'GuardianContact' => $this->input->post('GuardianContact'),
			'GuardianRelationship' => $this->input->post('GuardianRelationship'),
			'GuardianAddress' => $this->input->post('GuardianAddress'),
			'Sitio' => $this->input->post('Sitio'),
			'Brgy' => $this->input->post('Brgy'),
			'City' => $this->input->post('City'),
			'Province' => $this->input->post('Province'),
			'sitioPresent' => $this->input->post('Sitio'),
			'brgyPresent' => $this->input->post('Brgy'),
			'cityPresent' => $this->input->post('City'),
			'provincePresent' => $this->input->post('Province'),
			'email' => $this->input->post('email'),
			'working' => $this->input->post('working'),
			'nationality' => $this->input->post('nationality'),
			'settingsID' => 1,
			'Encoder' => $this->session->userdata('username')

		);

		return $this->db->insert('studeprofile', $data);
	}



	public function get_provinces()
	{
		$this->db->select('AddID, Province'); // Ensure AddID is included
		$this->db->group_by('Province'); // Group by Province to get distinct values
		$this->db->order_by('Province', 'ASC');
		$query = $this->db->get('settings_address');
		return $query->result();
	}

	public function get_cities($province = null)
	{
		if (!$province) {
			return []; // Return an empty array if no province is provided
		}

		$this->db->select('AddID, City');
		$this->db->where('Province', $province);
		$this->db->group_by('City');
		$this->db->order_by('City', 'ASC');
		$query = $this->db->get('settings_address');

		return $query->result();
	}

	// Get barangays based on selected city
	public function get_barangays($city)
	{
		$this->db->select('AddID, Brgy'); // Ensure AddID is included
		$this->db->where('City', $city); // Filter by city
		$this->db->group_by('Brgy');
		$this->db->order_by('Brgy', 'ASC');
		$query = $this->db->get('settings_address');
		return $query->result();
	}

public function userAccounts($settingsID)
{
    $this->db->where('position !=', 'Super Admin');
    $this->db->where('settingsID', $settingsID);
    $query = $this->db->get('o_users');
    return $query->result();
}


	function studeGradesGroup($studeno)
	{
		$this->db->select('Semester, SY');
		$this->db->from('studeprofile s');
		$this->db->join('grades g', 's.StudentNumber = g.StudentNumber');
		$this->db->where('s.StudentNumber', $studeno);

		$query = $this->db->get();
		return $query->result();
	}

	public function insert_students()
	{
		// Step 1: Prepare the subquery to get existing usernames
		$this->db->select('username');
		$this->db->from('o_users');
		$subquery = $this->db->get_compiled_select();

		// Step 2: Prepare the main query to select from `studeprofile`
		$this->db->select('StudentNumber AS username', FALSE);
		$this->db->select('SHA1(DATE_FORMAT(birthDate, "%Y-%m-%d")) AS password', FALSE);
		$this->db->select("'Student' AS position", FALSE);
		$this->db->select('FirstName AS fName');
		$this->db->select('MiddleName AS mName');
		$this->db->select('LastName AS lName');
		$this->db->select('email AS email');
		$this->db->select("'avatar.png' AS avatar", FALSE);
		$this->db->select("'active' AS acctStat", FALSE);
		$this->db->select('NOW() AS dateCreated', FALSE);
		$this->db->from('studeprofile');
		$this->db->where_not_in('StudentNumber', $subquery);

		// Compile the select part of the query
		$select_query = $this->db->get_compiled_select();

		// Step 3: Execute the insert query with INSERT IGNORE
		$this->db->query("INSERT IGNORE INTO o_users (username, password, position, fName, mName, lName, email, avatar, acctStat, dateCreated) $select_query");
	}

	public function copy_users_to_o_users()
	{
		$this->load->database();

		// Step 1: Get all usernames already in o_users
		$existingUsernames = $this->db->select('username')->get('o_users')->result_array();
		$existingUsernames = array_column($existingUsernames, 'username');

		// Step 2: Get users not in o_users
		$this->db->select('username, password, position, fName, mName, lName, email, avatar, acctStat, dateCreated, name');
		if (!empty($existingUsernames)) {
			$this->db->where_not_in('username', $existingUsernames);
		}
		$newUsersQuery = $this->db->get('users');
		$newUsers = $newUsersQuery->result_array();

		// Step 3: Insert each non-duplicate user into o_users
		$insertedCount = 0;
		foreach ($newUsers as $user) {
			$this->db->insert('o_users', $user);
			$insertedCount++;
		}

		// Step 4: Set flash message
		if ($insertedCount > 0) {
			$msg = "<div class='alert alert-success'>{$insertedCount} new user(s) copied to <strong>o_users</strong>.</div>";
		} else {
			$msg = "<div class='alert alert-info'>No new users to copy. All usernames already exist in <strong>o_users</strong>.</div>";
		}

		$this->session->set_flashdata('msg', $msg);
		redirect('YourController/your_redirect_target');
	}



	public function studentSignup($id)
	{
		$query = $this->db->get_where('studentsignup', ['StudentNumber' => $id]);
		return $query->result();
	}





	//ADMIN ANNOUNCEMENT ---------------------------------------------------------------------------------
	function announcement()
	{
		$query = $this->db->query("Select * from announcement order by aID desc");
		return $query->result();
	}

	function deleteAnnouncement($id)
	{
		$this->db->query("delete  from announcement where aID='" . $id . "'");
	}

	function deleteUserAccount($id)
	{
		$this->db->query("delete  from users_online where username='" . $id . "'");
	}

	function deleteRequirement($id)
	{
		$this->db->query("delete  from online_requirements where reqID='" . $id . "'");
	}

	public function gradesSummary($sy, $sem)
	{
		$this->db->select('
        ss.StudentNumber,
        CONCAT(sp.LastName, ", ", sp.FirstName, " ", sp.MiddleName) AS StudentName,
        ss.Course,
        ss.YearLevel,
        g.SubjectCode,
        g.Final,
        g.Semester,
        g.SY
    ');
		$this->db->from('grades g');
		$this->db->join('semesterstude ss', 'g.StudentNumber = ss.StudentNumber');
		$this->db->join('studeprofile sp', 'sp.StudentNumber = ss.StudentNumber');
		$this->db->where('ss.SY', $sy);
		$this->db->where('ss.Semester', $sem);
		$this->db->where('g.SY', $sy);
		$this->db->where('g.Semester', $sem);
		$this->db->order_by('g.SubjectCode');
		$this->db->order_by('StudentName');
		$this->db->order_by('ss.YearLevel');

		$query = $this->db->get();
		return $query->result();
	}

	function getTrackingNo()
	{
		$query = $this->db->query("select * from stude_request order by trackingNo desc limit 1");
		return $query->result();
	}

	function medInfo()
	{
		$query = $this->db->query("SELECT * FROM medical_info m join studeprofile p on m.StudentNumber=p.StudentNumber");
		return $query->result();
	}

	function medInfoInd($id)
	{
		$query = $this->db->query("SELECT * FROM medical_info m join studeprofile p on m.StudentNumber=p.StudentNumber where medID='" . $id . "'");
		return $query->result();
	}

	function incidents()
	{
		$query = $this->db->query("SELECT * FROM guidance_incidents m join studeprofile p on m.StudentNumber=p.StudentNumber");
		return $query->result();
	}

	function incidentsInd($id)
	{
		$query = $this->db->query("SELECT * FROM guidance_incidents m join studeprofile p on m.StudentNumber=p.StudentNumber where incID='" . $id . "'");
		return $query->result();
	}

	function counselling()
	{
		$query = $this->db->query("SELECT * FROM guidance_counselling m join studeprofile p on m.StudentNumber=p.StudentNumber");
		return $query->result();
	}

	function counsellingInd($id)
	{
		$query = $this->db->query("SELECT * FROM guidance_counselling m join studeprofile p on m.StudentNumber=p.StudentNumber where id='" . $id . "'");
		return $query->result();
	}

	function medRecords()
	{
		$query = $this->db->query("SELECT * FROM medical_records m join studeprofile p on m.StudentNumber=p.StudentNumber");
		return $query->result();
	}

	function medRecordsInd($id)
	{
		$query = $this->db->query("SELECT * FROM medical_records m join studeprofile p on m.StudentNumber=p.StudentNumber where mrID='" . $id . "'");
		return $query->result();
	}


	//VIEW DENIED ENROLLEES ---------------------------------------------------------------------------------
	function deniedEnrollees($sem, $sy)
	{
		$query = $this->db->query("Select * from online_enrollment_deny where sem='" . $sem . "' and sy='" . $sy . "'");
		return $query->result();
	}

	//VIEW DENIED PAYMENTS ---------------------------------------------------------------------------------
	function deniedPayments()
	{
		$query = $this->db->query("SELECT o.StudentNumber, p.LastName, p.FirstName, p.MiddleName, o.denyReason, o.deniedDate FROM studeprofile p join online_pay_deny o on p.StudentNumber=o.StudentNumber order by o.opID desc");
		return $query->result();
	}

	//VOID ORS ---------------------------------------------------------------------------------
	function voidORs()
	{
		$query = $this->db->query("SELECT * FROM voidreceipts order by ORNumber desc");
		return $query->result();
	}

	//VIEW REQUIREMENTS ---------------------------------------------------------------------------------
	function requirements($id)
	{
		$query = $this->db->query("Select * from online_requirements where StudentNumber='" . $id . "' order by fileAttachment");
		return $query->result();
	}

	//USER ACCOUNTS ---------------------------------------------------------------------------------
	function viewAccounts()
	{
		$query = $this->db->query("SELECT * FROM users_online order by lName");
		return $query->result();
	}

	function viewAccountsID($id)
	{
		$query = $this->db->query("SELECT * FROM users_online where username='" . $id . "'");
		return $query->result();
	}

	//STUDENTS REQUEST ---------------------------------------------------------------------------------
	function studerequest($id)
	{
		$query = $this->db->query("SELECT * FROM stude_request where StudentNumber='" . $id . "' order by dateReq desc");
		return $query->result();
	}

	function studerequest1()
	{
		$query = $this->db->query("SELECT * FROM stude_request order by dateReq desc");
		return $query->result();
	}





	//Released Request
	function totalReleased()
	{
		$query = $this->db->query("SELECT ongoingStat, count(ongoingStat) as requestCounts FROM stude_request_stat where ongoingStat='Released' group by ongoingStat");
		return $query->result();
	}

	//Released Request
	function releasedRequest()
	{
		$query = $this->db->query("select * from stude_request sr join stude_request_stat st on sr.trackingNo=st.trackingNo join studeprofile p on st.StudentNumber=p.StudentNumber where st.ongoingStat='Released'");
		return $query->result();
	}

	function studerequestTracking($id)
	{
		$query = $this->db->query("SELECT * FROM stude_request sr join stude_request_stat st on sr.trackingNo=st.trackingNo where sr.trackingNo='" . $id . "' order by statID desc");
		return $query->result();
	}

	function studeaccountById($id)
	{
		$this->db->select("s.Course, s.Sem, s.SY, 
                       CONCAT(s.Sem, ', ', s.SY) AS Semester, 
                       FORMAT(s.AcctTotal, 2) AS AcctTotal, 
                       FORMAT(s.TotalPayments, 2) AS TotalPayments, 
                       FORMAT(s.CurrentBalance, 2) AS CurrentBalance, 
                       s.Discount, 
                       s.StudentNumber, 
                       p.FirstName, p.MiddleName, p.LastName");
		$this->db->from('studeaccount s');
		$this->db->join('studeprofile p', 's.StudentNumber = p.StudentNumber');
		$this->db->where('s.StudentNumber', $id);
		$this->db->group_by('Semester'); // Warning: This may fail under strict SQL modes
		$this->db->order_by('s.AccountID', 'DESC');
		$this->db->order_by('s.Sem', 'ASC');

		$query = $this->db->get();
		return $query->result();
	}


	function studepayments($studentno, $sem, $sy)
	{
		$query = $this->db->query("SELECT p.StudentNumber, concat(s.FirstName,' ',s.LastName) as StudentName, s.Course, s.PDate, s.ORNumber, Format(s.Amount,2) as Amount, s.description, s.Sem, s.SY FROM paymentsaccounts s join studeprofile p on p.StudentNumber=s.StudentNumber where p.StudentNumber='" . $studentno . "' and s.Sem='" . $sem . "' and s.SY='" . $sy . "' and s.CollectionSource!='Services' and s.ORStatus='Valid'");
		return $query->result();
	}

	//Student Grades
	function studeGrades($studeno, $sem, $sy)
	{
		$query = $this->db->query("SELECT * FROM studeprofile s join grades g on s.StudentNumber=g.StudentNumber where s.StudentNumber='" . $studeno . "' and g.Semester='" . $sem . "' and g.SY='" . $sy . "'");
		return $query->result();
	}

	//Student COR
	function studeCOR($studeno, $sem, $sy)
	{
		$query = $this->db->query("SELECT * FROM studeprofile s join registration r on s.StudentNumber=r.StudentNumber where s.StudentNumber='" . $studeno . "' and r.Sem='" . $sem . "' and r.SY='" . $sy . "'");
		return $query->result();
	}

	//FTE Records
	function fteRecords($sem, $sy, $course, $yearlevel)
	{
		$query = $this->db->query("SELECT LastName, FirstName, MiddleName, Sem, SY, Course, Major, YearLevel, sum(LecUnit) as LecUnit, sum(LabUnit) as LabUnit FROM registration where Sem='" . $sem . "' and SY='" . $sy . "' and Course='" . $course . "' and YearLevel='" . $yearlevel . "' group by StudentNumber order by LastName");
		return $query->result();
	}


	//Display Students Profile
	function displayrecordsById($id)
	{
		$query = $this->db->query("select * from studeprofile where StudentNumber='" . $id . "'");
		return $query->result();
	}
	//Display Staff Profile
	function staffProfile($id)
	{
		$query = $this->db->query("select * from staff where IDNumber='" . $id . "'");
		return $query->result();
	}

	function getOR()
	{
		$query = $this->db->query("select * from paymentsaccounts order by ID desc limit 1");
		return $query->result();
	}

	function UploadedPayments($id, $sem, $sy)
	{
		$query = $this->db->query("select * from online_payments where StudentNumber='" . $id . "' and sy='" . $sy . "' and sem='" . $sem . "'");
		return $query->result();
	}

	function UploadedPaymentsAdmin($sem, $sy)
	{
		$query = $this->db->query("SELECT * FROM online_payments o join studeprofile p on o.StudentNumber=p.StudentNumber where o.sy='" . $sy . "' and o.sem='" . $sem . "' and o.depositStat='For Verification'");
		return $query->result();
	}

	function onlinePaymentsAll()
	{
		$query = $this->db->query("select * from online_payments op join studeprofile p on op.StudentNumber=p.StudentNumber join online_enrollment oe on p.StudentNumber=oe.StudentNumber group by op.opID");
		return $query->result();
	}

	function displayenrollees()
	{
		$query = $this->db->query("select * from online_enrollment order by LastName");
		return $query->result();
	}

	//Chart of Enrollment
	function chartEnrollment()
	{
		$query = $this->db->query("SELECT concat(Semester,', ',SY) as Sem, count(Semester) as Counts FROM semesterstude group by Sem");
		return $query->result();
	}

	//Counts of Teachers
	function teachersCount()
	{
		$query = $this->db->query("SELECT count(IDNumber) as staffCount FROM staff");
		return $query->result();
	}

	//Counts for Validation
	function forValidationCounts($Semester, $SY)
	{
		$query = $this->db->query("SELECT count(oe.StudentNumber) as StudeCount FROM online_enrollment oe join studeprofile p on oe.StudentNumber=p.StudentNumber where oe.Semester='" . $Semester . "' and oe.SY='" . $SY . "' and oe.enrolStatus='For Validation'");
		return $query->result();
	}


	//For payment verification count
	function forPaymentVerCount($sy, $sem)
	{
		$query = $this->db->query("SELECT count(o.StudentNumber) as Studecount FROM online_payments o join studeprofile p on o.StudentNumber=p.StudentNumber where o.sy='" . $sy . "' and o.sem='" . $sem . "' and o.depositStat='For Verification'");
		return $query->result();
	}

	//First Year Counts
	function enrolledFirst($sy, $sem)
	{
		$query = $this->db->query("SELECT count(StudentNumber) as StudeCount, SY, Semester, YearLevel, Course FROM semesterstude where SY='" . $sy . "' and Semester='" . $sem . "' and Status='Enrolled' and YearLevel='1st'");
		return $query->result();
	}

	//Second Year Counts
	function enrolledSecond($sy, $sem)
	{
		$query = $this->db->query("SELECT count(StudentNumber) as StudeCount, SY, Semester, YearLevel, Course FROM semesterstude where SY='" . $sy . "' and Semester='" . $sem . "' and Status='Enrolled' and YearLevel='2nd'");
		return $query->result();
	}

	//Incidents
	function incidentsCounts()
	{
		$query = $this->db->query("SELECT count(incID) as StudeCount FROM guidance_incidents");
		return $query->result();
	}

	//counselling
	function counsellingCounts()
	{
		$query = $this->db->query("SELECT count(id) as StudeCount FROM guidance_counselling");
		return $query->result();
	}

	//medicalInfo
	function medInfoCounts()
	{
		$query = $this->db->query("SELECT count(medID) as StudeCount FROM medical_info");
		return $query->result();
	}

	//medicalRecords
	function medRecordsCounts()
	{
		$query = $this->db->query("SELECT count(mrID) as StudeCount FROM medical_records");
		return $query->result();
	}


	//Third Year Counts
	function enrolledThird($sy, $sem)
	{
		$query = $this->db->query("SELECT count(StudentNumber) as StudeCount, SY, Semester, YearLevel, Course FROM semesterstude where SY='" . $sy . "' and Semester='" . $sem . "' and Status='Enrolled' and YearLevel='3rd'");
		return $query->result();
	}

	//Fourth Year Counts
	function enrolledFourth($sy, $sem)
	{
		$query = $this->db->query("SELECT count(StudentNumber) as StudeCount, SY, Semester, YearLevel, Course FROM semesterstude where SY='" . $sy . "' and Semester='" . $sem . "' and Status='Enrolled' and YearLevel='4th'");
		return $query->result();
	}

	//Semester Enrollees
	function getEnrolled($course, $yearlevel)
	{
		$this->db->select('*');
		if ($course)
			$this->db->where('Course', $course);
		if ($yearlevel)
			$this->db->where('YearLevel', $yearlevel);
		$query = $this->db->get('semesterstude');
		return $query->result();
	}

	//Course Count Summary Per Semester
	function dailyEnrollStat()
	{
		$query = $this->db->query("SELECT Status, count(Status)as Counts FROM semesterstude where DAY(enroledDate)=DAY(NOW()) and MONTH(enroledDate)=MONTH(NOW()) and YEAR(enroledDate)=YEAR(NOW()) group by Status");
		return $query->result();
	}
	//Payment Summary Per Semester
	function paymentSummary($sem, $sy)
	{
		$query = $this->db->query("SELECT CollectionSource, sum(Amount) as Amount FROM paymentsaccounts where ORStatus='Valid' and Sem='" . $sem . "' and SY='" . $sy . "' group by CollectionSource");
		return $query->result();
	}
	//Birthday Celebrants
	function birthdayCelebs($sem, $sy)
	{
		$query = $this->db->query("SELECT concat(p.LastName,', ',p.FirstName,' ',p.MiddleName) as StudeName, p.BirthDate FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where DAY(p.BirthDate)=DAY(NOW()) and MONTH(p.BirthDate)=MONTH(NOW()) and ss.Semester='" . $sem . "' and ss.SY='" . $sy . "'");
		return $query->result();
	}
	//Birthday Celebrants
	function birthdayMonths($sem, $sy)
	{
		$query = $this->db->query("SELECT concat(p.LastName,', ',p.FirstName,' ',p.MiddleName) as StudeName, Day(p.BirthDate) as Day, MONTH(p.BirthDate) as Month FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where MONTH(p.BirthDate)=MONTH(NOW()) and ss.Semester='" . $sem . "' and ss.SY='" . $sy . "' order by Day");
		return $query->result();
	}

	//Quick Today's Collection
	function collectionToday()
	{
		$query = $this->db->query("SELECT sum(Amount) as Amount FROM paymentsaccounts where ORStatus='Valid' and DAY(PDate)=DAY(NOW()) and MONTH(PDate)=MONTH(NOW()) and YEAR(PDate)=YEAR(NOW())");
		return $query->result();
	}
	//Quick This Month's Collection
	function collectionMonth()
	{
		$query = $this->db->query("SELECT sum(Amount) as Amount FROM paymentsaccounts where ORStatus='Valid' and MONTH(PDate)=MONTH(NOW()) and YEAR(PDate)=YEAR(NOW())");
		return $query->result();
	}
	//Quick This Year's Collection
	function YearlyCollections()
	{
		$query = $this->db->query("SELECT sum(Amount) as Amount FROM paymentsaccounts where ORStatus='Valid' and YEAR(PDate)=YEAR(NOW())");
		return $query->result();
	}
	//Course Count Summary Per Semester
	function CourseCount($sem, $sy)
	{
		$query = $this->db->query("SELECT Course, count(Course) as Counts FROM semesterstude where SY='" . $sy . "' and Semester='" . $sem . "' and Status='Enrolled' group by Course");
		return $query->result();
	}

	//Sex Count Summary Per Semester
	function SexCount($sem, $sy)
	{
		$query = $this->db->query("SELECT p.Sex, count(p.Sex) as Counts FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' group by p.Sex");
		return $query->result();
	}
	//Sex Summary
	function sexList($sem, $sy, $sex)
	{
		$query = $this->db->query("SELECT p.StudentNumber, p.FirstName, p.MiddleName, p.LastName, ss.Course, ss.YearLevel, p.Sex FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' and p.Sex='" . $sex . "'");
		return $query->result();
	}

	//City List Summary
	function cityList($sem, $sy, $city)
	{
		$query = $this->db->query("SELECT p.StudentNumber, p.FirstName, p.MiddleName, p.LastName, ss.Course, ss.YearLevel, p.city FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' and p.city='" . $city . "' order by p.LastName");
		return $query->result();
	}

	//Ethnicity List Summary
	function ethnicityList($sem, $sy, $ethnicity)
	{
		$query = $this->db->query("SELECT p.StudentNumber, p.FirstName, p.MiddleName, p.LastName, ss.Course, ss.YearLevel, p.ethnicity FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' and p.ethnicity='" . $ethnicity . "' order by p.LastName");
		return $query->result();
	}

	//Religion List Summary
	function religionList($sem, $sy, $religion)
	{
		$query = $this->db->query("SELECT p.StudentNumber, p.FirstName, p.MiddleName, p.LastName, ss.Course, ss.YearLevel, p.Religion FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' and p.Religion='" . $religion . "' order by p.LastName");
		return $query->result();
	}
	//Count by Religion
	function religionCount($sem, $sy)
	{
		$query = $this->db->query("SELECT p.Religion, count(p.Religion) as Counts FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' group by p.Religion");
		return $query->result();
	}
	//Count by Ethnicity
	function ethnicityCount($sem, $sy)
	{
		$query = $this->db->query("SELECT p.Ethnicity, count(p.Ethnicity) as Counts FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' group by p.Ethnicity");
		return $query->result();
	}
	//Count by City
	function cityCount($sem, $sy)
	{
		$query = $this->db->query("SELECT p.city, count(p.city) as Counts FROM studeprofile p join semesterstude ss on p.StudentNumber=ss.StudentNumber where ss.SY='" . $sy . "' and ss.Semester='" . $sem . "' group by p.city");
		return $query->result();
	}
	//Student's List
	function getProfile()
	{
		$query = $this->db->query("select * from studeprofile order by LastName");
		return $query->result();
	}


	function getsignProfile()
	{
		$query = $this->db->query("
        SELECT * FROM studentsignup s
        WHERE NOT EXISTS (
            SELECT 1 FROM studeprofile p WHERE p.StudentNumber = s.StudentNumber
        )
        ORDER BY s.LastName
    ");
		return $query->result();
	}


	public function getSignupStudentByNumber($studentNumber)
	{
		return $this->db->get_where('studentsignup', array('StudentNumber' => $studentNumber))->row();
	}

	public function insertStudeProfile($data)
	{
		return $this->db->insert('studeprofile', $data);
	}


	function signUpList()
	{
		$query = $this->db->query("select * from studentsignup order by signupID desc");
		return $query->result();
	}

	function getInventoryCategory()
	{
		$this->db->distinct();
		$this->db->select('Category');
		$this->db->from('ls_categories');
		$this->db->order_by('Category');

		$query = $this->db->get();
		return $query->result();
	}



	function getOffice()
	{
		$this->db->distinct(); // Ensure unique office values
		$this->db->select('office'); // Only select the 'office' column
		$this->db->from('ls_office');
		$this->db->order_by('office');

		$query = $this->db->get();
		return $query->result();
	}

	function getInventory()
	{
		$this->db->select('ls_items.*, staff.FirstName, staff.MiddleName, staff.LastName'); // Select all from ls_items and relevant columns from staff
		$this->db->from('ls_items');
		$this->db->join('staff', 'ls_items.IDNumber = staff.IDNumber'); // Join on accountable from ls_items and IDNumber from staff
		$this->db->order_by('staff.FirstName, staff.MiddleName, staff.LastName'); // Order by staff name fields

		$query = $this->db->get();
		return $query->result();
	}

	function inventorySummary()
	{
		$this->db->select('itemName, SUM(qty) as itemCount');
		$this->db->from('ls_items');
		$this->db->group_by('itemName');

		$query = $this->db->get();
		return $query->result();
	}


	function getInventoryAccountable($accountable)
	{
		$this->db->select('ls_items.*, staff.FirstName, staff.MiddleName, staff.LastName'); // Select required columns
		$this->db->from('ls_items');
		$this->db->join('staff', 'staff.IDNumber = ls_items.IDNumber'); // Join on IDNumber
		$this->db->where('ls_items.IDNumber', $accountable); // Filter by accountable ID

		$query = $this->db->get();
		return $query->result();
	}


	function inventorySummaryAccountable($accountable)
	{
		$this->db->select('ls_items.itemName, SUM(ls_items.qty) as itemCount, staff.FirstName, staff.MiddleName, staff.LastName');
		$this->db->from('ls_items');
		$this->db->join('staff', 'staff.IDNumber = ls_items.IDNumber'); // Join on IDNumber
		$this->db->where('ls_items.IDNumber', $accountable);
		$this->db->group_by('ls_items.itemName');

		$query = $this->db->get();
		return $query->result();
	}



	//Student's List
	function teachers()
	{
		$query = $this->db->query("select * from staff order by LastName");
		return $query->result();
	}

	function honor_dis($StudeNo)
	{
		$query = $this->db->query("select p.StudentNumber, p.Title, p.Pronoun, p.Pronoun2, p.Pronoun3, concat(p.FirstName,' ',p.MiddleName,' ',p.LastName) as StudeName, s.Course, s.YearLevel, s.Semester, s.SY, s.YearLevel from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber where s.StudentNumber='" . $StudeNo . "' order by s.semstudentid desc limit 1");
		return $query->result();
	}

	// function studProf($StudeNo)
	// {
	// 	$query=$this->db->query("select p.StudentNumber, p.Title, p.Pronoun, p.Pronoun2, p.Pronoun3, p.LastName, p.FirstName, p.MiddleName, p.occupation, p.sitioPresent, p.sitio, p.brgyPresent, p.cityPresent, p.Sex, p.CivilStatus, p.nationality, p.contactNo, p.email, p.birthDate, p.age, p.provincePresent, concat(p.FirstName,' ',p.MiddleName,' ',p.LastName) as StudeName, s.Course, s.YearLevel, s.Semester, s.SY, s.YearLevel from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber where s.StudentNumber='".$StudeNo."' order by p.StudentNumber");
	// 	return $query->result();
	// }

	function studProf($StudeNo)
	{
		$query = $this->db->query("SELECT * FROM studeprofile");
		return $query->result();
	}

	function report_coe($StudeNo, $sy, $sem)
	{
		$query = $this->db->query("Select p.StudentNumber, concat(p.FirstName,' ',p.MiddleName,' ',p.LastName) as studeName, r.Sem, r.SY, r.YearLevel, r.Course, Major, SubjectCode, Description, LecUnit, LabUnit from studeprofile p join registration r on p.StudentNumber=r.StudentNumber where p.StudentNumber='" . $StudeNo . "' and r.Sem='" . $sem . "' and r.SY='" . $sy . "' order by r.SubjectCode");
		return $query->result();
	}


	function report_coR($StudeNo, $sy, $sem)
	{
		$query = $this->db->query("Select p.StudentNumber, p.birthDate, p.Sex, concat(p.FirstName,' ',p.MiddleName,' ',p.LastName) as studeName, r.Sem, r.SY, r.Room, r.Instructor, r.Section, r.schedType, r.SchedTime, r.totalUnits, r.YearLevel, r.Course, Major, SubjectCode, Description, LecUnit, LabUnit from studeprofile p join registration r on p.StudentNumber=r.StudentNumber where p.StudentNumber='" . $StudeNo . "' and r.Sem='" . $sem . "' and r.SY='" . $sy . "' order by r.SubjectCode");
		return $query->result();
	}

	function report_cogmc($StudeNo)
	{
		$query = $this->db->query("Select p.StudentNumber, p.Title, p.birthDate, p.Sex, p.LastName, concat(p.FirstName,' ',p.MiddleName,' ',p.LastName) as studeName, r.Sem, r.SY, r.Room, r.Instructor, r.Section, r.schedType, r.SchedTime, r.totalUnits, r.YearLevel, r.Course, Major, SubjectCode, Description, LecUnit, LabUnit from studeprofile p join registration r on p.StudentNumber=r.StudentNumber where p.StudentNumber='" . $StudeNo . "' order by p.StudentNumber");
		return $query->result();
	}


	function report_rog($StudeNo, $sy, $sem)
	{
		$query = $this->db->query("Select p.StudentNumber, p.YearLevel, p.Title, p.birthDate, p.Sex, concat(p.FirstName,' ',p.MiddleName,' ',p.LastName) as studeName, g.Semester, g.SY, g.Instructor, g.Section, g.Course, Major, SubjectCode, Description, LecUnit, LabUnit, g.Final from studeprofile p join grades g on p.StudentNumber=g.StudentNumber where p.StudentNumber='" . $StudeNo . "' and g.Semester='" . $sem . "' and g.SY='" . $sy . "' order by g.SubjectCode");
		return $query->result();
	}






	//For Enrollment
	function forValidation($Semester, $SY)
	{
		$query = $this->db->query("select * from studeprofile p join online_enrollment oe on p.StudentNumber=oe.StudentNumber where oe.Semester='" . $Semester . "' and oe.SY='" . $SY . "' and oe.enrolStatus='For Validation'");
		return $query->result();
	}

	//get the latest semester and reflect it on the proof_payment
	function getSemesterfromOE($id)
	{
		$query = $this->db->query("select * from online_enrollment where StudentNumber='" . $id . "' order by oeID desc limit 1");
		return $query->result();
	}

	//Slot Monitoring
	function slotsMonitoring($sem, $sy)
	{
		$query = $this->db->query("select r.regnumber, r.SubjectCode, r.Description, count(*) as Enrolled, r.Section, r.SchedTime, r.Instructor, r.Sem, r.SY from registration r where r.Sem='" . $sem . "' and r.SY='" . $sy . "' group by r.SubjectCode, r.Section, r.Instructor, r.SchedTime order by r.SubjectCode");
		return $query->result();
	}

	public function getEnrolledStudents($SubjectCode, $Section, $Instructor, $SchedTime, $sy, $sem)
	{
		$this->db->where('SubjectCode', $SubjectCode);
		$this->db->where('Section', $Section);
		$this->db->where('Instructor', $Instructor);
		$this->db->where('SchedTime', $SchedTime);
		$this->db->where('sy', $sy);
		$this->db->where('sem', $sem);
		$query = $this->db->get('registration');
		return $query->result();
	}




	//Subject Masterlist
	function subjectMasterlist($sem, $sy, $subjectcode, $section)
	{
		$query = $this->db->query("select * from registration where Sem='" . $sem . "' and SY='" . $sy . "' and Section='" . $section . "' and subjectcode='" . $subjectcode . "' group by StudentNumber order by LastName");
		return $query->result();
	}


	//Grade
	function grades($sem, $sy)
	{
		$query = $this->db->query("select * from grades where Semester='" . $sem . "' and SY='" . $sy . "' group by SubjectCode, Section, Instructor order by SubjectCode");
		return $query->result();
	}
	//Grading Sheets
	function gradeSheets($sem, $sy, $SubjectCode, $Description, $Section)
	{
		$query = $this->db->query("select SubjectCode, Description, Final, Complied, Semester, Instructor, Section, g.Course, SY, p.StudentNumber, p.FirstName, p.MiddleName, p.LastName from grades g join studeprofile p on g.StudentNumber=p.StudentNumber where g.Semester='" . $sem . "' and g.SY='" . $sy . "' and g.SubjectCode='" . $SubjectCode . "' and g.Section='" . $Section . "' order by p.LastName");
		return $query->result();
	}
	//CrossEnrollees
	public function crossEnrollees($sem, $sy)
	{
		$this->db->select("
        CONCAT(p.LastName, ', ', p.FirstName, ' ', p.MiddleName) AS StudentName,
        ss.YearLevel,
        p.Sex,
        ss.Course,
        ss.classSession,
        ss.Semester,
        ss.SY
    ");
		$this->db->from('studeprofile p');
		$this->db->join('semesterstude ss', 'p.StudentNumber = ss.StudentNumber');
		$this->db->where('ss.Status', 'Enrolled');
		$this->db->where('ss.crossEnrollee', 'Yes');
		$this->db->where('ss.Semester', $sem);
		$this->db->where('ss.SY', $sy);
		$this->db->order_by('p.LastName'); // Corrected from "LastLName"

		$query = $this->db->get();
		return $query->result();
	}


	//Admission History
	function admissionHistory($id)
	{
		$query = $this->db->query("select p.StudentNumber, concat(p.FirstName,' ',p.MiddleName,' ',p.LastName) as StudentName, s.Course, s.Major, s.YearLevel, s.SY, s.Semester from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber join o_srms_settings st on p.settingsID=st.settingsID where p.StudentNumber='" . $id . "'");
		return $query->result();
	}
	//Get Course and Display on the combo box
	function getCourse()
	{
		$this->db->select('CourseDescription');
		$this->db->distinct();
		$this->db->order_by('CourseDescription', 'ASC');
		$query = $this->db->get('course_table');
		return $query->result();
	}

	// tyrone

	// function getNamesFromQuery($course, $sy, $sem)
	// 			{
	// 				 $query = $this->db->query("SELECT CONCAT(p.LastName,', ',p.FirstName,' ',p.MiddleName) as StudentName
	//                            FROM studeprofile p
	//                            JOIN semesterstude ss ON p.StudentNumber = ss.StudentNumber
	//                            WHERE ss.Status = 'Enrolled' AND ss.crossEnrollee = 'Yes'
	//                            AND ss.Semester = '".$sem."' AND ss.SY = '".$sy."'
	//                            AND ss.Course = '".$course."'
	//                            ORDER BY p.LastName");

	// 				 return $query->result();
	// 			}


	// tyrone



	// public function studeAccounts($sy, $yearlevel)
	// {
	// 	$this->db->select("sa.AccountID, 
	//                    sa.StudentNumber, 
	//                    CONCAT(sp.LastName, ', ', sp.FirstName, ' ', sp.MiddleName) as StudentName, 
	//                    sa.Course, 
	//                    FORMAT(sa.AcctTotal, 2) as AcctTotal, 
	//                    FORMAT(sa.TotalPayments, 2) as TotalPayments, 
	//                    FORMAT(sa.Discount, 2) as Discount, 
	//                    FORMAT(sa.CurrentBalance, 2) as CurrentBalance, 
	//                    sa.YearLevel, 
	//                    sa.Sem, 
	//                    sa.SY");
	// 	$this->db->from("studeaccount sa");
	// 	$this->db->join("studeprofile sp", "sp.StudentNumber = sa.StudentNumber");
	// 	$this->db->where("sa.SY", $sy);
	// 	$this->db->where("sa.YearLevel", $yearlevel);
	// 	$this->db->group_by("sa.StudentNumber");
	// 	$this->db->order_by("StudentName", "ASC");

	// 	$query = $this->db->get();
	// 	return $query->result();
	// }


	function getCourseMajor()
	{
		$this->db->select('Major');
		$this->db->distinct();
		$this->db->order_by('Major', 'ASC');
		$query = $this->db->get('course_table');
		return $query->result();
	}

	function getMajor($course)
	{
		$this->db->select('Major');
		$this->db->where('CourseDescription', $course);
		$this->db->distinct();
		$this->db->order_by('Major', 'ASC');
		$query = $this->db->get('course_table');
		return $query->result();
	}


	function getSection()
	{
		$this->db->select('Section');
		$this->db->distinct();
		$this->db->group_by('Section', 'ASC');
		$this->db->order_by('Section', 'ASC');
		$query = $this->db->get('sections');
		return $query->result();
	}


	function getscholarships()
	{
		$this->db->select('Scholarship');
		$this->db->distinct();
		$this->db->group_by('Scholarship', 'ASC');
		$this->db->order_by('Scholarship', 'ASC');
		$query = $this->db->get('scholarships');
		return $query->result();
	}


	function getSchoolInfo()
	{
		$this->db->query("select * from o_srms_settings");
	}

	//update enrollees status
	function updateEnrollees($id)
	{
		$this->db->query("update online_enrollment set enrolStatus='Verified' where oeID='" . $id . "'");
	}

	//Masterlist by Grade Level
	function byGradeLevel($yearlevel, $semester, $sy)
	{
		$query = $this->db->query("select * from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber where s.YearLevel='" . $yearlevel . "' and s.Semester='" . $semester . "' and s.SY='" . $sy . "' and s.Status='Enrolled' order by p.LastName, p.Sex");
		return $query->result();
	}

	//Student Enrollment Status
	function studeEnrollStat($id, $sem, $sy)
	{
		$query = $this->db->query("select * from semesterstude where StudentNumber='" . $id . "' and Semester='" . $sem . "' and SY='" . $sy . "'");

		return $query->result();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}
	//Student Current Balance
	function studeBalance($id)
	{
		//$query=$this->db->query("select * from studeaccount where StudentNumber='".$id."' and Sem='".$sem."' and SY='".$sy."'");
		$query = $this->db->query("select * from studeaccount where StudentNumber='" . $id . "' order by AccountID desc limit 1");

		return $query->result();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	//Faculty Load Counts
	function facultyLoadCounts($id, $sem, $sy)
	{
		$query = $this->db->query("SELECT COUNT(SubjectCode) AS subjectCounts FROM semsubjects WHERE IDNumber = ? AND Semester = ? AND SY = ?", array($id, $sem, $sy));

		if ($query->num_rows() > 0) {
			return $query->row(); // use row() since you're expecting a single result (count)
		}

		return false;
	}


	//Faculty Grades
	// function facultyGrades($instructor, $sem, $sy)
	// {
	// 	$query = $this->db->query("SELECT count(SubjectCode) as subjectCounts FROM grades where Instructor='" . $instructor . "' and Semester='" . $sem . "' and SY='" . $sy . "' group by SubjectCode");

	// 	return $query->result();

	// 	if ($query->num_rows() > 0) {
	// 		return $query->result();
	// 	}
	// 	return false;
	// }

	function facultyGrades($instructor, $sem, $sy)
	{
		$this->db->select('COUNT(SubjectCode) as subjectCounts');
		$this->db->from('grades');
		$this->db->where('IDNumber', $instructor);
		$this->db->where('Semester', $sem);
		$this->db->where('SY', $sy);
		$this->db->group_by('SubjectCode');

		$query = $this->db->get();

		return ($query->num_rows() > 0) ? $query->result() : false;
	}


	//Student Total Enrolled Subjects
	function studeTotalSubjects($id, $sem, $sy)
	{
		$query = $this->db->query("SELECT count(SubjectCode) as subjectCounts FROM registration where StudentNumber='" . $id . "' and Sem='" . $sem . "' and SY='" . $sy . "'");

		return $query->result();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	//Student Total Semesters Enrolled
	function semStudeCount($id)
	{
		$query = $this->db->query("SELECT StudentNumber, count(Semester) as SemesterCounts FROM semesterstude where StudentNumber='" . $id . "' group by StudentNumber");

		return $query->result();

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		return false;
	}

	//Statement of Account
	function studentStatement($id, $sem, $sy)
	{
		$query = $this->db->query("
        SELECT sa.*, sp.FirstName, sp.MiddleName, sp.LastName
        FROM studeaccount sa
        JOIN studeprofile sp ON sa.StudentNumber = sp.StudentNumber
        WHERE sa.StudentNumber = ? AND sa.Sem = ? AND sa.SY = ?
        ORDER BY sa.FeesDesc
    ", array($id, $sem, $sy));

		return $query->result();
	}


	//Masterlist (All)
	function masterlistAll2($id, $semester, $sy)
	{
		$query = $this->db->query("select * from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber where s.semstudentid='" . $id . "' and s.Semester='" . $semester . "' and s.SY='" . $sy . "' and s.Status='Enrolled' order by p.LastName, p.Sex");
		return $query->result();
	}

	//Count Summary Per Year Level
	function byGradeLevelCount($yearlevel, $semester, $sy)
	{
		$query = $this->db->query("SELECT Course, count(Course) enrollees FROM semesterstude where YearLevel='" . $yearlevel . "' and Semester='" . $semester . "' and SY='" . $sy . "' and Status='Enrolled' group by Course");
		return $query->result();
	}

	//Masterlist by Course
	function byCourse($course, $sy, $sem)
	{
		$query = $this->db->query("select * from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber where s.SY='" . $sy . "' and s.Semester='" . $sem . "' and s.Status='Enrolled' and s.Course='" . $course . "' and s.Status='Enrolled' order by p.LastName, p.Sex");
		return $query->result();
	}




	//Enrollees Counts Per Course (Year Level Counts)
	function CourseYLCounts($course, $sy, $sem)
	{
		$query = $this->db->query("SELECT YearLevel, count(YearLevel) as yearLevelCounts FROM semesterstude where SY='" . $sy . "' and Semester='" . $sem . "' and Status='Enrolled' and Course='" . $course . "' group by YearLevel");
		return $query->result();
	}



	public function byYearLevelAndCourse($yearLevel, $course, $sy, $sem)
	{
		$query = $this->db->query("SELECT * 
								   FROM studeprofile p 
								   JOIN semesterstude s ON p.StudentNumber = s.StudentNumber 
								   WHERE s.SY = '" . $sy . "' 
								   AND s.Semester = '" . $sem . "' 
								   AND s.Status = 'Enrolled' 
								   AND s.YearLevel = '" . $yearLevel . "' 
								   AND s.Course = '" . $course . "' 
								   ORDER BY p.LastName, p.Sex");

		return $query->result();
	}




	//Enrollees Counts Per Section (Year Level Counts)
	function SectionCounts($course, $sy, $sem)
	{
		$query = $this->db->query("SELECT Section, count(Section) as sectionCounts FROM semesterstude where SY='" . $sy . "' and Semester='" . $sem . "' and Status='Enrolled' and Course='" . $course . "' group by Section");
		return $query->result();
	}

	//Masterlist by Enrolled Online
	function byEnrolledOnline($department, $sy)
	{
		$query = $this->db->query("select * from studeprofile p join online_enrollment oe on p.StudentNumber=oe.StudentNumber where oe.SY='" . $sy . "' and oe.enrolStatus='Enrolled'");
		return $query->result();
	}

	//Masterlist by Enrolled Semester
	function byEnrolledOnlineSem($sem, $sy)
	{
		$query = $this->db->query("select * from studeprofile p join online_enrollment oe on p.StudentNumber=oe.StudentNumber where oe.Semester='" . $sem . "' and oe.SY='" . $sy . "' and oe.enrolStatus='Enrolled'");
		return $query->result();
	}

	//Masterlist by Enrolled Online (ALL)
	function byEnrolledOnlineAll()
	{
		$query = $this->db->query("select p.StudentNumber, concat(p.LastName,', ',p.FirstName,' ',p.MiddleName) as StudeName, oe.Course, oe.YearLevel, oe.enrolStatus, concat(oe.Semester,' ',oe.SY) as SY, oe.downPayment, oe.downPaymentStat  from studeprofile p join online_enrollment oe on p.StudentNumber=oe.StudentNumber order by p.LastName");
		return $query->result();
	}

	//Masterlist By Section
	function bySection($section, $semester, $sy)
	{
		$query = $this->db->query("select * from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber where s.Section='" . $section . "' and s.Semester='" . $semester . "' and s.SY='" . $sy . "' and s.Status='Enrolled' order by p.LastName, p.Sex");
		return $query->result();
	}

	//Masterlist by SY
	function bySY($sy, $sem)
	{
		$query = $this->db->query("select * from studeprofile p join semesterstude s on p.StudentNumber=s.StudentNumber where s.SY='" . $sy . "' and s.Semester='" . $sem . "' and s.Status='Enrolled' group by p.StudentNumber order by p.LastName");
		return $query->result();
	}

	//Masterlist by Qualification
	public function byQualification($qual)
	{
		// Use CodeIgniter's query builder to construct the query
		$this->db->select('*')
			->from('studeprofile p')
			->join('semesterstude s', 'p.StudentNumber = s.StudentNumber')
			->where('s.Course', 'TESDA Program')
			->where('s.Major', $qual)
			->where('s.Status', 'Enrolled')
			->group_by('p.StudentNumber')
			->order_by('p.LastName');

		// Execute the query and return the results
		$query = $this->db->get();

		// Return the result as an array of objects
		return $query->result();
	}


	public function byQualificationSection($qual, $section)
	{
		// Use CodeIgniter's query builder to construct the query
		$this->db->select('*')
			->from('studeprofile p')
			->join('semesterstude s', 'p.StudentNumber = s.StudentNumber')
			->where('s.Course', 'TESDA Program')
			->where('s.Major', $qual)
			->where('s.Section', $section)
			->where('s.Status', 'Enrolled')
			->group_by('p.StudentNumber')
			->order_by('p.LastName');

		// Execute the query and return the results
		$query = $this->db->get();

		// Return the result as an array of objects
		return $query->result();
	}


	public function byQualificationEmployment($qual, $section)
	{
		// Use CodeIgniter's query builder to construct the query
		$this->db->select('*')
			->from('studeprofile p')
			->join('employment e', 'p.StudentNumber = e.StudentNumber')
			->join('semesterstude s', 'p.StudentNumber = s.StudentNumber')
			->where('s.Course', 'TESDA Program')
			->where('s.Major', $qual)
			->where('s.Section', $section)
			->where('s.Status', 'Enrolled')
			->group_by('p.StudentNumber')
			->order_by('p.LastName');

		// Execute the query and return the results
		$query = $this->db->get();

		// Return the result as an array of objects
		return $query->result();
	}



	public function byQualificationSectionCounts($qual)
	{
		// Use CodeIgniter's query builder to construct the query
		$this->db->select('Section, COUNT(Section) as enrolledCounts, Major')
			->from('semesterstude')
			->where('status', 'Enrolled')
			->where('Course', 'TESDA Program')
			->where('Major', $qual)
			->group_by('Section')
			->order_by('Course')
			->order_by('Section');

		// Execute the query and return the results
		$query = $this->db->get();

		// Return the result as an array of objects
		return $query->result();
	}



	//Masterlist by Date
	public function byDate($date)
	{
		// Use CodeIgniter's query builder to construct the query
		$this->db->select('*')
			->from('studeprofile p')
			->join('semesterstude s', 'p.StudentNumber = s.StudentNumber')
			->where('s.enroledDate', $date)
			->where('s.Status', 'Enrolled')
			->group_by('p.StudentNumber')
			->order_by('p.LastName');

		// Execute the query and return the results
		$query = $this->db->get();

		// Return the result as an array of objects
		return $query->result();
	}


	//Masterlist by Date Summary
	public function byDateCourseSum($date)
	{
		// Use CodeIgniter's query builder to construct the query
		$this->db->select('Course, COUNT(Course) as Enrollees')
			->from('semesterstude')
			->where('enroledDate', $date)
			->where('Status', 'Enrolled')
			->group_by('Course')
			->order_by('Course');

		// Execute the query and return the results
		$query = $this->db->get();

		// Return the result as an array of objects
		return $query->result();
	}



	public function collectionReport($from = null, $to = null)
	{
		// Define the query
		$this->db->select("
        paymentsaccounts.PDate, 
        paymentsaccounts.ORNumber, 
        FORMAT(paymentsaccounts.Amount, 2) as Amount, 
        paymentsaccounts.description, 
        paymentsaccounts.StudentNumber, 
        CONCAT(studeprofile.LastName, ', ', studeprofile.FirstName, ' ', studeprofile.MiddleName) as Payor, 
        studeprofile.Course, 
        paymentsaccounts.PaymentType, 
        paymentsaccounts.Description, 
        paymentsaccounts.CheckNumber, 
        paymentsaccounts.Bank, 
        paymentsaccounts.CollectionSource, 
        CONCAT(paymentsaccounts.Sem, ' ', paymentsaccounts.SY) as Semester
    ")
			->from('paymentsaccounts')
			->join('studeprofile', 'paymentsaccounts.StudentNumber = studeprofile.StudentNumber')
			->where('paymentsaccounts.ORStatus', 'Valid');

		// Apply date filters if provided
		if ($from !== null) {
			$this->db->where('paymentsaccounts.PDate >=', $from);
		}
		if ($to !== null) {
			$this->db->where('paymentsaccounts.PDate <=', $to);
		}

		// Order by payment date descending
		$this->db->order_by('paymentsaccounts.PDate', 'DESC');

		// Execute the query and return the results
		return $this->db->get()->result();
	}

	function collectionTotal($from, $to)
	{
		$this->db->select_sum('Amount', 'TotalAmount');
		$this->db->from('paymentsaccounts');
		$this->db->where('PDate >=', $from);
		$this->db->where('PDate <=', $to);
		$this->db->where('ORStatus', 'Valid');

		$query = $this->db->get();

		return $query->result();
	}



	function getExpenseCategory()
	{
		$query = $this->db->query("SELECT Category FROM expenses_cat order by Category");
		return $query->result();
	}

	function expensesReportAll()
	{
		$query = $this->db->query("select * from expenses order by ExpenseDate desc");
		return $query->result();
	}

	function expensesReport($from, $to)
	{
		$query = $this->db->query("select * from expenses where ExpenseDate>='" . $from . "' and ExpenseDate<='" . $to . "' order by ExpenseDate desc");
		return $query->result();
	}

	function expensesTotal($from, $to)
	{
		$query = $this->db->query("select Sum(Amount) as TotalAmount from expenses where ExpenseDate>='" . $from . "' and ExpenseDate<='" . $to . "'");
		return $query->result();
	}




	function collectionSummary($from, $to)
	{
		$query = $this->db->query("SELECT PaymentType, format(sum(Amount),2) as TotalAmount FROM paymentsaccounts where PDate>='" . $from . "' and PDate<='" . $to . "' and ORStatus='Valid' group by PaymentType");
		return $query->result();
	}

	function expensesSummary($from, $to)
	{
		$query = $this->db->query("SELECT Category, format(sum(Amount),2) as TotalAmount FROM expenses where ExpenseDate>='" . $from . "' and ExpenseDate<='" . $to . "' group by Category");
		return $query->result();
	}

	function collectionTotalYear($year)
	{
		$query = $this->db->query("select Sum(Amount) as TotalAmount from paymentsaccounts where YEAR(PDate)='" . $year . "' and ORStatus='Valid' order by PDate desc");
		return $query->result();
	}

	function collectionYear($year)
	{
		$query = $this->db->query("SELECT PDate, ORNumber, Format(Amount,2) as Amount, description, StudentNumber, concat(LastName,', ',FirstName,' ',MiddleName) as Payor, Description, PaymentType, YEAR(PDate) FROM paymentsaccounts where YEAR(PDate)='" . $year . "' and ORStatus='Valid' order by PDate desc");
		return $query->result();
	}

	// function studeAccounts($sem, $sy, $course, $yearlevel)
	// {
	// 	$query = $this->db->query("Select AccountID, StudentNumber, concat(LastName,', ',FirstName,' ',MiddleName) as StudentName, Course, format(AcctTotal,2) as AcctTotal, format(TotalPayments,2) as TotalPayments, format(Discount,2) as Discount, format(CurrentBalance,2) as CurrentBalance, YearLevel, Sem, SY FROM studeaccount where Sem='" . $sem . "' and SY='" . $sy . "' and YearLevel='" . $yearlevel . "' and Course= '" . $course . "' group by StudentNumber order by StudentName");
	// 	return $query->result();
	// }

	function studeAccountsWithBalance($sem, $sy, $course, $yearlevel)
	{
		$query = $this->db->query("Select AccountID, StudentNumber, concat(LastName,', ',FirstName,' ',MiddleName) as StudentName, Course, format(AcctTotal,2) as AcctTotal, format(TotalPayments,2) as TotalPayments, format(Discount,2) as Discount, format(CurrentBalance,2) as CurrentBalance, YearLevel, Sem, SY FROM studeaccount where Sem='" . $sem . "' and SY='" . $sy . "' and YearLevel='" . $yearlevel . "' and Course= '" . $course . "' and CurrentBalance>'0' group by StudentNumber order by StudentName");
		return $query->result();
	}
	//PASSWORD ---------------------------------------------------------------------------------
	function is_current_password($username, $currentpass)
	{
		$this->db->select();
		$this->db->from('o_users');
		$this->db->where('username', $username);
		$this->db->where('password', $currentpass);
		$this->db->where('acctStat', 'active');
		$query = $this->db->get();
		$row = $query->row();
		if ($row) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function reset_userpassword($username, $newpass)
	{
		$data = array(
			'password' => $newpass
		);
		$this->db->where('username', $username);
		if ($this->db->update('o_users', $data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	//Get Profile Pictures
	public function profilepic($id)
	{
		$this->db->select('*');
		$this->db->from('o_users');
		$this->db->where('username', $id);
		$query = $this->db->get();
		return $query->result();
	}

	//Total Request
	function totalStudeRequest()
	{
		$query = $this->db->query("SELECT reqStat, count(reqStat) as requestCounts FROM stude_request");
		return $query->result();
	}

	//Open Request
	function openRequest()
	{
		$query = $this->db->query("SELECT reqStat, count(reqStat) as requestCounts FROM stude_request where reqStat='Open'");
		return $query->result();
	}

	//Open Request
	function closedRequest()
	{
		$query = $this->db->query("SELECT reqStat, count(reqStat) as requestCounts FROM stude_request where reqStat='Closed'");
		return $query->result();
	}

	//Student REQUEST
	function studeRequestList()
	{
		$query = $this->db->query("select * from stude_request sr join studeprofile p on sr.StudentNumber=p.StudentNumber where sr.reqStat='Open' order by trackingNo desc");
		return $query->result();
	}

	//Scholarship Applicants
	function scholarshipApplicants()
	{
		$query = $this->db->query("select * from reservation where appStatus='Pending' order by appNo");
		return $query->result();
	}

	//Student REQUEST
	function closedDocRequest()
	{
		$query = $this->db->query("select * from stude_request sr join studeprofile p on sr.StudentNumber=p.StudentNumber where sr.reqStat='Closed' order by sr.dateReq desc");
		return $query->result();
	}

	//Student REQUEST
	function openDocRequest()
	{
		$query = $this->db->query("select * from stude_request sr join studeprofile p on sr.StudentNumber=p.StudentNumber where sr.reqStat='Open' order by sr.dateReq desc");
		return $query->result();
	}

	//Student REQUEST
	function allDocRequest()
	{
		$query = $this->db->query("select * from stude_request sr join studeprofile p on sr.StudentNumber=p.StudentNumber order by sr.trackingNo desc");
		return $query->result();
	}

	function docReqCounts()
	{
		$query = $this->db->query("SELECT docName, count(docName) as docCounts FROM stude_request group by docName");
		return $query->result();
	}

	function reservationCounts()
	{
		$query = $this->db->query("SELECT course, count(course) as courseCount FROM reservation where appStatus='Pending' group by course order by course");
		return $query->result();
	}

	function enrolledCounts()
	{
		$query = $this->db->query("SELECT Major, count(Major) as courseCount FROM semesterstude where Course='TESDA Program' and Status='Enrolled' group by Major");
		return $query->result();
	}

	function scholarshipReservation($program)
	{
		$query = $this->db->query("SELECT * FROM reservation where course='" . $program . "' and appStatus='Pending' order by appNo");
		return $query->result();
	}

	function newestSignup()
	{
		$query = $this->db->query("SELECT * FROM studentsignup order by signupID desc limit 5 ");
		return $query->result();
	}


	public function gradesUploading($record)
	{
		if (!empty($record)) {
			$sem = $this->session->userdata('semester');
			$sy = $this->session->userdata('sy');
			$subjectcode = $this->input->post('subjectcode');
			$description = $this->input->post('description');
			$instructor = $this->input->post('instructor');
			$section = $this->input->post('section');

			// $takenAt=$this->input->post('section');
			// $settingsID=$this->input->post('section');
			date_default_timezone_set('Asia/Manila');
			$timeEncoded = date('h:i:s A');
			$dateEncoded = date('Y-m-d');

			$grades = array(
				"StudentNumber" => trim($record[0]),
				"SubjectCode"   => $subjectcode,
				"Description"   => $description,
				"Instructor"    => $instructor,
				"Section"       => $section,
				"Final"         => trim($record[2]),
				"Semester"      => $sem,
				"SY"           	=> $sy,
				"SY"           	=>  trim($record[0]),
				// "settingsID"   	=> $settingsID,
				// "takenAt"       => $takenAt,
				"dateEncoded"   => $dateEncoded,
				"timeEncoded"   => $timeEncoded,
			);

			$this->db->insert('grades', $grades);
		}
	}

	function countItemsByCategory($itemCategory)
	{
		$this->db->where('itemCategory', $itemCategory); // Filter by description
		$this->db->from('ls_items'); // Specify the table
		return $this->db->count_all_results(); // Return the count
	}

	function getStaff()
	{

		$this->db->select('*');
		$this->db->from('staff');
		$this->db->order_by('FirstName, MiddleName, LastName');

		$query = $this->db->get();
		return $query->result();
	}

	function getBrand()
	{
		$this->db->select('*');
		$this->db->distinct();
		$this->db->from('ls_brands');
		$this->db->order_by('brand');

		$query = $this->db->get();
		return $query->result();
	}


	public function getMajorsByCourse($course)
	{
		$this->db->select('Major');
		$this->db->where('CourseDescription', $course);
		$this->db->distinct();
		$this->db->order_by('Major', 'ASC');
		$query = $this->db->get('course_table');
		return $query->result();
	}


	public function getSectionsByCourseYearLevel($course, $yearLevel)
	{
		$this->db->select('Section');
		$this->db->distinct();
		$this->db->where('Course', $course);
		$this->db->where('YearLevel', $yearLevel);
		$this->db->order_by('Section', 'ASC');
		$query = $this->db->get('sections');
		return $query->result();
	}


	function display_itemsById($itemID)
	{
		$query = $this->db->query("select * from ls_items where itemID='" . $itemID . "'");
		return $query->result();
	}

	public function updateItem($itemID, $updatedData)
	{
		// Assuming the table name is 'inventory' and the primary key column is 'itemID'
		$this->db->where('itemID', $itemID); // Match the itemID with the existing record
		$this->db->update('ls_items', $updatedData); // Update the record in the 'inventory' table

		// Check if the update was successful
		if ($this->db->affected_rows() > 0) {
			return true; // Success
		} else {
			return false; // Failure (could be because nothing changed)
		}
	}




	public function getstudentsignupbyId($StudentNumber)
	{
		$query = $this->db->query("SELECT * FROM studentsignup WHERE StudentNumber = '" . $StudentNumber . "'");
		return $query->result();
	}


	public function getstudentbyId($StudentNumber)
	{
		$query = $this->db->query("SELECT * FROM studeprofile WHERE StudentNumber = '" . $StudentNumber . "'");
		return $query->result();
	}


	public function updatestudentsignup($StudentNumber, $updateData)
	{
		$this->db->where('StudentNumber', $StudentNumber);
		$this->db->update('studentsignup', $updateData);
	}



	function bySection1($section, $semester, $sy)
	{
		$query = $this->db->query("
        SELECT * 
        FROM studeprofile p
        JOIN semesterstude s ON p.StudentNumber = s.StudentNumber
        WHERE s.Section = '$section' 
        AND s.Semester = '$semester' 
        AND s.SY = '$sy'
        AND s.Status = 'Enrolled'
        ORDER BY p.LastName, p.Sex
    ");
		return $query->result();
	}






	public function getAccountDetailsByStudentNumberAndSY($studentNumber, $SY)
	{
		$this->db->select('studeaccount.*, studeprofile.FirstName, studeprofile.MiddleName, studeprofile.LastName');
		$this->db->from('studeaccount');
		$this->db->join('studeprofile', 'studeprofile.StudentNumber = studeaccount.StudentNumber');
		$this->db->where('studeaccount.StudentNumber', $studentNumber);
		$this->db->where('studeaccount.SY', $SY); // Filter by SY
		return $this->db->get()->row(); // Return a single row
	}

	public function insertIntoStudeAdditional($data)
	{
		return $this->db->insert('studeadditional', $data);
	}

	public function updateStudentAccount($studentNumber, $SY, $data)
	{
		$this->db->where('StudentNumber', $studentNumber);
		$this->db->where('SY', $SY); // Ensure only the correct SY is updated
		return $this->db->update('studeaccount', $data);
	}


	public function getAccountDetails($accountID)
	{
		// Join the studeaccount table with the studeprofile table
		$this->db->select('studeaccount.*, studeprofile.FirstName, studeprofile.MiddleName, studeprofile.LastName');
		$this->db->from('studeaccount');
		$this->db->join('studeprofile', 'studeprofile.StudentNumber = studeaccount.StudentNumber');
		$this->db->where('studeaccount.AccountID', $accountID);
		return $this->db->get()->row();
	}

	public function getAllStudents()
	{
		$this->db->select('StudentNumber, FirstName, MiddleName, LastName');
		return $this->db->get('studeprofile')->result();
	}


	public function addDiscount($data)
	{
		return $this->db->insert('studediscount', $data);
	}


	public function updateStudentAccountFields($studentNumber, $sy, $data)
	{
		$this->db->where('StudentNumber', $studentNumber);
		$this->db->where('SY', $sy);
		return $this->db->update('studeaccount', $data);
	}


	public function deleteStudentAccount($StudentNumber, $SY)
	{
		// Ensure deletion only affects the current SY and the given StudentNumber
		$this->db->where('StudentNumber', $StudentNumber);
		$this->db->where('SY', $SY); // Filter by SY

		// Attempt to delete the record
		return $this->db->delete('studeaccount'); // Return true/false based on success
	}



	public function getAccountDetailsByStudentNumber($studentNumber)
	{
		// Join the studeaccount table with the studeprofile table
		$this->db->select('studeaccount.*, studeprofile.FirstName, studeprofile.MiddleName, studeprofile.LastName');
		$this->db->from('studeaccount');
		$this->db->join('studeprofile', 'studeprofile.StudentNumber = studeaccount.StudentNumber');
		$this->db->where('studeaccount.StudentNumber', $studentNumber); // Use StudentNumber to fetch account details
		return $this->db->get()->row(); // Return a single row
	}



	public function getStudentsWithoutAccounts($schoolYear)
	{
		// Subquery: Get students with accounts in the current school year
		$this->db->distinct()
			->select('StudentNumber')
			->from('studeaccount')
			->where('SY', $schoolYear);
		$subQuery = $this->db->get_compiled_select();

		// Main query: Get students without accounts for the current SY
		$this->db->select('sa.StudentNumber, sp.FirstName, sp.MiddleName, sp.LastName')
			->from('semesterstude sa')
			->join('studeprofile sp', 'sa.StudentNumber = sp.StudentNumber', 'left')
			->where('sa.SY', $schoolYear)
			->where("sa.StudentNumber NOT IN ($subQuery)", NULL, FALSE);

		$query = $this->db->get();
		return $query->result();  // Return result set
	}



	public function getStudentDetails($studentNumber)
	{
		$this->db->where('StudentNumber', $studentNumber);
		$query = $this->db->get('semesterstude'); // Replace 'students' with your actual table name
		return $query->row();
	}


	public function checkExistingAccount($studentNumber, $currentSY)
	{
		$this->db->where('StudentNumber', $studentNumber);
		$this->db->where('SY', $currentSY);  // Check against the current School Year
		$query = $this->db->get('studeaccount'); // Replace with your actual table name
		return $query->num_rows() > 0;
	}



	public function getDescriptionsByYearLevelAndSY($yearLevel, $SY)
	{
		$this->db->where('YearLevel', $yearLevel);
		$this->db->where('SY', $SY);  // Filter by the logged-in SY
		$query = $this->db->get('fees');
		return $query->result();
	}


	public function insertstudeAccount($data)
	{
		return $this->db->insert('studeaccount', $data);
	}

	public function getAmountPaid($studentNumber, $currentSY)
	{
		$this->db->select_sum('Amount'); // Assuming 'Amount' is the column name for payment amount
		$this->db->where('StudentNumber', $studentNumber);
		$this->db->where('SY', $currentSY);  // Ensure it matches the current SY
		$this->db->where('ORStatus !=', 'Void'); // Tyrone
		$this->db->where('CollectionSource !=', 'Services'); // Tyrone

		$query = $this->db->get('paymentsaccounts'); // Replace with your actual payments table name
		return $query->row()->Amount ?? 0; // Return the sum or 0 if no payments found
	}



	public function getStudentDetailsWithFees()
	{
		$studentNumber = $this->input->post('StudentNumber');
		$currentSY = $this->session->userdata('sy');  // Get logged-in SY

		$studentDetails = $this->StudentModel->getStudentDetails($studentNumber);

		if ($studentDetails) {
			$yearLevel = $studentDetails->YearLevel;
			// Fetch fees by YearLevel and current SY only
			$fees = $this->StudentModel->getDescriptionsByYearLevelAndSY($yearLevel, $currentSY);

			// Fetch the amount paid, restricted by the current SY
			$amountPaid = $this->StudentModel->getAmountPaid($studentNumber, $currentSY);

			// Combine student details, fees, and amount paid into one response
			$response = [
				'studentDetails' => $studentDetails,
				'fees' => $fees,
				'amountPaid' => $amountPaid  // Add amount paid to the response
			];

			echo json_encode($response);
		} else {
			echo json_encode(['error' => 'Student not found']);
		}
	}





	public function collectionReportAll($SY)
	{
		// Set date limit for the last 3 months
		$date_limit = date('Y-m-d', strtotime('-3 months'));

		// Main collection report query
		$this->db->select("
        paymentsaccounts.PDate, 
        paymentsaccounts.ORNumber, 
        FORMAT(paymentsaccounts.Amount, 2) as Amount, 
        paymentsaccounts.description, 
        paymentsaccounts.StudentNumber, 
        CONCAT(studeprofile.LastName, ', ', studeprofile.FirstName, ' ', studeprofile.MiddleName) as Payor, 
        studeprofile.Course, 
        paymentsaccounts.PaymentType, 
        paymentsaccounts.Description, 
        paymentsaccounts.CheckNumber, 
        paymentsaccounts.Bank, 
        paymentsaccounts.CollectionSource, 
        CONCAT(paymentsaccounts.Sem, ' ', paymentsaccounts.SY) as Semester
    ");
		$this->db->from('paymentsaccounts');
		$this->db->join('studeprofile', 'paymentsaccounts.StudentNumber = studeprofile.StudentNumber');
		$this->db->where('paymentsaccounts.ORStatus', 'Valid');
		$this->db->where('paymentsaccounts.SY', $SY);
		$this->db->where('paymentsaccounts.PDate >=', $date_limit);
		$this->db->order_by('paymentsaccounts.PDate', 'DESC');
		$collection_data = $this->db->get()->result();

		// Yearly collection report query
		$this->db->select("
        YEAR(paymentsaccounts.PDate) as Year, 
        SUM(paymentsaccounts.Amount) as TotalAmount
    ");
		$this->db->from('paymentsaccounts');
		$this->db->where('paymentsaccounts.ORStatus', 'Valid');
		$this->db->where('paymentsaccounts.SY', $SY);
		$this->db->where('paymentsaccounts.PDate >=', $date_limit);
		$this->db->group_by('Year');
		$this->db->order_by('Year', 'DESC');
		$yearly_data = $this->db->get()->result();

		// Monthly collection report query
		$this->db->select("
        DATE_FORMAT(paymentsaccounts.PDate, '%Y-%m') as Month, 
        SUM(paymentsaccounts.Amount) as TotalAmount
    ");
		$this->db->from('paymentsaccounts');
		$this->db->where('paymentsaccounts.ORStatus', 'Valid');
		$this->db->where('paymentsaccounts.SY', $SY);
		$this->db->where('paymentsaccounts.PDate >=', $date_limit);
		$this->db->group_by('Month');
		$this->db->order_by('Month', 'DESC');
		$monthly_data = $this->db->get()->result();

		// Return all data
		return [
			'collection_data' => $collection_data,
			'yearly_data' => $yearly_data,
			'monthly_data' => $monthly_data
		];
	}



	public function collectionReport1($SY)
	{
		$this->db->select('*');
		$this->db->from('paymentsaccounts p');
		$this->db->join('studeprofile pp', 'p.StudentNumber = pp.StudentNumber');
		$this->db->where('sy', $SY);  // Corrected this line
		$query = $this->db->get();
		return $query->result();
	}




	public function studepayments_summary($SY)
	{
		$this->db->select('*');
		$this->db->from('paymentsaccounts p');
		$this->db->join('studeprofile pp', 'p.StudentNumber = pp.StudentNumber');
		$this->db->where('sy', $SY);  // Corrected this line
		$query = $this->db->get();
		return $query->result();
	}



	function yearLevel()
	{
		$this->db->select('YearLevel');
		$this->db->distinct();
		$this->db->order_by('YearLevel', 'ASC');
		$query = $this->db->get('subjects');
		return $query->result();
	}


	function get_Scholar()
	{
		$this->db->select('*');
		$this->db->distinct();
		$query = $this->db->get('scholarships');
		return $query->result();
	}

	function get_prevSchool()
	{
		$this->db->distinct();
		$this->db->select('School, Address');
		$query = $this->db->get('prevschool');
		return $query->result();
	}


	public function getDescriptionsByYearLevel($yearLevel)
	{
		$this->db->where('YearLevel', $yearLevel);
		$query = $this->db->get('fees');  // Assuming the fees are stored in a table named 'fees'
		return $query->result();
	}




	public function studeAccountsFiltered($sy, $yearlevel, $course)
	{
		$this->db->select("sa.AccountID, 
						   sa.StudentNumber, 
						   CONCAT(sp.LastName, ', ', sp.FirstName, ' ', sp.MiddleName) as StudentName, 
						   sa.Course, 
						   FORMAT(sa.AcctTotal, 2) as AcctTotal, 
						   FORMAT(sa.TotalPayments, 2) as TotalPayments, 
						   FORMAT(sa.Discount, 2) as Discount, 
						   FORMAT(sa.CurrentBalance, 2) as CurrentBalance, 
						   sa.YearLevel, 
						   sa.Sem, 
						   sa.SY");
		$this->db->from("studeaccount sa");
		$this->db->join("studeprofile sp", "sp.StudentNumber = sa.StudentNumber");
		$this->db->where("sa.SY", $sy);

		// Add filters if they are provided
		if (!empty($yearlevel)) {
			$this->db->where("sa.YearLevel", $yearlevel);
		}
		if (!empty($course)) {
			$this->db->where("sa.Course", $course);
		}

		$this->db->group_by("sa.StudentNumber");
		$this->db->order_by("StudentName", "ASC");

		$query = $this->db->get();
		return $query->result();
	}



	public function studeAccountsFiltered1($sy, $yearlevel, $course)
	{
		// Fetch filtered student records
		$this->db->select("sa.AccountID, 
                       sa.StudentNumber, 
                       CONCAT(sp.LastName, ', ', sp.FirstName, ' ', sp.MiddleName) as StudentName, 
                       sa.Course, 
                       FORMAT(sa.AcctTotal, 2) as AcctTotal, 
                       FORMAT(sa.TotalPayments, 2) as TotalPayments, 
                       FORMAT(sa.Discount, 2) as Discount, 
                       FORMAT(sa.CurrentBalance, 2) as CurrentBalance, 
                       sa.YearLevel, 
                       sa.Sem, 
                       sa.SY");
		$this->db->from("studeaccount sa");
		$this->db->join("studeprofile sp", "sp.StudentNumber = sa.StudentNumber");
		$this->db->where("sa.SY", $sy);

		if (!empty($yearlevel)) {
			$this->db->where("sa.YearLevel", $yearlevel);
		}
		if (!empty($course)) {
			$this->db->where("sa.Course", $course);
		}

		$this->db->group_by("sa.StudentNumber");
		$this->db->order_by("StudentName", "ASC");

		$students = $this->db->get()->result();

		// ✅ Totals for unique StudentNumbers only
		$subQuery = $this->db->select("
			StudentNumber,
			MAX(AcctTotal) as AcctTotal,
			MAX(TotalPayments) as TotalPayments,
			MAX(Discount) as Discount,
			MAX(CurrentBalance) as CurrentBalance
		")
			->from("studeaccount")
			->where("SY", $sy);

		if (!empty($yearlevel)) {
			$subQuery->where("YearLevel", $yearlevel);
		}
		if (!empty($course)) {
			$subQuery->where("Course", $course);
		}

		$subQuery->group_by("StudentNumber");
		$subQuerySQL = $subQuery->get_compiled_select();

		$this->db->select("
		SUM(AcctTotal) as TotalAcctTotal,
		SUM(TotalPayments) as TotalPayments,
		SUM(Discount) as TotalDiscount,
		SUM(CurrentBalance) as TotalBalance
	");
		$this->db->from("($subQuerySQL) as grouped");

		$totals = $this->db->get()->row();

		return ['students' => $students, 'totals' => $totals];
	}




	public function studeAccountsWithBalance1($sem, $sy, $course = null, $yearlevel = null)
	{
		// Fetch individual student records (same as before)
		$this->db->select("
		sa.AccountID, 
		sa.StudentNumber, 
		CONCAT(sp.LastName, ', ', sp.FirstName, ' ', sp.MiddleName) as StudentName, 
		sa.Course, 
		FORMAT(sa.AcctTotal, 2) as AcctTotal, 
		FORMAT(sa.TotalPayments, 2) as TotalPayments, 
		FORMAT(sa.Discount, 2) as Discount, 
		FORMAT(sa.CurrentBalance, 2) as CurrentBalance, 
		sa.YearLevel, 
		sa.Sem, 
		sa.SY
	");
		$this->db->from("studeaccount sa");
		$this->db->join("studeprofile sp", "sp.StudentNumber = sa.StudentNumber");
		$this->db->where("sa.Sem", $sem);
		$this->db->where("sa.SY", $sy);
		$this->db->where("sa.CurrentBalance >", 0);

		if (!empty($yearlevel)) {
			$this->db->where("sa.YearLevel", $yearlevel);
		}
		if (!empty($course)) {
			$this->db->where("sa.Course", $course);
		}

		$this->db->group_by("sa.StudentNumber");
		$this->db->order_by("StudentName", "ASC");
		$students = $this->db->get()->result();

		// ✅ Totals only for unique StudentNumbers
		$subQuery = $this->db->select("
			StudentNumber,
			MAX(AcctTotal) as AcctTotal,
			MAX(TotalPayments) as TotalPayments,
			MAX(Discount) as Discount,
			MAX(CurrentBalance) as CurrentBalance
		")
			->from("studeaccount")
			->where("Sem", $sem)
			->where("SY", $sy)
			->where("CurrentBalance >", 0);

		if (!empty($yearlevel)) {
			$subQuery->where("YearLevel", $yearlevel);
		}
		if (!empty($course)) {
			$subQuery->where("Course", $course);
		}

		$subQuery->group_by("StudentNumber");
		$subQuerySQL = $subQuery->get_compiled_select();

		// Use subquery to total unique students' amounts
		$this->db->select("
		SUM(AcctTotal) as TotalAcctTotal,
		SUM(TotalPayments) as TotalPayments,
		SUM(Discount) as TotalDiscount,
		SUM(CurrentBalance) as TotalBalance
	");
		$this->db->from("($subQuerySQL) as grouped");

		$totals = $this->db->get()->row();

		return [
			'students' => $students,
			'totals' => $totals,
		];
	}


	// working rn!



	public function insert_profile($data)
	{
		return $this->db->insert('studeprofile', $data);
	}

	public function update_profile($data, $StudentNumber)
	{
		$this->db->where('StudentNumber', $StudentNumber);
		return $this->db->update('studeprofile', $data);
	}


	public function insert_user_account($data)
	{
		return $this->db->insert('o_users', $data);
	}

	public function getRequirements()
	{
		return $this->db->get_where('requirements', ['is_active' => 1])->result();
	}

	public function getStudentRequirements($studentNumber)
	{
		$this->db->select('r.id as req_id, r.name, sr.date_submitted, sr.file_path, sr.is_verified, comment');
		$this->db->from('requirements r');
		$this->db->join('student_requirements sr', 'r.id = sr.requirement_id AND sr.StudentNumber = ' . $this->db->escape($studentNumber), 'left');
		return $this->db->get()->result();
	}

	public function submitRequirement($data)
	{
		return $this->db->insert('student_requirements', $data);
	}

	public function get_student_by_number($studentNumber)
	{
		return $this->db->get_where('studeprofile', ['StudentNumber' => $studentNumber])->row();
	}

	public function getPendingRequirements()
	{
		$this->db->select("sr.id, sr.StudentNumber, CONCAT(s.LastName, ', ', s.FirstName) AS FullName, r.name as requirement_name, sr.date_submitted, sr.file_path");
		$this->db->from('student_requirements sr');
		$this->db->join('studeprofile s', 's.StudentNumber = sr.StudentNumber');
		$this->db->join('requirements r', 'r.id = sr.requirement_id');
		$this->db->where('sr.is_verified', 0);
		$this->db->order_by('sr.date_submitted', 'DESC');
		return $this->db->get()->result();
	}

	public function approved_uploads()
	{
		$this->db->select("sr.id, sr.StudentNumber, CONCAT(s.LastName, ', ', s.FirstName) AS FullName, r.name as requirement_name, sr.date_submitted, sr.file_path");
		$this->db->from('student_requirements sr');
		$this->db->join('studeprofile s', 's.StudentNumber = sr.StudentNumber');
		$this->db->join('requirements r', 'r.id = sr.requirement_id');
		$this->db->where('sr.is_verified', 1);
		$this->db->order_by('sr.date_submitted', 'DESC');
		return $this->db->get()->result();
	}

	public function req_list()
	{
		$this->db->select('*');
		$this->db->from('requirements');
		return $this->db->get()->result();
	}


	public function approveRequirement($id, $verifier)
	{
		$data = [
			'is_verified' => 1,
			'verified_by' => $verifier,
			'verified_at' => date('Y-m-d H:i:s')
		];
		$this->db->where('id', $id);
		return $this->db->update('student_requirements', $data);
	}


	public function get_registration_details($filters)
	{
		$this->db->select('Sem, Term, LecUnit, LabUnit, settingsID, Course, Major, IDNumber');
		$this->db->from('registration');
		$this->db->where('SubjectCode', $filters['SubjectCode']);
		$this->db->where('Description', $filters['Description']);
		$this->db->where('Instructor', $filters['Instructor']);
		$this->db->where('Section', $filters['Section']);
		$this->db->where('Sem', $filters['semester']);
		$this->db->limit(1); // Get only one matching record since details are shared across students
		return $this->db->get()->row(); // Return a single object
	}

	public function get_students_by_registration($filters)
	{
		$this->db->select('*');
		$this->db->from('registration r');
		$this->db->join('studeprofile sp', 'sp.StudentNumber = r.StudentNumber', 'left');
		$this->db->where('r.SubjectCode', $filters['SubjectCode']);
		$this->db->where('r.Description', $filters['Description']);
		$this->db->where('r.Instructor', $filters['Instructor']);
		$this->db->where('r.Section', $filters['Section']);
		$this->db->where('Sem', $filters['semester']);

		return $this->db->get()->result();
	}



	public function getApplicableFees($filters)
	{
		$this->db->select('Description, Amount');
		$this->db->from('fees');
		$this->db->where('SY', $filters['SY']);
		$this->db->where('Semester', $filters['Semester']);
		$this->db->where('Course', $filters['Course']);
		$this->db->where('YearLevel', $filters['YearLevel']);

		if (!empty($filters['Major'])) {
			$this->db->where('Major', $filters['Major']);
		}

		return $this->db->get()->result();
	}



	// Existing grades by student number
	public function get_existing_grades($filters)
	{
		$this->db->where('SubjectCode', $filters['SubjectCode']);
		$this->db->where('Description', $filters['Description']);
		$this->db->where('Instructor', $filters['Instructor']);
		$this->db->where('Section', $filters['Section']);
		$this->db->where('SY', $filters['SY']);
		$query = $this->db->get('grades');

		$result = [];
		foreach ($query->result() as $row) {
			$result[$row->StudentNumber] = $row;
		}
		return $result;
	}


	public function getGradeDisplay()
	{
		$query = $this->db->get('srms_settings_o');
		if ($query->num_rows() > 0) {
			return $query->row()->gradeDisplay; // Assuming only 1 row
		}
		return 'Numeric'; // Default fallback
	}
}
