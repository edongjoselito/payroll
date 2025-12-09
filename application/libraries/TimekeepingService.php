<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TimekeepingService
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('WeeklyAttendance_model');
    }

    /**
     * Save weekly attendance batch.
     * Wraps WeeklyAttendance_model::saveAttendance().
     */
    public function saveWeeklyAttendance(array $post): array
    {
        // We keep all structure of $post as is
        $this->CI->WeeklyAttendance_model->saveAttendance($post);

        // Fetch project for feedback
        $project = $this->CI->db
            ->get_where('project', ['projectID' => $post['projectID']])
            ->row();

        return [
            'projectID'    => $post['projectID'],
            'projectTitle' => $project->projectTitle ?? 'N/A',
            'from'         => $post['from'],
            'to'           => $post['to'],
        ];
    }
}
