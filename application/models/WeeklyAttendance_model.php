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

        foreach ($data['personnel'] as $personnelID => $row) {
            foreach ($dates as $date) {
                $present = isset($row['present'][$date]) ? 1 : 0;
                $this->db->insert('attendance', [
                    'personnelID' => $personnelID,
                    'projectID' => $projectID,
                    'date' => $date,
                    'status' => $present ? 'Present' : 'Absent',
                ]);
            }

            // Save total hours
            $this->db->insert('work_hours', [
                'personnelID' => $personnelID,
                'projectID' => $projectID,
                'from' => $from,
                'to' => $to,
                'total_hours' => $row['hours']
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
    $this->db->select('personnelID, total_hours');
    $this->db->from('work_hours');
    $this->db->where('projectID', $projectID);
    $this->db->where('`from`', $from);
    $this->db->where('`to`', $to);
    $query = $this->db->get();

    $hours = [];
    foreach ($query->result() as $row) {
        $hours[$row->personnelID] = $row->total_hours;
    }
    return $hours;
}

}
