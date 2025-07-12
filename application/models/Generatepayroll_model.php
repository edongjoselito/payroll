<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Generatepayroll_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }
public function getProjectsBySettingsID($settingsID)
{
    return $this->db
        ->select('projectID, projectTitle')
        ->from('project')
        ->where('settingsID', $settingsID)
        ->get()
        ->result();
}

}
