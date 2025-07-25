<?php
class Monthly_model extends CI_Model {

    public function get_personnel($settingsID) {
        return $this->db->get_where('personnel', ['settingsID' => $settingsID])->result();
    }

    public function save_monthly_attendance($post) {
    $attendance_status = $post['attendance_status'];
    $regular_hours = $post['regular_hours'];
    $overtime_hours = $post['overtime_hours'];
    $settingsID = $this->session->userdata('settingsID');
    $encoder = $this->session->userdata('username'); // or IDNumber

    foreach ($attendance_status as $personnelID => $days) {
        foreach ($days as $date => $status) {
            $regHours = $regular_hours[$personnelID][$date] ?? 0;
            $otHours = $overtime_hours[$personnelID][$date] ?? 0;

            $data = [
                'personnelID' => $personnelID,
                'date' => $date,
                'status' => $status,
                'regular_hours' => $regHours,
                'overtime_hours' => $otHours,
                'settingsID' => $settingsID,
                'encoded_by' => $encoder
            ];

            $this->db->replace('monthly', $data);
        }
    }
}
public function get_all_personnel($settingsID)
{
    return $this->db->where('settingsID', $settingsID)
                    ->order_by('last_name', 'asc')
                    ->get('personnel')->result();
}

public function get_monthly_records($month, $settingsID)
{
    $this->db->select('personnelID, date, status, regular_hours, overtime_hours');
    $this->db->where('DATE_FORMAT(date, "%Y-%m") =', $month);  // ✅ add '=' here
    $this->db->where('settingsID', $settingsID);
    $query = $this->db->get('monthly');

    $result = [];
    foreach ($query->result() as $row) {
        $day = date('j', strtotime($row->date)); // 1–31
        $result[$row->personnelID][$day] = [
            'status' => $row->status,
            'reg' => $row->regular_hours,
            'ot' => $row->overtime_hours
        ];
    }
    return $result;
}
// 🔁 Update one attendance record
    public function updateAttendance($personnelID, $date, $data)
    {
        $updateData = [
            'status' => $data['status'],
            'regular_hours' => $data['reg'],
            'holiday_hours' => $data['holiday'],
            'overtime_hours' => $data['ot'],
            'encoded_by' => $this->session->userdata('username') ?? 'system',
            'encoded_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->where('personnelID', $personnelID);
        $this->db->where('date', $date);
        return $this->db->update('monthly', $updateData);
    }

    // ❌ Delete records by full month
    public function deleteMonth($month)
    {
        $this->db->like('date', $month, 'after'); // e.g., '2025-07'
        return $this->db->delete('monthly');
    }

}
