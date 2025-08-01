<?php
function getWorkingDaysInMonth($anyDateInMonth) {
    $year = date('Y', strtotime($anyDateInMonth));
    $month = date('m', strtotime($anyDateInMonth));
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

function displayAmount($value) {
    return ($value == 0 || $value === 0.00) ? '––' : number_format($value, 2);
}

$hasRegularHoliday = false;
$hasSpecialHoliday = false;

foreach ($attendance_data as $row) {
    foreach ($row->reg_hours_per_day as $day) {
        if (isset($day['status'])) {
            $status = strtolower($day['status']);

            if (!$hasRegularHoliday && (strpos($status, 'regular') !== false || strpos($status, 'legal') !== false)) {
                $hasRegularHoliday = true;
            }

            if (!$hasSpecialHoliday && strpos($status, 'special') !== false) {
                $hasSpecialHoliday = true;
            }

            if ($hasRegularHoliday && $hasSpecialHoliday) {
                break 2; // stop both loops
            }
        }
    }
}


$amountColspan = 2;
if ($hasRegularHoliday) $amountColspan++;
if ($hasSpecialHoliday) $amountColspan++;
function computePayroll($row, $start, $end) {
    $regTotalMinutes = 0;
    $otTotalMinutes = 0;
    $totalDays = 0;
    $regAmount = 0;
    $otAmount = 0;
    $amountRegularHoliday = 0;
    $amountSpecialHoliday = 0;

    $loopDate = strtotime($start);
    $endDate = strtotime($end);

    while ($loopDate <= $endDate) {
        $curDate = date('Y-m-d', $loopDate);
        $raw = $row->reg_hours_per_day[$curDate] ?? null;

        $status = strtolower(preg_replace('/\s+/', '', trim($raw['status'] ?? '')));
        $regHours = floatval($raw['hours'] ?? 0);
        $otHours = floatval($raw['overtime_hours'] ?? 0);
        $holidayHours = floatval($raw['holiday_hours'] ?? 0);

        if ($row->rateType === 'Hour') {
            $base = $row->rateAmount;
        } elseif ($row->rateType === 'Day') {
            $base = $row->rateAmount / 8;
        } elseif ($row->rateType === 'Month') {
            $base = ($row->rateAmount / 30) / 8;
        } elseif ($row->rateType === 'Bi-Month') {
            $base = ($row->rateAmount / 15) / 8;
        } else {
            $base = 0;
        }

        if (preg_match('/holiday|regularho|legal|special/i', $status) || $holidayHours > 0) {
            if (strpos($status, 'regularho') !== false || strpos($status, 'legal') !== false) {
                $amountRegularHoliday += 8 * $base;
                $regAmount += $holidayHours * $base;
                $regTotalMinutes += $holidayHours * 60;
                $totalDays += $holidayHours / 8;
            } else {
                $regAmount += $holidayHours * $base;
                $amountSpecialHoliday += $holidayHours * $base * 0.30;
                $regTotalMinutes += $holidayHours * 60;
                $totalDays += $holidayHours / 8;
            }

            $otAmount += $otHours * $base;
            $otTotalMinutes += $otHours * 60;
        } else {
            $regAmount += $regHours * $base;
            $otAmount += $otHours * $base;

            $regTotalMinutes += $regHours * 60;
            $otTotalMinutes += $otHours * 60;

            $totalDays += $regHours / 8;
        }

        $loopDate = strtotime('+1 day', $loopDate);
    }

    $salary = $regAmount + $otAmount + $amountRegularHoliday + $amountSpecialHoliday;
    $cash_advance = $row->ca_cashadvance ?? 0;
    $sss = $row->gov_sss ?? 0;
    $pagibig = $row->gov_pagibig ?? 0;
    $philhealth = $row->gov_philhealth ?? 0;
    $loan = $row->loan ?? 0;
    $other_deduction = $row->other_deduction ?? 0;
    $total_deduction = $cash_advance + $sss + $pagibig + $philhealth + $loan + $other_deduction;
    $netPay = $salary - $total_deduction;

    return [
        'regTotalMinutes' => $regTotalMinutes,
        'otTotalMinutes' => $otTotalMinutes,
        'totalDays' => $totalDays,
        'regAmount' => $regAmount,
        'otAmount' => $otAmount,
        'amountRegularHoliday' => $amountRegularHoliday,
        'amountSpecialHoliday' => $amountSpecialHoliday,
        'salary' => $salary,
        'cash_advance' => $cash_advance,
        'sss' => $sss,
        'pagibig' => $pagibig,
        'philhealth' => $philhealth,
        'loan' => $loan,
        'other_deduction' => $other_deduction,
        'total_deduction' => $total_deduction,
        'netPay' => $netPay
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   <title>PMS - Monthly/Bi-Month Payroll Report</title>

    <?php include('includes/head.php'); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
 <style>
body, html {
   font-family: 'Segoe UI', 'Calibri', 'Arial', sans-serif;
  font-size: 14px;
  margin: 0;
  padding: 0;
  color: #000;
  background: #fff;
  height: 100%;
}

.print-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  justify-content: space-between;
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
  font-size: 14px;
}

th, td {
  border: 1px solid #000;
  padding: 8px 10px;
  text-align: center;
  vertical-align: middle;
}

thead th {
  padding: 4px 6px !important;
  font-size: 15px !important;
  line-height: 1.2;
  vertical-align: middle !important;
  text-align: center;
  font-weight: 600;
}

.badge-warning {
  background-color: #ffc107;
  color: #000;
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 3px;
}

tbody td {
  padding: 4px 6px;
  font-size: 13px;
  line-height: 1.2;
  vertical-align: middle;
  text-align: center;
}

tbody td:nth-child(2) {
  text-align: left;
}

td.unused-holiday {
  background-color: #f9f9f9;
  color: #aaa;
  font-style: italic;
}

.holiday-cell {
  background-color: #ffe5e5 !important;
  color: red !important;
  font-weight: bold !important;
  text-align: center !important;
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
  break-inside: avoid;
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
  font-size: 14px;
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
  flex-grow: 1;
}

/* Header info box */
.header-box {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  border-radius: 10px;
  background-color: #fefefe;
  padding: 12px 20px;
  margin: 10px 0 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.header-box .box-content {
  flex: 1 1 auto;
  min-width: 300px;
}

.header-box .info-row {
  display: flex;
  align-items: center;
  margin: 3px 0;
  font-size: 15px;
}

.header-box .info-row i {
  font-size: 13px;
  color: #666;
  width: 18px;
  margin-right: 8px;
  text-align: center;
}

.header-box strong {
  display: inline-block;
  min-width: 125px;
  font-weight: 600;
}

.header-box span {
  flex: 1;
  text-align: left;
}

.print-button {
  margin-top: 4px;
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
}

.modal-body h6 {
  font-weight: bold;
  border-bottom: 1px solid #ccc;
  padding-bottom: 4px;
  margin-bottom: 10px;
}
#print-all-payslips-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
  padding: 20px;
  margin: 0;
  width: 100%;
  box-sizing: border-box;
}



.print-card p {
  margin: 4px 0;
}

/* === PRINT FIXES === */
@media print {
  html, body {
    height: 100%;
    margin: 0 !important;
    padding: 0 !important;
    font-size: 10px !important; 
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    overflow: visible !important;
    zoom: 75%;
  }
 #print-all-payslips-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  justify-content: center;
  padding: 20px;
}


.print-card {
  width: 390px;    
  height: 550px;   
  padding: 18px 20px;
  font-size: 13.5px;
  line-height: 1.4;
  background-color: #f8f9fa;
  border: 1.5px solid #444;
  border-radius: 6px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  page-break-inside: avoid;
  break-inside: avoid;
}


.print-card p {
  margin: 4px 0;
  font-size: 12.5px;
}

  .page-break {
    page-break-after: always;
  }

  .no-print,
  .no-print *, 
  .modal, 
  .modal-backdrop {
    display: none !important;
  }

  .page-break {
    page-break-after: always;
    break-after: page;
  }

  
  .no-print-payslip { display: none !important; } 
  .no-print-payroll { display: none !important; }

  @page {
    size: A4 landscape;
    margin: 1cm;
  }

  hr {
    border: none !important;
    height: 1px !important;
    color: #ccc !important;
    background-color: #ccc !important;
    margin: 4px 0 !important;
  }

  .payroll-table {
    width: 100% !important;
    table-layout: fixed !important;
    font-size: 9px !important;
    word-break: break-word !important;
    border-collapse: collapse !important;
  }

  .payroll-table th,
  .payroll-table td {
    padding: 3px 4px !important;
    font-size: 9px !important;
    word-break: break-word;
    page-break-inside: avoid !important;
    break-inside: avoid;
  }

  .payroll-table th:first-child,
  .payroll-table td:first-child {
    min-width: 25px;
    max-width: 30px;
  }



  .btn, .modal, .no-print, .modal-backdrop {
    display: none !important;
  }

  .print-container {
    display: flex !important;
    flex-direction: column !important;
    min-height: 100vh !important;
    justify-content: space-between !important;
  }

  .scrollable-wrapper {
    overflow: visible !important;
    flex-grow: 1 !important;
  }

  table, thead, tbody, tr, td, th {
    page-break-inside: avoid !important;
  }

  thead {
    display: table-header-group;
  }

  .signature {
    margin-top: 100px !important;
    padding-top: 40px;
    break-inside: avoid !important;
    page-break-inside: avoid !important;
  }

  .signature div {
    width: 30%;
    text-align: center;
  }

  .modal-content, .modal-content * {
    visibility: visible !important;
  }

  .modal-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 50%;
    overflow: hidden;
    padding: 20px;
  }

  div, footer {
    border: none !important;
  }
}

</style>


</head>
<body>
<div class="print-container">

<div class="header-box">
  <div class="box-content">
   
    <div class="info-row">
      <i class="far fa-calendar-alt"></i>
      <strong>PERIOD</strong><span>: <?= date('F d, Y', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></span>
    </div>
    <div class="info-row">
      <i class="fas fa-clock"></i>
      <strong>WORKING DAYS</strong><span>: <?= getWorkingDaysInMonth($start) ?> days</span>
    </div>
    <?php if (!empty($_GET['rateType'])): ?>
      <div class="info-row">
        <i class="fas fa-wallet"></i>
        <strong>SALARY TYPE</strong><span>: Per <?= htmlspecialchars($_GET['rateType']) ?></span>
      </div>
    <?php endif; ?>
  </div>

  <div class="print-button no-print">
    <button onclick="window.print()" class="btn btn-primary btn-sm">
      <i class="fas fa-print"></i> Print Payroll
    </button>
    <div class="print-button no-print mt-2">
  <button onclick="printAllPayslips()" class="btn btn-success btn-sm">
    <i class="fas fa-file-invoice-dollar"></i> Print Payslips
  </button>
</div>

  </div>
  
</div>


<div class="scrollable-wrapper">
  <?php
$hasRegularHoliday = false;
$hasSpecialHoliday = false;

// Scan attendance to detect holiday presence
foreach ($attendance_data as $row) {
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    while ($startDate <= $endDate) {
        $curDate = date('Y-m-d', $startDate);
        $raw = $row->reg_hours_per_day[$curDate] ?? null;

        if (is_array($raw)) {
            $status = strtolower(preg_replace('/\s+/', '', trim($raw['status'] ?? '')));
            $holidayHours = floatval($raw['holiday_hours'] ?? 0);

            if (strpos($status, 'regularho') !== false || strpos($status, 'legal') !== false) {
                $hasRegularHoliday = true;
            }

            if (strpos($status, 'special') !== false) {
                $hasSpecialHoliday = true;
            }

            if ($hasRegularHoliday && $hasSpecialHoliday) break 2;
        }

        $startDate = strtotime('+1 day', $startDate);
    }
}
?>
<?php
$showCA = $showSSS = $showPHIC = $showLoan = $showOther = $showPagibig = false;

foreach ($attendance_data as $row) {
    if (!empty($row->ca_cashadvance)) $showCA = true;
    if (!empty($row->gov_sss)) $showSSS = true;
    if (!empty($row->gov_philhealth)) $showPHIC = true;
    if (!empty($row->loan)) $showLoan = true;
    if (!empty($row->other_deduction)) $showOther = true;
    if (!empty($row->gov_pagibig)) $showPagibig = true;
}

// Only show Total Deduction if any deduction type is shown
$showTotalDeduction = $showCA || $showSSS || $showPHIC || $showLoan || $showOther || $showPagibig;
?>

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
    <th colspan="3">TOTAL TIME</th>

  <th colspan="<?= $amountColspan ?>" class="text-center">AMOUNT</th>
    <th rowspan="3">Gross</th>
   <?php if ($showCA): ?><th rowspan="3">Cash Advance</th><?php endif; ?>
<?php if ($showSSS): ?><th rowspan="3">SSS (Gov’t)</th><?php endif; ?>
<?php if ($showPagibig): ?><th rowspan="3">Pag-IBIG (Gov’t)</th><?php endif; ?>
<?php if ($showPHIC): ?><th rowspan="3">PHIC (Gov’t)</th><?php endif; ?>
<?php if ($showLoan): ?><th rowspan="3">Loan</th><?php endif; ?>
<?php if ($showOther): ?><th rowspan="3">Other Deduction</th><?php endif; ?>
<?php if ($showTotalDeduction): ?><th rowspan="3">Total Deduction</th><?php endif; ?>


    <th rowspan="3">Take Home</th>
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

    <th rowspan="2">Reg.</th>
    <th rowspan="2">O.T</th>
<?php if ($hasRegularHoliday): ?>
    <th>Regular Holiday</th>
<?php endif; ?>
<?php if ($hasSpecialHoliday): ?>
    <th>Special Holiday</th>
<?php endif; ?>




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
  <!-- <?php
$totalPayroll = 0;
$hasRegularHoliday = false;
$hasSpecialHoliday = false;

// Scan attendance to detect holiday presence
foreach ($attendance_data as $row) {
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    while ($startDate <= $endDate) {
        $curDate = date('Y-m-d', $startDate);
        $raw = $row->reg_hours_per_day[$curDate] ?? null;

        if (is_array($raw)) {
            $status = strtolower(preg_replace('/\s+/', '', trim($raw['status'] ?? '')));
            $holidayHours = floatval($raw['holiday_hours'] ?? 0);

            if (strpos($status, 'regularho') !== false || strpos($status, 'legal') !== false) {
                $hasRegularHoliday = true;
            }

            if (strpos($status, 'special') !== false) {
                $hasSpecialHoliday = true;
            }

            if ($hasRegularHoliday && $hasSpecialHoliday) break 2;
        }

        $startDate = strtotime('+1 day', $startDate);
    }
}
?> -->

<?php
// Initialize total accumulators
$totalGross = 0;
$totalCA = 0;
$totalSSS = 0;
$totalPagibig = 0;
$totalPHIC = 0;
$totalLoan = 0;
$totalOther = 0;
$totalDeduction = 0;
$totalNet = 0;

// 👉 Calculate dynamic colspan for the TOTAL row
$dateColumnCount = 0;
$loopDate = strtotime($start);
while ($loopDate <= strtotime($end)) {
    $dateColumnCount += 2; // 2 columns per day (Reg. & OT)
    $loopDate = strtotime('+1 day', $loopDate);
}

$fixedColsBeforeDays = 5; // L/N, Name, Position, Rate, Rate/Hour
$totalTimeCols = 3;       // Reg, OT, Days
$amountCols = 2;          // Reg, OT in Amount section

if ($hasRegularHoliday) $amountCols++;
if ($hasSpecialHoliday) $amountCols++;

$totalPrefixCols = $fixedColsBeforeDays + $dateColumnCount + $totalTimeCols + $amountCols;
?>

<?php $ln = 1; foreach ($attendance_data as $row): ?>
<?php if (!in_array($row->rateType, ['Month', 'Bi-Month'])) continue; ?>


<tr>
<?php

$regAmount = 0;
$otAmount = 0;

$regTotalMinutes = 0;
$otTotalMinutes = 0;
$totalMinutes = 0;
$totalDays = 0;
$amountRegularHoliday = 0;
$amountSpecialHoliday = 0;

$startDate = strtotime($start);
$endDate = strtotime($end);
?>
    <td><?= $ln++ ?></td>
   <td><?= htmlspecialchars($row->last_name . ', ' . $row->first_name) ?></td>

    <td><?= htmlspecialchars($row->position) ?></td>
    
    <td colspan="2">
    <?php if ($row->rateType === 'Day'): ?>
        ₱<?= number_format($row->rateAmount, 2) ?> / day
    <?php elseif ($row->rateType === 'Hour'): ?>
    <?php elseif ($row->rateType === 'Month'): ?>
        ₱<?= number_format($row->rateAmount, 2) ?> / month
        <?php elseif ($row->rateType === 'Bi-Month'): ?>
        ₱<?= number_format($row->rateAmount, 2) ?> / bi-month
    <?php endif; ?>
    </td>

<?php
$loopDate = strtotime($start);
while ($loopDate <= $endDate):
    $curDate = date('Y-m-d', $loopDate);
    $raw = $row->reg_hours_per_day[$curDate] ?? '-';

    $regHours = 0;
    $otHours = 0;
    $holidayHours = 0;
    $showHoliday = false;
    $holidayLabel = '';
    $status = '';
    $base = 0;

    if (is_array($raw)) {
        $status = strtolower(preg_replace('/\s+/', '', trim($raw['status'] ?? '')));
        $regHours = floatval($raw['hours'] ?? 0);
        $otHours = floatval($raw['overtime_hours'] ?? 0);
        $holidayHours = floatval($raw['holiday_hours'] ?? 0);

       // Rate per hour
if ($row->rateType === 'Hour') {
    $base = $row->rateAmount;
} elseif ($row->rateType === 'Day') {
    $base = $row->rateAmount / 8;
} elseif ($row->rateType === 'Month') {
    $base = ($row->rateAmount / 30) / 8;
} elseif ($row->rateType === 'Bi-Month') {
    $base = ($row->rateAmount / 15) / 8;
} else {
    $base = 0;
}


        // Check if it's a holiday
        if (preg_match('/holiday|regularho|legal|special/i', $status) || $holidayHours > 0) {
            $showHoliday = true;

            if ($holidayHours <= 0 && $regHours > 0) {
                $holidayHours = $regHours;
                $regHours = 0;
            }

            $holidayLabel = ucfirst($status ?: 'Holiday');
        }

        if ($showHoliday) {
            if (strpos($status, 'regularho') !== false || strpos($status, 'legal') !== false) {
                // ✅ REGULAR HOLIDAY LOGIC
                $amountRegularHoliday += 8 * $base;

                if ($holidayHours > 0) {
                    $regAmount += $holidayHours * $base;
                    $regTotalMinutes += $holidayHours * 60;
                    $totalMinutes += $holidayHours * 60;
                    $totalDays += $holidayHours / 8;

                }

                if ($otHours > 0) {
                    $otAmount += $otHours * $base;
                    $otTotalMinutes += $otHours * 60;
                    $totalMinutes += $otHours * 60;
                }

                $holidayLabel = 'R.Holiday';

            } else {
                // ✅ SPECIAL HOLIDAY LOGIC
                if ($holidayHours > 0) {
                    $regAmount += $holidayHours * $base;
                    $amountSpecialHoliday += $holidayHours * $base * 0.30;
                    $regTotalMinutes += $holidayHours * 60;
                    $totalMinutes += $holidayHours * 60;
                    $totalDays += $holidayHours / 8;

                } else {
                    $amountSpecialHoliday += 8 * $base * 0.30;
                }

                if ($otHours > 0) {
                    $otAmount += $otHours * $base;
                    $otTotalMinutes += $otHours * 60;
                    $totalMinutes += $otHours * 60;
                }

                $holidayLabel = 'S.Holiday';
            }

            // ✅ Output holiday cell
            echo "<td colspan='2' style='background-color: #ffe5e5; color: red; font-weight: bold; text-align: center;'>";
            echo "{$holidayLabel}<br>(";
            $parts = [];
            if ($holidayHours > 0) $parts[] = number_format($holidayHours, 2);
            if ($regHours > 0) $parts[] = displayAmount($regHours) . " R";
            if ($otHours > 0) $parts[] = displayAmount($otHours) . " OT";
            echo implode(" + ", $parts);
            echo ")</td>";

        } else {
            // ✅ Normal workday logic
            if ($regHours <= 0 && $otHours > 0 && in_array($status, ['absent', 'absentee'])) {
                echo "<td class='text-danger text-center font-weight-bold'>A</td>";
                echo "<td>" . number_format($otHours, 2) . "</td>";
            } elseif ($regHours <= 0 && $otHours <= 0 && in_array($status, ['absent', 'absentee'])) {
                echo "<td colspan='2' class='absent text-center' style='background-color: #f8d7da; color: red;'>Absent</td>";
            } else {
                echo "<td>" . displayAmount($regHours) . "</td>";
                echo "<td>" . displayAmount($otHours) . "</td>";
            }

            $regAmount += $regHours * $base;
            $otAmount += $otHours * $base;

            $regTotalMinutes += $regHours * 60;
            $otTotalMinutes += $otHours * 60;
            $totalMinutes += ($regHours + $otHours) * 60;
            $totalDays += $regHours / 8;

        }

    } elseif (strtolower(trim($raw)) === 'day off') {
        echo "<td colspan='2' class='text-info font-bold text-center'>Day Off</td>";

    } elseif (is_numeric($raw)) {
        $decimalHours = floatval($raw);
        $reg = min($decimalHours * 60, 480);
        $ot = max(0, ($decimalHours * 60) - 480);
        $regHours = $reg / 60;
        $otHours = $ot / 60;

        // Rate per hour
if ($row->rateType === 'Hour') {
    $base = $row->rateAmount;
} elseif ($row->rateType === 'Day') {
    $base = $row->rateAmount / 8;
} elseif ($row->rateType === 'Month') {
    $base = ($row->rateAmount / 30) / 8;
} elseif ($row->rateType === 'Bi-Month') {
    $base = ($row->rateAmount / 15) / 8;
} else {
    $base = 0;
}


        $regAmount += $regHours * $base;
        $otAmount  += $otHours * $base;

        $regTotalMinutes += $reg;
        $otTotalMinutes += $ot;
        $totalMinutes += ($reg + $ot);
        if ($decimalHours > 0) $totalDays += 1;

        echo "<td>" . displayAmount($regHours) . "</td>";
        echo "<td>" . displayAmount($otHours) . "</td>";

    } else {
        echo "<td colspan='2' class='absent text-center' style='background-color: #f8d7da; color: red;'>Absent</td>";
    }

    $loopDate = strtotime('+1 day', $loopDate);
endwhile;



// Totals
$salary = $regAmount + $otAmount + $amountRegularHoliday + $amountSpecialHoliday;
$cash_advance = $row->ca_cashadvance ?? 0;
$other_deduction = $row->other_deduction ?? 0;
$sss = $row->gov_sss ?? 0;
$pagibig = $row->gov_pagibig ?? 0;
$philhealth = $row->gov_philhealth ?? 0;
$loan = $row->loan ?? 0;
$total_deduction = $cash_advance + $sss + $pagibig + $philhealth + $loan + $other_deduction;
$netPay = $salary - $total_deduction;
if ($netPay > 0) {
    $totalPayroll += $netPay;
}

// Ensure integers to avoid float-string conversion warning
$regTotalMinutes = intval($regTotalMinutes);
$otTotalMinutes = intval($otTotalMinutes);

$totalMinutesFormatted = $regTotalMinutes + $otTotalMinutes;
$formattedH = floor($totalMinutesFormatted / 60);
$formattedM = $totalMinutesFormatted % 60;
$customDecimal = $formattedH . '.' . str_pad($formattedM, 2, '0', STR_PAD_LEFT);

// Also reformat these as strings to avoid PHP 8.1 float warnings
$regFormatted = floor($regTotalMinutes / 60) . '.' . str_pad($regTotalMinutes % 60, 2, '0', STR_PAD_LEFT);
$otFormatted = floor($otTotalMinutes / 60) . '.' . str_pad($otTotalMinutes % 60, 2, '0', STR_PAD_LEFT);


// FINAL AMOUNT COMPUTATION BEFORE DISPLAY
$salary = $regAmount + $otAmount + $amountRegularHoliday + $amountSpecialHoliday;
$total_deduction = $cash_advance + $sss + $pagibig + $philhealth + $loan + $other_deduction;
$netPay = $salary - $total_deduction;
$totalGross += $salary;
$totalCA += $cash_advance;
$totalSSS += $sss;
$totalPagibig += $pagibig;
$totalPHIC += $philhealth;
$totalLoan += $loan;
$totalOther += $other_deduction;
$totalDeduction += $total_deduction;
$totalNet += $netPay;


?>

<td><?= displayAmount($regTotalMinutes / 60) ?></td>
<td><?= number_format($otTotalMinutes / 60, 2) ?></td>  
<td><?= number_format($totalDays, 2) ?></td> 

<td><?= displayAmount($regAmount) ?></td>
<td><?= displayAmount($otAmount) ?></td>
<?php if ($hasRegularHoliday): ?>
  <td><?= displayAmount($amountRegularHoliday) ?></td>
<?php endif; ?>
<?php if ($hasSpecialHoliday): ?>
  <td><?= displayAmount($amountSpecialHoliday) ?></td>
<?php endif; ?>


<td><?= number_format($regAmount + $otAmount + $amountRegularHoliday + $amountSpecialHoliday, 2) ?></td>


<?php if ($showCA): ?><td><?= displayAmount($cash_advance) ?></td><?php endif; ?>
<?php if ($showSSS): ?><td><?= displayAmount($sss) ?></td><?php endif; ?>
<?php if ($showPagibig): ?><td><?= displayAmount($pagibig) ?></td><?php endif; ?>
<?php if ($showPHIC): ?><td><?= displayAmount($philhealth) ?></td><?php endif; ?>
<?php if ($showLoan): ?><td><?= displayAmount($loan) ?></td><?php endif; ?>
<?php if ($showOther): ?><td><?= displayAmount($other_deduction) ?></td><?php endif; ?>
<?php if ($showTotalDeduction): ?><td><?= displayAmount($total_deduction) ?></td><?php endif; ?>

<td>
  <span class="d-print-block d-none"><?= number_format($netPay, 2) ?></span>
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

      <h5 class="modal-title">Payslip - <?= htmlspecialchars($row->last_name . ', ' . $row->first_name) ?></h5>

        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-4">
        <div id="print-all-payslips-container">

          <div class="col-md-6">
            <p><strong>Employee:</strong> <?= htmlspecialchars($row->last_name . ', ' . $row->first_name) ?></p>

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
   <p><strong>Overtime Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p>
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
            <li>SSS (Gov’t): <?= number_format($sss, 2) ?></li>
<li>Pag-IBIG (Gov’t): <?= number_format($pagibig, 2) ?></li>
<li>PHIC (Gov’t): <?= number_format($philhealth, 2) ?></li>

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

<?php endforeach; ?>

<tr style="font-weight:bold; background:#f1f1f1;">
  <td colspan="<?= $totalPrefixCols ?>" class="text-right">TOTAL</td>
  <td><?= number_format($totalGross, 2) ?></td>
<?php if ($showCA): ?><td><?= number_format($totalCA, 2) ?></td><?php endif; ?>
<?php if ($showSSS): ?><td><?= number_format($totalSSS, 2) ?></td><?php endif; ?>
<?php if ($showPagibig): ?><td><?= number_format($totalPagibig, 2) ?></td><?php endif; ?>
<?php if ($showPHIC): ?><td><?= number_format($totalPHIC, 2) ?></td><?php endif; ?>
<?php if ($showLoan): ?><td><?= number_format($totalLoan, 2) ?></td><?php endif; ?>
<?php if ($showOther): ?><td><?= number_format($totalOther, 2) ?></td><?php endif; ?>
<?php if ($showTotalDeduction): ?><td><?= number_format($totalDeduction, 2) ?></td><?php endif; ?>


  <td><?= number_format($totalNet, 2) ?></td>
  <?php if (empty($is_summary)): ?>
    <td colspan="3"></td>
  <?php endif; ?>
</tr>

</tbody>

</table>




<!-- === PRINTABLE ALL PAYSLIPS SECTION (hidden by default) === -->
<div id="allPayslips" class="no-print-payroll d-none">
  <div id="print-all-payslips-container">
   <?php foreach ($attendance_data as $ln => $row): ?>
  <?php if (!in_array($row->rateType, ['Month', 'Bi-Month'])) continue; ?>

  <?php
    $fullName = htmlspecialchars($row->last_name . ', ' . $row->first_name);
    $position = htmlspecialchars($row->position);
    $rateType = $row->rateType;
    $rateAmount = $row->rateAmount ?? 0;
    $printedDate = date('F d, Y');

    // Compute values based on attendance
    $pay = computePayroll($row, $start, $end);

    $regFormatted = number_format($pay['regTotalMinutes'] / 60, 2);
    $otFormatted = number_format($pay['otTotalMinutes'] / 60, 2);
    $totalDays = number_format($pay['totalDays'], 2);
    $salary = $pay['salary'];
    $cash_advance = $pay['cash_advance'];
    $sss = $pay['sss'];
    $pagibig = $pay['pagibig'];
    $philhealth = $pay['philhealth'];
    $loan = $pay['loan'];
    $other_deduction = $pay['other_deduction'];
    $total_deduction = $pay['total_deduction'];
    $netPay = $pay['netPay'];

    $workingDaysInMonth = getWorkingDaysInMonth($start);
    $dailyRate = ($rateType === 'Month') ? ($rateAmount / $workingDaysInMonth) : ($rateAmount / 8);
    $hourlyRate = ($rateType === 'Month') ? ($dailyRate / 8) : (($rateType === 'Day') ? ($rateAmount / 8) : $rateAmount);
    $otRate = $hourlyRate * 1.25;
  ?>

  <div class="print-card" style="page-break-inside: avoid; margin-bottom: 30px; padding: 20px; border: 1px solid #ddd;">
    <h4 style="margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 6px;">
      Payslip - <?= $fullName ?>
    </h4>

    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
      <div>
        <p><strong>Employee:</strong> <?= $fullName ?></p>
        <p><strong>Position:</strong> <?= $position ?></p>
        <p><strong>Rate:</strong> ₱<?= number_format($rateAmount, 2) ?> / <?= $rateType ?></p>
        <?php if ($rateType === 'Month'): ?>
          <p><strong>Daily Rate:</strong> ₱<?= number_format($dailyRate, 2) ?></p>
          <p><strong>Hourly Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p>
          <p><strong>Overtime Rate:</strong> ₱<?= number_format($otRate, 2) ?></p>
        <?php endif; ?>
      </div>
      <div style="text-align: right;">
        <p><strong>Period:</strong><br><?= date('F d', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></p>
        <p><strong>Printed:</strong> <?= $printedDate ?></p>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 15px;">
      <div style="width: 48%;">
        <h5 style="border-bottom: 1px solid #ccc;">Earnings</h5>
        <p>Regular Time: <?= $regFormatted ?> hrs</p>
        <p>Overtime: <?= $otFormatted ?> hrs</p>
        <p>Total Days: <?= $totalDays ?></p>
        <p><strong>Gross Salary: ₱<?= number_format($salary, 2) ?></strong></p>
      </div>

      <div style="width: 48%;">
        <h5 style="border-bottom: 1px solid #ccc;">Deductions</h5>
        <p>Cash Advance: ₱<?= number_format($cash_advance, 2) ?></p>
        <p>SSS (Gov’t): ₱<?= number_format($sss, 2) ?></p>
        <p>Pag-IBIG (Gov’t): ₱<?= number_format($pagibig, 2) ?></p>
        <p>PHIC (Gov’t): ₱<?= number_format($philhealth, 2) ?></p>
        <p>Loan: ₱<?= number_format($loan, 2) ?></p>
        <p>Other Deduction: ₱<?= number_format($other_deduction, 2) ?></p>
        <p><strong>Total Deduction: ₱<?= number_format($total_deduction, 2) ?></strong></p>
      </div>
    </div>

    <div style="margin-top: 10px; text-align: right;">
      <h4><strong>Net Pay: ₱<?= number_format($netPay, 2) ?></strong></h4>
    </div>
  </div>
<?php endforeach; ?>

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
</div>
</div>
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
<script>
function printAllPayslips() {
  // Store original content
  const originalContent = document.body.innerHTML;
  const payslips = document.getElementById("allPayslips").innerHTML;

  // Replace with only payslips
  document.body.innerHTML = payslips;

  // Trigger print
  window.print();

  // Restore original content
  document.body.innerHTML = originalContent;
  location.reload(); // Restore Bootstrap modals, etc.
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
