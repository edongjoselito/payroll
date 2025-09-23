<?php
class Login extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Login_model');
        $this->load->model('SettingsModel');
        $this->load->model('StudentModel');
        $this->load->library('AuditLogger');
    }

    function index()
    {
        $settings = $this->Login_model->loginImage(); // returns an array of result objects
        $result['data'] = $settings;

        // Assuming there's at least one row returned
        if (!empty($settings)) {
            $result['active_sem'] = $settings[0]->active_sem;
            $result['active_sy'] = $settings[0]->active_sy;
            $result['allow_signup'] = $settings[0]->allow_signup; // <- Add this line
        } else {
            $result['active_sem'] = null;
            $result['active_sy'] = null;
            $result['allow_signup'] = 'No'; // default to No
        }

        $this->load->view('home_page', $result);
    }


    function faq()
    {
        $result['data'] = $this->Login_model->loginImage();
        //$this->output->cache(60);
        $this->load->view('web-faq', $result);
    }

    function login()
    {
        $result['data'] = $this->Login_model->loginImage();
        $data['allow_signup'] = 'Yes';
        //$this->output->cache(60);
        $this->load->view('home_page', $result);
    }



    function registration()
    {
        $courseVal = $this->input->post('course');
        $result['course'] = $this->StudentModel->getCourse();
        $result['major'] = $this->StudentModel->getCourseMajor();
        $result['province'] = $this->StudentModel->getProvince();
        $result['city'] = $this->StudentModel->getCity();
        $this->load->view('registration_form', $result);

        if ($this->input->post('register')) {
            $query1 = $this->db->query("SELECT *  from o_srms_settings");
            $row = $query1->result_array();

            $StudentNumber = $this->input->post('StudentNumber');
            $FirstName = strtoupper($this->input->post('FirstName'));
            $MiddleName = strtoupper($this->input->post('MiddleName'));
            $LastName = strtoupper($this->input->post('LastName'));
            $nameExtn = strtoupper($this->input->post('nameExtn'));
            $completeName = $FirstName . ' ' . $LastName;
            $Sex = $this->input->post('Sex');
            $bdate = $this->input->post('bdate');
            $BirthPlace = $this->input->post('BirthPlace');
            $age = $this->input->post('age');
            $contactNo = $this->input->post('contactNo');
            $email = $this->input->post('email');
            $date = date('Y-m-d');
            $pass = $this->input->post('bdate');
            $h_upass = sha1($pass);

            $MobileNumber = $this->input->post('MobileNumber');
            $CivilStatus = $this->input->post('CivilStatus');
            $Religion = $this->input->post('Religion');
            $ethnicity = $this->input->post('ethnicity');
            $working = $this->input->post('working');
            $VaccStat = $this->input->post('VaccStat');
            $province = $this->input->post('province');
            $city = $this->input->post('city');
            $brgy = $this->input->post('brgy');
            $sitio = $this->input->post('sitio');

            $course = $this->input->post('Course');
            $major = $this->input->post('Major');


            $que = $this->db->query("select * from studeprofile where FirstName='" . $FirstName . "' and LastName='" . $LastName . "'");
            $row = $que->num_rows();
            if ($row) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Duplicate record!</b></div>');
            } else {

                // $que=$this->db->query("insert into users (username, password, position, fName, mName, lName, email, avatar, acctStat, dateCreated, name) values('$lrn','$h_upass','Student','$fname','$mname','$lname','$email','avatar.png','active','$date','$fname.' '.$lname ')");
                $que1 = $this->db->query("insert into studeprofile (StudentNumber, FirstName, MiddleName, LastName, nameExtn, Sex, CivilStatus, birthDate, age, BirthPlace, contactNo, ethnicity, Religion, working, province, city, brgy, sitio, provincePresent, cityPresent, brgyPresent, sitioPresent, email, VaccStat, settingsID) values('$StudentNumber','$FirstName','$MiddleName','$LastName','$nameExtn','$Sex','$CivilStatus','$bdate','$age','$BirthPlace','$contactNo','$ethnicity','$Religion','$working','$province','$city','$brgy','$sitio','$province','$city','$brgy','$sitio','$email','$VaccStat','1')");
                $que = $this->db->query("insert into studentsignup values('','$StudentNumber','$FirstName','$MiddleName','$LastName','For Confirmation','$date','$course','$major')");
                $que2 = $this->db->query("insert into users values('$StudentNumber','$h_upass','Student','$FirstName','$MiddleName','$LastName','$email','avatar.png','active','$date','$completeName','$StudentNumber')");
                echo '<script language="javascript">';
                echo 'alert("Registration details have been submitted successfully. You will be notified via email for your login credentials after the processing of your enrollment.")';
                echo '</script>';
                //redirect('Login');

                //      Email Notification
                $this->load->config('email');
                $this->load->library('email');
                $mail_message = 'Dear ' . $FirstName . ',' . "\r\n";
                $mail_message .= '<br><br>Thank you for signing up!' . "\r\n";
                $mail_message .= '<br><br>You may now login to the system using <span style="color:red; font-weight:bold;">' . $StudentNumber . '</span> as your username and <span style="color:red; font-weight:bold;">' . $pass . ' </span> as your password.' . "\r\n";
                $mail_message .= '<br><br>Thanks & Regards,';
                $mail_message .= '<br>SRMS - Online';

                $this->email->from('no-reply@lxeinfotechsolutions.com', 'School Records Management System')
                    ->to($email)
                    ->subject('Account Created')
                    ->message($mail_message);
                $this->email->send();
                // redirect('Login');
            }
        }
    }

    function fetch_major()
    {

        if ($this->input->post('course')) {
            $output = '<option value=""></option>';
            $yearlevel = $this->StudentModel->getMajor($this->input->post('course'));
            foreach ($yearlevel as $row) {
                $output .= '<option value ="' . $row->Major . '">' . $row->Major . '</option>';
            }
            echo $output;
        }
    }


    function reservation()
    {
        $this->load->view('reservation_form');

        if ($this->input->post('reserve')) {
            $appDate = date("Y-m-d");
            $firstName = strtoupper($this->input->post('firstName'));
            $middleName = strtoupper($this->input->post('middleName'));
            $lastName = strtoupper($this->input->post('lastName'));
            $nameExtn = strtoupper($this->input->post('nameExtn'));
            $sex = $this->input->post('sex');
            $bDate = $this->input->post('bDate');
            $age = $this->input->post('age');
            $civilStatus = $this->input->post('civilStatus');
            $empStatus = $this->input->post('empStatus');
            $ad_street = $this->input->post('ad_street');
            $ad_barangay = $this->input->post('ad_barangay');
            $ad_city = $this->input->post('ad_city');
            $ad_province = $this->input->post('ad_province');
            $email = $this->input->post('email');
            $contactNos = $this->input->post('contactNos');
            $course = $this->input->post('course');


            // $que=$this->db->query("select * from reservation where username='".$lrn."'");
            // $row = $que->num_rows();
            // if($row)
            // {
            // $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Record already exist.</b></div>');
            // }
            // else
            // {
            $que = $this->db->query("insert into reservation values(0,'$appDate','$firstName','$middleName','$lastName','$nameExtn','$sex','$bDate','$age','$civilStatus','$empStatus','$ad_street','$ad_barangay','$ad_city','$ad_province','$email','$contactNos','$course','Pending')");
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center"><b>Reservation details have been processed successfully.  You will be notified via text or phone call for the status of your reservation.  Thank you.</b></div>');
            redirect('Login/reservation');

            //   //Email Notification
            //     $this->load->config('email');
            //     $this->load->library('email');
            //     $mail_message = 'Dear ' . $fname . ',' . "\r\n"; 
            //     $mail_message .= '<br><br>Thank you for reservation!' . "\r\n"; 
            //     $mail_message .= '<br><br>You may now login to the system using <span style="color:red; font-weight:bold;">' .$lrn. '</span> as your username and <span style="color:red; font-weight:bold;">' . $pass . ' </span> as your password.' ."\r\n";
            //     $mail_message .= '<br><br>Thanks & Regards,';
            //     $mail_message .= '<br>SRMS - Online';

            //     $this->email->from('no-reply@lxeinfotechsolutions.com', 'SRMS Online Team')
            //         ->to($email)
            //         ->subject('Scholarship Reservation')
            //         ->message($mail_message);
            //         $this->email->send();
            //redirect('Login/reservation');

        }
    }


//    function auth()
// {
//     $username = $this->input->post('username', TRUE);
//     $password = $this->input->post('password', TRUE);
//     $sy = $this->input->post('sy', TRUE);
//     $semester = $this->input->post('semester', TRUE);

//     // Fetch user data
//     $user = $this->Login_model->get_user_by_username($username);

//     if ($user) {
//         $storedHash = $user->password;

//         // Handle old SHA1 passwords
//         if (strlen($storedHash) === 40 && ctype_xdigit($storedHash)) {
//             if (sha1($password) === $storedHash) {
//                 // ✅ Matches old SHA1 — upgrade to secure hash
//                 $newHash = password_hash($password, PASSWORD_DEFAULT);
//                 $this->db->where('username', $username)->update('o_users', ['password' => $newHash]);
//             } else {
//                 // Invalid SHA1 password
//                 $this->session->set_flashdata('danger', 'The username or password is incorrect!');
//                 redirect('login');
//                 return;
//             }
//         } elseif (!password_verify($password, $storedHash)) {
//             // bcrypt/secure hash failed
//             $this->session->set_flashdata('danger', 'The username or password is incorrect!');
//             redirect('login');
//             return;
//         }

//         // ✅ Valid password — continue login
//         $acctStat = $user->acctStat;
//         if (strtolower($acctStat) === 'active') {
//           $user_data = array(
//     'username'   => $user->username,
//     'fname'      => $user->fName,
//     'mname'      => $user->mName,
//     'lname'      => $user->lName,
//     'avatar'     => $user->avatar,
//     'email'      => $user->email,
//     'position'   => $user->position,   // NEW: primary role key
//     'level'      => $user->position,   // BACKWARD-COMPAT for existing checks
//     'IDNumber'   => $user->IDNumber,
//     'sy'         => $sy,
//     'semester'   => $semester,
//     'settingsID' => $user->settingsID,
//     'logged_in'  => TRUE
// );
// $this->session->set_userdata($user_data);


//             // Role-based redirection
//             switch ($user->position) {
//                 case 'Admin': redirect('page/admin'); break;
//                 case 'School Admin': redirect('page/school_admin'); break;
//                 case 'Registrar': case 'Head Registrar': redirect('page/registrar'); break;
//                 case 'Super Admin': redirect('page/superAdmin'); break;
//                 case 'Property Custodian': redirect('page/p_custodian'); break;
//                 case 'Academic Officer': redirect('page/a_officer'); break;
//                 case 'Student': redirect('page/student'); break;
//                 case 'Stude Applicant': redirect('page/student_registration'); break;
//                 case 'Accounting': redirect('page/accounting'); break;
//                 case 'Instructor': redirect('page/Instructor'); break;
//                 case 'Teacher/Adviser': redirect('page/adviser'); break;
//                 case 'HR Admin': redirect('page/hr'); break;
//                 case 'Guidance': redirect('page/guidance'); break;
//                 case 'School Nurse': redirect('page/medical'); break;
//                 case 'IT': redirect('page/IT'); break;
//                 case 'Librarian': redirect('page/library'); break;
//                 case 'Principal': redirect('page/s_principal'); break;
//                   case 'Payroll User': redirect('WeeklyAttendance'); break;

//                 default:
//                     $this->session->set_flashdata('danger', 'Unauthorized access.');
//                     redirect('login');
//             }
//         } else {
//             $this->session->set_flashdata('danger', 'Your account is not active. Please contact support.');
//             redirect('login');
//         }
//     } else {
//         $this->session->set_flashdata('danger', 'The username or password is incorrect!');
//         redirect('login');
//     }
// }
function auth()
{
    $username  = $this->input->post('username', TRUE);
    $password  = $this->input->post('password', TRUE);
    $sy        = $this->input->post('sy', TRUE);
    $semester  = $this->input->post('semester', TRUE);

    // Fetch user data
    $user = $this->Login_model->get_user_by_username($username);

    if ($user) {
        $storedHash = $user->password;

        // Handle old SHA1 passwords
        if (strlen($storedHash) === 40 && ctype_xdigit($storedHash)) {
            if (sha1($password) === $storedHash) {
                // ✅ Matches old SHA1 — upgrade to secure hash
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $this->db->where('username', $username)->update('o_users', ['password' => $newHash]);

                // AUDIT: note hash upgrade
                $this->auditlogger->log(
                    'other', 'o_users', 'username', $username,
                    null, null,
                    'Login success; upgraded hash from SHA1 → bcrypt'
                );

            } else {
                // AUDIT: bad password (SHA1 branch)
                $this->auditlogger->log(
                    'other', 'o_users', 'username', $username,
                    null, null,
                    'Login failed: bad password (legacy SHA1)'
                );

                $this->session->set_flashdata('danger', 'The username or password is incorrect!');
                redirect('login');
                return;
            }
        } elseif (!password_verify($password, $storedHash)) {
            // AUDIT: bad password (modern bcrypt/argon)
            $this->auditlogger->log(
                'other', 'o_users', 'username', $username,
                null, null,
                'Login failed: bad password'
            );

            $this->session->set_flashdata('danger', 'The username or password is incorrect!');
            redirect('login');
            return;
        }

        // ✅ Valid password — continue login
        $acctStat = $user->acctStat;
        if (strtolower($acctStat) === 'active') {
            $user_data = array(
                'username'   => $user->username,
                'fname'      => $user->fName,
                'mname'      => $user->mName,
                'lname'      => $user->lName,
                'avatar'     => $user->avatar,
                'email'      => $user->email,
                'position'   => $user->position,
                'level'      => $user->position,
                'IDNumber'   => $user->IDNumber,
                'sy'         => $sy,
                'semester'   => $semester,
                'settingsID' => $user->settingsID,
                'logged_in'  => TRUE
            );
            $this->session->set_userdata($user_data);

            // AUDIT: login success (include SY/Sem in note)
            $this->auditlogger->log(
                'login', 'o_users', 'username', $username,
                null, null,
                'Login success | role='.$user->position.' | SY='.$sy.' | Sem='.$semester
            );

            // Role-based redirection
            switch ($user->position) {
                case 'Admin': redirect('page/admin'); break;
                case 'School Admin': redirect('page/school_admin'); break;
                case 'Registrar': case 'Head Registrar': redirect('page/registrar'); break;
                case 'Super Admin': redirect('page/superAdmin'); break;
                case 'Property Custodian': redirect('page/p_custodian'); break;
                case 'Academic Officer': redirect('page/a_officer'); break;
                case 'Student': redirect('page/student'); break;
                case 'Stude Applicant': redirect('page/student_registration'); break;
                case 'Accounting': redirect('page/accounting'); break;
                case 'Instructor': redirect('page/Instructor'); break;
                case 'Teacher/Adviser': redirect('page/adviser'); break;
                case 'HR Admin': redirect('page/hr'); break;
                case 'Guidance': redirect('page/guidance'); break;
                case 'School Nurse': redirect('page/medical'); break;
                case 'IT': redirect('page/IT'); break;
                case 'Librarian': redirect('page/library'); break;
                case 'Principal': redirect('page/s_principal'); break;
                case 'Payroll User': redirect('WeeklyAttendance'); break;
                default:
                    // AUDIT: blocked by unknown role
                    $this->auditlogger->log(
                        'other', 'o_users', 'username', $username,
                        null, null,
                        'Login blocked: unknown/unauthorized role'
                    );
                    $this->session->set_flashdata('danger', 'Unauthorized access.');
                    redirect('login');
            }
        } else {
            // AUDIT: inactive account
            $this->auditlogger->log(
                'other', 'o_users', 'username', $username,
                null, null,
                'Login blocked: account not active'
            );

            $this->session->set_flashdata('danger', 'Your account is not active. Please contact support.');
            redirect('login');
        }
    } else {
        // AUDIT: unknown user
        $this->auditlogger->log(
            'other', 'o_users', 'username', $username,
            null, null,
            'Login failed: username not found'
        );

        $this->session->set_flashdata('danger', 'The username or password is incorrect!');
        redirect('login');
    }
}

function logout()
{
    // AUDIT: log before session is destroyed
    $this->auditlogger->log(
        'logout', 'o_users', 'username',
        $this->session->userdata('username'),
        null, null,
        'User logged out'
    );

    $this->session->sess_destroy();
    redirect('login');
}

    public function forgot_pass()
    {
        $email = $this->input->post('email');
        $findemail = $this->Login_model->forgotPassword($email);
        if ($findemail) {
            $this->Login_model->sendpassword($findemail);
        } else {
            $this->session->set_flashdata('msg', ' Email not found!');
            redirect(base_url() . 'login', 'refresh');
        }
    }
}
