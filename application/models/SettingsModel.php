<?php
class SettingsModel extends CI_Model
{
    public function get_expenses()
    {
        $query = $this->db->get('expenses');
        return $query->result();
    }

    public function expenses()
    {
        $query = $this->db->get('expenses');
        return $query->result();
    }

    public function insertexpenses($data)
    {
        return $this->db->insert('expenses', $data);
    }

    public function get_active_settings_id()
    {
        $this->db->select('settingsID');
        $this->db->from('o_srms_settings');
        $this->db->limit(1); // Limit the result to 1 record only
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->settingsID;
        }
        return null;
    }

    public function getFeesByCourse($courseCode)
    {
        $this->db->select('Description, Amount, Major, YearLevel, Semester, feesType, feesid');
        $this->db->from('fees');
        $this->db->where('Course', $courseCode);
        $query = $this->db->get();
        return $query->result();
    }

    public function getFeesWithLecOrLab($courseCode)
{
    $this->db->select('YearLevel, Semester, lecRate, labRate, feesid');
    $this->db->from('fees');
    $this->db->where('Course', $courseCode);
    $this->db->group_start();
        $this->db->where('lecRate !=', '');
        $this->db->or_where('labRate !=', '');
    $this->db->group_end();
    $this->db->order_by('YearLevel, Semester');
    $query = $this->db->get();
    return $query->result();
}


    public function deleteFeeById($id)
    {
        $this->db->where('feesid', $id);
        return $this->db->delete('fees');
    }

    public function getCourseByCode($courseCode)
    {
        return $this->db
            ->where('CourseDescription', $courseCode)
            ->get('course_table') // use your actual course table name
            ->row();
    }


    public function getSectionsByCourse($course)
    {
        return $this->db->get_where('sections', ['Course' => $course])->result();
    }


    public function getexpensesbyId($expensesid)
    {
        $query = $this->db->query("SELECT * FROM expenses WHERE expensesid = '" . $expensesid . "'");
        return $query->result();
    }

    public function updateexpenses($expensesid, $Description, $Amount, $Responsible, $ExpenseDate, $Category)
    {
        $data = array(
            'Description' => $Description,
            'Amount' => $Amount,
            'Responsible' => $Responsible,
            'ExpenseDate' => $ExpenseDate,
            'Category' => $Category,



        );
        $this->db->where('expensesid', $expensesid);
        $this->db->update('expenses', $data);
    }

    public function Delete_expenses($expensesid)
    {
        $this->db->where('expensesid', $expensesid);
        $this->db->delete('expenses');
    }


    public function get_expensesCategory()
    {
        $query = $this->db->get('expensescategory');
        return $query->result();
    }

    public function insertexpensesCategory($data)
    {
        return $this->db->insert('expensescategory', $data);
    }

    public function getexpensescategorybyId($categoryID)
    {
        $query = $this->db->query("SELECT * FROM expensescategory WHERE categoryID = '" . $categoryID . "'");
        return $query->result();
    }

    public function updateexpensescategory($categoryID, $Category)
    {
        $data = array(
            'Category' => $Category,
        );
        $this->db->where('categoryID', $categoryID);
        $this->db->update('expensescategory', $data);
    }

    public function Delete_expensescategory($categoryID)
    {
        $this->db->where('categoryID', $categoryID);
        $this->db->delete('expensescategory');
    }

    public function get_staff()
    {
        $query = $this->db->get('staff');
        return $query->result();
    }


    public function get_categories()
    {
        $this->db->distinct();
        $this->db->select('Category');
        $this->db->from('expenses');
        $query = $this->db->get();
        return $query->result_array(); // Fetches categories as an array
    }


    public function getDescriptionCategories()
    {
        $this->db->distinct();
        $this->db->select('description');
        $query = $this->db->get('paymentsaccounts');
        return $query->result_array();
    }


    public function get_brand()
    {
        $query = $this->db->get('ls_brands');
        return $query->result();
    }

    public function get_brandbyID($brandID)
    {
        $query = $this->db->query("SELECT * FROM ls_brands WHERE brandID = '" . $brandID . "'");
        return $query->result();
    }

    public function insertBrand($data)
    {
        $this->db->insert('ls_brands', $data);
    }

    public function update_brand($brandID, $brand)
    {
        $data = array(
            'brand' => $brand,
        );
        $this->db->where('brandID', $brandID);
        $this->db->update('ls_brands', $data);
    }

    public function Delete_brand($brandID)
    {
        $this->db->where('brandID', $brandID);
        $this->db->delete('ls_brands');
    }

    public function get_category()
    {
        $query = $this->db->get('ls_categories');
        return $query->result();
    }

    public function get_categorybyID($CatNo)
    {
        $query = $this->db->query("SELECT * FROM ls_categories WHERE CatNo = '" . $CatNo . "'");
        return $query->result();
    }

    public function insertCategory($data)
    {
        $this->db->insert('ls_categories', $data);
    }

    public function update_category($CatNo, $Category, $Sub_category)
    {
        $data = array(
            'Category' => $Category,
            'Sub_category' => $Sub_category,
        );
        $this->db->where('CatNo', $CatNo);
        $this->db->update('ls_categories', $data);
    }

    public function Delete_category($CatNo)
    {
        $this->db->where('CatNo', $CatNo);
        $this->db->delete('ls_categories');
    }


    public function get_office()
    {
        $query = $this->db->get('ls_office');
        return $query->result();
    }

    public function get_officebyID($officeID)
    {
        $query = $this->db->query("SELECT * FROM ls_office WHERE officeID = '" . $officeID . "'");
        return $query->result();
    }

    public function insertOffice($data)
    {
        $this->db->insert('ls_office', $data);
    }

    public function update_office($officeID, $office)
    {
        $data = array(
            'office' => $office,
        );
        $this->db->where('officeID', $officeID);
        $this->db->update('ls_office', $data);
    }

    public function Delete_office($officeID)
    {
        $this->db->where('officeID', $officeID);
        $this->db->delete('ls_office');
    }


    //Get Track
    function getTrack()
    {
        $query = $this->db->query("select * from qualifications group by Track order by Track");
        return $query->result();
    }

    //Get Strand
    function getStrand()
    {
        $query = $this->db->query("select * from qualifications group by Qualification order by Qualification");
        return $query->result();
    }

    //Get Track List
    function getSectionList()
    {
        $query = $this->db->query("select * from sections order by Section");
        return $query->result();
    }

    //Get Track List
    function getDepartmentList()
    {
        $query = $this->db->query("select * from course_table order by CourseDescription ASC");
        return $query->result();
    }

    public function getFeesByCourseAndMajor($courseDescription, $major)
    {
        $courseRow = $this->db->get_where('course_table', [
            'CourseDescription' => $courseDescription,
            'Major' => $major
        ])->row();

        if ($courseRow) {
            $this->db->where('Course', $courseRow->CourseDescription);
            if (!empty($major)) {
                $this->db->where('Major', $major);
            }
            $this->db->order_by('Semester', 'ASC');
            $query = $this->db->get('fees');
            return $query->result();
        }

        return [];
    }


    public function getYearLevelTotals($courseDescription, $major)
    {
        $this->db->select('YearLevel, SUM(Amount) as total_amount');
        $this->db->from('fees');
        $this->db->where('Course', $courseDescription);
        $this->db->where('Major', $major);
        $this->db->group_by('YearLevel');
        $query = $this->db->get();

        return $query->result_array();
    }


    public function getTotalByYearLevel($courseDescription, $major, $yearLevel)
    {
        $this->db->select_sum('Amount');
        $this->db->from('fees');
        $this->db->where('Course', $courseDescription);
        $this->db->where('Major', $major);
        $this->db->where('YearLevel', $yearLevel);
        $query = $this->db->get();

        return $query->row()->Amount ?? 0;
    }




    public function getFeesByCourseAndMajorinTuition($courseDescription, $major)
    {
        // First, find the matching Course in course_table
        $courseRow = $this->db->get_where('course_table', [
            'CourseDescription' => $courseDescription,
            'Major' => $major
        ])->row();

        if ($courseRow) {
            // Now search the fees table where Course matches CourseDescription
            $this->db->where('Course', $courseRow->CourseDescription);
            $this->db->where('Major', $major); // Optional if 'Major' is also in 'fees' table
            $this->db->order_by('Sem', 'ASC');
            $query = $this->db->get('coursefees');

            return $query->result();
        }

        return []; // If no matching course, return empty
    }



    public function getYearLevelTotalsinTuition($courseDescription, $major)
    {
        $this->db->select('YearLevel, SUM(LecRate + LabRate) as total_amount');
        $this->db->from('coursefees');
        $this->db->where('Course', $courseDescription);
        $this->db->where('Major', $major);
        $this->db->group_by('YearLevel');
        $query = $this->db->get();

        return $query->result_array();
    }


    public function getTotalByYearLevelinTuition($courseDescription, $major, $yearLevel)
    {
        $this->db->select_sum('LecRate + LabRate');
        $this->db->from('coursefees');
        $this->db->where('Course', $courseDescription);
        $this->db->where('Major', $major);
        $this->db->where('YearLevel', $yearLevel);
        $query = $this->db->get();

        return $query->row()->Amount ?? 0;
    }








    //Get School Information
    function getSchoolInfo()
    {
        $query = $this->db->query("select * from o_srms_settings limit 1");
        return $query->result();
    }

    public function getcoursebyId($courseid)
    {
        $query = $this->db->query("SELECT * FROM course_table WHERE courseid = '" . $courseid . "'");
        return $query->result();
    }
    public function updateCourse($courseid, $data)
    {
        $this->db->where('courseid', $courseid);
        $this->db->update('course_table', $data);
    }

    public function updateSection($sectionID, $data)
    {
        $this->db->where('sectionID', $sectionID);
        $this->db->update('sections', $data);
    }


    public function getsectionbyId($sectionID)
    {
        $query = $this->db->query("SELECT * FROM sections WHERE sectionID = ?", [$sectionID]);
        return $query->row(); // Use row() to get a single object
    }


    public function get_ethnicity()
    {
        $this->db->order_by('ethnicity', 'ASC');
        $query = $this->db->get('settings_ethnicity');
        return $query->result();
    }

    public function insertethnicity($data)
    {
        $this->db->insert('settings_ethnicity', $data);
    }

    public function getethnicitybyId($id)
    {
        $query = $this->db->query("SELECT * FROM settings_ethnicity WHERE id = '" . $id . "'");
        return $query->result();
    }

    public function updateethnicity($id, $ethnicity)
    {
        $data = array(
            'ethnicity' => $ethnicity,

        );
        $this->db->where('id', $id);
        $this->db->update('settings_ethnicity', $data);
    }

    public function Delete_ethnicity($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('settings_ethnicity');
    }




    public function get_religion()
    {
        $this->db->order_by('Religion', 'ASC');
        $query = $this->db->get('settings_religion');
        return $query->result();
    }


    public function insertreligion($data)
    {
        $this->db->insert('settings_religion', $data);
    }

    public function getreligionbyId($id)
    {
        $query = $this->db->query("SELECT * FROM settings_religion WHERE id = '" . $id . "'");
        return $query->result();
    }

    public function updatereligion($id, $religion)
    {
        $data = array(
            'religion' => $religion,

        );
        $this->db->where('id', $id);
        $this->db->update('settings_religion', $data);
    }

    public function Delete_religion($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('settings_religion');
    }



    public function get_provinces()
    {
        $this->db->distinct();
        $this->db->select('Province');
        $this->db->order_by('Province', 'ASC');
        $query = $this->db->get('settings_address');
        $provinces = $query->result_array();

        $formatted_provinces = [];
        foreach ($provinces as $province) {
            $formatted_provinces[] = [
                'id' => $province['Province'],
                'name' => $province['Province']
            ];
        }

        return $formatted_provinces;
    }

    public function get_cities($province)
    {
        $this->db->where('Province', $province);
        $this->db->distinct();
        $this->db->select('City');
        $query = $this->db->get('settings_address');
        $cities = $query->result_array();

        $formatted_cities = [];
        foreach ($cities as $city) {
            $formatted_cities[] = [
                'id' => $city['City'],
                'name' => $city['City']
            ];
        }

        return $formatted_cities;
    }

    public function get_barangays($city)
    {
        $this->db->where('City', $city);
        $this->db->distinct();
        $this->db->select('Brgy');
        $query = $this->db->get('settings_address');
        $barangays = $query->result_array();

        $formatted_barangays = [];
        foreach ($barangays as $barangay) {
            $formatted_barangays[] = [
                'id' => $barangay['Brgy'],
                'name' => $barangay['Brgy']
            ];
        }

        return $formatted_barangays;
    }




    function getSchoolInformation()
    {
        $query = $this->db->query("select * from o_srms_settings");
        return $query->result();
    }


    public function count_projects($settingsID)
{
    return $this->db->where('settingsID', $settingsID)
                    ->count_all_results('project');
}

public function count_personnel($settingsID)
{
    return $this->db->where('settingsID', $settingsID)
                    ->count_all_results('personnel');
}



    public function getSuperAdminbyId($settingsID)
    {
        $query = $this->db->query("SELECT * FROM o_srms_settings WHERE settingsID = '" . $settingsID . "'");
        return $query->result();
    }


public function getSuperAdminbyIds($settingsID)
{
    return $this->db->get_where('o_srms_settings', ['settingsID' => $settingsID])->row(); // ✅ use row() not result()
}


    public function insertSuperAdmin($data)
{
	$this->db->insert('o_srms_settings', $data);
}


    public function updateSuperAdmin($settingsID, $data)
    {
        $this->db->where('settingsID', $settingsID);
        $this->db->update('o_srms_settings', $data);
    }



    public function getYearLevels()
    {
        // Fetch distinct YearLevel from the fees table
        $this->db->select('YearLevel');
        $this->db->distinct();
        $query = $this->db->get('fees');
        return $query->result();
    }
    function course()
    {
        $this->db->distinct();
        $this->db->select('CourseDescription');
        $this->db->from('course_table');
        $this->db->order_by('CourseDescription');

        $query = $this->db->get();
        return $query->result();
    }



    function Major()
    {
        $this->db->distinct();
        $this->db->select('Major');
        $this->db->from('course_table');
        $this->db->order_by('Major');

        $query = $this->db->get();
        return $query->result();
    }


 public function Delete_subjects($id)
    {
        $this->db->where('subjectid', $id);
        $this->db->delete('subjects');
    }

    // Get fees data based on selected YearLevel and SY
    public function getFeesByYearLevelAndSY($yearLevel, $SY)
    {
        $this->db->where('YearLevel', $yearLevel);
        $this->db->where('SY', $SY); // Filter by logged-in SY
        $query = $this->db->get('fees');
        return $query->result();
    }


    // Get total amount for a specific year level and SY
    public function getTotalFeesByYearLevelAndSY($yearLevel, $SY)
    {
        $this->db->select_sum('Amount');
        $this->db->where('YearLevel', $yearLevel);
        $this->db->where('SY', $SY); // Filter by logged-in SY
        $query = $this->db->get('fees');
        return $query->row()->Amount;
    }

    public function getTotalFeesGroupedByYearLevel($SY)
    {
        $this->db->select('YearLevel, SUM(Amount) as total_amount');
        $this->db->from('fees');
        $this->db->where('SY', $SY);
        $this->db->group_by('YearLevel');
        $query = $this->db->get();
        return $query->result_array();
    }

    // Get fees data for the logged-in SY (all year levels)
    public function getCourseFeesBySY($SY)
    {
        $this->db->where('SY', $SY); // Filter by logged-in SY
        $query = $this->db->get('fees');
        return $query->result();
    }


    // Get total amount for all year levels for the logged-in SY
    public function getTotalFeesBySY($SY)
    {
        $this->db->select_sum('Amount');
        $this->db->where('SY', $SY); // Filter by logged-in SY
        $query = $this->db->get('fees');
        return $query->row()->Amount;
    }

    public function insertfees($data)
    {
        return $this->db->insert('fees', $data);
    }

    public function insertTuitionfees($data)
    {
        return $this->db->insert('coursefees', $data);
    }



    public function updateFees($feesid, $YearLevel, $Course, $Description, $Amount)
    {
        $data = array(
            'YearLevel' => $YearLevel,
            'Course' => $Course,
            'Description' => $Description,
            'Amount' => $Amount
        );

        $this->db->where('feesid', $feesid);
        if ($this->db->update('fees', $data)) {
            return TRUE; // Return TRUE if update operation succeeds
        } else {
            return FALSE; // Return FALSE if update operation fails
        }
    }


    public function Deletefees($feesid)
    {
        $this->db->where('feesid', $feesid);
        if ($this->db->delete('fees')) {
            return TRUE; // Return TRUE if delete operation succeeds
        } else {
            return FALSE; // Return FALSE if delete operation fails
        }
    }


    public function getMajorsByCourse($CourseDescription)
    {
        $this->db->select('Major');
        $this->db->from('course_table');
        $this->db->where('CourseDescription', $CourseDescription);
        $query = $this->db->get();

        return $query->result(); // Return the result as an array
    }


    public function updateCourseFeesbyId($feesid)
    {
        $query = $this->db->query("SELECT * FROM fees WHERE feesid = '" . $feesid . "'");
        return $query->result();
    }

    public function updateTuitionFeesbyId($coursefeesID)
    {
        $query = $this->db->query("SELECT * FROM coursefees WHERE coursefeesID = '" . $coursefeesID . "'");
        return $query->result();
    }

    public function updateTuitionFees($coursefeesID, $LectRate, $LabRate, $Session)
    {
        $data = array(
            'LecRate' => $LectRate,
            'LabRate' => $LabRate,
            'Session' => $Session
        );

        $this->db->where('coursefeesID', $coursefeesID);
        if ($this->db->update('coursefees', $data)) {
            return TRUE; // Return TRUE if update operation succeeds
        } else {
            return FALSE; // Return FALSE if update operation fails
        }
    }



    public function DeleteTuitionFees($coursefeesID)
    {
        $this->db->where('coursefeesID', $coursefeesID);
        if ($this->db->delete('coursefees')) {
            return TRUE; // Return TRUE if delete operation succeeds
        } else {
            return FALSE; // Return FALSE if delete operation fails
        }
    }


    public function Payment($SY)
    {
        $this->db->select('paymentsaccounts.*, studeprofile.*');
        $this->db->from('paymentsaccounts');
        $this->db->join('studeprofile', 'studeprofile.StudentNumber = paymentsaccounts.StudentNumber');
        $this->db->where('paymentsaccounts.SY', $SY); // Filter by SY
        $this->db->where('paymentsaccounts.CollectionSource', "Student's Account"); // Filter by CollectionSource
        $this->db->where('paymentsaccounts.ORStatus', "Valid");
        $this->db->order_by('studeprofile.LastName', 'ASC');
        $query = $this->db->get();
        return $query->result(); // Return the filtered data
    }

    public function getLastORNumber()
    {
        $this->db->select('ORNumber');
        $this->db->from('paymentsaccounts');
        $this->db->order_by('ORNumber', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->ORNumber;
        }
        return '1'; // Default starting OR number if no records found
    }


    public function semesterstude()
    {
        $query = $this->db->query("
        SELECT semesterstude.*, studeprofile.*
        FROM semesterstude
        JOIN studeprofile ON semesterstude.StudentNumber = studeprofile.StudentNumber
        GROUP BY semesterstude.StudentNumber
        ORDER BY LastName
    ");
        return $query->result();
    }

    public function getStudentCourse($studentNumber)
    {
        $query = $this->db->select('course')
            ->from('semesterstude')
            ->where('StudentNumber', $studentNumber)
            ->get();

        return $query->row(); // Return a single row with the course
    }


    public function getTotalPayments1($studentNumber, $SY)
    {
        $this->db->select_sum('Amount', 'TotalAmount');
        $this->db->where('StudentNumber', $studentNumber);
        $this->db->where('SY', $SY);
        $this->db->where('CollectionSource !=', 'Services');
        $this->db->where('ORStatus !=', 'Void');
        $query = $this->db->get('paymentsaccounts');

        // Ensure it returns 0 if no result is found
        return (float) ($query->row()->TotalAmount ?? 0);
    }

    public function getAcctTotal($studentNumber, $SY)
    {
        $this->db->select('AcctTotal');
        $this->db->where('StudentNumber', $studentNumber);
        $this->db->where('SY', $SY);

        $result = $this->db->get('studeaccount')->row();

        // Ensure it returns 0 if no result is found
        return (float) ($result->AcctTotal ?? 0);
    }

    public function getDiscount($studentNumber, $SY)
    {
        $this->db->select('Discount');
        $this->db->where('StudentNumber', $studentNumber);
        $this->db->where('SY', $SY);
        return (float) ($this->db->get('studeaccount')->row()->Discount ?? 0);
    }

    public function insertpaymentsaccounts($data)
    {
        return $this->db->insert('paymentsaccounts', $data);
    }

    public function updateStudentAccount($studentNumber, $newTotalPayments, $newBalance, $SY)
    {
        $data = [
            'TotalPayments' => $newTotalPayments,
            'CurrentBalance' => $newBalance
        ];

        $this->db->where('StudentNumber', $studentNumber);
        $this->db->where('SY', $SY);

        $success = $this->db->update('studeaccount', $data);

        if (!$success) {
            log_message('error', "Failed to update studeaccount for Student {$studentNumber}, SY {$SY}");
            return false;
        }

        return true;
    }

    public function getPaymentById($id)
    {
        $this->db->select('paymentsaccounts.*, studeprofile.FirstName, studeprofile.MiddleName, studeprofile.LastName');
        $this->db->from('paymentsaccounts');
        $this->db->join('studeprofile', 'studeprofile.StudentNumber = paymentsaccounts.StudentNumber');
        $this->db->where('paymentsaccounts.ID', $id);
        $query = $this->db->get();

        return $query->row();
    }



    public function isORNumberExists($ORNumber)
    {
        $this->db->where('ORNumber', $ORNumber);
        $query = $this->db->get('paymentsaccounts');
        return $query->num_rows() > 0;
    }


    public function updatePayment($id, $description, $Amount, $CheckNumber, $Bank, $PaymentType)
    {
        $data = array(
            'description' => $description,
            'Amount' => $Amount,
            'CheckNumber' => $CheckNumber,
            'Bank' => $Bank,
            'PaymentType' => $PaymentType,



        );
        $this->db->where('id', $id);
        $this->db->update('paymentsaccounts', $data);
    }




    public function getCollectionReport($description, $collectionSource)
    {
        $this->db->select('paymentsaccounts.*, studeprofile.*');
        $this->db->from('paymentsaccounts');
        $this->db->join('studeprofile', 'studeprofile.StudentNumber = paymentsaccounts.StudentNumber');
        $this->db->where('paymentsaccounts.CollectionSource', "Student's Account"); // Filter by CollectionSource
        $this->db->where('paymentsaccounts.ORStatus', "Valid");
        // Apply filters for both description and CollectionSource
        if ($description) {
            $this->db->where('paymentsaccounts.description', $description); // Filter by description
        }

        if ($collectionSource) {
            $this->db->where('paymentsaccounts.CollectionSource', $collectionSource); // Filter by CollectionSource
        }

        $query = $this->db->get();
        return $query->result();
    }

    public function calculateTotalPayments($studentNumber, $SY)
    {
        $this->db->select_sum('Amount');
        $this->db->where('StudentNumber', $studentNumber);
        $this->db->where('SY', $SY);
        $query = $this->db->get('paymentsaccounts');

        if ($query->num_rows() > 0) {
            return (float)$query->row()->Amount; // Return the sum of payments
        }

        return 0; // Return 0 if no payments exist
    }


    public function services($SY)
    {
        $this->db->select('paymentsaccounts.*, studeprofile.*');
        $this->db->from('paymentsaccounts');
        $this->db->join('studeprofile', 'studeprofile.StudentNumber = paymentsaccounts.StudentNumber');
        $this->db->where('paymentsaccounts.SY', $SY); // Filter by SY
        $this->db->where('paymentsaccounts.CollectionSource', 'Services'); // Filter by CollectionSource = 'Services'
        $query = $this->db->get();
        return $query->result(); // Return the filtered data
    }

    public function Paymentlist($SY)
    {
        $this->db->select('description, SUM(amount) as total_amount, CollectionSource');
        $this->db->from('paymentsaccounts');
        $this->db->where('SY', $SY); // Apply the SY filter
        $this->db->group_by(['description', 'CollectionSource']); // Group by description and CollectionSource
        $query = $this->db->get();
        return $query->result_array(); // Return as an array for easier handling
    }


    function studeAcc()
    {
        $this->db->distinct();
        $this->db->select('Course');
        $this->db->from('coursefees');
        $this->db->order_by('Course');

        $query = $this->db->get();
        return $query->result();
    }


    public function user($id)
    {
        $this->db->select('*');
        $this->db->from('o_users');
        $this->db->where('IDNumber', $id);
        $query = $this->db->get();
        return $query->result();
    }






    public function get_year_levels()
    {
        $this->db->distinct();
        $this->db->select('yearLevel');
        $query = $this->db->get('subjects');
        return $query->result();
    }

    public function get_subjects_by_year($yearLevel)
    {
        $this->db->where('yearLevel', $yearLevel);  // Add a where clause to filter by yearLevel
        $query = $this->db->get('subjects');
        return $query->result();  // Return the result as an array of objects
    }


    public function get_subjects()
    {
        $query = $this->db->get('subjects');
        return $query->result();
    }

    public function insertsubjects($data)
    {
        $this->db->insert('subjects', $data);
    }

    public function update_subject(
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
        $SemEffective,
        $SYEffective,
        $Effectivity
    ) {
        $data = array(
            'SubjectCode' => $SubjectCode,
            'description' => $description,
            'YearLevel' => $YearLevel,
            'Course' => $Course,
            'Semester' => $Semester,
            'Major' => $Major,
            'lecunit' => $lecunit,
            'labunit' => $labunit,
            'prereq' => $prereq,
            'totalUnits' => $totalUnits,
            'Semester' => $SemEffective,
            'SYEffective' => $SYEffective,
            'Effectivity' => $Effectivity
        );

        // Update the subject in the database
        $this->db->where('subjectid', $subjectid);
        $this->db->update('subjects', $data);
    }

    public function get_subjectbyId($subjectid)
    {
        $query = $this->db->query("SELECT * FROM subjects WHERE subjectid = '" . $subjectid . "'");
        return $query->result();
    }



    // function display_course()
    // {
    //     // Make the query distinct and select the course description
    //     $this->db->distinct();
    //     $this->db->select('course_table.CourseDescription, subjects.SubjectName, subjects.Major');

    //     // From the course_table and join with the subjects table on Major
    //     $this->db->from('course_table');
    //     $this->db->join('subjects', 'course_table.Major = subjects.Major');

    //     // Order by CourseDescription
    //     $this->db->order_by('course_table.CourseDescription');

    //     // Execute the query and return the result
    //     $query = $this->db->get();
    //     return $query->result();
    // }


    public function display_course()
    { {
            // SQL query to join course_table and subjects on the Major column
            $query = $this->db->query("
            SELECT 
                course_table.*, 
                subjects.* 
            FROM 
                course_table
            JOIN 
                subjects 
            ON 
                subjects.Major = course_table.Major COLLATE latin1_swedish_ci
            ORDER BY 
                course_table.Major
        ");

            return $query->result();
        }
    }

    public function insertSection($data)
{
    return $this->db->insert('sections', $data);
}


    public function get_semesters()
    {
        $this->db->distinct();
        $this->db->select('Semester');
        $query = $this->db->get('subjects'); // Assuming 'semester' field is in the 'subjects' table
        return $query->result();
    }

    // public function get_Yearlevels() {
    //     $this->db->distinct();
    //     $this->db->select('YearLevel');
    //     $query = $this->db->get('semsubjects');
    //     return $query->result();
    // }


    public function get_subjects_by_yearlevel($YearLevel, $sy)
    {
        $this->db->select("semsubjects.*, CONCAT(staff.FirstName, ' ', staff.MiddleName, ' ', staff.LastName) AS Fullname");
        $this->db->from('semsubjects');
        $this->db->join('staff', 'semsubjects.IDNumber = staff.IDNumber', 'left');
        $this->db->where('semsubjects.YearLevel', $YearLevel);
        $this->db->where('semsubjects.SY', $sy);
        $this->db->order_by('semsubjects.SubjectCode', 'ASC'); 
        $query = $this->db->get();

        return $query->result();
    }

    public function get_classProgram($sy, $sem, $course)
    {
        $this->db->select("semsubjects.*, CONCAT(staff.FirstName, ' ', staff.MiddleName, ' ', staff.LastName) AS Fullname");
        $this->db->from('semsubjects');
        $this->db->join('staff', 'semsubjects.IDNumber = staff.IDNumber', 'left');
        $this->db->where('semsubjects.SY', $sy);
        $this->db->where('semsubjects.Semester', $sem);
        $this->db->where('semsubjects.Course', $course);
        $this->db->order_by('semsubjects.SubjectCode', 'ASC'); 
        $query = $this->db->get();

        return $query->result();
    }

    public function getClassProgramById($subjectid) {
        return $this->db->get_where('your_classprogram_table', ['subjectid' => $subjectid])->row();
    }
    



    function GetSub3()
    {
        $this->db->distinct();
        $this->db->select('yearLevel');
        $this->db->from('subjects');
        $this->db->order_by('yearLevel');
        $query = $this->db->get();
        return $query->result();
    }

    function GetSub4()
    {
        $this->db->distinct();
        $this->db->select('YearLevel,SubjectCode, description');
        $this->db->from('subjects');
        $this->db->order_by('YearLevel');
        $this->db->group_by('SubjectCode');
        $query = $this->db->get();
        return $query->result();
    }

    function getSubjectsByYearLevel($yearLevel)
{
    $this->db->select('SubjectCode, Description');
    $this->db->from('subjects');
    $this->db->where('YearLevel', $yearLevel);
    $this->db->group_by('SubjectCode');
    $this->db->order_by('SubjectCode');
    $query = $this->db->get();
    return $query->result();
}


    function GetSection()
    {
        $this->db->distinct();
        $this->db->select('Section, YearLevel');
        $this->db->from('sections');
        $this->db->order_by('Section');
        $this->db->group_by('YearLevel');
        $query = $this->db->get();
        return $query->result();
    }



    function GetSection1()
    {
        $this->db->distinct();
        $this->db->select('Section');
        $this->db->from('sections');
        $this->db->order_by('Section');
        $this->db->group_by('Section');
        $query = $this->db->get();
        return $query->result();
    }
    

    public function get_subjects_by_yearlevel2($yearLevel)
    {
        $this->db->where('YearLevel', $yearLevel);
        $this->db->order_by('SubjectCode', 'ASC'); // Sort by Subject Code
        return $this->db->get('subjects')->result();
    }



    public function insertclass($data)
    {
        $this->db->insert('semsubjects', $data);
    }


    


    public function get_sections_by_yearlevel($yearLevel)
    {
        $this->db->select('Section');
        $this->db->from('sections');
        $this->db->where('YearLevel', $yearLevel);
        $this->db->order_by('Section', 'ASC');
        $query = $this->db->get();

        return $query->result();
    }


    public function checkClassExists($yearLevel, $subjectCode, $section, $SY)
    {
        $this->db->where('YearLevel', $yearLevel);
        $this->db->where('SubjectCode', $subjectCode);
        $this->db->where('Section', $section);
        $this->db->where('SY', $SY);
        $query = $this->db->get('semsubjects');

        // Log the generated SQL query to debug if it's working correctly
        log_message('debug', 'SQL Query: ' . $this->db->last_query());

        return $query->num_rows() > 0; // Returns true if a record exists
    }

    public function getDescriptionBySubjectCode($subjectCode)
{
    $this->db->select('description');
    $this->db->from('subjects');
    $this->db->where('SubjectCode', $subjectCode);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->row()->description;
    }
    return '';
}



    public function get_subjects_by_yearlevel1($yearLevel)
    {
        $this->db->select('subjectCode, description');
        $this->db->from('subjects');
        $this->db->where('yearLevel', $yearLevel);
        $this->db->order_by('subjectCode', 'ASC'); // Sort by Subject Code
        $query = $this->db->get();
        return $query->result();
    }



    public function get_sections_by_course_and_yearlevel($yearLevel, $course)
    {
        $this->db->select('Section');
        $this->db->from('sections');
        $this->db->where('YearLevel', $yearLevel);
        $this->db->where('Course', $course);
        $this->db->order_by('Section', 'ASC');
        $query = $this->db->get();

        return $query->result(); // Return the results
    }



    public function get_filtered_subjects($sy, $YearLevel = null, $Course = null, $Semester = null)
    {
        $this->db->select("semsubjects.*, CONCAT(staff.FirstName, ' ', staff.MiddleName, ' ', staff.LastName) AS Fullname");
        $this->db->from('semsubjects');
        $this->db->join('staff', 'semsubjects.IDNumber = staff.IDNumber', 'left');
        $this->db->where('semsubjects.SY', $sy);

        if ($YearLevel) {
            $this->db->where('semsubjects.YearLevel', $YearLevel);
        }
        if ($Course) {
            $this->db->where('semsubjects.Course', $Course);
        }
        if ($Semester) {
            $this->db->where('semsubjects.Semester', $Semester);
        }

        $this->db->order_by('semsubjects.SubjectCode', 'ASC');
        $query = $this->db->get();

        return $query->result();
    }



    public function get_courses()
    {
        $this->db->distinct();
        $this->db->select('*'); 
        $this->db->from('course_table');
        $query = $this->db->get();

        return $query->result();
    }


    public function get_Yearlevels()
    {
        $this->db->distinct();
        $this->db->select('YearLevel'); // Replace with the actual column name for year levels
        $this->db->from('semsubjects');
        $query = $this->db->get();

        return $query->result();
    }


    public function get_semesters1()
    {
        $this->db->distinct();
        $this->db->select('Semester'); // Replace with the actual column name for semesters
        $this->db->from('semsubjects');
        $query = $this->db->get();

        return $query->result();
    }



    public function get_courseTable()
    {
        $this->db->distinct();
        $this->db->select('CourseDescription'); // Replace 'Course' with the actual column name for courses in your table
        $this->db->from('course_table');
        $query = $this->db->get();

        return $query->result();
    }


    public function get_subjects_by_course_and_yearlevel($yearLevel, $course)
    {
        $this->db->select('subjectCode, description');
        $this->db->from('subjects');
        $this->db->where('yearLevel', $yearLevel);
        $this->db->where('course', $course);
        $this->db->order_by('subjectCode', 'ASC'); // Sort by Subject Code
        $query = $this->db->get();
        return $query->result();
    }



    public function update_class($subjectid, $SubjectCode, $Description, $Section, $SchedTime, $IDNumber, $SY, $Course, $YearLevel, $SubjectStatus)
    {
        $data = array(
            'subjectid' => $subjectid,
            'SubjectCode' => $SubjectCode,
            'Description' => $Description,
            'Section' => $Section,
            'SchedTime' => $SchedTime,
            'IDNumber' => $IDNumber,
            'SY' => $SY,
            'Course' => $Course,
            'YearLevel' => $YearLevel,
            'SubjectStatus' => $SubjectStatus,

        );
        $this->db->where('subjectid', $subjectid);
        $this->db->update('semsubjects', $data);
    }

    public function get_classbyId($subjectid)
    {
        $query = $this->db->query("SELECT * FROM semsubjects WHERE subjectid = '" . $subjectid . "'");
        return $query->result();
    }

    public function Delete_class($subjectid)
    {
        $this->db->where('subjectid', $subjectid);
        $this->db->delete('semsubjects');
    }


    public function get_year_levels1()
    {
        $this->db->distinct();
        $this->db->select('YearLevel');
        $this->db->from('sections');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_courseTable1()
    {
        $this->db->distinct();
        $this->db->select('Course');
        $this->db->from('sections');
        $query = $this->db->get();
        return $query->result();
    }



    public function get_course()
    {
        $this->db->distinct();
        $this->db->select('CourseDescription');
        $this->db->from('course_table');
        $query = $this->db->get();
        return $query->result();
    }



    public function get_payment($SY)
    {
        // First, get ORNumbers from the 'voidreceipts' table
        $voided_ORNumbers = $this->db->select('ORNumber')
            ->from('voidreceipts')
            ->get()
            ->result_array();

        // Extract ORNumbers into an array
        $voided_ORNumbers = array_column($voided_ORNumbers, 'ORNumber');

        // Now query 'paymentsaccounts' for records from the last 7 days excluding voided ORNumbers
        $this->db->select('*');
        $this->db->from('paymentsaccounts');
        $this->db->where('paymentsaccounts.SY', $SY);

        // $this->db->where('pDate >=', date('Y-m-d', strtotime('-7 days')));

        // Exclude ORNumbers found in voidreceipts
        if (!empty($voided_ORNumbers)) {
            $this->db->where_not_in('ORNumber', $voided_ORNumbers);
        }

        $query = $this->db->get();
        return $query->result();
    }





    public function getFilteredPayments($startDate, $endDate)
    {
        // First, get ORNumbers from the 'voidreceipts' table
        $voided_ORNumbers = $this->db->select('ORNumber')
            ->from('voidreceipts')
            ->get()
            ->result_array();

        // Extract ORNumbers into an array
        $voided_ORNumbers = array_column($voided_ORNumbers, 'ORNumber');

        // Now query 'paymentsaccounts' for records within the date range, excluding voided ORNumbers
        $this->db->select('*');
        $this->db->from('paymentsaccounts');
        $this->db->where('pDate >=', $startDate);
        $this->db->where('pDate <=', $endDate);

        // Exclude ORNumbers found in voidreceipts
        if (!empty($voided_ORNumbers)) {
            $this->db->where_not_in('ORNumber', $voided_ORNumbers);
        }

        $query = $this->db->get();
        return $query->result();
    }





    public function get_payment1()
    {
        $this->db->select('Cashier');
        $this->db->from('paymentsaccounts');
        $query = $this->db->get();
        return $query->row(); // Return a single record as an object
    }

    public function void($SY)
    {
        $this->db->select('voidreceipts.ORNumber, voidreceipts.description, voidreceipts.Amount, voidreceipts.voidDate as PDate, voidreceipts.Reasons, studeprofile.FirstName, studeprofile.MiddleName, studeprofile.LastName, paymentsaccounts.ORStatus'); // Added ORStatus
        $this->db->from('voidreceipts');
        $this->db->join('paymentsaccounts', 'paymentsaccounts.ID = voidreceipts.ID');
        $this->db->join('studeprofile', 'studeprofile.StudentNumber = paymentsaccounts.StudentNumber');
        $this->db->where('paymentsaccounts.SY', $SY);
        $this->db->where('paymentsaccounts.ORStatus', 'Void');
        $query = $this->db->get();
        return $query->result();
    }



    public function getPaymentDetailsByORNumber($ORNumber)
    {
        $this->db->select('paymentsaccounts.*, studeprofile.FirstName, studeprofile.MiddleName, studeprofile.LastName');
        $this->db->from('paymentsaccounts');
        $this->db->join('studeprofile', 'studeprofile.StudentNumber = paymentsaccounts.StudentNumber');
        $this->db->where('paymentsaccounts.ORNumber', $ORNumber);
        $query = $this->db->get();

        return $query->row();
    }




    public function updateORStatus($ORNumber, $ORStatus, $voidData = [])
    {
        // Update the ORStatus in paymentsaccounts
        $this->db->set('ORStatus', $ORStatus);
        $this->db->where('ORNumber', $ORNumber);
        $updateResult = $this->db->update('paymentsaccounts');

        if ($updateResult) {
            // Ensure 'ID' is provided
            if (!isset($voidData['ID'])) {
                // Handle missing ID, possibly log an error
                log_message('error', 'ID is missing in voidData when inserting into voidreceipts.');
                return false;
            }

            // Prepare data for insertion into voidreceipts
            $voidReceiptData = [
                'ID' => $voidData['ID'], // Include ID for foreign key
                'ORNumber' => $ORNumber,
                'Amount' => $voidData['amount'],
                'PaymentDate' => $voidData['pDate'],
                'Description' => $voidData['description'],
                'VoidDate' => $voidData['voidDate'],
                'Cashier' => $voidData['cashier'],
                'Reasons' => $voidData['Reasons']
            ];

            // Insert into voidreceipts table
            $insertResult = $this->db->insert('voidreceipts', $voidReceiptData);

            if (!$insertResult) {
                // Handle insertion error, possibly log
                log_message('error', 'Failed to insert into voidreceipts: ' . $this->db->error()['message']);
                return false;
            }
        }

        return $updateResult;
    }



    public function grade_view($sy, $sem)
    {
        $this->db->select('*');
        $this->db->from('registration');
        $this->db->where('SY', $sy);
        $this->db->where('Sem', $sem);  // 🔄 corrected here
        $this->db->group_by(['SubjectCode', 'Description', 'Instructor', 'Section']);
        $query = $this->db->get();
        return $query->result();
    }
    
public function get_company_info($settingsID) {
    return $this->db->get_where('o_srms_settings', ['settingsID' => $settingsID])->row();
}


public function update_company_info($id, $data) {
    unset($data['settingsID']);
    $this->db->where('settingsID', $id);
    return $this->db->update('o_srms_settings', $data);
}

    // SIGNATORIES------------
    // END-------------------

}
