<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Borrow_model extends CI_Model {

    public function get_personnel() {
        return $this->db->get('personnel')->result();
    }

   public function get_cash_advances() {
    $this->db->select("
        ca.*, 
        CONCAT(
            p.first_name, ' ',
            LEFT(p.middle_name, 1), '. ',
            p.last_name,
            IF(p.name_ext IS NOT NULL AND p.name_ext != '', CONCAT(' ', p.name_ext), '')
        ) AS fullname
    ");
    $this->db->from('cashadvance ca');
    $this->db->join('personnel p', 'p.personnelID = ca.personnelID');
    $this->db->where('ca.description', 'Cash Advance');
    return $this->db->get()->result();
}

public function get_cash_advances_by_settings($settingsID)
{
    $this->db->select("
        ca.*, 
        CONCAT(
            p.last_name, ', ',
            p.first_name,
            IF(p.middle_name IS NOT NULL AND p.middle_name != '', CONCAT(' ', LEFT(p.middle_name, 1), '.'), ''),
            IF(p.name_ext IS NOT NULL AND p.name_ext != '', CONCAT(' ', p.name_ext), '')
        ) AS fullname
    ");
    $this->db->from('cashadvance ca');
    $this->db->join('personnel p', 'p.personnelID = ca.personnelID');
    $this->db->where('ca.description', 'Cash Advance'); 
    $this->db->where('ca.settingsID', $settingsID);
    $this->db->order_by('p.last_name, p.first_name'); 
    return $this->db->get()->result();
}


  public function insert_cash_advance($data) {
    $insert = [
        'personnelID'  => $data['personnelID'],
        'description'  => 'Cash Advance',
        'amount'       => $data['amount'],
        'date'         => $data['date'],
        'deduct_from'  => $data['deduct_from'],
        'deduct_to'    => $data['deduct_to'],
        'settingsID'   => $this->session->userdata('settingsID'),
        'type'         => 'cash'
    ];
    return $this->db->insert('cashadvance', $insert);
}


public function update_cash_advance($data) {
    $update = [
        'amount' => $data['amount'],
        'date' => $data['date'],
        'deduct_from' => $data['deduct_from'] ?? null,
        'deduct_to' => $data['deduct_to'] ?? null,
    ];
    $this->db->where('id', $data['id']);
    $this->db->update('cashadvance', $update);
}



    public function delete_cash_advance($id) {
        $this->db->where('id', $id)->delete('cashadvance');
    }

public function get_materials() {
    $this->db->select("
        ca.*, 
        CONCAT(
            p.first_name, ' ',
            LEFT(p.middle_name, 1), '. ',
            p.last_name,
            IF(p.name_ext IS NOT NULL AND p.name_ext != '', CONCAT(' ', p.name_ext), '')
        ) AS fullname
    ");
    $this->db->from('cashadvance ca');
    $this->db->join('personnel p', 'p.personnelID = ca.personnelID');
    $this->db->where('ca.description !=', 'Cash Advance');
    return $this->db->get()->result();
}



    public function insert_material($data) {
        $insert = [
            'personnelID' => $data['personnelID'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'date' => $data['date']
        ];
        $this->db->insert('cashadvance', $insert);
    }

    public function update_material($data) {
        $update = [
            'description' => $data['description'],
            'amount' => $data['amount'],
            'date' => $data['date']
        ];
        $this->db->where('id', $data['id'])->update('cashadvance', $update);
    }

    public function delete_material($id) {
        $this->db->where('id', $id)->delete('cashadvance');
    }


public function get_max_borrowable_amount($personnelID, $date)
{
    $this->db->where('personnelID', $personnelID);
    $personnel = $this->db->get('personnel')->row();

    if (!$personnel) return 0;

    $rateType = strtolower($personnel->rateType);
    $rateAmount = floatval($personnel->rateAmount);
    $sss = floatval($personnel->sss_deduct);
    $pagibig = floatval($personnel->pagibig_deduct);
    $philhealth = floatval($personnel->philhealth_deduct);

    // Get attendance count on this date
    $this->db->where('personnelID', $personnelID);
    $this->db->where('attendance_date', $date);
    $daysPresent = $this->db->count_all_results('personnelattendance');

    // Calculate salary based on rateType
    $salary = 0;
    if ($rateType == 'hour' || $rateType == 'hourly') {
        $this->db->select_sum('workDuration');
        $this->db->where('personnelID', $personnelID);
        $this->db->where('attendance_date', $date);
        $query = $this->db->get('personnelattendance')->row();
        $hoursWorked = floatval($query->workDuration ?? 0);
        $salary = $rateAmount * $hoursWorked;
    } elseif ($rateType == 'day' || $rateType == 'daily') {
        $salary = $rateAmount * $daysPresent;
    } elseif ($rateType == 'month' || $rateType == 'monthly') {
        $salary = ($daysPresent > 0) ? $rateAmount / 30 * $daysPresent : 0;
    }

    // Total Deduction
    $total_deduct = $sss + $pagibig + $philhealth;

    // Borrowable amount
    $borrowable = $salary - $total_deduct;

    return max(0, round($borrowable, 2));
}










}
