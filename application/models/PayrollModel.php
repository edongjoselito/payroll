<?php
class PayrollModel extends CI_Model 
{
	
	function totalAlumni()
	{
	$query=$this->db->query("SELECT count(StudentNumber) as graduatesCounts FROM alum_reg");
	return $query->result();}	
  
  
  function employedCounts()
	{
	$query=$this->db->query("SELECT count(StudentNumber) as graduatesCounts FROM alum_reg where empStat='Employed'");
	return $query->result();}	
	
	function unemployedCounts()
	{
	$query=$this->db->query("SELECT count(StudentNumber) as graduatesCounts FROM alum_reg where empStat='Unemployed'");
	return $query->result();}	

	
	function noStatCounts()
	{
	$query=$this->db->query("SELECT count(StudentNumber) as graduatesCounts FROM alum_reg where empStat='No Status'");
	return $query->result();}	
	
	function byCourseCount()
	{
	$query=$this->db->query("SELECT course, count(course) as studeCount FROM alum_reg");
	return $query->result();}

	function byNatureWorkCount()
	{
	$query=$this->db->query("SELECT workNature, count(workNature) as workcounts FROM alum_reg");
	return $query->result();}

	function byPositionCount()
	{
	$query=$this->db->query("SELECT empPosition, count(empPosition) as studeCount FROM alum_reg");
	return $query->result();}
	
	function selfEmployedCounts()
	{
	$query=$this->db->query("SELECT count(StudentNumber) as graduatesCounts FROM alum_reg where empType='Self-Employed' group by StudentNumber");
	return $query->result();}
	
	function governmentEmployedCounts()
	{
	$query=$this->db->query("SELECT count(StudentNumber) as graduatesCounts FROM alum_reg where empType='Government-Employed' group by StudentNumber");
	return $query->result();}
	
	function privateEmployedCounts()
	{
	$query=$this->db->query("SELECT count(StudentNumber) as graduatesCounts FROM alum_reg where empType='Private-Employed' group by StudentNumber");
	return $query->result();}
	

	function alumniBatch($syGraduated){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber where a.syGraduated='".$syGraduated."' order by LastName");
	return $query->result();
	}
	
	function alumniAll(){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber order by LastName");
	return $query->result();
	}
	

	function selfEmployed(){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber where a.empType='Self-Employed' order by LastName");
	return $query->result();
	}
	
	function masterlistGovernmentEmployed(){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber where a.empType='Government-Employed' order by LastName");
	return $query->result();
	}
	
	function masterlistPrivateEmployed(){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber where a.empType='Private-Employed' order by LastName");
	return $query->result();
	}
	

	function employed(){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber where a.empStat='Employed' order by LastName");
	return $query->result();
	}

	function unemployed(){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber where a.empStat='Unemployed' order by LastName");
	return $query->result();
	}

	function nostatus(){
	$query=$this->db->query("SELECT * FROM alum_reg a join studeprofile p on a.StudentNumber=p.StudentNumber where a.empStat='No Status' order by LastName");
	return $query->result();
	}

//Display Students Profile
	function displayrecordsById($id)
	{
	$query=$this->db->query("select * from studeprofile p join alum_reg a on p.StudentNumber=a.StudentNumber where a.StudentNumber='".$id."'");
	return $query->result();
	}	
	

public function getPayrollData($projectID, $start, $end, $rateType)
{
    $this->db->select('p.*, s.fName, s.lName, s.position, p.status as attendance_status, s.rateType, s.rate');
    $this->db->from('personnel_assignment pa');
    $this->db->join('staff s', 'pa.staffID = s.IDNumber');
    $this->db->join('project_attendance p', 'p.staffID = s.IDNumber');
    $this->db->where('pa.projectID', $projectID);
    $this->db->where('p.date >=', $start);
    $this->db->where('p.date <=', $end);
    $this->db->where('s.rateType', $rateType);
    $query = $this->db->get();

    return $query->result();
}

	public function get_personnel_by_project($projectID)
{
    return $this->db->get_where('payroll_masterlist', ['projectID' => $projectID])->result();
}

public function getGovDeduction($personnelID, $start, $end, $settingsID)
{
    $this->db->select('description, SUM(amount) AS total');
    $this->db->from('government_deductions');
    $this->db->where('personnelID', $personnelID);
    $this->db->where('deduct_from <=', $end);
    $this->db->where('deduct_to >=', $start);

    $this->db->where('settingsID', $settingsID);
    $this->db->group_by('description');

    $query = $this->db->get();

    $deductions = [
        'SSS' => 0,
        'PAGIBIG' => 0,
        'PHILHEALTH' => 0
    ];

    foreach ($query->result() as $row) {
        $desc = strtoupper(str_replace(['-', ' '], '', $row->description));
        if ($desc === 'SSS') {
            $deductions['SSS'] = $row->total;
        } elseif ($desc === 'PAGIBIG') {
            $deductions['PAGIBIG'] = $row->total;
        } elseif (in_array($desc, ['PHILHEALTH', 'PHIC'])) {
            $deductions['PHILHEALTH'] = $row->total;
        }
    }

    return $deductions;
}





}