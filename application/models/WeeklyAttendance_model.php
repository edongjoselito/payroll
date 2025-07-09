<?php
class WeeklyAttendance_model extends CI_Model {

    public function getProjects() {
        return $this->db->get('project')->result();
    }
public function getEmployeesByProject($projectID) {
    // For now, just return all personnel
    return $this->db->get('personnel')->result();
}


  public function saveAttendance($data) {
    $dates = $data['dates'];
    $projectID = $data['projectID'];
    $from = $data['from'];
    $to = $data['to'];
    $attendance = $data['attendance'];

    foreach ($attendance as $personnelID => $rows) {
        $totalHours = 0;

        foreach ($rows as $date => $entry) {
            $status = isset($entry['status']) ? 'Present' : 'Absent';
            $hours = isset($entry['hours']) ? floatval($entry['hours']) : 0;
            $totalHours += $hours;

            // Insert or replace attendance record per date
            $this->db->replace('attendance', [
                'personnelID'    => $personnelID,
                'projectID'      => $projectID,
                'date'           => $date,
                'status'         => $status,
                'work_duration'  => $hours
            ]);
        }

        // Insert or replace total work hours for date range
        $this->db->replace('work_hours', [
            'personnelID'  => $personnelID,
            'projectID'    => $projectID,
            '`from`'       => $from,
            '`to`'         => $to,
            'total_hours'  => $totalHours
        ]);
    }
}


    public function getProjectById($id) {
    return $this->db->where('projectID', $id)->get('project')->row();
}
// DISPLAY SAVED ATTENDANCE

public function getAttendanceRecords($projectID, $from, $to) {
    $this->db->select('a.*, p.first_name, p.last_name');
    $this->db->from('attendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.projectID', $projectID);
    $this->db->where('a.date >=', $from);
    $this->db->where('a.date <=', $to);
    $query = $this->db->get();

    $result = [];
    foreach ($query->result() as $row) {
        $result[$row->personnelID]['name'] = $row->last_name . ', ' . $row->first_name;
        $result[$row->personnelID]['dates'][$row->date] = $row->status;
    }
    return $result;
}

public function getWorkHours($projectID, $from, $to) {
    $this->db->select('personnelID, SUM(work_duration) as total_hours');
    $this->db->from('attendance');
    $this->db->where('projectID', $projectID);
    $this->db->where('date >=', $from);
    $this->db->where('date <=', $to);
    $this->db->group_by('personnelID');

    $query = $this->db->get();

    $hours = [];
    foreach ($query->result() as $row) {
        $hours[$row->personnelID] = $row->total_hours;
    }
    return $hours;
}

}
