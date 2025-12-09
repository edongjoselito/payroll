<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LoanService
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Loan_model');
    }

    /**
     * Create a loan entry (loans table).
     */
    public function createLoan(int $settingsID, string $description, float $amount, float $monthly)
    {
        $data = [
            'settingsID'        => $settingsID,
            'loan_description'  => $description,
            'loan_amount'       => $amount,
            'monthly_deduction' => $monthly,
            'status'            => 1,
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        $ok = $this->CI->Loan_model->insert_loan($data);
        return [$ok, $data];
    }

    /**
     * Update an existing loan entry.
     */
    public function updateLoan(int $loan_id, string $description, float $amount, float $monthly)
    {
        $data = [
            'loan_description'  => $description,
            'loan_amount'       => $amount,
            'monthly_deduction' => $monthly,
        ];

        $ok = $this->CI->Loan_model->update_loan($loan_id, $data);
        return [$ok, $data];
    }

    /**
     * Delete loan entry.
     */
    public function deleteLoan(int $loan_id): bool
    {
        return $this->CI->Loan_model->delete_loan($loan_id);
    }

    /**
     * Assign a loan to personnel (personnelloans table logic).
     * This wraps your existing save_personnel_loan logic at service level.
     */
    public function assignLoanToPersonnel(int $settingsID, int $personnelID, int $loan_id)
    {
        // Check if already exists
        if ($this->CI->Loan_model->check_existing_personnel_loan($personnelID, $loan_id)) {
            return [
                'ok'      => false,
                'message' => 'This personnel already has this loan assigned.',
            ];
        }

        $data = [
            'settingsID'        => $settingsID,
            'personnelID'       => $personnelID,
            'loan_id'           => $loan_id,
            'status'            => 1,
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        // In your controller you eventually call assign_personnel_loan()
        // after building a data array with description/amount/monthly.
        $ok = $this->CI->Loan_model->assign_personnel_loan($data);

        return [
            'ok'      => $ok,
            'message' => $ok ? 'Loan assigned successfully.' : 'Failed to assign loan.',
            'data'    => $data,
        ];
    }

    /**
     * Delete personnel loan assignment.
     */
    public function deletePersonnelLoan(int $loan_id, int $personnelID): bool
    {
        $this->CI->db->where('loan_id', $loan_id);
        $this->CI->db->where('personnelID', $personnelID);
        return (bool) $this->CI->db->delete('personnelloans');
    }
}
