<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OtherDeduction_model extends CI_Model {

public function get_other_deductions($settingsID) {
    $this->db->select("
        ca.*, 
        ca.deduct_from,
        ca.deduct_to,
        CONCAT(
            p.last_name, ', ',
            p.first_name,
            IF(p.middle_name IS NOT NULL AND p.middle_name != '', CONCAT(' ', LEFT(p.middle_name, 1), '.'), ''),
            IF(p.name_ext IS NOT NULL AND p.name_ext != '', CONCAT(' ', p.name_ext), '')
        ) AS fullname
    ");
    $this->db->from('cashadvance ca');
    $this->db->join('personnel p', 'p.personnelID = ca.personnelID');
    $this->db->where('ca.settingsID', $settingsID);
    $this->db->where('ca.type', 'Others');
$this->db->order_by('ca.`date`', 'ASC');
$this->db->order_by('p.last_name', 'ASC');
$this->db->order_by('p.first_name', 'ASC');

    return $this->db->get()->result();
}


    public function get_personnel($settingsID) {
        $this->db->where('settingsID', $settingsID);
        return $this->db->get('personnel')->result();
    }

  public function save_other_deduction($data) {
    $record = [
        'personnelID' => $data['personnelID'],
        'description' => $data['description'],
        'amount'      => $data['amount'],
        'date'        => $data['date'],
        'deduct_from' => $data['deduct_from'] ?? null,
        'deduct_to'   => $data['deduct_to'] ?? null,
        'type'        => 'Others',
        'settingsID'  => $this->session->userdata('settingsID'),
    ];

    if (!empty($data['id'])) {
        $this->db->where('id', $data['id']);
        $this->db->update('cashadvance', $record);
    } else {
        $this->db->insert('cashadvance', $record);
    }

}


    public function delete_other_deduction($id) {
        $this->db->where('id', $id);
        $this->db->delete('cashadvance');
    }

    public function update_other_deduction($data) {
    if (!isset($data['id'])) {
        return false;
    }

    $updateData = [
        'personnelID' => $data['personnelID'],
        'description' => $data['description'],
        'amount'      => $data['amount'],
        'date'        => $data['date'],
        'deduct_from' => isset($data['deduct_from']) && $data['deduct_from'] !== '' ? $data['deduct_from'] : null,
        'deduct_to'   => isset($data['deduct_to'])   && $data['deduct_to']   !== '' ? $data['deduct_to']   : null,
        'type'        => 'Others'
    ];

    $this->db->where('id', $data['id']);
    return $this->db->update('cashadvance', $updateData);
}


    public function insert_other_deduction($data) {
        $data['type'] = 'Others';
        return $this->db->insert('cashadvance', $data);
    }

public function get_deductions_by_date_range($from, $to, $settingsID)
{
    return $this->db
        ->select('personnelID, amount, description, date')
        ->from('cashadvance')
        ->where('date >=', $from)
        ->where('date <=', $to)
        ->where('settingsID', $settingsID)
        ->where('type', 'Others') // ✅ THIS is critical
        ->get()
        ->result();
}

public function get_all_deductions($settingsID)
    {
        $this->db->select("
            p.personnelID,
            CONCAT(
                p.last_name, ', ', p.first_name,
                IF(p.middle_name != '', CONCAT(' ', LEFT(p.middle_name, 1), '.'), ''),
                IF(p.name_ext != '', CONCAT(' ', p.name_ext), '')
            ) AS full_name,
            ca.description AS ca_desc, ca.amount AS ca_amount, ca.date AS ca_date,
            gd.description AS gd_desc, gd.amount AS gd_amount, gd.date AS gd_date
        ");
        $this->db->from('personnel p');
        $this->db->join('cashadvance ca', 'ca.personnelID = p.personnelID AND ca.settingsID = p.settingsID', 'left');
        $this->db->join('government_deductions gd', 'gd.personnelID = p.personnelID AND gd.settingsID = p.settingsID', 'left');
        $this->db->where('p.settingsID', $settingsID);
        $this->db->order_by('p.last_name');

        return $this->db->get()->result();
    }
public function get_loan_summary($settingsID)
{
    $this->db->select("
        pl.*, 
        CONCAT(
            p.last_name, ', ', p.first_name,
            IF(p.middle_name != '', CONCAT(' ', LEFT(p.middle_name, 1), '.'), ''),
            IF(p.name_ext != '', CONCAT(' ', p.name_ext), '')
        ) AS full_name
    ");
    $this->db->from('personnelloans pl');
    $this->db->join('personnel p', 'p.personnelID = pl.personnelID');
    $this->db->where('pl.settingsID', $settingsID);
    $this->db->order_by('p.last_name', 'ASC');
    $this->db->order_by('p.first_name', 'ASC');
    return $this->db->get()->result();
}
public function get_attendance_summary($settingsID)
{
    $sql = "
        SELECT 
            p.personnelID,
            CONCAT(
                p.last_name, ', ', p.first_name,
                IF(p.middle_name != '', CONCAT(' ', LEFT(p.middle_name, 1), '.'), ''),
                IF(p.name_ext != '', CONCAT(' ', p.name_ext), '')
            ) AS full_name,
            SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
            SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS late_count,
            GROUP_CONCAT(DISTINCT CASE WHEN a.status = 'Absent' THEN DATE_FORMAT(a.date, '%Y-%m-%d') END ORDER BY a.date SEPARATOR ', ') AS absent_dates,
            GROUP_CONCAT(DISTINCT CASE WHEN a.status = 'Late' THEN DATE_FORMAT(a.date, '%Y-%m-%d') END ORDER BY a.date SEPARATOR ', ') AS late_dates
        FROM personnel p
        LEFT JOIN attendance a ON a.personnelID = p.personnelID AND a.settingsID = p.settingsID
        WHERE p.settingsID = ?
        GROUP BY p.personnelID
        ORDER BY p.last_name, p.first_name
    ";

    return $this->db->query($sql, array($settingsID))->result();
}

public function get_attendance_summary_filtered($type, $baseDate)
{
    $this->db->select("CONCAT_WS(' ', CONCAT(p.last_name, ','), p.first_name, p.middle_name, p.name_ext) AS full_name");

    $this->db->select("a.personnelID, GROUP_CONCAT(a.date) AS absent_dates, COUNT(*) AS absent_count");
    $this->db->from('attendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.status', 'Absent');
    $this->db->where('a.settingsID', $this->session->userdata('settingsID'));

    if ($type == 'weekly') {
        $monday = date('Y-m-d', strtotime('monday this week', strtotime($baseDate)));
        $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($baseDate)));
        $this->db->where('a.date >=', $monday);
        $this->db->where('a.date <=', $sunday);
    } elseif ($type == 'monthly') {
        $this->db->where('MONTH(a.date)', date('m', strtotime($baseDate)));
        $this->db->where('YEAR(a.date)', date('Y', strtotime($baseDate)));
    } elseif ($type == 'yearly') {
        $this->db->where('YEAR(a.date)', date('Y', strtotime($baseDate)));
    }

    $this->db->group_by('a.personnelID');
    $this->db->order_by('p.last_name, p.first_name');
    return $this->db->get()->result();
}

}
