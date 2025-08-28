<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BimonthPayroll_model extends CI_Model
{
    // public function create_batch($settingsID, $projectID, $start, $end, $month, $totals_json, $userID = null)
    // {
    //     $data = [
    //         'settingsID' => $settingsID,
    //         'projectID'  => $projectID,
    //         'start_date' => $start,
    //         'end_date'   => $end,
    //         'month'      => $month,
    //         'totals_json'=> json_encode($totals_json),
    //         'created_by' => $userID,
    //     ];
    //     $this->db->insert('payroll_bimonth_batches', $data);
    //     return $this->db->insert_id();
    // }
public function create_batch($settingsID, $projectID, $start, $end, $month, array $totals, $userID)
{
    $projectID = $projectID ?: 0; // normalize nulls

    $existing = $this->db->get_where('payroll_bimonth_batches', [
        'settingsID' => $settingsID,
        'projectID'  => $projectID,
        'start_date' => $start,
        'end_date'   => $end
    ])->row();

    $payload = [
        'settingsID' => $settingsID,
        'projectID'  => $projectID,
        'start_date' => $start,
        'end_date'   => $end,
        'month'      => $month,
        'totals_json'=> json_encode($totals),
        'created_by' => $userID,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    if ($existing) {
        // update existing batch
        $this->db->where('id', $existing->id)->update('payroll_bimonth_batches', $payload);
        $this->db->delete('payroll_bimonth_lines', ['batch_id' => $existing->id]);
        return (int)$existing->id;
    } else {
        // insert new
        $this->db->insert('payroll_bimonth_batches', $payload);
        return (int)$this->db->insert_id();
    }
}

    public function insert_lines($batch_id, array $lines)
    {
        // $lines: [ ['personnelID'=>..., 'amounts_json'=>[...] ], ... ]
        foreach ($lines as &$l) $l = ['batch_id' => $batch_id, 'personnelID' => $l['personnelID'], 'amounts_json' => json_encode($l['amounts_json'])];
        if (!empty($lines)) $this->db->insert_batch('payroll_bimonth_lines', $lines);
    }

//    public function list_batches($settingsID, $projectID = null)
// {
//     $this->db->select('id, settingsID, projectID, start_date, end_date, month, totals_json, created_by, MIN(created_at) as created_at')
//              ->from('payroll_bimonth_batches')
//              ->where('settingsID', $settingsID);

//     if (!empty($projectID)) {
//         $this->db->where('projectID', $projectID);
//     }

//     // 👇 group duplicates (multiple saved runs for same period)
//     $this->db->group_by(['start_date', 'end_date', 'projectID', 'month']);

//     $this->db->order_by('start_date', 'DESC');
//     $this->db->order_by('end_date', 'DESC');

//     return $this->db->get()->result();
// }
public function list_batches($settingsID, $projectID = null)
{
    $this->db->select('MIN(id) as id, start_date, end_date, month, totals_json, created_by, MIN(created_at) as created_at');
    $this->db->from('payroll_bimonth_batches');
    $this->db->where('settingsID', $settingsID);
    if ($projectID) {
        $this->db->where('projectID', $projectID);
    }
    $this->db->group_by(['start_date','end_date','month']);
    $this->db->order_by('start_date','DESC');
    return $this->db->get()->result();
}


    public function get_batch($batch_id)
    {
        $batch = $this->db->get_where('payroll_bimonth_batches', ['id'=>$batch_id])->row();
        $lines = $this->db->get_where('payroll_bimonth_lines', ['batch_id'=>$batch_id])->result();
        return [$batch, $lines];
    }
}
