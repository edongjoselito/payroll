<?php
function getWorkingDaysInMonth($anyDateInMonth) {
    $year = date('Y', strtotime($anyDateInMonth));
    $month = date('m', strtotime($anyDateInMonth));
    
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PMS - Payroll Report</title>
    <?php include('includes/head.php'); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
body {
  font-family: 'Calibri', 'Arial', sans-serif;
  font-size: 13px;
  margin: 20px;
  color: #000;
  background: #fff;
}

.header {
  text-align: center;
  margin-bottom: 20px;
}

.header h2 {
  font-size: 18px;
  margin: 0;
}

.header p {
  margin: 3px 0;
  font-size: 14px;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

th, td {
  border: 1px solid #000;
  padding: 6px 8px;
  text-align: center;
  vertical-align: middle;
}

thead th {
  padding: 4px 6px !important;
  font-size: 14px !important;
  line-height: 1.2;
  vertical-align: middle !important;
  text-align: center;
  font-weight: 600;
}

tbody td {
  padding: 4px 6px;
  font-size: 11.5px;
  line-height: 1.2;
  vertical-align: middle;
  text-align: center;
}

tbody td:nth-child(2) {
  text-align: left;
}

.signature strong {
  font-size: 13px;
}

tbody td:nth-last-child(-n+10) {
  font-size: 10.5px;
}

.signature {
  margin-top: 60px;
  padding-top: 30px;
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  page-break-inside: avoid;
}

.signature div {
  width: 32%;
  text-align: center;
}

.signature p {
  margin: 3px 0;
}

.signature .name-line {
  display: inline-block;
  border-bottom: 1px solid #000;
  min-width: 250px;
  font-size: 15px;
  font-weight: 400;
  padding-bottom: 2px;
}

.signature em {
  font-size: 13px;
  color: #333;
}

th {
  background-color: #d9d9d9;
  font-weight: bold;
}

.absent {
  background-color: #f4cccc;
  color: #000;
}

.scrollable-wrapper {
  overflow-x: auto;
  width: 100%;
}

/* Payslip Modal Styling */
.modal-content {
  background: #fff;
  border-radius: 6px;
  border: 1px solid #ddd;
  color: #000;
  font-family: Arial, sans-serif;
}

.modal-header {
  background: #fff !important;
  color: #000 !important;
  border-bottom: 1px solid #ddd;
}

.modal-body h6 {
  font-weight: bold;
  border-bottom: 1px solid #ccc;
  padding-bottom: 4px;
  margin-bottom: 10px;
}

/* === PRINT FIXES === */
@media print {
  body {
    transform: scale(0.75);
    transform-origin: top left;
    margin: 0;
    font-size: 10px;
    overflow: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  @page {
    size: A4 landscape;
    margin: 0.5cm;
  }

  .btn, .modal, .no-print, .modal-backdrop {
    display: none !important;
  }

  .scrollable-wrapper {
    overflow: visible !important;
  }

  .payroll-table {
    width: 100% !important;
    table-layout: auto !important;
    font-size: 9px;
  }

  .payroll-table th,
  .payroll-table td {
    word-wrap: break-word;
    padding: 2px !important;
    font-size: 9px !important;
    page-break-inside: avoid !important;
    break-inside: avoid;
  }

  .payroll-table th:first-child,
  .payroll-table td:first-child {
    min-width: 25px;
    max-width: 30px;
  }

  thead {
    display: table-header-group;
    font-size: 14px !important;
  }

  table, thead, tbody, tr, td, th {
    page-break-inside: avoid !important;
  }

  .header, .signature {
    page-break-inside: avoid;
  }

  .signature {
    margin-top: 100px;
    padding-top: 50px;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    page-break-inside: avoid;
  }

  .signature div {
    width: 30%;
    text-align: center;
  }

  /* Print only the modal for payslip, half-page layout */
  .modal-content, .modal-content * {
    visibility: visible;
  }

  .modal-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 50%;
    overflow: hidden;
    padding: 20px;
    box-sizing: border-box;
  }
}
</style>

</head>
<body>
  
<div class="header text-left mb-3" style="margin-left: 10px; font-size: 13px; line-height: 1.6;">
    <p><strong>PROJECT</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= $project->projectTitle ?? 'N/A' ?></p>
    <p><strong>LOCATION</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= $project->projectLocation ?? 'Unknown' ?></p>
    <p><strong>PERIOD</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= date('F d, Y', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></p>
    <p><strong>WORKING DAYS</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= getWorkingDaysInMonth($start) ?> days</p>

    <?php if (!empty($_GET['rateType'])): ?>
    <p><strong>SALARY TYPE</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Per <?= htmlspecialchars($_GET['rateType']) ?></p>
    <?php endif; ?>
</div>

<div class="text-right no-print mb-3">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> Print Payroll Summary
    </button>
</div>

<div class="scrollable-wrapper">
<table class="payroll-table">
<thead>
<tr>
    <th rowspan="3">L/N</th>
    <th rowspan="3">NAME</th>
    <th rowspan="3">POSITION</th>
    <th rowspan="3">RATE</th>
    <th rowspan="3">Rate / Hour</th>
    <?php
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    while ($startDate <= $endDate):
        echo '<th colspan="2">' . date('M d', $startDate) . '</th>';
        $startDate = strtotime('+1 day', $startDate);
    endwhile;
    ?>
  <th colspan="4">TOTAL TIME</th>

    <th colspan="2">AMOUNT</th>
    <th rowspan="3">TOTAL</th>
       <th rowspan="3">Cash Advance</th>
    <th rowspan="3">SSS (<?= date('F Y', strtotime($start)) ?>)</th>
    <th rowspan="3">Pag-IBIG (<?= date('F Y', strtotime($start)) ?>)</th>
    <th rowspan="3">PHIC</th>
    <th rowspan="3">Loan</th>
    <th rowspan="3">Other Deduction</th>
<th rowspan="3">Total Deduction</th>
    <th rowspan="3">Take Home Pay</th>
   <?php if (empty($is_summary)): ?>
    <th rowspan="3" colspan="3">Signature</th>
<?php endif; ?>

</tr>
<tr>
    <?php
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    while ($startDate <= $endDate):
        echo '<th colspan="2">' . date('l', $startDate) . '</th>';
        $startDate = strtotime('+1 day', $startDate);
    endwhile;
    ?>
    <th rowspan="2">Reg.</th>
    <th rowspan="2">O.T</th>
    <th rowspan="2">Days</th>
    <th rowspan="2">Work Hours</th>

    <th rowspan="2">Reg.</th>
    <th rowspan="2">O.T</th>
</tr>
<tr>
    <?php
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    while ($startDate <= $endDate):
        echo '<th>Reg.</th><th>O.T</th>';
        $startDate = strtotime('+1 day', $startDate);
    endwhile;
    ?>
</tr>
</thead>


<tbody>
  <?php $totalPayroll = 0; ?>

<?php $ln = 1; foreach ($attendance_data as $row): ?>
  
<tr>
<?php

$regAmount = 0;
$otAmount = 0;
$regTotalMinutes = 0;
$otTotalMinutes = 0;
$totalMinutes = 0;
$totalDays = 0;
$startDate = strtotime($start);
$endDate = strtotime($end);
?>
    <td><?= $ln ?></td>
    <td><?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?></td>
    <td><?= htmlspecialchars($row->position) ?></td>
    
  <td colspan="2">
  <?php if ($row->rateType === 'Day'): ?>
    ₱<?= number_format($row->rateAmount, 2) ?> / day
  <?php elseif ($row->rateType === 'Hour'): ?>
    ₱<?= number_format($row->rateAmount, 2) ?> / hour
  <?php elseif ($row->rateType === 'Month'): ?>
    ₱<?= number_format($row->rateAmount, 2) ?> / month
  <?php endif; ?>
</td>


    <?php
    $loopDate = strtotime($start);
    while ($loopDate <= $endDate):
        $curDate = date('Y-m-d', $loopDate);
        $log = $row->logs[$curDate] ?? null;

        if ($log && $log->attendance_status === 'Present') {
           $parts = explode(':', $log->workDuration);
$h = isset($parts[0]) ? (int)$parts[0] : 0;
$m = isset($parts[1]) ? (int)$parts[1] : 0;
$workMinutes = ($h * 60) + $m;

            $reg = min($workMinutes, 480);
            $ot = max(0, $workMinutes - 480);
$regHours = floor($reg / 60);
$otHours = floor($ot / 60);
if ($row->rateType === 'Hour') {
    $regAmount += $regHours * $row->rateAmount;
    $otAmount += $otHours * ($row->rateAmount * 1.25);
} elseif ($row->rateType === 'Day') {
    $regAmount += ($regHours / 8) * $row->rateAmount;
    $otAmount += $otHours * ($row->rateAmount / 8) * 1.25;
} elseif ($row->rateType === 'Month') {
  $workingDaysInMonth = getWorkingDaysInMonth($start);

    $dailyRate = $row->rateAmount / $workingDaysInMonth;
    $hourlyRate = $dailyRate / 8;

$regAmount += ($regHours / 8) * $dailyRate;

    $otAmount += $otHours * $hourlyRate * 1.25;
    
}


            $regTotalMinutes += $reg;
            $otTotalMinutes += $ot;
            $totalMinutes += $workMinutes;
            $totalDays++;

            echo "<td>" . floor($reg / 60) . "</td><td>" . floor($ot / 60) . "</td>";
        } elseif ($log && $log->attendance_status === 'Absent') {
            echo "<td colspan='2' class='absent'>Absent</td>";
        } else {
            echo "<td colspan='2'>-</td>";
        }

        $loopDate = strtotime('+1 day', $loopDate);
    endwhile;

 $salary = $regAmount + $otAmount;


    $cash_advance = $row->ca_cashadvance ?? 0;
   $other_deduction = $row->other_deduction ?? 0;
    $sss = $row->sss ?? 0;
    $pagibig = $row->pagibig ?? 0;
    $philhealth = $row->philhealth ?? 0;
    $loan = $row->loan ?? 0;

   $total_deduction = $cash_advance + $sss + $pagibig + $philhealth + $loan + $other_deduction;
   $netPay = $salary - $total_deduction;
if ($netPay > 0) {
    $totalPayroll += $netPay; 
}



    $regFormatted = floor($regTotalMinutes / 60);
    $otFormatted = floor($otTotalMinutes / 60);
    ?>
    <td><?= $regFormatted ?></td>
    <td><?= $otFormatted ?></td>
    <td><?= $totalDays ?></td>
    <td><?= $row->total_hours ?? 0 ?></td>

    <td><?= number_format($regAmount, 2) ?></td>
<td><?= number_format($otAmount, 2) ?></td>
<td><?= number_format($regAmount + $otAmount, 2) ?></td>

    <td><?= number_format($cash_advance, 2) ?></td>
    <td><?= number_format($sss, 2) ?></td>
    <td><?= number_format($pagibig, 2) ?></td>

    <td><?= number_format($philhealth, 2) ?></td>
    <td><?= number_format($loan, 2) ?></td>
    <td><?= number_format($row->other_deduction ?? 0, 2) ?>
</td>

    <td><?= number_format($total_deduction, 2) ?></td>
   <td>
  <span class="d-print-block d-none"><?= number_format($netPay, 2) ?></span> <!-- Show on print -->
  <a href="#" class="btn btn-link btn-sm d-print-none" data-toggle="modal" data-target="#payslipModal<?= $ln ?>">
    <?= number_format($netPay, 2) ?>
  </a>
</td>

 <?php if (empty($is_summary)): ?>
    <td colspan="3"></td>
<?php endif; ?>

</tr>

<!-- Payslip Modal -->
<div class="modal fade" id="payslipModal<?= $ln ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="printablePayslip<?= $ln ?>">
      <div class="modal-header" style="background: #fff; border-bottom: 1px solid #ddd;">

        <h5 class="modal-title">Payslip - <?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?></h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Employee:</strong> <?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?></p>
            <p><strong>Position:</strong> <?= htmlspecialchars($row->position) ?></p>
          <?php if ($row->rateType === 'Month'): ?>
    <?php
        $workingDaysInMonth = getWorkingDaysInMonth($start);
        $dailyRate = $row->rateAmount / $workingDaysInMonth;
        $hourlyRate = $dailyRate / 8;
        $otRate = $hourlyRate * 1.25;
    ?>
    <p><strong>Rate:</strong> ₱<?= number_format($row->rateAmount, 2) ?> / Month</p>
    <p><strong>Daily Rate:</strong> ₱<?= number_format($dailyRate, 2) ?></p>
    <p><strong>Hourly Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p>
    <p><strong>Overtime Rate (125%):</strong> ₱<?= number_format($otRate, 2) ?></p>
<?php else: ?>
    <p><strong>Rate:</strong> ₱<?= number_format($row->rateAmount, 2) ?> / <?= $row->rateType ?></p>
<?php endif; ?>

          </div>
          <div class="col-md-6 text-right">
            <p><strong>Period:</strong><br><?= date('F d', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></p>
            <p><strong>Printed:</strong> <?= date('F d, Y') ?></p>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-md-6">
            <h6>Earnings</h6>
            <ul class="list-unstyled">
              <li>Regular Time: <?= $regFormatted ?> hrs</li>
              <li>Overtime: <?= $otFormatted ?> hrs</li>
              <li>Total Days: <?= $totalDays ?></li>
              <li><strong>Gross Salary: <?= number_format($salary, 2) ?></strong></li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>Deductions</h6>
            <ul class="list-unstyled">
              <li>Cash Advance: <?= number_format($cash_advance, 2) ?></li>
              <li>SSS: <?= number_format($sss, 2) ?></li>
              <li>PHIC: <?= number_format($philhealth, 2) ?></li>
              <li>Pag-IBIG: <?= number_format($pagibig, 2) ?></li>
              <li>Loan: <?= number_format($loan, 2) ?></li>
              <li>Other Deduction: <?= number_format($other_deduction, 2) ?></li>

              <li><strong>Total Deduction: <?= number_format($total_deduction, 2) ?></strong></li>
            </ul>
          </div>
        </div>
        <hr>
        <div class="text-right">
          <h5><strong>Net Pay: <?= number_format($netPay, 2) ?></strong></h5>
          <button onclick="printPayslip('printablePayslip<?= $ln ?>')" class="btn btn-sm btn-secondary mt-2"><i class="fas fa-print"></i> Print</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $ln++; endforeach; ?>
</tbody>

</table>
<div style="text-align: right; font-weight: bold; font-size: 15px; margin-top: 10px;">
    TOTAL PAYROLL = ₱ <?= number_format($totalPayroll ?? 0, 2) ?>
</div>




</div>
<br>
<?php if (!empty($signatories) && $show_signatories): ?>
<div class="row mt-5 signature">
  <!-- Prepared by -->
  <div class="col text-center">
    <p><strong>Prepared by:</strong></p>
    <br><br>
    <p class="name-line"><?= $signatories->prepared_by_name ?? '' ?></p>
    <p><em><?= $signatories->prepared_by_position ?? '' ?></em></p>
  </div>

  <!-- Checked by -->
  <div class="col text-center">
    <p><strong>Checked by:</strong></p>
    <br><br>
    <p class="name-line"><?= $signatories->checked_by_name ?? '' ?></p>
    <p><em><?= $signatories->checked_by_position ?? '' ?></em></p>
  </div>

  <!-- 3rd Signatory -->
  <div class="col text-center">
    <br><br><br><br>
    <p class="name-line"><?= $signatories->additional_name ?? '' ?></p>
    <p><em><?= $signatories->additional_position ?? '' ?></em></p>
  </div>
</div>
<?php endif; ?>


<!--
/*
|--------------------------------------------------------------------------
| PAYROLL COMPUTATION METHOD
|--------------------------------------------------------------------------
| This system supports: Hourly, Daily, and Monthly rates.
| Salary is based strictly on actual hours or days worked.
|
| 1. REGULAR TIME PAY:
| -------------------------------------------------------------------------
| • Hourly Rate (e.g. ₱10/hour):
|     → Reg Pay = Hours worked × ₱10
|
| • Daily Rate (e.g. ₱400/day):
|     → Reg Pay = (Hours worked ÷ 8) × ₱400
|
| • Monthly Rate (e.g. ₱10,000/month):
|     → Total days in month are counted (including Sundays)
|     → Daily Rate = ₱10,000 ÷ total days in month
|     → Hourly Rate = Daily Rate ÷ 8
|     → Reg Pay = Hours worked × Hourly Rate
|
| 2. OVERTIME PAY:
| -------------------------------------------------------------------------
| • Overtime is paid at 125% of the hourly rate
|     → OT Pay = OT hours × Hourly Rate × 1.25
|
| 3. GROSS PAY:
| -------------------------------------------------------------------------
| • Gross Pay = Regular Pay + OT Pay
|
| 4. DEDUCTIONS:
| -------------------------------------------------------------------------
| • Includes SSS, PHIC, Pag-IBIG, Loans, etc.
| • Total Deductions = sum of all applicable deductions
|
| 5. NET PAY:
| -------------------------------------------------------------------------
| • Net Pay = Gross Pay − Total Deductions
|
| 6. WORKING DAYS:
| -------------------------------------------------------------------------
| • Uses getWorkingDaysInMonth($startDate)
| • Counts all days in the month (Sundays included)
*/
-->





<script>
function printPayslip(elementId) {
    var printContents = document.getElementById(elementId).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // Restore modal functionality
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
