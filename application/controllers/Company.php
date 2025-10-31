<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends CI_Controller {

    /**
     * Allowed mime types for uploaded branding images.
     * @var array<string>
     */
    private $allowedImageMime = array('image/png', 'image/jpeg');

    /**
     * Hard limit for logo / letterhead uploads (2MB).
     * @var int
     */
    private $maxImageSize = 2097152;

    public function __construct() {
        parent::__construct();
        $this->load->model('SettingsModel');
        $this->load->library('form_validation');
    }

    // Display company information (view-only)
    public function index() {
        $settingsID = $this->session->userdata('settingsID');
        if (empty($settingsID)) {
            $settingsID = $this->SettingsModel->get_active_settings_id();
            if (!empty($settingsID)) {
                $this->session->set_userdata('settingsID', $settingsID);
            }
        }

        if (empty($settingsID)) {
            show_error('Unable to determine the active company profile.', 500, 'Company Information Missing');
        }

        $info = $this->SettingsModel->get_company_info($settingsID);
        if (!$info) {
            show_error('Company information could not be found.', 404, 'Company Information Missing');
        }

        $data = array(
            'info' => $info
        );

        $this->load->view('company_info', $data);
    }

    // Edit page
    public function edit($id) {
        $info = $this->SettingsModel->get_company_info($id);
        if (!$info) {
            show_error('Company information could not be found.', 404, 'Company Information Missing');
        }

        $this->load->view('company_info_edit', array('info' => $info));
    }

    // Update action
    public function update() {
        $id = $this->input->post('settingsID');

        if (empty($id)) {
            $this->session->set_flashdata('error', 'Invalid company identifier supplied.');
            redirect('Company');
            return;
        }

        if ($id != $this->session->userdata('settingsID')) {
            show_error('Unauthorized access');
        }

        $this->form_validation->set_rules('SchoolName', 'Company Name', 'required|trim');
        $this->form_validation->set_rules('SchoolAddress', 'Company Address', 'required|trim');
        $this->form_validation->set_rules('SchoolHead', 'Company Head', 'required|trim');
        $this->form_validation->set_rules('sHeadPosition', 'Head Position', 'required|trim');
        $this->form_validation->set_rules('contactNos', 'Contact Number', 'trim');
        $this->form_validation->set_rules('telNo', 'Telephone Number', 'trim');
        $this->form_validation->set_rules('tinNo', 'TIN', 'trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Company');
            return;
        }

        $data = array(
            'SchoolName'            => $this->input->post('SchoolName', TRUE),
            'SchoolAddress'         => $this->input->post('SchoolAddress', TRUE),
            'SchoolHead'            => $this->input->post('SchoolHead', TRUE),
            'sHeadPosition'         => $this->input->post('sHeadPosition', TRUE),
            'contactNos'            => $this->input->post('contactNos', TRUE),
            'telNo'                 => $this->input->post('telNo', TRUE),
            'tinNo'                 => $this->input->post('tinNo', TRUE),
            'prepared_by_name'      => $this->input->post('prepared_by_name', TRUE),
            'prepared_by_position'  => $this->input->post('prepared_by_position', TRUE),
            'checked_by_name'       => $this->input->post('checked_by_name', TRUE),
            'checked_by_position'   => $this->input->post('checked_by_position', TRUE),
            'additional_name'       => $this->input->post('additional_name', TRUE),
            'additional_position'   => $this->input->post('additional_position', TRUE)
        );

        $logoBlob = $this->handleImageUpload('schoolLogo');
        if ($logoBlob === FALSE) {
            redirect('Company');
            return;
        }
        if ($logoBlob !== NULL) {
            $data['schoolLogo'] = $logoBlob;
        }

        $letterHeadBlob = $this->handleImageUpload('letterHead');
        if ($letterHeadBlob === FALSE) {
            redirect('Company');
            return;
        }
        if ($letterHeadBlob !== NULL) {
            $data['letterHead'] = $letterHeadBlob;
        }

        $updated = $this->SettingsModel->update_company_info($id, $data);

        if ($updated) {
            $this->session->set_flashdata('success', 'Company information updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'No changes were saved. Please try again.');
        }

        redirect('Company');
    }

    /**
     * Validate and return an image blob for the given field.
     *
     * @param string $fieldName
     * @return string|null|false Returns binary string on success, null when no file was uploaded, false on validation failure.
     */
    private function handleImageUpload($fieldName) {
        if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
            return NULL;
        }

        $file = $_FILES[$fieldName];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return NULL;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'Failed to upload ' . $fieldName . '. Please try again.');
            return FALSE;
        }

        if ($file['size'] > $this->maxImageSize) {
            $this->session->set_flashdata('error', 'The uploaded file for ' . $fieldName . ' exceeds the 2MB size limit.');
            return FALSE;
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === FALSE || !in_array($imageInfo['mime'], $this->allowedImageMime, TRUE)) {
            $this->session->set_flashdata('error', 'Only PNG and JPEG images are allowed for ' . $fieldName . '.');
            return FALSE;
        }

        $blob = @file_get_contents($file['tmp_name']);
        if ($blob === FALSE) {
            $this->session->set_flashdata('error', 'Could not read the uploaded file for ' . $fieldName . '.');
            return FALSE;
        }

        return $blob;
    }

}
