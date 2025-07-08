<?php
class Monthly_model extends CI_Model {

    // Get attendance records for a given month for office-based personnel
  public function getMonthlyAttendance($month)
{
    $start = $month . '-01';
    $end = date("Y-m-t", strtotime($start));
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select("a.*, CONCAT(p.first_name, ' ', COALESCE(p.middle_name, ''), ' ', p.last_name, ' ', COALESCE(p.name_ext, '')) AS fullname");
    $this->db->from('personnelattendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where("a.attendance_date BETWEEN '$start' AND '$end'");
    $this->db->where('p.settingsID', $settingsID);
    return $this->db->get()->result();
}


    // Calculate salaries for office-based personnel based on rateType
public function calculateMonthlySalary($month)
{
    $settingsID = $this->session->userdata('settingsID');

    $firstCutoffStart = $month . '-01';
    $firstCutoffEnd = $month . '-15';
    $secondCutoffStart = $month . '-16';
    $secondCutoffEnd = date("Y-m-t", strtotime($month . '-01'));

    // Get personnel with monthly rate
    $this->db->select('personnelID, first_name, middle_name, last_name, name_ext, rateAmount');
    $this->db->from('personnel');
    $this->db->where('settingsID', $settingsID);
    $this->db->where('rateType', 'Month');
    $personnelList = $this->db->get()->result();

    $salaries = [];

    foreach ($personnelList as $person) {
        // Get attendance records for the month
        $this->db->select('attendance_date, attendance_status, workDuration');
        $this->db->from('personnelattendance');
        $this->db->where('personnelID', $person->personnelID);
        $this->db->where("attendance_date BETWEEN '$firstCutoffStart' AND '$secondCutoffEnd'");
        $attendanceRecords = $this->db->get()->result();

        $firstHours = 0;
        $secondHours = 0;

        foreach ($attendanceRecords as $record) {
            $recordDate = $record->attendance_date;

            if ($record->attendance_status === 'Present') {
                $hours = floatval($record->workDuration);
            } elseif ($record->attendance_status === 'Half-Day') {
                $hours = 4;
            } else {
                continue;
            }

            if ($recordDate >= $firstCutoffStart && $recordDate <= $firstCutoffEnd) {
                $firstHours += $hours;
            } elseif ($recordDate >= $secondCutoffStart && $recordDate <= $secondCutoffEnd) {
                $secondHours += $hours;
            }
        }

        $perHourRate = $person->rateAmount / (30 * 8); // 240 hours in a month
        $firstSalary = $perHourRate * $firstHours;
        $secondSalary = $perHourRate * $secondHours;
        $totalSalary = $firstSalary + $secondSalary;

        $salaries[] = (object)[
            'fullname'       => trim("{$person->first_name} {$person->middle_name} {$person->last_name} {$person->name_ext}"),
            'present_days'   => round(($firstHours + $secondHours) / 8, 2),
            'total_hours'    => $firstHours + $secondHours,
            'first_hours'    => $firstHours,
            'second_hours'   => $secondHours,
            'per_hour'       => $perHourRate,
            'first_half'     => $firstSalary,
            'second_half'    => $secondSalary,
            'total_salary'   => $totalSalary
        ];
    }

    return $salaries;
}




public function getOfficePersonnel()
{
    $settingsID = $this->session->userdata('settingsID'); // filter by company/tenant
    $this->db->select('*');
    $this->db->from('personnel');
    $this->db->where('settingsID', $settingsID); 
    $this->db->where('rateType', 'Month'); // ✅ Only include monthly paid personnel
    return $this->db->get()->result();
}


public function getAttendanceByDate($date)
{
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.*, p.first_name, p.middle_name, p.last_name, p.name_ext');
    $this->db->from('personnelattendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.attendance_date', $date);
    $this->db->where('p.settingsID', $settingsID);
    return $this->db->get()->result_array();
}
public function getPresentAttendanceByDate($date)
{
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.*, p.first_name, p.middle_name, p.last_name, p.name_ext');
    $this->db->from('personnelattendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.attendance_date', $date);
    $this->db->where('a.attendance_status', 'Present');
    $this->db->where('p.settingsID', $settingsID);
    return $this->db->get()->result();
}
public function getPresentPersonnelByDate($date)
{
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.*, p.first_name, p.middle_name, p.last_name, p.name_ext');
    $this->db->from('personnelattendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.attendance_date', $date);
    $this->db->where('a.attendance_status', 'Present');
    $this->db->where('p.settingsID', $settingsID);
    return $this->db->get()->result();
}


}
