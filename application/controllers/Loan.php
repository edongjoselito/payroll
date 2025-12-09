<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Loan extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Loan_model');
        $this->load->model('SettingsModel'); // <-- add this so we can fetch defaults
        $this->load->library('LoanService');   // ← add this

        // 1) Require login
        if ($this->session->userdata('logged_in') !== TRUE) {
            redirect('login'); // use the actual route / lowercase to avoid case issues on Linux
            return;
        }

        // 2) Ensure settingsID exists; if not, try to load an active/default settings row
        if (!$this->session->userdata('settingsID')) {
            $active = $this->SettingsModel->getSchoolInfo(); // or your own "active settings" getter
            if (!empty($active) && !empty($active[0]->settingsID)) {
                $this->session->set_userdata('settingsID', $active[0]->settingsID);
            } else {
                // If you really can’t determine a default, then show a friendly page or redirect to a “choose school/company” page
                $this->session->set_flashdata('error', 'No active company/settings found. Please select one.');
                redirect('login');
                return;
            }
        }
    }

    public function personnel_loan()
    {
        $settingsID = $this->session->userdata('settingsID');

        $data['assigned_loans'] = $this->Loan_model->get_assigned_loans($settingsID);
        $data['personnel']      = $this->Loan_model->get_personnel_by_settings($settingsID);
        $data['loans']          = $this->Loan_model->get_all_loans($settingsID); // pass settingsID if your model supports scoping

        $this->load->view('personnel_loan', $data);
    }





    public function edit_loan($id)
    {
        // Load edit form here
    }
    public function delete_loan($loan_id)
    {
        if ($this->Loan_model->delete_loan($loan_id)) {
            $this->session->set_flashdata('success', 'Loan deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete loan.');
        }

        redirect('Loan/loans_view');
    }


    public function get_loan_options()
    {
        $personnelID = $this->input->post('personnelID');

        $person = $this->db->get_where('personnel', ['personnelID' => $personnelID])->row();

        if (!$person) {
            echo json_encode(['status' => 'error', 'message' => 'Personnel not found.']);
            return;
        }

        // Match rateType → type_rate (e.g., 'Month' = 'monthly')
        $rateMap = ['Hour' => 'hourly', 'Day' => 'daily', 'Month' => 'monthly'];
        $mappedRate = $rateMap[$person->rateType] ?? '';

        $loans = $this->db->get_where('loans', ['type_rate' => $mappedRate])->result();

        echo json_encode([
            'status' => 'success',
            'loans' => $loans,
            'person' => $person
        ]);
    }
    // public function update_personnel()
    // {
    //     $personnelID = $this->input->post('personnelID');

    //     $data = [
    //         'first_name'        => $this->input->post('first_name'),
    //         'middle_name'       => $this->input->post('middle_name'),
    //         'last_name'         => $this->input->post('last_name'),
    //         'name_ext'          => $this->input->post('name_ext'),
    //         'birthdate'         => $this->input->post('birthdate'),
    //         'gender'            => $this->input->post('gender'),
    //         'civil_status'      => $this->input->post('civil_status'),
    //         'address'           => $this->input->post('address'),
    //         'contact_number'    => $this->input->post('contact_number'),
    //         'email'             => $this->input->post('email'),
    //         'position'          => $this->input->post('position'),
    //         'rateType'          => $this->input->post('rateType'),
    //         'rateAmount'        => $this->input->post('rateAmount'),
    //         'philhealth_number' => $this->input->post('philhealth_number'),
    //         'pagibig_number'    => $this->input->post('pagibig_number'),
    //         'sss_number'        => $this->input->post('sss_number'),
    //         'tin_number'        => $this->input->post('tin_number'),
    //     ];

    //     $this->db->where('personnelID', $personnelID);
    //     if ($this->db->update('personnel', $data)) {
    //         $this->session->set_flashdata('success', 'Personnel updated successfully.');
    //     } else {
    //         $this->session->set_flashdata('error', 'Failed to update personnel.');
    //     }

    //     redirect('Loan/personnel_loan');
    // }
    public function loans_view()
    {
        $this->load->model('Loan_model');
        $settingsID = $this->session->userdata('settingsID');
        $data['loans'] = $this->Loan_model->get_all_loans($settingsID);

        $this->load->view('loans_view', $data);
    }
    public function add_loan_entry()
    {
        $settingsID   = (int) $this->session->userdata('settingsID');
        $description  = (string) $this->input->post('loan_description');
        $amount       = (float) $this->input->post('loan_amount');
        $monthly      = (float) $this->input->post('monthly_deduction');

        [$ok, $data] = $this->loanservice
            ->createLoan($settingsID, $description, $amount, $monthly);

        if ($ok) {
            $this->session->set_flashdata('success', 'Loan added successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to add loan.');
        }

        redirect('Loan/loans_view');
    }

    public function update_loan_entry()
    {
        $loan_id     = (int) $this->input->post('loan_id');
        $description = (string) $this->input->post('loan_description');
        $amount      = (float) $this->input->post('loan_amount');
        $monthly     = (float) $this->input->post('monthly_deduction');

        [$ok, $data] = $this->loanservice
            ->updateLoan($loan_id, $description, $amount, $monthly);

        if ($ok) {
            $this->session->set_flashdata('success', 'Loan updated successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to update loan.');
        }

        redirect('Loan/loans_view');
    }



    public function delete_loan_entry($loan_id)
    {
        $ok = $this->loanservice->deleteLoan((int) $loan_id);

        if ($ok) {
            $this->session->set_flashdata('success', 'Loan deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete loan.');
        }

        redirect('Loan/loans_view');
    }

    public function get_loans_by_ratetype()
    {
        $rateType = $this->input->post('rateType');
        $loans = $this->Loan_model->get_loans_by_ratetype($rateType);

        if (!empty($loans)) {
            echo json_encode(['status' => 'success', 'loans' => $loans]);
        } else {
            echo json_encode(['status' => 'empty']);
        }
    }

    public function get_loans_all()
    {
        $loans = $this->Loan_model->get_all_loans();
        if (!empty($loans)) {
            echo json_encode(['status' => 'success', 'loans' => $loans]);
        } else {
            echo json_encode(['status' => 'empty']);
        }
    }







    public function save_personnel_loan()
    {
        $settingsID  = (int) $this->session->userdata('settingsID');
        $personnelID = (int) $this->input->post('personnelID');
        $loan_id     = (int) $this->input->post('loan_id');

        $result = $this->loanservice
            ->assignLoanToPersonnel($settingsID, $personnelID, $loan_id);

        if (!$result['ok']) {
            $this->session->set_flashdata('error', $result['message']);
        } else {
            $this->session->set_flashdata('success', $result['message']);
        }

        redirect('Loan/personnel_loan');
    }


    public function assign_personnel_loan()
    {
        $personnelID = $this->input->post('personnelID');
        $description = $this->input->post('loan_description');
        $amount      = $this->input->post('loan_amount');
        $monthly     = $this->input->post('monthly_deduction');
        $settingsID  = $this->session->userdata('settingsID');

        if (!$personnelID || !$description || !$amount || !$monthly) {
            $this->session->set_flashdata('error', 'All fields are required.');
            redirect('Loan/personnel_loan');
            return;
        }

        // Create unique loan_id for personnel loan assignment
        $this->db->select_max('loan_id');
        $max = $this->db->get('personnelloans')->row();
        $new_loan_id = $max ? $max->loan_id + 1 : 1;

        $data = [
            'loan_id'           => $new_loan_id,
            'loan_description'  => $description,
            'amount'            => $amount,
            'monthly_deduction' => $monthly,
            'rateType'          => '',
            'personnelID'       => $personnelID,
            'settingsID'        => $settingsID,
            'date_assigned'     => date('Y-m-d'),
            'status'            => 1,
            'created_at'        => date('Y-m-d H:i:s')
        ];

        if ($this->Loan_model->insert_personnel_loan($data)) {
            $this->session->set_flashdata('success', 'Loan assigned successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to assign loan.');
        }

        redirect('Loan/personnel_loan');
    }

    public function update_personnel_loan()
    {
        $settingsID = $this->session->userdata('settingsID');
        $loan_id = $this->input->post('loan_id');
        $personnelID = $this->input->post('personnelID');
        $loan_amount = $this->input->post('loan_amount');
        $monthly_deduction = $this->input->post('monthly_deduction');
        $loan_description = $this->input->post('loan_description');

        if ($personnelID && $loan_id && $loan_amount && $monthly_deduction && $loan_description) {
            $data = [
                'amount'            => $loan_amount,
                'monthly_deduction' => $monthly_deduction,
                'loan_description'  => $loan_description
            ];
            if ($this->Loan_model->update_personnel_loan($settingsID, $personnelID, $loan_id, $data)) {
                $this->session->set_flashdata('success', 'Loan updated successfully.');
            } else {
                $this->session->set_flashdata('error', 'Database update failed.');
            }
        } else {
            $this->session->set_flashdata('error', 'Missing data. Update failed.');
        }

        redirect('Loan/personnel_loan');
    }
    public function delete_personnel_loan($loan_id, $personnelID)
    {
        $ok = $this->loanservice
            ->deletePersonnelLoan((int) $loan_id, (int) $personnelID);

        if ($ok) {
            $this->session->set_flashdata('success', 'Personnel loan deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete personnel loan.');
        }

        redirect('Loan/personnel_loan');
    }
}
