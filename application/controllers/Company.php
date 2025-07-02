<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('SettingsModel'); // Make sure this model exists
    }

    // Display company information (view-only)
    public function index() {
        $settingsID = $this->session->userdata('settingsID');
        $data['info'] = $this->SettingsModel->get_company_info($settingsID);

        $this->load->view('company_info', $data);
    }

    // Edit page
    public function edit($id) {
        $data['info'] = $this->SettingsModel->get_company_info($id);
        $this->load->view('company_info_edit', $data);
    }

    // Update action
   // Update action
public function update() {
    $id = $this->input->post('settingsID');

    // Prevent editing other company info
    if ($id != $this->session->userdata('settingsID')) {
        show_error('Unauthorized access');
    }

    $data = array(
        'SchoolName'    => $this->input->post('SchoolName'),
        'SchoolAddress' => $this->input->post('SchoolAddress'),
        'SchoolHead'    => $this->input->post('SchoolHead'),
        'sHeadPosition' => $this->input->post('sHeadPosition'),
        'contactNos'    => $this->input->post('contactNos'),
        'telNo'         => $this->input->post('telNo'),
        'tinNo'         => $this->input->post('tinNo'),
    );

    // Handle image uploads
    if (!empty($_FILES['schoolLogo']['tmp_name'])) {
        $data['schoolLogo'] = file_get_contents($_FILES['schoolLogo']['tmp_name']);
    }

    if (!empty($_FILES['letterHead']['tmp_name'])) {
        $data['letterHead'] = file_get_contents($_FILES['letterHead']['tmp_name']);
    }

    $this->db->where('settingsID', $id);
    $this->db->update('o_srms_settings', $data);

    $this->session->set_flashdata('success', 'Company information updated successfully.');
    redirect('Company');
}

private function is_valid_image($file) {
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    return in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize;
}


}
