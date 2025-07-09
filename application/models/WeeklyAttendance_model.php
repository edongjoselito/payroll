<?php
class WeeklyAttendance_model extends CI_Model {

public function getProjects() {
    $settingsID = $this->session->userdata('settingsID');

    return $this->db
        ->where('settingsID', $settingsID)
        ->get('project')
        ->result();
}


    
    
public function getEmployeesByProject($projectID) {
    $settingsID = $this->session->userdata('settingsID');

    return $this->db
        ->where('settingsID', $settingsID)
        ->get('personnel')
        ->result();
}





 public function saveAttendance($data) {
    $dates = $data['dates'];
    $projectID = $data['projectID'];
    $from = $data['from'];
    $to = $data['to'];
    $attendance = $data['attendance'];
    $settingsID = $data['settingsID'];

    foreach ($attendance as $personnelID => $rows) {
        $totalHours = 0;

        foreach ($rows as $date => $entry) {
            $status = isset($entry['status']) ? 'Present' : 'Absent';

            $raw = isset($entry['hours']) ? strval($entry['hours']) : '0';
            $parts = explode('.', $raw);

            $hours = (int)$parts[0];
            $mins = 0;

            if (isset($parts[1])) {
                $minsStr = str_pad($parts[1], 2, '0', STR_PAD_LEFT); 
                $mins = (int)$minsStr;
                if ($mins > 59) $mins = 59;
            }

            $converted = $hours + ($mins / 60);
            $totalHours += $converted;

            // Save per-day attendance
            $this->db->replace('attendance', [
                'personnelID'    => $personnelID,
                'projectID'      => $projectID,
                'date'           => $date,
                'status'         => $status,
                'work_duration'  => $converted,
                'settingsID'     => $settingsID
            ]);
        }

        // Save total weekly hours
        $this->db->replace('work_hours', [
            'personnelID'  => $personnelID,
            'projectID'    => $projectID,
            '`from`'       => $from,
            '`to`'         => $to,
            'total_hours'  => $totalHours,
            'settingsID'   => $settingsID
        ]);
    }
}




    public function getProjectById($id) {
    return $this->db->where('projectID', $id)->get('project')->row();
}
// DISPLAY SAVED ATTENDANCE

public function getAttendanceRecords($projectID, $from, $to) {
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.*, p.first_name, p.last_name');
    $this->db->from('attendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.projectID', $projectID);
    $this->db->where('a.date >=', $from);
    $this->db->where('a.date <=', $to);
       $this->db->where('p.settingsID', $this->session->userdata('settingsID'));


    $query = $this->db->get();
    $result = [];
    foreach ($query->result() as $row) {
        $result[$row->personnelID]['name'] = $row->last_name . ', ' . $row->first_name;
        $result[$row->personnelID]['dates'][$row->date] = $row->status;
    }
    return $result;
}


public function getWorkHours($projectID, $from, $to) {
    $settingsID = $this->session->userdata('settingsID');

    $this->db->select('a.personnelID, SUM(a.work_duration) as total_hours');
    $this->db->from('attendance a');
    $this->db->join('personnel p', 'p.personnelID = a.personnelID');
    $this->db->where('a.projectID', $projectID);
    $this->db->where('a.date >=', $from);
    $this->db->where('a.date <=', $to);
    $this->db->where('p.settingsID', $settingsID);
    $this->db->group_by('a.personnelID');

    $query = $this->db->get();
    $hours = [];
    foreach ($query->result() as $row) {
        $hours[$row->personnelID] = $row->total_hours;
    }
    return $hours;
}





public function deleteAttendanceByDateRange($projectID, $from, $to)
{
    $this->db->where('projectID', $projectID);
    $this->db->where('date >=', $from);
    $this->db->where('date <=', $to);
    $this->db->delete('attendance');

    $this->db->where('projectID', $projectID);
    $this->db->where('`from` >=', $from);
    $this->db->where('`to` <=', $to);
    $this->db->delete('work_hours');
}

}
