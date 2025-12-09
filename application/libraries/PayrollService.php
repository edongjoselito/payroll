<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PayrollService
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();

        // Existing models in your project
        $this->CI->load->model('PayrollModel');          // or Payroll_model if that's the actual name
        $this->CI->load->model('Loan_model');
        $this->CI->load->model('OtherDeduction_model');
    }

    /**
     * Core SOA-style payroll generator.
     *
     * @param string $dateFrom   Inclusive start date (Y-m-d)
     * @param string $dateTo     Inclusive end date (Y-m-d)
     * @param string $cutoff     e.g. "2025-12A"
     * @param int    $settingsID Active settings / project context
     *
     * @return array  Array of enriched payroll rows (objects)
     */
    public function generatePayroll(string $dateFrom, string $dateTo, string $cutoff, int $settingsID)
    {
        // 1) Base payroll rows (hours, gross pay, etc.)
        $payroll_data = $this->CI->PayrollModel->generate_payroll($dateFrom, $dateTo, $cutoff);

        // 2) Other deductions (tools, materials, etc.)
        $other_deductions = $this->CI->OtherDeduction_model
            ->get_deductions_by_date_range($dateFrom, $dateTo, $settingsID);

        $groupedDeductions = [];
        foreach ($other_deductions as $deduction) {
            $pid = (int) $deduction->personnelID;
            if (!isset($groupedDeductions[$pid])) {
                $groupedDeductions[$pid] = 0.0;
            }
            $groupedDeductions[$pid] += (float) $deduction->amount;
        }

        // 3) Cash advances (summed directly from cashadvance table by date range)
        $cashAdvanceMap   = [];
        $cash_advance_qry = $this->CI->db->select('personnelID, SUM(amount) AS total_amount')
            ->from('cashadvance')
            ->where('settingsID', $settingsID)
            ->where('date >=', $dateFrom)
            ->where('date <=', $dateTo)
            ->group_by('personnelID')
            ->get()
            ->result();

        foreach ($cash_advance_qry as $ca) {
            $cashAdvanceMap[(int) $ca->personnelID] = (float) $ca->total_amount;
        }

        // 4) Government deductions (SSS, Pag-IBIG, PhilHealth) for the range
        $govt_qry = $this->CI->db->select('personnelID, description, amount')
            ->from('government_deductions')
            ->where('settingsID', $settingsID)
            ->where('(
                (deduct_from IS NULL OR deduct_from <= ' . $this->CI->db->escape($dateTo) . ') AND
                (deduct_to   IS NULL OR deduct_to   >= ' . $this->CI->db->escape($dateFrom) . ')
            )', null, false)
            ->get()
            ->result();

        $govtMap = [];
        foreach ($govt_qry as $deduct) {
            $pid = (int) $deduct->personnelID;
            if (!isset($govtMap[$pid])) {
                $govtMap[$pid] = [];
            }
            $govtMap[$pid][$deduct->description] = (float) $deduct->amount;
        }

        // 5) Enrich rows with deductions / benefits
        foreach ($payroll_data as &$row) {
            $pid = (int) $row->personnelID;

            // Other Deduction (tools, materials, etc.)
            $row->other_deduction = $groupedDeductions[$pid] ?? 0.0;

            // Loan deduction (from personnelloans via Loan_model::get_personnel_loan)
            $loan = $this->CI->Loan_model->get_personnel_loan($pid, $settingsID);
            $row->loan_deduction = $loan->monthly_deduction ?? 0.0;

            // Cash Advance (summed by date range)
            $row->cash_advance = $cashAdvanceMap[$pid] ?? 0.0;

            // Government deductions (if description labels match SSS/Pag-IBIG/PhilHealth)
            $row->govt_sss     = $govtMap[$pid]['SSS']        ?? 0.0;
            $row->govt_pagibig = $govtMap[$pid]['Pag-IBIG']   ?? 0.0;
            $row->govt_phic    = $govtMap[$pid]['PhilHealth'] ?? 0.0;

            // Total Gov’t deduction
            $row->govt_total_deduction =
                $row->govt_sss + $row->govt_pagibig + $row->govt_phic;

            // We only compute totals and return them to caller (SOA-style).
        }
        unset($row);

        return $payroll_data;
    }
}
