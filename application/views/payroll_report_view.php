<?php
function getWorkingDaysInMonth($anyDateInMonth) {
    $year = date('Y', strtotime($anyDateInMonth));
    $month = date('m', strtotime($anyDateInMonth));
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

function getDaysInPeriod($startDate, $endDate) {
    $s = strtotime($startDate);
    $e = strtotime($endDate);
    if ($s === false || $e === false || $s > $e) return 0;
    return (int) floor(($e - $s) / 86400) + 1;
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
                break 2;
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

    $salary = bcadd(bcadd($regAmount, $otAmount, 2), bcadd($amountRegularHoliday, $amountSpecialHoliday, 2), 2);

    $cash_advance = (string) ($row->ca_cashadvance ?? 0);
    $sss = (string) ($row->gov_sss ?? 0);
    $pagibig = (string) ($row->gov_pagibig ?? 0);
    $philhealth = (string) ($row->gov_philhealth ?? 0);
    $loan = (string) ($row->loan ?? 0);
    $other_deduction = (string) ($row->other_deduction ?? 0);

    $total_deduction = bcadd(
        bcadd(bcadd($cash_advance, $sss, 2), bcadd($pagibig, $philhealth, 2), 2),
        bcadd($loan, $other_deduction, 2),
        2
    );

    $netPay = bcsub($salary, $total_deduction, 2);

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

<?php
function fetch_other_deduction_lines($personnelID, $start, $end, $settingsID = null) {
    static $cache = [];
    $key = "{$personnelID}|{$start}|{$end}|{$settingsID}";
    if (isset($cache[$key])) return $cache[$key];

    $CI =& get_instance();
    $db = $CI->db;

    // Build query
    $db->select('description, amount, date, deduct_from, deduct_to')
       ->from('cashadvance')
       ->where('personnelID', $personnelID)
       ->where('type', 'Others');

    $db->group_start()
          ->group_start()
              ->where("deduct_from IS NOT NULL AND deduct_from <> '0000-00-00'", null, false)
              ->where("deduct_to   IS NOT NULL AND deduct_to   <> '0000-00-00'", null, false)
              ->where('deduct_from <=', $end)
              ->where('deduct_to >=', $start)
          ->group_end()
          ->or_group_start()
              ->group_start()
                  ->where("deduct_from IS NULL OR deduct_from = '0000-00-00'", null, false)
              ->group_end()
              ->group_start()
                  ->where("deduct_to   IS NULL OR deduct_to   = '0000-00-00'", null, false)
              ->group_end()
              ->where('date >=', $start)
              ->where('date <=', $end)
          ->group_end()
       ->group_end();

    if (!empty($settingsID)) {
        $db->where('settingsID', $settingsID);
    }

    $rows = $db->get()->result();

    $total = 0.0;
    foreach ($rows as $r) {
        $total += (float)$r->amount;
    }

    return $cache[$key] = ['rows' => $rows, 'total' => $total];
}
function fetch_gov_deduction_lines($personnelID, $start, $end, $settingsID = null) {
    static $cache = [];
    $key = "GOV|{$personnelID}|{$start}|{$end}|{$settingsID}";
    if (isset($cache[$key])) return $cache[$key];

    $CI =& get_instance();
    $db = $CI->db;

    $db->select('description, amount, date, deduct_from, deduct_to')
       ->from('government_deductions')
       ->where('personnelID', $personnelID);

    $db->group_start()
          ->group_start()
              ->where("deduct_from IS NOT NULL AND deduct_from <> '0000-00-00'", null, false)
              ->where("deduct_to   IS NOT NULL AND deduct_to   <> '0000-00-00'", null, false)
              ->where('deduct_from <=', $end)
              ->where('deduct_to >=', $start)
          ->group_end()
          ->or_group_start()
              ->group_start()->where("deduct_from IS NULL OR deduct_from = '0000-00-00'", null, false)->group_end()
              ->group_start()->where("deduct_to   IS NULL OR deduct_to   = '0000-00-00'", null, false)->group_end()
              ->where('date >=', $start)
              ->where('date <=', $end)
          ->group_end()
       ->group_end();

    if (!empty($settingsID)) {
        $db->where('settingsID', $settingsID);
    }

    $rows = $db->get()->result();

    $by  = ['SSS'=>[], 'Pag-IBIG'=>[], 'PhilHealth'=>[]];
    $tot = ['SSS'=>0.0, 'Pag-IBIG'=>0.0, 'PhilHealth'=>0.0];

    foreach ($rows as $r) {
        // normalize: remove non-letters, compare upper-case
        $norm = strtoupper(preg_replace('/[^A-Z]/', '', (string)$r->description));
        if (strpos($norm, 'SSS') !== false) {
            $by['SSS'][] = $r;             $tot['SSS'] += (float)$r->amount;
        } elseif (strpos($norm, 'PAGIBIG') !== false) {
            $by['Pag-IBIG'][] = $r;        $tot['Pag-IBIG'] += (float)$r->amount;
        } elseif (
            strpos($norm, 'PHILHEALTH') !== false ||  // PhilHealth
            strpos($norm, 'PHIC') !== false           // PHIC
        ) {
            $by['PhilHealth'][] = $r;      $tot['PhilHealth'] += (float)$r->amount;
        }
    }

    return $cache[$key] = ['rows'=>$rows, 'by'=>$by, 'totals'=>$tot];
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
/* ===== Base ===== */
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

.header { text-align: center; margin-bottom: 20px; }
.header h2 { font-size: 18px; margin: 0; }
.header p { margin: 3px 0; font-size: 14px; }

table { width: 100%; border-collapse: collapse; font-size: 14px; }
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
tbody td:nth-child(2) { text-align: left; }

td.unused-holiday { background-color: #f9f9f9; color: #aaa; font-style: italic; }
.holiday-cell { background-color: #ffe5e5 !important; color: red !important; font-weight: bold !important; text-align: center !important; }

tbody td:nth-last-child(-n+10) { font-size: 10.5px; }

.signature {
  margin-top: 60px;
  padding-top: 30px;
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  page-break-inside: avoid;
  break-inside: avoid;
}
.signature div { width: 32%; text-align: center; }
.signature p { margin: 3px 0; }
.signature .name-line {
  display: inline-block;
  border-bottom: 1px solid #000;
  min-width: 250px;
  font-size: 15px;
  font-weight: 400;
  padding-bottom: 2px;
}
.signature em { font-size: 14px; color: #333; }

th { background-color: #d9d9d9; font-weight: bold; }
.absent { background-color: #f4cccc; color: #000; }

.scrollable-wrapper { overflow-x: auto; width: 100%; flex-grow: 1; }

/* ===== Header info box ===== */
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
.header-box .box-content { flex: 1 1 auto; min-width: 300px; }
.header-box .info-row { display: flex; align-items: center; margin: 3px 0; font-size: 15px; }
.header-box .info-row i { font-size: 13px; color: #666; width: 18px; margin-right: 8px; text-align: center; }
.header-box strong { display: inline-block; min-width: 125px; font-weight: 600; }
.header-box span { flex: 1; text-align: left; }

.print-button { margin-top: 4px; }
.print-button button { transition: all 0.2s ease-in-out; }
.print-button button:hover {
  background-color: #0056b3 !important;
  color: #fff !important;
  transform: scale(1.03);
  box-shadow: 0 0 6px rgba(0,0,0,0.15);
}

/* ===== Payslip Modal ===== */
.modal-content {
  background: #fff;
  border-radius: 6px;
  border: 1px solid #ddd;
  color: #000;
  font-family: Arial, sans-serif;
}
.modal-header { background: #fff !important; color: #000 !important; border-bottom: 1px solid #ddd; }
.modal-body h6 { font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 10px; }

/* ===== NEW: Other Deduction cell + list ===== */
.od-cell { text-align: left; vertical-align: top; }
.od-lines{
  font-size: 12px;
  line-height: 1.25;
  color: #555;
  word-break: break-word;   /* wrap long words */
  white-space: normal;      /* ensure wrapping in table cells */
}

/* Screen-only: keep rows compact when descriptions are long */
@media screen {
  .od-lines { max-height: 84px; overflow: auto; padding-right: 4px; }
  .deduction-sublist{ max-height: 220px; overflow: auto; }
}

/* ===== PRINT ===== */
@media print {
  html, body {
    height: 100%;
    margin: 0 !important;
    padding: 0 !important;
    font-size: 10px !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    overflow: visible !important;
  }

  /* Show full deduction lists on paper */
  .od-lines { max-height: none; overflow: visible; }
  .deduction-sublist{ max-height: none; overflow: visible; }

  #print-all-payslips-container{
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    justify-content: center;
    padding: 20px;
  }

  .print-card{
    width: 390px;   /* ≈ 4.1 in */
    height: 550px;  /* ≈ 5.8 in */
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
  .print-card p { margin: 4px 0; font-size: 12.5px; }

  @page { size: A4 landscape; margin: 1cm; }

  .print-container {
    display: flex !important;
    flex-direction: column !important;
    min-height: 100vh !important;
    justify-content: space-between !important;
  }

  .scrollable-wrapper { overflow: visible !important; flex-grow: 1 !important; }

  .btn, .modal, .no-print, .modal-backdrop { display: none !important; }

  .payroll-table {
    width: 100% !important;
    table-layout: fixed !important;
    font-size: 10px !important;
    word-wrap: break-word !important;
  }
  .payroll-table th, .payroll-table td{
    padding: 4px !important;
    font-size: 10px !important;
    page-break-inside: avoid !important;
    break-inside: avoid;
    word-break: break-word;
  }
  .payroll-table th:first-child, .payroll-table td:first-child{ min-width: 25px; max-width: 30px; }

  thead { display: table-header-group; font-size: 14px !important; }
  table, thead, tbody, tr, td, th { page-break-inside: avoid !important; }

  .signature { margin-top: auto !important; padding-top: 40px; break-inside: avoid !important; page-break-inside: avoid !important; }
  .signature div { width: 30%; text-align: center; }

  /* Keep modal content printable without clipping long lists */
  .modal-content, .modal-content * { visibility: visible !important; }
  .modal-content{
    position: static;   /* was absolute */
    top: auto; left: auto;
    width: 100%;
    height: auto;       /* was 50% */
    overflow: visible;  /* was hidden */
    padding: 20px;
    box-sizing: border-box;
  }
}
</style>



</head>
<body>
  <class="print-container">
<div class="header-box">
  <div class="box-content">
    <div class="info-row">
      <i class="fas fa-project-diagram"></i>
      <strong>PROJECT</strong><span>: <?= $project->projectTitle ?? 'N/A' ?></span>
    </div>
    <div class="info-row">
      <i class="fas fa-map-marker-alt"></i>
      <strong>LOCATION</strong><span>: <?= $project->projectLocation ?? 'Unknown' ?></span>
    </div>
    <div class="info-row">
      <i class="far fa-calendar-alt"></i>
      <strong>PERIOD</strong><span>: <?= date('F d, Y', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></span>
    </div>
    <div class="info-row">
      <i class="fas fa-clock"></i>
<strong>WORKING DAYS</strong><span>: <?= getDaysInPeriod($start, $end) ?> days</span>
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
$showCA = $showSSS = $showPHIC = $showLoan = $showOther = false;

foreach ($attendance_data as $row) {
    if (!empty($row->ca_cashadvance)) $showCA = true;
    if (!empty($row->gov_sss)) $showSSS = true;
    if (!empty($row->gov_philhealth)) $showPHIC = true;
    if (!empty($row->loan)) $showLoan = true;
    if (!empty($row->other_deduction)) $showOther = true;
}
// If numeric fields are blank but there are gov rows, still show the columns
if (!$showSSS || !$showPHIC) {
    $settingsID = isset($project->settingsID) ? $project->settingsID : null;
    foreach ($attendance_data as $r0) {
      $g0 = fetch_gov_deduction_lines($r0->personnelID, $start, $end, $settingsID);
      if (!$showSSS && !empty($g0['by']['SSS'])) $showSSS = true;
      if (!$showPHIC && !empty($g0['by']['PhilHealth'])) $showPHIC = true;
      if ($showSSS && $showPHIC) break;
    }
}

// 🔐 Only show total deduction column if any deduction type has value
$showTotalDeduction = $showCA || $showSSS || $showPHIC || $showLoan || $showOther;
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
   <?php if ($showCA): ?>
  <th rowspan="3">Cash Advance</th>
<?php endif; ?>
<?php if ($showSSS): ?>
  <th rowspan="3">SSS (Gov’t)</th>
<?php endif; ?>
<?php if ($showPHIC): ?>
  <th rowspan="3">PHIC (Gov’t)</th>
<?php endif; ?>
<?php if ($showLoan): ?>
  <th rowspan="3">Loan</th>
<?php endif; ?>
<?php if ($showOther): ?>
  <th rowspan="3">Other Deduction</th>
<?php endif; ?>

  <?php if ($showTotalDeduction): ?>
  <th rowspan="3">Total Deduction</th>
<?php endif; ?>
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
<?php if ($row->rateType === 'Month' || $row->rateType === 'Bi-Month') continue; ?>


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
        ₱<?= number_format($row->rateAmount, 2) ?> / hour
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
        }elseif ($row->rateType === 'Bi-Month') {
            $base = ($row->rateAmount / 15) / 8;
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
     if ($regHours > 0) {
    $totalDays += $regHours / 8; // prorated days from regular hours only
}


        }

    } elseif (strtolower(trim($raw)) === 'day off') {
        echo "<td colspan='2' class='text-info font-bold text-center'>Day Off</td>";

    } elseif (is_numeric($raw)) {
        $decimalHours = floatval($raw);
        $reg = min($decimalHours * 60, 480);
        $ot = max(0, ($decimalHours * 60) - 480);
        $regHours = $reg / 60;
        $otHours = $ot / 60;

        if ($row->rateType === 'Hour') {
            $base = $row->rateAmount;
        } elseif ($row->rateType === 'Day') {
            $base = $row->rateAmount / 8;
        } elseif ($row->rateType === 'Month') {
            $base = ($row->rateAmount / 30) / 8;
        } elseif ($row->rateType === 'Bi-Month') {
            $base = ($row->rateAmount / 15) / 8;
        }

        $regAmount += $regHours * $base;
        $otAmount  += $otHours * $base;

        $regTotalMinutes += $reg;
        $otTotalMinutes += $ot;
        $totalMinutes += ($reg + $ot);
      if ($regHours > 0) {
    $totalDays += $regHours / 8;
}



        echo "<td>" . displayAmount($regHours) . "</td>";
        echo "<td>" . displayAmount($otHours) . "</td>";

    } else {
        echo "<td colspan='2' class='absent text-center' style='background-color: #f8d7da; color: red;'>Absent</td>";
    }

    $loopDate = strtotime('+1 day', $loopDate);
endwhile;



// Totals
$salary = bcadd(bcadd($regAmount, $otAmount, 2), bcadd($amountRegularHoliday, $amountSpecialHoliday, 2), 2);

$cash_advance = (string) ($row->ca_cashadvance ?? 0);
$sss = (string) ($row->gov_sss ?? 0);
$pagibig = (string) ($row->gov_pagibig ?? 0);
$philhealth = (string) ($row->gov_philhealth ?? 0);
$loan = (string) ($row->loan ?? 0);
$settingsID = isset($project->settingsID) ? $project->settingsID : null;
$odetail = fetch_other_deduction_lines($row->personnelID, $start, $end, $settingsID);
$gdetail   = fetch_gov_deduction_lines($row->personnelID, $start, $end, $settingsID);
$g_by_type = $gdetail['by'];
$g_totals  = $gdetail['totals'];

// fallback the numeric fields if blank
$sss        = (string) ($sss        !== '' ? $sss        : $g_totals['SSS']);
$pagibig    = (string) ($pagibig    !== '' ? $pagibig    : $g_totals['Pag-IBIG']);
$philhealth = (string) ($philhealth !== '' ? $philhealth : $g_totals['PhilHealth']);

$other_deduction = (string) (
    isset($row->other_deduction) && $row->other_deduction !== ''
        ? $row->other_deduction
        : $odetail['total']
);



$total_deduction = $cash_advance + $sss + $philhealth + $loan + $other_deduction;

$netPay = bcsub($salary, $total_deduction, 2);

if (bccomp($netPay, '0', 2) > 0) {
    $totalPayroll = bcadd($totalPayroll, $netPay, 2);
}


$regTotalMinutes = intval($regTotalMinutes);
$otTotalMinutes = intval($otTotalMinutes);

$totalMinutesFormatted = $regTotalMinutes + $otTotalMinutes;
$formattedH = floor($totalMinutesFormatted / 60);
$formattedM = $totalMinutesFormatted % 60;
$customDecimal = $formattedH . '.' . str_pad($formattedM, 2, '0', STR_PAD_LEFT);

$regFormatted = floor($regTotalMinutes / 60) . '.' . str_pad($regTotalMinutes % 60, 2, '0', STR_PAD_LEFT);
$otFormatted = floor($otTotalMinutes / 60) . '.' . str_pad($otTotalMinutes % 60, 2, '0', STR_PAD_LEFT);


$salary = bcadd(bcadd($regAmount, $otAmount, 2), bcadd($amountRegularHoliday, $amountSpecialHoliday, 2), 2);

$total_deduction = bcadd(
    bcadd(bcadd($cash_advance, $sss, 2), bcadd($pagibig, $philhealth, 2), 2),
    bcadd($loan, $other_deduction, 2),
    2
);

$netPay = bcsub($salary, $total_deduction, 2);


$totalGross = bcadd($totalGross, $salary, 2);
$totalCA = bcadd($totalCA, $cash_advance, 2);
$totalSSS = bcadd($totalSSS, $sss, 2);
$totalPHIC = bcadd($totalPHIC, $philhealth, 2);
$totalLoan = bcadd($totalLoan, $loan, 2);
$totalOther = bcadd($totalOther, $other_deduction, 2);
$totalDeduction = bcadd($totalDeduction, $total_deduction, 2);
$totalNet = bcadd($totalNet, $netPay, 2);

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


<?php if ($showCA): ?>
  <td><?= displayAmount($cash_advance) ?></td>
<?php endif; ?>
<?php if ($showSSS): ?>
  <td class="od-cell">
    <?php if (!empty($g_by_type['SSS'])): ?>
      <div class="od-lines">
        <?php foreach ($g_by_type['SSS'] as $it): ?>
          <div>• <?= htmlspecialchars($it->description ?: 'SSS') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?= displayAmount($sss) ?>
    <?php endif; ?>
  </td>
<?php endif; ?>

<?php if ($showPHIC): ?>
  <td class="od-cell">
    <?php if (!empty($g_by_type['PhilHealth'])): ?>
      <div class="od-lines">
        <?php foreach ($g_by_type['PhilHealth'] as $it): ?>
          <div>• <?= htmlspecialchars($it->description ?: 'PhilHealth') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
        <?php endforeach; ?>
      </div>
    <?php elseif ((float)$philhealth > 0): ?>
      <div class="od-lines">
        <div>• PhilHealth — ₱<?= number_format((float)$philhealth, 2) ?></div>
      </div>
    <?php else: ?>
      <?= displayAmount($philhealth) ?>
    <?php endif; ?>
  </td>
<?php endif; ?>



<?php if ($showLoan): ?>
  <td><?= displayAmount($loan) ?></td>
<?php endif; ?>
<?php if ($showOther): ?>
 <td class="od-cell">
  <?php if (!empty($odetail['rows'])): ?>
    <div class="od-lines">
      <?php foreach ($odetail['rows'] as $it): ?>
        <div>• <?= htmlspecialchars($it->description) ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    ––
  <?php endif; ?>
</td>


<?php endif; ?>



<?php if ($showTotalDeduction): ?>
  <td><?= displayAmount($total_deduction) ?></td>
<?php endif; ?>
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
<?php
?>
<div class="modal fade" id="payslipModal<?= $ln ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="printablePayslip<?= $ln ?>">
      <div class="modal-header" style="background: #fff; border-bottom: 1px solid #ddd;">

        <h5 class="modal-title">Payslip - <?= htmlspecialchars($row->last_name . ', ' . $row->first_name) ?></h5>

        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <?php
        $rateTypeRaw   = (string)($row->rateType ?? '');
        $rateTypeLower = strtolower($rateTypeRaw);
        $rateAmountNum = (float)($row->rateAmount ?? 0);

        $workingDaysInMonth = getWorkingDaysInMonth($start);
        $dailyRate  = 0.0;
        $hourlyRate = 0.0;

        if ($rateTypeLower === 'month') {
          $dailyRate  = ($workingDaysInMonth > 0) ? $rateAmountNum / $workingDaysInMonth : 0;
          $hourlyRate = ($dailyRate > 0) ? $dailyRate / 8 : 0;
        } elseif (in_array($rateTypeLower, ['bi-month','bi-monthly','bimonth','bi-month '], true)) {
          $dailyRate  = 15 > 0 ? $rateAmountNum / 15 : 0;
          $hourlyRate = ($dailyRate > 0) ? $dailyRate / 8 : 0;
        } elseif ($rateTypeLower === 'day') {
          $dailyRate  = $rateAmountNum;
          $hourlyRate = ($rateAmountNum > 0) ? $rateAmountNum / 8 : 0;
        } else {
          $hourlyRate = $rateAmountNum;
          $dailyRate  = $hourlyRate * 8;
        }

        $otRate = $hourlyRate * 1.0;

        $regHours = (float)$regFormatted;
        $otHours  = (float)$otFormatted;

        $regAmount = $regHours * $hourlyRate;
        $otAmount  = $otHours  * $otRate;

        $regularHolidayPay = 0.0;
        if (isset($regular_holiday_pay)) {
          $regularHolidayPay = (float)$regular_holiday_pay;
        } elseif (isset($holiday_pay)) {
          $regularHolidayPay = (float)$holiday_pay;
        }

        $salary          = (float)($salary ?? 0);
        $cash_advance    = (float)($cash_advance ?? 0);
        $sss             = (float)($sss ?? 0);
        $philhealth      = (float)($philhealth ?? 0);
        $loan            = (float)($loan ?? 0);
        $other_deduction = (float)($other_deduction ?? 0);
        $total_deduction = (float)($total_deduction ?? 0);
        $netPay          = (float)($netPay ?? 0);
        $totalDays       = (float)($totalDays ?? 0);

        $hasEarningsLines =
          ($regHours > 0 && $regAmount > 0) ||
          ($otHours  > 0 && $otAmount  > 0) ||
          ($regularHolidayPay > 0) ||
          ($totalDays > 0) ||
          ($salary > 0);

        $hasDeductionsLines =
          ($cash_advance > 0) ||
          ($sss > 0) ||
          ($philhealth > 0) ||
          ($loan > 0) ||
          ($other_deduction > 0) ||
          ($total_deduction > 0); 

        $hasAnyData = $hasEarningsLines || $hasDeductionsLines || ($netPay > 0);
      ?>

      <div class="modal-body p-4"<?= $hasAnyData ? '' : ' style="display:none;"' ?>>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Employee:</strong> <?= htmlspecialchars($row->last_name . ', ' . $row->first_name) ?></p>
            <p><strong>Position:</strong> <?= htmlspecialchars($row->position) ?></p>

            <?php if ($rateTypeLower === 'month'): ?>
              <p><strong>Rate:</strong> ₱<?= number_format($rateAmountNum, 2) ?> / Month</p>
              <?php if ($dailyRate > 0): ?><p><strong>Daily Rate:</strong> ₱<?= number_format($dailyRate, 2) ?></p><?php endif; ?>
              <?php if ($hourlyRate > 0): ?><p><strong>Hourly Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p><?php endif; ?>
              <?php if ($otRate > 0): ?><p><strong>Overtime Rate:</strong> ₱<?= number_format($otRate, 2) ?></p><?php endif; ?>
            <?php elseif (in_array($rateTypeLower, ['bi-month','bi-monthly','bimonth','bi-month '], true)): ?>
              <p><strong>Rate:</strong> ₱<?= number_format($rateAmountNum, 2) ?> / Bi-Month</p>
              <?php if ($dailyRate > 0): ?><p><strong>Assumed Daily Rate (15 days):</strong> ₱<?= number_format($dailyRate, 2) ?></p><?php endif; ?>
              <?php if ($hourlyRate > 0): ?><p><strong>Hourly Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p><?php endif; ?>
              <?php if ($otRate > 0): ?><p><strong>Overtime Rate:</strong> ₱<?= number_format($otRate, 2) ?></p><?php endif; ?>
            <?php elseif ($rateTypeLower === 'day'): ?>
              <p><strong>Rate:</strong> ₱<?= number_format($rateAmountNum, 2) ?> / Day</p>
              <?php if ($hourlyRate > 0): ?><p><strong>Hourly Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p><?php endif; ?>
              <?php if ($otRate > 0): ?><p><strong>Overtime Rate:</strong> ₱<?= number_format($otRate, 2) ?></p><?php endif; ?>
            <?php else: ?>
              <p><strong>Rate:</strong> ₱<?= number_format($rateAmountNum, 2) ?> / Hour</p>
              <?php if ($hourlyRate > 0): ?><p><strong>Hourly Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p><?php endif; ?>
              <?php if ($otRate > 0): ?><p><strong>Overtime Rate:</strong> ₱<?= number_format($otRate, 2) ?></p><?php endif; ?>
            <?php endif; ?>

          </div>
          <div class="col-md-6 text-right">
            <p><strong>Period:</strong><br><?= date('F d', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></p>
            <p><strong>Printed:</strong> <?= date('F d, Y') ?></p>
          </div>
        </div>
        <hr>

        <div class="row">
          <?php if ($hasEarningsLines): ?>
          <div class="col-md-6">
            <h6>Earnings</h6>
            <ul class="list-unstyled">
              <?php if ($regHours > 0 && $regAmount > 0): ?>
                <li>
                  Regular Time: <?= number_format($regHours, 2) ?> hrs × ₱<?= number_format($hourlyRate, 2) ?>/hr
                  = <strong><?= number_format($regAmount, 2) ?></strong>
                </li>
              <?php endif; ?>

              <?php if ($otHours > 0 && $otAmount > 0): ?>
                <li>
                  Overtime: <?= number_format($otHours, 2) ?> hrs × ₱<?= number_format($otRate, 2) ?>/hr
                  = <strong><?= number_format($otAmount, 2) ?></strong>
                </li>
              <?php endif; ?>

              <?php if ($regularHolidayPay > 0): ?>
                <li>Regular Holiday: <strong><?= number_format($regularHolidayPay, 2) ?></strong></li>
              <?php endif; ?>

              <?php if ($totalDays > 0): ?>
                <li>Total Days: <?= number_format($totalDays, 2) ?></li>
              <?php endif; ?>

              <?php if ($salary > 0): ?>
                <li><strong>Gross Salary: <?= number_format($salary, 2) ?></strong></li>
              <?php endif; ?>
            </ul>
          </div>
          <?php endif; ?>

          <div class="col-md-6">
            <h6>Deductions</h6>
            <ul class="list-unstyled">
              <?php if ($cash_advance > 0): ?>
                <li>Cash Advance: <?= number_format($cash_advance, 2) ?></li>
              <?php endif; ?>

              <?php if ($sss > 0): ?>
                <li>SSS (Gov’t): <?= number_format($sss, 2) ?></li>
              <?php endif; ?>

              <?php if ($philhealth > 0): ?>
                <li>PHIC (Gov’t): <?= number_format($philhealth, 2) ?></li>
              <?php endif; ?>

              <?php if ($loan > 0): ?>
                <li>Loan: <?= number_format($loan, 2) ?></li>
              <?php endif; ?>

              <?php if ($other_deduction > 0): ?>
                <li>Other Deduction: <?= number_format($other_deduction, 2) ?></li>
              <?php endif; ?>
              
<?php if (!empty($odetail['rows'])): ?>
  <ul class="deduction-sublist" style="margin-left:12px;">
    <?php foreach ($odetail['rows'] as $it): ?>
      <li><?= htmlspecialchars($it->description) ?> — ₱<?= number_format((float)$it->amount, 2) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>



              <?php if ($total_deduction > 0): ?>
                <li><strong>Total Deduction: <?= number_format($total_deduction, 2) ?></strong></li>
              <?php endif; ?>

            </ul>
          </div>
        </div>

        <?php if ($netPay > 0): ?>
          <hr>
          <div class="text-right">
            <h5><strong>Net Pay: <?= number_format($netPay, 2) ?></strong></h5>
            <button onclick="printPayslip('printablePayslip<?= $ln ?>')" class="btn btn-sm btn-secondary mt-2"><i class="fas fa-print"></i> Print</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php endforeach; ?>


</tbody>
</table>

<!-- === PRINTABLE ALL PAYSLIPS SECTION (hidden by default) === -->
<div id="allPayslips" class="no-print-payroll d-none">
  <div id="print-all-payslips-container">
<?php foreach ($attendance_data as $ln => $row): ?>
<?php if ($row->rateType === 'Month' || $row->rateType === 'Bi-Month') continue; ?>

  <?php
    $fullName    = htmlspecialchars($row->last_name . ', ' . $row->first_name);
    $position    = htmlspecialchars($row->position);
    $rateType    = $row->rateType;
    $rateAmount  = (float)($row->rateAmount ?? 0);
    $printedDate = date('F d, Y');

    $pay = computePayroll($row, $start, $end);

    $settingsID = isset($project->settingsID) ? $project->settingsID : null;
    $odetail_print = fetch_other_deduction_lines($row->personnelID, $start, $end, $settingsID);

    $regHoursRaw     = (float)$pay['regTotalMinutes'] / 60;
    $otHoursRaw      = (float)$pay['otTotalMinutes'] / 60;
    $totalDays       = (float)$pay['totalDays'];

    $salary          = (float)$pay['salary'];
    $cash_advance    = (float)$pay['cash_advance'];
    $sss             = (float)$pay['sss'];
    $pagibig         = (float)$pay['pagibig'];
    $philhealth      = (float)$pay['philhealth'];
    $loan            = (float)$pay['loan'];
    $other_deduction = (float)$pay['other_deduction'];
    $total_deduction = (float)$pay['total_deduction'];
    $netPay          = (float)$pay['netPay'];

    $regularHolidayPay = 0.0;
    if (isset($pay['regular_holiday_pay'])) {
      $regularHolidayPay = (float)$pay['regular_holiday_pay'];
    } elseif (isset($pay['holiday_pay'])) {
      $regularHolidayPay = (float)$pay['holiday_pay'];
    }

    $rateTypeLower = strtolower((string)$rateType);
    $workingDaysInMonth = getWorkingDaysInMonth($start);
    $dailyRate  = 0.0;
    $hourlyRate = 0.0;

    if ($rateTypeLower === 'month') {
      $dailyRate  = ($workingDaysInMonth > 0) ? $rateAmount / $workingDaysInMonth : 0;
      $hourlyRate = ($dailyRate > 0) ? $dailyRate / 8 : 0;
    } elseif (in_array($rateTypeLower, ['bi-month','bi-monthly','bimonth','bi-month '], true)) {
      $dailyRate  = 15 > 0 ? $rateAmount / 15 : 0;
      $hourlyRate = ($dailyRate > 0) ? $dailyRate / 8 : 0;
    } elseif ($rateTypeLower === 'day') {
      $dailyRate  = $rateAmount;
      $hourlyRate = ($rateAmount > 0) ? $rateAmount / 8 : 0;
    } else { 
      $hourlyRate = $rateAmount;
      $dailyRate  = $hourlyRate * 8;
    }
    $otRate    = $hourlyRate * 1.0;

    $regAmount = $regHoursRaw * $hourlyRate;
    $otAmount  = $otHoursRaw  * $otRate;

    $hasEarningsLines =
      ($regHoursRaw > 0 && $regAmount > 0) ||
      ($otHoursRaw  > 0 && $otAmount  > 0) ||
      ($regularHolidayPay > 0) ||
      ($totalDays > 0) ||
      ($salary > 0);

    $hasDeductionsLines =
      ($cash_advance > 0) ||
      ($sss > 0) ||
      ($pagibig > 0) ||
      ($philhealth > 0) ||
      ($loan > 0) ||
      ($other_deduction > 0) ||
      ($total_deduction > 0);

    $hasAnyData = $hasEarningsLines || $hasDeductionsLines || ($netPay > 0);

    if (!$hasAnyData) {
      continue;
    }
?>


  <div class="print-card" style="page-break-inside: avoid; margin-bottom: 30px; padding: 20px; border: 1px solid #ddd;">
    <h4 style="margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 6px;">
      Payslip - <?= $fullName ?>
    </h4>

    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
      <div>
        <p><strong>Employee:</strong> <?= $fullName ?></p>
        <p><strong>Position:</strong> <?= $position ?></p>
        <p><strong>Rate:</strong> ₱<?= number_format($rateAmount, 2) ?> / <?= htmlspecialchars($rateType) ?></p>
        <?php if ($hourlyRate > 0): ?><p><strong>Hourly Rate:</strong> ₱<?= number_format($hourlyRate, 2) ?></p><?php endif; ?>
        <?php if ($otRate > 0): ?><p><strong>Overtime Rate:</strong> ₱<?= number_format($otRate, 2) ?></p><?php endif; ?>
        <?php if ($rateTypeLower !== 'hour' && $dailyRate > 0): ?>
          <p><strong>Daily Rate:</strong> ₱<?= number_format($dailyRate, 2) ?></p>
        <?php endif; ?>
      </div>
      <div style="text-align: right;">
        <p><strong>Period:</strong><br><?= date('F d', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></p>
        <p><strong>Printed:</strong> <?= $printedDate ?></p>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 15px;">
      <?php if ($hasEarningsLines): ?>
      <div style="width: 48%;">
        <h5 style="border-bottom: 1px solid #ccc;">Earnings</h5>

        <?php if ($regHoursRaw > 0 && $regAmount > 0): ?>
          <p>
            Regular Time: <?= number_format($regHoursRaw, 2) ?> hrs × ₱<?= number_format($hourlyRate, 2) ?>/hr
            = <strong><?= number_format($regAmount, 2) ?></strong>
          </p>
        <?php endif; ?>

        <?php if ($otHoursRaw > 0 && $otAmount > 0): ?>
          <p>
            Overtime: <?= number_format($otHoursRaw, 2) ?> hrs × ₱<?= number_format($otRate, 2) ?>/hr
            = <strong><?= number_format($otAmount, 2) ?></strong>
          </p>
        <?php endif; ?>

        <?php if ($regularHolidayPay > 0): ?>
          <p>Regular Holiday: <strong><?= number_format($regularHolidayPay, 2) ?></strong></p>
        <?php endif; ?>

        <?php if ($totalDays > 0): ?>
          <p>Total Days: <?= number_format($totalDays, 2) ?></p>
        <?php endif; ?>

        <?php if ($salary > 0): ?>
          <p><strong>Gross Salary: ₱<?= number_format($salary, 2) ?></strong></p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ($hasDeductionsLines): ?>
      <div style="width: 48%;">
        <h5 style="border-bottom: 1px solid #ccc;">Deductions</h5>
        <?php if ($cash_advance > 0): ?><p>Cash Advance: ₱<?= number_format($cash_advance, 2) ?></p><?php endif; ?>
        <?php if ($sss > 0): ?><p>SSS (Gov’t): ₱<?= number_format($sss, 2) ?></p><?php endif; ?>
        <?php if ($pagibig > 0): ?><p>Pag-IBIG (Gov’t): ₱<?= number_format($pagibig, 2) ?></p><?php endif; ?>
        <?php if ($philhealth > 0): ?><p>PHIC (Gov’t): ₱<?= number_format($philhealth, 2) ?></p><?php endif; ?>
        <?php if ($loan > 0): ?><p>Loan: ₱<?= number_format($loan, 2) ?></p><?php endif; ?>
        <?php if ($other_deduction > 0): ?><p>Other Deduction: ₱<?= number_format($other_deduction, 2) ?></p><?php endif; ?>
          <?php if (!empty($odetail_print['rows'])): ?>
  <div style="margin-left:12px; margin-top:2px;">
    <?php foreach ($odetail_print['rows'] as $it): ?>
      <div>• <?= htmlspecialchars($it->description) ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

        <?php if ($total_deduction > 0): ?><p><strong>Total Deduction: ₱<?= number_format($total_deduction, 2) ?></strong></p><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($netPay > 0): ?>
    <div style="margin-top: 10px; text-align: right;">
      <h4><strong>Net Pay: ₱<?= number_format($netPay, 2) ?></strong></h4>
    </div>
    <?php endif; ?>
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
