<?php
class Ren_model extends CI_Model
{



    public function profile_insert()
    {

        $data = array(
            'StudentNumber' => $this->input->post('StudentNumber'),
            'FirstName' => strtoupper($this->input->post('FirstName')),
            'MiddleName' => strtoupper($this->input->post('MiddleName')),
            'LastName' => strtoupper($this->input->post('LastName')),
            'Sex' => $this->input->post('Sex'),
            'CivilStatus' => $this->input->post('CivilStatus'),
            'BirthPlace' => $this->input->post('BirthPlace'),
            'Citizenship' => $this->input->post('Citizenship'),
            'Religion' => $this->input->post('Religion'),
            'BloodType' => $this->input->post('BloodType'),
            'TelNumber' => $this->input->post('TelNumber'),
            'MobileNumber' => $this->input->post('MobileNumber'),
            'BirthDate' => $this->input->post('BirthDate'),
            'Guardian' => $this->input->post('Guardian'),
            'GuardianContact' => $this->input->post('GuardianContact'),
            'GuardianAddress' => $this->input->post('GuardianAddress'),
            'GuardianRelationship' => $this->input->post('GuardianRelationship'),
            'GuardianTelNo' => $this->input->post('GuardianTelNo'),
            'EmailAddress' => $this->input->post('EmailAddress'),
            'Father' => $this->input->post('Father'),
            'FOccupation' => $this->input->post('FOccupation'),
            'Mother' => $this->input->post('Mother'),
            'MOccupation' => $this->input->post('MOccupation'),
            'Age' => $this->input->post('Age'),
            'Ethnicity' => $this->input->post('Ethnicity'),
            'Province' => $this->input->post('Province'),
            'City' => $this->input->post('City'),
            'Brgy' => $this->input->post('Brgy'),
            'Sitio' => $this->input->post('Sitio'),
            'guardianOccupation' => $this->input->post('guardianOccupation'),
            'nameExt' => $this->input->post('nameExt'),
            'LRN' => $this->input->post('LRN'),
            'ParentsMonthly' => $this->input->post('ParentsMonthly'),
            'Notes' => $this->input->post('Notes'),
            'Elementary' => $this->input->post('Elementary')
        );

        return $this->db->insert('studeprofile', $data);
    }


    public function profile_update()
    {

        $data = array(
            'StudentNumber' => $this->input->post('StudentNumber'),
            'FirstName' => $this->input->post('FirstName'),
            'MiddleName' => $this->input->post('MiddleName'),
            'LastName' => $this->input->post('LastName'),
            'Sex' => $this->input->post('Sex'),
            'CivilStatus' => $this->input->post('CivilStatus'),
            'BirthPlace' => $this->input->post('BirthPlace'),
            'Citizenship' => $this->input->post('Citizenship'),
            'Religion' => $this->input->post('Religion'),
            'BloodType' => $this->input->post('BloodType'),
            'TelNumber' => $this->input->post('TelNumber'),
            'MobileNumber' => $this->input->post('MobileNumber'),
            'BirthDate' => $this->input->post('BirthDate'),
            'Guardian' => $this->input->post('Guardian'),
            'GuardianContact' => $this->input->post('GuardianContact'),
            'GuardianAddress' => $this->input->post('GuardianAddress'),
            'GuardianRelationship' => $this->input->post('GuardianRelationship'),
            'GuardianTelNo' => $this->input->post('GuardianTelNo'),
            'EmailAddress' => $this->input->post('EmailAddress'),
            'Father' => $this->input->post('Father'),
            'FOccupation' => $this->input->post('FOccupation'),
            'Mother' => $this->input->post('Mother'),
            'MOccupation' => $this->input->post('MOccupation'),
            'Age' => $this->input->post('Age'),
            'Ethnicity' => $this->input->post('Ethnicity'),
            'Province' => $this->input->post('Province'),
            'City' => $this->input->post('City'),
            'Brgy' => $this->input->post('Brgy'),
            'Sitio' => $this->input->post('Sitio'),
            'guardianOccupation' => $this->input->post('guardianOccupation'),
            'nameExt' => $this->input->post('nameExt'),
            'LRN' => $this->input->post('LRN'),
            'ParentsMonthly' => $this->input->post('ParentsMonthly'),
            'Notes' => $this->input->post('Notes'),
            'Elementary' => $this->input->post('Elementary')
        );

        $this->db->where('StudentNumber', $this->input->get('id'));
        return $this->db->update('studeprofile', $data);
    }

    public function user_insert()
    {

        date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
        $now = date('H:i:s A');

        $AdmissionDate = date("Y-m-d");
        $Password = sha1($this->input->post('BirthDate'));
        $Encoder = $this->session->userdata('username');

        $data = array(
            'username' => $this->input->post('StudentNumber'),
            'password' => $Password,
            'position' => 'Student',
            'fName' => $this->input->post('FirstName'),
            'mName' => $this->input->post('MiddleName'),
            'lName' => $this->input->post('LastName'),
            'email' => $this->input->post('EmailAddress'),
            'avatar' => 'avatar.png',
            'acctStat' => 'Active',
            'dateCreated' => $AdmissionDate,
            'IDNumber' => $this->input->post('StudentNumber')
        );

        return $this->db->insert('o_users', $data);
    }

    public function atrail_insert($desc)
    {

        date_default_timezone_set('Asia/Manila'); # add your city to set local time zone
        $now = date('H:i:s A');

        $AdmissionDate = date("Y-m-d");
        $Password = sha1($this->input->post('BirthDate'));
        $Encoder = $this->session->userdata('username');

        $data = array(
            'atDesc' => $desc,
            'atDate' => $AdmissionDate,
            'atTime' => $now,
            'atRes' => $Encoder,
            'atSNo' => $this->input->post('StudentNumber')
        );

        return $this->db->insert('atrail', $data);
    }

    public function enroll_insert()
    {


        $data = array(
            'StudentNumber' => $this->input->post('StudentNumber'),
            'Course' => $this->input->post('Course'),
            'YearLevel' => $this->input->post('YearLevel'),
            'Status' => 'Enrolled',
            'Semester' => $this->input->post('Semester'),
            'SY' => $this->input->post('SY'),
            'Section' => $this->input->post('Section'),
            'StudeStatus' => $this->input->post('StudeStatus'),
            //'Scholarship' => $this->input->post('Scholarship'), 
            //'YearLevelStat' => $this->input->post('YearLevelStat'), 
            //'Major' => $this->input->post('Major'), 
            'Track' => $this->input->post('Track'),
            'Qualification' => $this->input->post('Qualification'),
            'BalikAral' => $this->input->post('BalikAral'),
            'IP' => $this->input->post('IP'),
            'FourPs' => $this->input->post('FourPs'),
            'Repeater' => $this->input->post('Repeater'),
            'Transferee' => $this->input->post('Transferee'),
            'EnrolledDate' => date("Y-m-d"),
            'Adviser' => $this->input->post('Adviser'),
            'IDNumber' => $this->input->post('IDNumber'),
        );

        return $this->db->insert('semesterstude', $data);
    }

    public function online_enrollment_update()
    {

        $data = array(
            'enrolStatus' => 'Verified'
        );

        $this->db->where('StudentNumber', $this->input->post('StudentNumber'));
        $this->db->where('Semester', $this->input->post('Semester'));
        $this->db->where('SY', $this->input->post('SY'));
        return $this->db->update('online_enrollment', $data);
    }

    public function enlistment_insert()
    {
        $SubjectCode = implode(',', $this->input->post('SubjectCode'));
        $sc = explode(',', $SubjectCode);

        for ($i = 0; $i < count($sc); $i++) {
            $pda1 = $this->input->post('pda' . $i . '1');
            $pda2 = $this->input->post('pda' . $i . '2');
            $pda3 = $this->input->post('pda' . $i . '3');

            $item = array(
                'SubjectCode' => $SubjectCode,
                'Description' => $this->input->post('Description'),
                'Section' => $this->input->post('Section'),
                'SchedTime' => $this->input->post('SchedTime'),
            );

            $this->db->insert('registration', $item);
        }
    }

    public function insert_batch($data)
    {
        $this->db->insert_batch('registration', $data);
    }

    public function ebook_insert($file, $file2)
    {


        $data = array(
            'title' => $this->input->post('title'),
            'author' => $this->input->post('author'),
            'isbn' => $this->input->post('isbn'),
            'pub_date' => $this->input->post('pub_date'),
            'genre' => $this->input->post('genre'),
            'description' => $this->input->post('description'),
            'file_path' => $file,
            'cover_image' => $file2
        );

        return $this->db->insert('ebooks', $data);
    }

    public function ebook_update()
    {


        $data = array(
            'title' => $this->input->post('title'),
            'author' => $this->input->post('author'),
            'isbn' => $this->input->post('isbn'),
            'pub_date' => $this->input->post('pub_date'),
            'genre' => $this->input->post('genre'),
            'description' => $this->input->post('description')
        );

        $this->db->where('id', $this->input->post('id'));
        return $this->db->update('ebooks', $data);
    }

    public function ebook_cover_update($file)
    {


        $data = array(
            'cover_image' => $file
        );


        $this->db->where('id', $this->input->post('id'));
        return $this->db->update('ebooks', $data);
    }

    public function ebook_file_update($file)
    {


        $data = array(
            'file_path' => $file
        );


        $this->db->where('id', $this->input->post('id'));
        return $this->db->update('ebooks', $data);
    }


    //common delete function

    public function delete($table, $col_id, $segment)
    {
        $id = $this->uri->segment($segment);
        $this->db->where($col_id, $id);
        $this->db->delete($table);
        return true;
    }

    function delete_ebook($table, $col, $segment, $attach)
    {
        $this->db->where($col, $segment);
        unlink("upload/ebook/" . $attach);
        $this->db->delete($table);
    }

    public function tcd($table, $col, $val, $col2, $val2)
    { // two cond delete
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->delete($table);
        return true;
    }

    public function del($table, $col, $val)
    { // one cond delete
        $this->db->where($col, $val);
        $this->db->delete($table);
        return true;
    }

    public function get_foos($page)
    {
        // First count all foos
        $count = $this->db->count_all('ebooks');

        // Create the pagination links
        //$this->load->library('pagination');
        //$this->load->helper('url');

        $paging_conf = [
            'uri_segment'      => 3,
            'per_page'         => 2,
            'total_rows'       => $count,
            'base_url'         => site_url('Library/page'),
            'first_url'        => site_url('Library/page/1'),
            'use_page_numbers' => TRUE,
            'attributes'       => ['class' => 'number'],
            'prev_link'        => 'Previous',
            'next_link'        => 'Next',

            // Custom classes for pagination links
            'prev_tag_open'    => '<ul>',
            'prev_tag_close'   => '</ul>',
            'prev_tag_open'    => '<li class="page-item prev-item">',
            'prev_tag_close'   => '</li>',
            'next_tag_open'    => '<li class="page-item next-item">',
            'next_tag_close'   => '</li>',
        ];



        $this->pagination->initialize($paging_conf);

        // Create the paging buttons for the view
        $this->load->vars('pagination_links', $this->pagination->create_links());

        // The pagination offset
        $offset = $page * $paging_conf['per_page'] - $paging_conf['per_page'];

        // Get our set of foos
        $query = $this->db->get('ebooks', $paging_conf['per_page'], $offset);

        // Make sure we have foos
        if ($query->num_rows() > 0)
            return $query->result();

        // Else return default
        return NULL;
    }


    // public function insert_grades($data)
    // {
    //     return $this->db->insert_batch('grades', $data);
    // }

    public function insert_grades($data)
    {
        $this->db->insert_batch('grades', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            log_message('error', 'DB Error: ' . $this->db->_error_message());
            return false;
        }
    }

    public function update_grades($data)
    {
        return $this->db->update_batch('grades', $data, 'gradeID');
    }

    public function batch_update_grades($update_data)
    {
        // Update the grades in the database
        $this->db->update_batch('grades', $update_data, 'gradeID'); // 'id' is the primary key
    }

    public function update_batchren($data)
    {
        return $this->db->update_batch('grades', $data, 'gradeID');
    }

    public function update_batch_stud($data)
    {
        return $this->db->update_batch('grades', $data, 'gradeID');
    }


    public function insert_enlist_sub($data)
    {
        $this->db->insert('registration', $data);
    }

    // common function single row
    public function one_cond_row($table, $col, $val)
    {
        $this->db->where($col, $val);
        $result = $this->db->get($table)->row();
        return $result;
    }

    public function two_cond_row($table, $col, $val, $col2, $val2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $result = $this->db->get($table)->row();
        return $result;
    }

    public function three_cond_row($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $result = $this->db->get($table)->row();
        return $result;
    }

    // common functions loop

    public function no_cond($table)
    {
        $query = $this->db->get($table);
        return $query->result();
    }

    public function one_cond($table, $col, $val)
    {
        $this->db->where($col, $val);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function two_cond($table, $col, $val, $col2, $val2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $query = $this->db->get($table);
        return $query->result();
    }
    public function three_cond($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function four_cond($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function one_cond_loop_order_by($table, $col, $val, $orderby, $orderbyvalue)
    {
        $this->db->where($col, $val);
        $this->db->order_by($orderby, $orderbyvalue);
        $query = $this->db->get($table);
        return $query->result();
    }


    // group by and order by

    public function one_cond_loop_order_group($table, $col, $val, $orderby, $orderbyvalue, $gc)
    {
        $this->db->where($col, $val);
        $this->db->group_by($gc);
        $this->db->order_by($orderby, $orderbyvalue);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function no_cond_loop_order_group($table, $orderby, $orderbyvalue, $gc)
    {
        $this->db->group_by($gc);
        $this->db->order_by($orderby, $orderbyvalue);
        $query = $this->db->get($table);
        return $query->result();
    }


    public function get_registration_data($sy, $sem)
    {
        $this->db->select('*');
        $this->db->from('registration');
        $this->db->where('SY', $sy);
        $this->db->where('Sem', $sem);
        $this->db->group_by('StudentNumber');
        $this->db->order_by('Course');
        $this->db->order_by('YearLevel');
        //$this->db->order_by('LastName');

        $query = $this->db->get();
        return $query->result();
    }


    // count rows in the table

    public function four_cond_count($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $query = $this->db->get($table);
        return $query;
    }

    public function three_cond_count($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $query = $this->db->get($table);
        return $query;
    }

    public function get_lec_units($sem, $sy, $id, $col, $val)
    {
        // Use Query Builder class to construct the query
        $this->db->select_sum($col, $val);
        $this->db->from('grades');
        $this->db->where('Semester', $sem);
        $this->db->where('SY', $sy);
        $this->db->where('StudentNumber', $id);
        $query = $this->db->get();

        // Return the result as an associative array
        return $query->row_array();
    }

    public function get_lec_units_reg($sem, $sy, $id, $col, $val)
    {
        // Use Query Builder class to construct the query
        $this->db->select_sum($col, $val);
        $this->db->from('registration');
        $this->db->where('Sem', $sem);
        $this->db->where('SY', $sy);
        $this->db->where('StudentNumber', $id);
        $query = $this->db->get();

        // Return the result as an associative array
        return $query->row_array();
    }

    public function get_students($sy, $sem)
    {

        // Start building the query
        $this->db->select('*');
        $this->db->from('registration');
        $this->db->join('grades', 'registration.StudentNumber = grades.StudentNumber', 'inner');
        $this->db->where('registration.SY', $sy);
        $this->db->where('registration.Sem', $sem);
        $this->db->group_by('registration.StudentNumber');
        $this->db->order_by('registration.Course');
        $this->db->order_by('registration.YearLevel');
        //$this->db->order_by('registration.LastName');

        // Execute the query and return the results
        $query = $this->db->get();
        return $query->result();
    }

    public function no_cond_group($table, $col)
    {
        $this->db->group_by($col);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function four_cond_group($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $gc)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->group_by($gc);
        $query = $this->db->get($table);
        return $query->result();
    }
}
