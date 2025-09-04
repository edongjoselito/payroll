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
function getPrintSlices($start, $end) {
    $startTs = strtotime($start);
    $endTs   = strtotime($end);
    if ($startTs === false || $endTs === false || $startTs > $endTs) return [];

    // month cursor at the 1st day of the start month
    $monthCur = strtotime(date('Y-m-01', $startTs));
    $lastMon  = strtotime(date('Y-m-01', $endTs));

    $out = [];

    while ($monthCur <= $lastMon) {
        $y = (int)date('Y', $monthCur);
        $m = (int)date('m', $monthCur);
        $eom = cal_days_in_month(CAL_GREGORIAN, $m, $y);

        // three standard slices for this month
        $ranges = [
            ["$y-$m-01", "$y-$m-10"],
            ["$y-$m-11", "$y-$m-20"],
            ["$y-$m-21", "$y-$m-$eom"],
        ];

        foreach ($ranges as [$rs, $re]) {
            $rsTs = strtotime($rs);
            $reTs = strtotime($re);

            $s = max($rsTs, $startTs);
            $e = min($reTs, $endTs);

            if ($s <= $e) {
                $out[] = [
                    'label' => date('M d', $s) . ' – ' . date('M d, Y', $e),
                    'start' => date('Y-m-d', $s),
                    'end'   => date('Y-m-d', $e),
                ];
            }
        }

        // next month
        $monthCur = strtotime('+1 month', $monthCur);
    }

    return $out;
}

?>

<?php
function fetch_other_deduction_lines($personnelID, $start, $end, $settingsID = null) {
    static $cache = [];
    $key = "{$personnelID}|{$start}|{$end}|{$settingsID}";
    if (isset($cache[$key])) return $cache[$key];

    $CI =& get_instance();
    $db = $CI->db;

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
    $desc = (string)($r->description ?? '');

    $isSSS = (bool)preg_match('/\bSSS\b/i', $desc);

    $isPagibig = (bool)preg_match('/\b(?:PAG\s*[-]?\s*IBIG|PAGIBIG|HDMF)\b/i', $desc);

    $isPhilHealth = (bool)preg_match('/\b(?:PHIL\s*HEALTH|PHILHEALTH|PHIC)\b/i', $desc);

    if ($isSSS) {
        $by['SSS'][] = $r;            $tot['SSS']       += (float)$r->amount;
    } elseif ($isPhilHealth) {
        $by['PhilHealth'][] = $r;     $tot['PhilHealth'] += (float)$r->amount;
    } elseif ($isPagibig) {
        $by['Pag-IBIG'][] = $r;       $tot['Pag-IBIG']   += (float)$r->amount;
    } else {
    }
}


return $cache[$key] = ['rows'=>$rows, 'by'=>$by, 'totals'=>$tot];
}
function fetch_loan_lines($personnelID, $start, $end, $settingsID = null) {
    static $cache = [];
    $key = "LOAN|{$personnelID}|{$start}|{$end}|{$settingsID}";
    if (isset($cache[$key])) return $cache[$key];

    $CI =& get_instance();
    $db = $CI->db;

    $db->select("
        loan_description AS description,
        COALESCE(
            NULLIF(monthly_deduction, 0),
            NULLIF(deduction_amount, 0),
            CASE WHEN COALESCE(term_months,0) > 0 THEN amount / term_months ELSE NULL END,
            amount
        ) AS amount,
        start_date, end_date, deduct_from, deduct_to, date_assigned, status, is_paid
    ", false)
    ->from('personnelloans')
    ->where('personnelID', $personnelID)
    ->where('status', 1);

    $db->group_start()

        ->group_start()
            ->where("(start_date IS NOT NULL AND start_date <> '0000-00-00')", null, false)
            ->where("(end_date   IS NOT NULL AND end_date   <> '0000-00-00')",   null, false)
            ->where('start_date <=', $end)
            ->where('end_date   >=', $start)
        ->group_end()

        ->or_group_start()
            ->where("(start_date IS NULL OR start_date = '0000-00-00')", null, false)
            ->where("(end_date   IS NULL OR end_date   = '0000-00-00')", null, false)
            ->where("(deduct_from IS NOT NULL AND deduct_from <> '0000-00-00')", null, false)
            ->where("(deduct_to   IS NOT NULL AND deduct_to   <> '0000-00-00')", null, false)
            ->where('deduct_from <=', $end)
            ->where('deduct_to   >=', $start)
        ->group_end()

        ->or_group_start()
            ->where("(start_date IS NULL OR start_date = '0000-00-00')", null, false)
            ->where("(end_date   IS NULL OR end_date   = '0000-00-00')", null, false)
            ->where("(deduct_from IS NULL OR deduct_from = '0000-00-00')", null, false)
            ->where("(deduct_to   IS NULL OR deduct_to   = '0000-00-00')", null, false)
            ->group_start()
                ->where("COALESCE(monthly_deduction,0) >", 0)
                ->or_where("COALESCE(deduction_amount,0) >", 0)
            ->group_end()
        ->group_end()

    ->group_end();

    if (!empty($settingsID)) {
        $db->where('settingsID', $settingsID);
    }

    $rows = $db->get()->result();

    $total = 0.0;
    foreach ($rows as $r) $total += (float)$r->amount;

    return $cache[$key] = ['rows' => $rows, 'total' => $total];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PMS - Payroll Report</title>
    <?php include('includes/head.php'); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet"
      href="<?= base_url('assets/css/payroll.css') ?>">


</head>
<body>
 <div class="print-container">
<div class="print-letterhead">
  <img src="<?= base_url('assets/images/pms-logo1.png') ?>" alt="PMS Logo">
</div>

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
<button onclick="$('.modal').modal('hide'); setTimeout(() => window.print(), 250);" class="btn btn-primary btn-sm">
      <i class="fas fa-print"></i> Print Payroll
    </button>
     <div class="print-button no-print mt-2">
  <button onclick="printAllPayslips()" class="btn btn-success btn-sm">
    <i class="fas fa-file-invoice-dollar"></i> Print Payslips
  </button>
</div>
  </div>
</div>


<div class="scrollable-wrapper" id="mainScroll">
  <div id="mainUnsliced">

  <?php
$hasRegularHoliday = false;
$hasSpecialHoliday = false;

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
$showCA = $showSSS = $showPHIC = $showPAGIBIG = $showLoan = $showOther = false;

foreach ($attendance_data as $row) {
    if (!empty($row->ca_cashadvance)) $showCA = true;
    if (!empty($row->gov_sss)) $showSSS = true;
    if (!empty($row->gov_philhealth)) $showPHIC = true;
    if (!empty($row->loan)) $showLoan = true;
    if (!empty($row->other_deduction)) $showOther = true;
    if (!empty($row->gov_pagibig)) $showPAGIBIG = true;

}
if (!$showSSS || !$showPHIC || !$showPAGIBIG) {
    $settingsID = isset($project->settingsID) ? $project->settingsID : null;
    foreach ($attendance_data as $r0) {
      $g0 = fetch_gov_deduction_lines($r0->personnelID, $start, $end, $settingsID);
      if (!$showSSS && !empty($g0['by']['SSS']))           $showSSS = true;
      if (!$showPHIC && !empty($g0['by']['PhilHealth']))   $showPHIC = true;
      if (!$showPAGIBIG && !empty($g0['by']['Pag-IBIG']))  $showPAGIBIG = true;
      if ($showSSS && $showPHIC && $showPAGIBIG) break;
    }
}

if (!$showLoan) {
    foreach ($attendance_data as $r0) {
        $ld0 = fetch_loan_lines($r0->personnelID, $start, $end, $settingsID);
        if (!empty($ld0['rows'])) { $showLoan = true; break; }
    }
}
$showTotalDeduction = $showCA || $showSSS || $showPHIC || $showPAGIBIG || $showLoan || $showOther;

?>


<table class="payroll-table">
  <colgroup>
    <col style="width:28px">
   <col style="width:150px">
<col style="width:110px">

    <col style="width:90px">
    <col style="width:70px">
    <?php $d=strtotime($start); while($d<=strtotime($end)){ echo '<col style="width:34px"><col style="width:34px">'; $d=strtotime('+1 day',$d);} ?>
    <col style="width:46px"><col style="width:46px"><col style="width:46px"><!-- totals -->
    <col style="width:70px"><col style="width:70px"><?php if($hasRegularHoliday):?><col style="width:70px"><?php endif; ?><?php if($hasSpecialHoliday):?><col style="width:70px"><?php endif; ?>
    <col style="width:80px">
    <?php if ($showCA): ?><col style="width:80px"><?php endif; ?>
    <?php if ($showSSS): ?><col style="width:110px"><?php endif; ?>
    <?php if ($showPHIC): ?><col style="width:110px"><?php endif; ?>
    <?php if ($showPAGIBIG): ?><col style="width:110px"><?php endif; ?>
    <?php if ($showLoan): ?><col style="width:110px"><?php endif; ?>
    <?php if ($showOther): ?><col style="width:110px"><?php endif; ?>
    <?php if ($showTotalDeduction): ?><col style="width:90px"><?php endif; ?>
    <col style="width:90px">
<?php if (empty($is_summary)): ?><col style="width:120px"><?php endif; ?>
  </colgroup>

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
<?php if ($showPAGIBIG): ?>
  <th rowspan="3">Pag-IBIG (Gov’t)</th>
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
echo '<th colspan="2" class="center">' . date('D', $startDate) . '</th>';
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
$totalGross = 0;
$totalCA = 0;
$totalSSS = 0;
$totalPHIC = 0;
$totalPAGIBIG = 0;
$totalLoan = 0;
$totalOther = 0;
$totalDeduction = 0;
$totalNet = 0;


$dateColumnCount = 0;
$loopDate = strtotime($start);
while ($loopDate <= strtotime($end)) {
    $dateColumnCount += 2; 
    $loopDate = strtotime('+1 day', $loopDate);
}

$fixedColsBeforeDays = 5;
$totalTimeCols = 3; 
$amountCols = 2; 

if ($hasRegularHoliday) $amountCols++;
if ($hasSpecialHoliday) $amountCols++;

$totalPrefixCols = $fixedColsBeforeDays + $dateColumnCount + $totalTimeCols + $amountCols;
?>

<?php $ln = 1; foreach ($attendance_data as $row): ?>
<?php if ($row->rateType === 'Month' || $row->rateType === 'Bi-Month') continue; ?>

<?php
  $p_pre = computePayroll($row, $start, $end);
  $rowHasAny = (
      ($p_pre['regTotalMinutes'] + $p_pre['otTotalMinutes']) > 0 ||
      ((float)$p_pre['regAmount'] + (float)$p_pre['otAmount'] +
       (float)$p_pre['amountRegularHoliday'] + (float)$p_pre['amountSpecialHoliday']) > 0 ||
      (float)$p_pre['salary'] > 0 ||
      (float)$p_pre['cash_advance'] > 0 ||
      (float)$p_pre['sss'] > 0 ||
      (float)$p_pre['pagibig'] > 0 ||
      (float)$p_pre['philhealth'] > 0 ||
      (float)$p_pre['loan'] > 0 ||
      (float)$p_pre['other_deduction'] > 0 ||
      (float)$p_pre['netPay'] > 0
  );
?>

<tr class="<?= $rowHasAny ? '' : 'no-data-print' ?>">

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

        if ($row->rateType === 'Hour') {
            $base = $row->rateAmount;
        } elseif ($row->rateType === 'Day') {
            $base = $row->rateAmount / 8;
        } elseif ($row->rateType === 'Month') {
            $base = ($row->rateAmount / 30) / 8;
        }elseif ($row->rateType === 'Bi-Month') {
            $base = ($row->rateAmount / 15) / 8;
        }

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

            echo "<td colspan='2' style='background-color: #ffe5e5; color: red; font-weight: bold; text-align: center;'>";
            echo "{$holidayLabel}<br>(";
            $parts = [];
            if ($holidayHours > 0) $parts[] = number_format($holidayHours, 2);
            if ($regHours > 0) $parts[] = displayAmount($regHours) . " R";
            if ($otHours > 0) $parts[] = displayAmount($otHours) . " OT";
            echo implode(" + ", $parts);
            echo ")</td>";

        } else {
            if ($regHours <= 0 && $otHours > 0 && in_array($status, ['absent', 'absentee'])) {
                echo "<td class='text-danger text-center font-weight-bold'>A</td>";
                echo "<td>" . number_format($otHours, 2) . "</td>";
            } elseif ($regHours <= 0 && $otHours <= 0 && in_array($status, ['absent', 'absentee'])) {
                echo "<td colspan='2' class='absent text-center' style='background-color: #f8d7da; color: red;'>Absent</td>";
            } else {
               echo '<td class="num">' . displayAmount($regHours) . '</td>';
echo '<td class="num">' . displayAmount($otHours) . '</td>';

            }

            $regAmount += $regHours * $base;
            $otAmount += $otHours * $base;

            $regTotalMinutes += $regHours * 60;
            $otTotalMinutes += $otHours * 60;
            $totalMinutes += ($regHours + $otHours) * 60;
     if ($regHours > 0) {
    $totalDays += $regHours / 8;
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



$salary = bcadd(bcadd($regAmount, $otAmount, 2), bcadd($amountRegularHoliday, $amountSpecialHoliday, 2), 2);

$cash_advance = (string) ($row->ca_cashadvance ?? 0);
$sss = (string) ($row->gov_sss ?? 0);
$pagibig = (string) ($row->gov_pagibig ?? 0);
$philhealth = (string) ($row->gov_philhealth ?? 0);
$loan = (string) ($row->loan ?? 0);
$settingsID = isset($project->settingsID) ? $project->settingsID : null;
$odetail = fetch_other_deduction_lines($row->personnelID, $start, $end, $settingsID);
$gdetail   = fetch_gov_deduction_lines($row->personnelID, $start, $end, $settingsID);
$ldetail = fetch_loan_lines($row->personnelID, $start, $end, $settingsID);
$loan    = (string) ($loan !== '' ? $loan : $ldetail['total']);

$g_by_type = $gdetail['by'];
$g_totals  = $gdetail['totals'];

$sss        = (string)((trim($sss)        !== '' && (float)$sss        > 0) ? $sss        : $g_totals['SSS']);
$pagibig    = (string)((trim($pagibig)    !== '' && (float)$pagibig    > 0) ? $pagibig    : $g_totals['Pag-IBIG']);
$philhealth = (string)((trim($philhealth) !== '' && (float)$philhealth > 0) ? $philhealth : $g_totals['PhilHealth']);




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
$totalPAGIBIG = bcadd($totalPAGIBIG, $pagibig, 2);

$totalPHIC = bcadd($totalPHIC, $philhealth, 2);
$totalLoan = bcadd($totalLoan, $loan, 2);
$totalOther = bcadd($totalOther, $other_deduction, 2);
$totalDeduction = bcadd($totalDeduction, $total_deduction, 2);
if (bccomp($netPay, '0', 2) > 0) {
    $totalNet = bcadd($totalNet, $netPay, 2);
}

?>

<td class="num"><?= displayAmount($regTotalMinutes / 60) ?></td>
<td class="num"><?= number_format($otTotalMinutes / 60, 2) ?></td>
<td class="num"><?= number_format($totalDays, 2) ?></td>



<td class="num"><?= displayAmount($regAmount) ?></td>
<td class="num"><?= displayAmount($otAmount) ?></td>
<?php if ($hasRegularHoliday): ?>
  <td class="num"><?= displayAmount($amountRegularHoliday) ?></td>
<?php endif; ?>
<?php if ($hasSpecialHoliday): ?>
  <td class="num"><?= displayAmount($amountSpecialHoliday) ?></td>
<?php endif; ?>
<td class="num"><?= number_format($regAmount + $otAmount + $amountRegularHoliday + $amountSpecialHoliday, 2) ?></td>



<?php if ($showCA): ?>
  <td><?= displayAmount($cash_advance) ?></td>
<?php endif; ?>
<?php if ($showSSS): ?>
  <td class="od-cell">
    <?php if (!empty($g_by_type['SSS'])): ?>
      <div class="od-lines">
        <?php foreach ($g_by_type['SSS'] as $it): ?>
          <div><?= htmlspecialchars($it->description ?: 'SSS') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
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
          <div><?= htmlspecialchars($it->description ?: 'PhilHealth') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
        <?php endforeach; ?>
      </div>
    <?php elseif ((float)$philhealth > 0): ?>
      <div class="od-lines">
        <div>PhilHealth — ₱<?= number_format((float)$philhealth, 2) ?></div>
      </div>
    <?php else: ?>
      <?= displayAmount($philhealth) ?>
    <?php endif; ?>
  </td>
<?php endif; ?>
<?php if ($showPAGIBIG): ?>
  <td class="od-cell">
    <?php if (!empty($g_by_type['Pag-IBIG'])): ?>
      <div class="od-lines">
        <?php foreach ($g_by_type['Pag-IBIG'] as $it): ?>
          <div><?= htmlspecialchars($it->description ?: 'Pag-IBIG') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
        <?php endforeach; ?>
      </div>
    <?php elseif ((float)$pagibig > 0): ?>
      <div class="od-lines">
        <div>Pag-IBIG — ₱<?= number_format((float)$pagibig, 2) ?></div>
      </div>
    <?php else: ?>
      <?= displayAmount($pagibig) ?>
    <?php endif; ?>
  </td>
<?php endif; ?>


<?php if ($showLoan): ?>
  <td class="od-cell">
    <?php if (!empty($ldetail['rows'])): ?>
      <div class="od-lines">
        <?php foreach ($ldetail['rows'] as $it): ?>
          <div><?= htmlspecialchars($it->description ?: 'Loan') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?= displayAmount($loan) ?>
    <?php endif; ?>
  </td>
<?php endif; ?>


<?php if ($showOther): ?>
  <td class="od-cell">
    <?php if (!empty($odetail['rows'])): ?>
      <div class="od-lines">
        <?php foreach ($odetail['rows'] as $it): ?>
          <div><?= htmlspecialchars($it->description) ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?= displayAmount($other_deduction) ?>
    <?php endif; ?>
  </td>
<?php endif; ?>
 



<?php if ($showTotalDeduction): ?>
  <td><?= displayAmount($total_deduction) ?></td>
<?php endif; ?>
<?php
  $isNeg  = (bccomp($netPay, '0', 2) < 0);
  $netFmt = number_format($netPay, 2);
?>
<td class="num">
  <span class="d-none d-print-inline <?= $isNeg ? 'neg' : '' ?>"><?= $netFmt ?></span>
  <a href="#" class="btn btn-link btn-sm d-print-none <?= $isNeg ? 'neg' : '' ?>"
     data-toggle="modal" data-target="#payslipModal<?= $ln ?>">
    <?= $netFmt ?>
  </a>
</td>





<?php if (empty($is_summary)): ?>
  <td colspan="3"></td>
<?php endif; ?>
</tr>
<div class="modal fade" id="payslipModal<?= $ln ?>" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content" id="printablePayslip<?= $ln ?>">

      <style>
        #payslipModal<?= $ln ?> .payslip-card{
          max-width: 520px;
          margin: 0 auto;
          padding: 14px 6px 2px 6px;
        }
        #payslipModal<?= $ln ?> .modal-header{
          background:#fff !important;
          border-bottom:1px solid #ddd;
          padding: .6rem .9rem;
        }
        #payslipModal<?= $ln ?> .modal-title{
          font-weight:700; color:#2b2b2b; font-size: 1.05rem;
        }
        #payslipModal<?= $ln ?> .close{
          color:#333 !important; opacity:1 !important; text-shadow:none; font-size:26px; line-height:1;
        }

        #payslipModal<?= $ln ?> .info-row{
          display:flex; justify-content:space-between; align-items:flex-start; gap:10px;
          margin:3px 0;
        }
        #payslipModal<?= $ln ?> .info-row .label{ font-weight:600; min-width:110px; }
        #payslipModal<?= $ln ?> .info-row .value{ flex:1; text-align:right; }

        #payslipModal<?= $ln ?> .section-title{
          font-weight:700; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin:12px 0 8px;
          font-size:.95rem;
        }
        #payslipModal<?= $ln ?> ul{ list-style:none; padding:0; margin:0; }
        #payslipModal<?= $ln ?> li{ padding:4px 0; }
        #payslipModal<?= $ln ?> .line{
          display:flex; justify-content:space-between; gap:12px; align-items:baseline;
        }
        #payslipModal<?= $ln ?> .amt{
          font-weight:600; font-variant-numeric:tabular-nums; white-space:nowrap;
        }
        #payslipModal<?= $ln ?> .sublist{
          margin:4px 0 4px 10px; padding-left:10px; border-left:2px solid #f1f5f9;
        }
        #payslipModal<?= $ln ?> .total-line{ border-top:1px solid #e5e7eb; margin-top:6px; padding-top:6px; }
        #payslipModal<?= $ln ?> .netpay-box{ border-top:2px solid #e5e7eb; margin-top:12px; padding-top:10px; }

        @media (max-width: 575.98px){
          #payslipModal<?= $ln ?> .modal-dialog{ margin: .5rem; }
          #payslipModal<?= $ln ?> .payslip-card{ max-width: 100%; }
        }
      </style>

      <div class="modal-header">
        <h5 class="modal-title">Payslip — <?= htmlspecialchars($row->last_name . ', ' . $row->first_name) ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
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
        if (isset($regular_holiday_pay))       $regularHolidayPay = (float)$regular_holiday_pay;
        elseif (isset($holiday_pay))           $regularHolidayPay = (float)$holiday_pay;

        $salary          = (float)($salary ?? 0);
        $cash_advance    = (float)($cash_advance ?? 0);
        $sss             = (float)($sss ?? 0);
        $philhealth      = (float)($philhealth ?? 0);
        $pagibig         = (float)($pagibig ?? 0);
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
          ($pagibig > 0) ||
          ($loan > 0) ||
          ($other_deduction > 0) ||
          ($total_deduction > 0);

        $hasAnyData = $hasEarningsLines || $hasDeductionsLines || ($netPay > 0);
      ?>

      <div class="modal-body pt-2 pb-3 px-3"<?= $hasAnyData ? '' : ' style="display:none;"' ?>>
        <div class="payslip-card">

        
          <div class="info-row">
            <div class="label">Position</div>
            <div class="value"><?= htmlspecialchars($row->position) ?></div>
          </div>
          <div class="info-row">
            <div class="label">Period</div>
            <div class="value"><?= date('F d', strtotime($start)) ?> – <?= date('F d, Y', strtotime($end)) ?></div>
          </div>
        

          <div class="section-title">Rates</div>
          <ul>
            <li class="line">
              <span>Base Rate (<?= htmlspecialchars(ucfirst($rateTypeLower === 'bi-month' ? 'bi-month' : $rateTypeLower)) ?>)</span>
              <span class="amt">₱<?= number_format($rateAmountNum, 2) ?></span>
            </li>
            
            <?php if ($hourlyRate > 0): ?>
              <li class="line"><span>Hourly Rate</span><span class="amt">₱<?= number_format($hourlyRate, 2) ?></span></li>
            <?php endif; ?>
            <?php if ($otRate > 0): ?>
              <li class="line"><span>Overtime Rate</span><span class="amt">₱<?= number_format($otRate, 2) ?></span></li>
            <?php endif; ?>
          </ul>

          <div class="row mt-2">
            <?php if ($hasEarningsLines): ?>
            <div class="col-12 col-md-6">
              <div class="section-title">Earnings</div>
              <ul>
                <?php if ($regHours > 0 && $regAmount > 0): ?>
                  <li class="line">
                    <span>Regular Time (<?= number_format($regHours, 2) ?>h × ₱<?= number_format($hourlyRate, 2) ?>/h)</span>
                    <span class="amt">₱<?= number_format($regAmount, 2) ?></span>
                  </li>
                <?php endif; ?>

                <?php if ($otHours > 0 && $otAmount > 0): ?>
                  <li class="line">
                    <span>Overtime (<?= number_format($otHours, 2) ?>h × ₱<?= number_format($otRate, 2) ?>/h)</span>
                    <span class="amt">₱<?= number_format($otAmount, 2) ?></span>
                  </li>
                <?php endif; ?>

                <?php if ($regularHolidayPay > 0): ?>
                  <li class="line">
                    <span>Regular Holiday</span>
                    <span class="amt">₱<?= number_format($regularHolidayPay, 2) ?></span>
                  </li>
                <?php endif; ?>

                <?php if ($totalDays > 0): ?>
                  <li>Total Days: <?= number_format($totalDays, 2) ?></li>
                <?php endif; ?>

                <?php if ($salary > 0): ?>
                  <li class="line total-line">
                    <span><strong>Gross Salary</strong></span>
                    <span class="amt"><strong>₱<?= number_format($salary, 2) ?></strong></span>
                  </li>
                <?php endif; ?>
              </ul>
            </div>
            <?php endif; ?>

            <?php if ($hasDeductionsLines): ?>
            <div class="col-12 col-md-6">
              <div class="section-title">Deductions</div>
              <ul>
                <?php if ($cash_advance > 0): ?>
                  <li class="line"><span>Cash Advance</span><span class="amt">₱<?= number_format($cash_advance, 2) ?></span></li>
                <?php endif; ?>

                <?php if ($sss > 0): ?>
                  <li class="line"><span>SSS (Gov’t)</span><span class="amt">₱<?= number_format($sss, 2) ?></span></li>
                  <?php if (!empty($gdetail['by']['SSS'])): ?>
                    <ul class="sublist">
                      <?php foreach ($gdetail['by']['SSS'] as $it): ?>
                        <li class="line"><span><?= htmlspecialchars($it->description ?: 'SSS') ?></span><span class="amt">₱<?= number_format((float)$it->amount, 2) ?></span></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                <?php endif; ?>

                <?php if ($philhealth > 0): ?>
                  <li class="line"><span>PHIC (Gov’t)</span><span class="amt">₱<?= number_format($philhealth, 2) ?></span></li>
                  <?php if (!empty($gdetail['by']['PhilHealth'])): ?>
                    <ul class="sublist">
                      <?php foreach ($gdetail['by']['PhilHealth'] as $it): ?>
                        <li class="line"><span><?= htmlspecialchars($it->description ?: 'PhilHealth') ?></span><span class="amt">₱<?= number_format((float)$it->amount, 2) ?></span></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                <?php endif; ?>

                <?php if ($pagibig > 0): ?>
                  <li class="line"><span>Pag-IBIG (Gov’t)</span><span class="amt">₱<?= number_format($pagibig, 2) ?></span></li>
                  <?php if (!empty($gdetail['by']['Pag-IBIG'])): ?>
                    <ul class="sublist">
                      <?php foreach ($gdetail['by']['Pag-IBIG'] as $it): ?>
                        <li class="line"><span><?= htmlspecialchars($it->description ?: 'Pag-IBIG') ?></span><span class="amt">₱<?= number_format((float)$it->amount, 2) ?></span></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                <?php endif; ?>

                <?php if ($loan > 0): ?>
                  <li class="line"><span>Loan</span><span class="amt"></li>
                  <?php if (!empty($ldetail['rows'])): ?>
                    <ul class="sublist">
                      <?php foreach ($ldetail['rows'] as $it): ?>
                        <li class="line"><span><?= htmlspecialchars($it->description ?: 'Loan') ?></span><span class="amt">₱<?= number_format((float)$it->amount, 2) ?></span></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                <?php endif; ?>

                <?php if ($other_deduction > 0): ?>
                  <li class="line"><span>Other Deduction</span><span class="amt">₱<?= number_format($other_deduction, 2) ?></span></li>
                <?php endif; ?>

                <?php if (!empty($odetail['rows'])): ?>
                  <ul class="sublist">
                    <?php foreach ($odetail['rows'] as $it): ?>
                      <li class="line"><span><?= htmlspecialchars($it->description) ?></span><span class="amt">₱<?= number_format((float)$it->amount, 2) ?></span></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <?php if ($total_deduction > 0): ?>
                  <li class="line total-line">
                    <span><strong>Total Deduction</strong></span>
                    <span class="amt"><strong>₱<?= number_format($total_deduction, 2) ?></strong></span>
                  </li>
                <?php endif; ?>
              </ul>
            </div>
            <?php endif; ?>
          </div>

          <?php $netClass = (bccomp((string)$netPay, '0', 2) < 0) ? 'neg' : ''; ?>
<div class="text-right netpay-box">
  <h5 class="mb-2">
    <strong>Net Pay:
      <span class="amt <?= $netClass ?>">₱<?= number_format((float)$netPay, 2) ?></span>
    </strong>
  </h5>
  <button onclick="printPayslip('printablePayslip<?= $ln ?>')" class="btn btn-sm btn-secondary">
    <i class="fas fa-print"></i> Print this payslip
  </button>
</div>


        </div>
      </div>

    </div>
  </div>
</div>



<?php endforeach; ?>
<tr style="background:#f3f3f3; font-weight:600;">
  <td colspan="<?= (int)$totalPrefixCols; ?>" class="text-right">TOTAL</td>
  <td><?= number_format((float)$totalGross, 2) ?></td>
  <?php if ($showCA): ?>
    <td><?= number_format((float)$totalCA, 2) ?></td>
  <?php endif; ?>
  <?php if ($showSSS): ?>
    <td><?= number_format((float)$totalSSS, 2) ?></td>
  <?php endif; ?>
  <?php if ($showPHIC): ?>
    <td><?= number_format((float)$totalPHIC, 2) ?></td>
  <?php endif; ?>
  <?php if ($showPAGIBIG): ?>
  <td><?= number_format((float)$totalPAGIBIG, 2) ?></td>
<?php endif; ?>

  <?php if ($showLoan): ?>
    <td><?= number_format((float)$totalLoan, 2) ?></td>
  <?php endif; ?>
  <?php if ($showOther): ?>
    <td><?= number_format((float)$totalOther, 2) ?></td>
  <?php endif; ?>
  <?php if ($showTotalDeduction): ?>
    <td><?= number_format((float)$totalDeduction, 2) ?></td>
  <?php endif; ?>
<?php $isTotalNeg = (bccomp($totalNet, '0', 2) < 0); ?>
<td class="num <?= $isTotalNeg ? 'neg' : '' ?>">
  <?= number_format((float)$totalNet, 2) ?>
</td>

  <?php if (empty($is_summary)): ?>
    <td colspan="3"></td>
  <?php endif; ?>
</tr>


</tbody>
</table>
  </div>
<div id="print-sliced-perday" style="display:none;">
<?php
  $slices = getPrintSlices($start, $end);

  $hasRegularHoliday = false;
  $hasSpecialHoliday = false;
  foreach ($attendance_data as $row) {
    $d = strtotime($start);
    $E = strtotime($end);
    while ($d <= $E) {
      $cur = date('Y-m-d', $d);
      $raw = $row->reg_hours_per_day[$cur] ?? null;
      if (is_array($raw)) {
        $status = strtolower(preg_replace('/\s+/', '', trim($raw['status'] ?? '')));
        $holidayHours = (float)($raw['holiday_hours'] ?? 0);
        if (strpos($status,'regularho') !== false || strpos($status,'legal') !== false) $hasRegularHoliday = true;
        if (strpos($status,'special')   !== false || $holidayHours > 0)                 $hasSpecialHoliday = true;
        if ($hasRegularHoliday && $hasSpecialHoliday) break 2;
      }
      $d = strtotime('+1 day', $d);
    }
  }
?>

<?php foreach ($slices as $sl): ?>
  <div style="page-break-inside: avoid; margin-bottom: 16px;">
    <h4 style="margin:6px 0 8px;">
      <?= htmlspecialchars($project->projectTitle ?? 'Payroll') ?> — <?= $sl['label'] ?>
    </h4>

    <table class="payroll-table" style="font-size:11px; table-layout:fixed;">
<colgroup>
  <col style="width:28px">
  <col style="width:110px">
  <col style="width:90px">
  <col style="width:60px">
  <col style="width:50px">
  <?php $d=strtotime($sl['start']); $E=strtotime($sl['end']);
    while($d<=$E){ echo '<col style="width:34px"><col style="width:34px">'; $d=strtotime('+1 day',$d); } ?>
  <col style="width:50px"><col style="width:50px"><col style="width:50px">
</colgroup>



<thead>
  <tr>
    <th rowspan="3">L/N</th>
    <th rowspan="3">NAME</th>
    <th rowspan="3">POSITION</th>
    <th rowspan="3">RATE</th>
    <th rowspan="3">Rate / Hour</th>
    <?php for($d=strtotime($sl['start']); $d<=strtotime($sl['end']); $d=strtotime('+1 day',$d)): ?>
      <th colspan="2"><?= date('M d', $d) ?></th>
    <?php endfor; ?>
    <th colspan="3">TOTAL TIME</th>
  </tr>
  <tr>
    <?php for($d=strtotime($sl['start']); $d<=strtotime($sl['end']); $d=strtotime('+1 day',$d)): ?>
      <th colspan="2" class="center"><?= date('D', $d) ?></th>
    <?php endfor; ?>
    <th rowspan="2">Reg.</th>
    <th rowspan="2">O.T</th>
    <th rowspan="2">Days</th>
  </tr>
  <tr>
    <?php for($d=strtotime($sl['start']); $d<=strtotime($sl['end']); $d=strtotime('+1 day',$d)): ?>
      <th>Reg.</th><th>O.T</th>
    <?php endfor; ?>
  </tr>
</thead>



      <tbody>
      <?php $ln=1; foreach ($attendance_data as $row): ?>
        <?php if ($row->rateType === 'Month' || $row->rateType === 'Bi-Month') continue; ?>

        <?php
          $regAmount=0; $otAmount=0; $regMin=0; $otMin=0; $days=0;
          $amtRegHol=0; $amtSpecHol=0;

          $hrBase = function($row) {
            if ($row->rateType === 'Hour')  return (float)$row->rateAmount;
            if ($row->rateType === 'Day')   return ((float)$row->rateAmount) / 8;
            if ($row->rateType === 'Month') return ((float)$row->rateAmount) / 30 / 8;
            if ($row->rateType === 'Bi-Month') return ((float)$row->rateAmount) / 15 / 8;
            return 0;
          };
          $base = $hrBase($row);
        ?>

        <tr>
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
            <?php else: ?>
              ₱<?= number_format($row->rateAmount, 2) ?> / bi-month
            <?php endif; ?>
          </td>

          <?php
          for ($d=strtotime($sl['start']); $d<=strtotime($sl['end']); $d=strtotime('+1 day',$d)) {
            $cur = date('Y-m-d', $d);
            $raw = $row->reg_hours_per_day[$cur] ?? '-';

            $regH = 0; $otH = 0; $holH = 0; $status = ''; $isHoliday=false; $isRegHol=false; $isSpecHol=false;

            if (is_array($raw)) {
              $status = strtolower(preg_replace('/\s+/', '', trim($raw['status'] ?? '')));
              $regH   = (float)($raw['hours'] ?? 0);
              $otH    = (float)($raw['overtime_hours'] ?? 0);
              $holH   = (float)($raw['holiday_hours'] ?? 0);

              if (preg_match('/holiday|regularho|legal|special/i', $status) || $holH > 0) {
                $isHoliday = true;
                if ($holH <= 0 && $regH > 0) { $holH = $regH; $regH = 0; }
                $isRegHol  = (strpos($status,'regularho') !== false || strpos($status,'legal') !== false);
                $isSpecHol = (!$isRegHol);
              }

              if ($isHoliday) {
                echo "<td colspan='2' class='holiday-cell'>";
                echo ($isRegHol ? 'R.Holiday' : 'S.Holiday');
                $parts = [];
                if ($holH > 0) $parts[] = number_format($holH,2) . 'h';
                if ($otH  > 0) $parts[] = number_format($otH,2)  . 'h OT';
                echo "<br>(" . implode(' + ', $parts) . ")</td>";

                if ($isRegHol) { $amtRegHol += 8 * $base; }
                else           { $amtSpecHol += $holH > 0 ? $holH * $base * 0.30 : (8 * $base * 0.30); }

                if ($holH > 0) { $regAmount += $holH * $base; $regMin += $holH * 60; $days += $holH/8; }
                if ($otH  > 0) { $otAmount  += $otH  * $base; $otMin  += $otH  * 60; }
              } else {
                echo '<td class="num">'.displayAmount($regH).'</td>';
                echo '<td class="num">'.displayAmount($otH).'</td>';

                $regAmount += $regH * $base;
                $otAmount  += $otH * $base;
                $regMin    += $regH * 60;
                $otMin     += $otH  * 60;
                if ($regH > 0) $days += $regH/8;
              }
            } elseif (strtolower(trim($raw)) === 'day off') {
              echo "<td colspan='2' class='text-info text-center'>Day Off</td>";
            } elseif (is_numeric($raw)) {
              $dec = (float)$raw;
              $regm = min($dec*60, 480);
              $otm  = max(0, $dec*60 - 480);
              $rH = $regm/60; $oH = $otm/60;
              echo '<td class="num">'.displayAmount($rH).'</td>';
              echo '<td class="num">'.displayAmount($oH).'</td>';

              $regAmount += $rH * $base; $otAmount += $oH * $base;
              $regMin += $regm; $otMin += $otm; if ($rH>0) $days += $rH/8;
            } else {
              echo "<td colspan='2' class='absent text-center'>Absent</td>";
            }
          }

        
          $regHrs = $regMin/60; $otHrs = $otMin/60;
          ?>
        <td class="num"><?= number_format($regHrs, 2) ?></td>
<td class="num"><?= number_format($otHrs, 2) ?></td>
<td class="num"><?= number_format($days, 2) ?></td>

</tr>

      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>


<div style="page-break-inside: avoid; margin-top: 8px;">
  <h4 style="margin:10px 0 8px;">Deductions & Net Pay — <?= date('M d', strtotime($start)) ?> – <?= date('M d, Y', strtotime($end)) ?></h4>
  <table class="payroll-table summary-table" style="font-size:11px;">
    <thead>
      <tr>
        <th style="width:35px;">L/N</th>
        <th style="width:200px;">NAME</th>
        <th style="width:90px;">Gross</th>
        <?php if ($showCA): ?><th style="width:90px;">Cash Advance</th><?php endif; ?>
        <?php if ($showSSS): ?><th style="width:100px;">SSS (Gov’t)</th><?php endif; ?>
        <?php if ($showPHIC): ?><th style="width:100px;">PHIC (Gov’t)</th><?php endif; ?>
        <?php if ($showPAGIBIG): ?><th style="width:100px;">Pag-IBIG (Gov’t)</th><?php endif; ?>
        <?php if ($showLoan): ?><th style="width:100px;">Loan</th><?php endif; ?>
        <?php if ($showOther): ?><th style="width:120px;">Other Deduction</th><?php endif; ?>
        <?php if ($showCA || $showSSS || $showPHIC || $showPAGIBIG || $showLoan || $showOther): ?>
          <th style="width:95px;">Total Deduction</th>
        <?php endif; ?>
        <th style="width:95px;">Net Pay</th>
        <th style="width:120px;">Signature</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $gTot='0.00'; $caTot='0.00'; $sssTot='0.00'; $phicTot='0.00'; $piTot='0.00'; $loanTot='0.00'; $odTot='0.00'; $dedTot='0.00'; $netTot='0.00';
        $ln=1;
        $settingsID = isset($project->settingsID) ? $project->settingsID : null;

        foreach ($attendance_data as $row):
          if ($row->rateType === 'Month' || $row->rateType === 'Bi-Month') continue;

          $p       = computePayroll($row, $start, $end);
          $odetail = fetch_other_deduction_lines($row->personnelID, $start, $end, $settingsID);
          $gdetail = fetch_gov_deduction_lines($row->personnelID, $start, $end, $settingsID);
          $ldetail = fetch_loan_lines($row->personnelID, $start, $end, $settingsID);

          $salary = (float)$p['salary'];

          $sss = (float)((float)$p['sss']        ?: ($gdetail['totals']['SSS']       ?? 0));
          $pi  = (float)((float)$p['pagibig']    ?: ($gdetail['totals']['Pag-IBIG']  ?? 0));
          $ph  = (float)((float)$p['philhealth'] ?: ($gdetail['totals']['PhilHealth']?? 0));
          $loan= (float)((float)$p['loan']       ?: ($ldetail['total']               ?? 0));
          $od  = (float)((float)$p['other_deduction'] ?: ($odetail['total'] ?? 0));
          $ca  = (float)($p['cash_advance'] ?? 0);

          $tded = (float)bcadd(
                    bcadd(bcadd($ca, $sss, 2), bcadd($pi, $ph, 2), 2),
                    bcadd($loan, $od, 2),
                  2);
          $net  = (float)bcsub($salary, $tded, 2);

          $hasAnyPrint = (
              ($p['regTotalMinutes'] + $p['otTotalMinutes']) > 0 ||
              (float)$p['regAmount'] > 0 || (float)$p['otAmount'] > 0 ||
              (float)$p['amountRegularHoliday'] > 0 || (float)$p['amountSpecialHoliday'] > 0 ||
              $salary > 0 ||
              $ca > 0 || $sss > 0 || $pi > 0 || $ph > 0 || $loan > 0 || $od > 0 ||
              $tded > 0 || $net > 0
          );
          if (!$hasAnyPrint) continue;

          $gTot   = bcadd($gTot,   $salary, 2);
          $caTot  = bcadd($caTot,  $ca, 2);
          $sssTot = bcadd($sssTot, $sss, 2);
          $phicTot= bcadd($phicTot,$ph, 2);
          $piTot  = bcadd($piTot,  $pi, 2);
          $loanTot= bcadd($loanTot,$loan, 2);
          $odTot  = bcadd($odTot,  $od, 2);
          $dedTot = bcadd($dedTot, $tded, 2);
          $netTot = (bccomp($net, '0', 2) > 0) ? bcadd($netTot, $net, 2) : $netTot;
      ?>
        <tr>
          <td><?= $ln++ ?></td>
          <td><?= htmlspecialchars($row->last_name . ', ' . $row->first_name) ?></td>
          <td class="num"><?= number_format($salary, 2) ?></td>
          <?php if ($showCA): ?><td class="num"><?= displayAmount($ca) ?></td><?php endif; ?>
          <?php if ($showSSS): ?><td class="num"><?= displayAmount($sss) ?></td><?php endif; ?>
          <?php if ($showPHIC): ?><td class="num"><?= displayAmount($ph) ?></td><?php endif; ?>
          <?php if ($showPAGIBIG): ?><td class="num"><?= displayAmount($pi) ?></td><?php endif; ?>
          <?php if ($showLoan): ?><td class="num"><?= displayAmount($loan) ?></td><?php endif; ?>
          <?php if ($showOther): ?><td class="num"><?= displayAmount($od) ?></td><?php endif; ?>
          <?php if ($showTotalDeduction): ?><td class="num"><?= number_format($tded, 2) ?></td><?php endif; ?>
          <?php $isNeg = (bccomp((string)$net,'0',2) < 0); ?>
          <td class="num <?= $isNeg ? 'neg' : '' ?>"><?= number_format($net, 2) ?></td>
          <td class="signature-cell"></td>
        </tr>
      <?php endforeach; ?>

      <tr style="background:#f3f3f3; font-weight:600;">
        <td colspan="2" class="text-right">TOTAL</td>
        <td class="num"><?= number_format((float)$gTot, 2) ?></td>
        <?php if ($showCA): ?><td class="num"><?= number_format((float)$caTot, 2) ?></td><?php endif; ?>
        <?php if ($showSSS): ?><td class="num"><?= number_format((float)$sssTot, 2) ?></td><?php endif; ?>
        <?php if ($showPHIC): ?><td class="num"><?= number_format((float)$phicTot, 2) ?></td><?php endif; ?>
        <?php if ($showPAGIBIG): ?><td class="num"><?= number_format((float)$piTot, 2) ?></td><?php endif; ?>
        <?php if ($showLoan): ?><td class="num"><?= number_format((float)$loanTot, 2) ?></td><?php endif; ?>
        <?php if ($showOther): ?><td class="num"><?= number_format((float)$odTot, 2) ?></td><?php endif; ?>
        <?php if ($showTotalDeduction): ?><td class="num"><?= number_format((float)$dedTot, 2) ?></td><?php endif; ?>
        <?php $isTNeg = (bccomp((string)$netTot,'0',2) < 0); ?>
        <td class="num <?= $isTNeg ? 'neg' : '' ?>"><?= number_format((float)$netTot, 2) ?></td>
        <td class="signature-cell"></td>
      </tr>
    </tbody>
  </table>
</div>
</div>

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

    $settingsID     = isset($project->settingsID) ? $project->settingsID : null;
    $odetail_print  = fetch_other_deduction_lines($row->personnelID, $start, $end, $settingsID);
    $gdetail_print  = fetch_gov_deduction_lines($row->personnelID, $start, $end, $settingsID);
    $ldetail_print  = fetch_loan_lines($row->personnelID, $start, $end, $settingsID);

    $regHoursRaw     = (float)$pay['regTotalMinutes'] / 60;
    $otHoursRaw      = (float)$pay['otTotalMinutes'] / 60;
    $totalDays       = (float)$pay['totalDays'];

    $salary          = (float)$pay['salary'];
    $cash_advance    = (float)$pay['cash_advance'];
    $loan            = (float)$pay['loan'];
    $other_deduction = (float)$pay['other_deduction'];

    $g_totals_print = $gdetail_print['totals'] ?? ['SSS'=>0.0,'Pag-IBIG'=>0.0,'PhilHealth'=>0.0];

    $sss        = (float)((isset($pay['sss'])        && (float)$pay['sss']        > 0) ? $pay['sss']        : $g_totals_print['SSS']);
    $pagibig    = (float)((isset($pay['pagibig'])    && (float)$pay['pagibig']    > 0) ? $pay['pagibig']    : $g_totals_print['Pag-IBIG']);
    $philhealth = (float)((isset($pay['philhealth']) && (float)$pay['philhealth'] > 0) ? $pay['philhealth'] : $g_totals_print['PhilHealth']);

    if (!isset($pay['loan']) || (float)$pay['loan'] <= 0) {
        $loan = (float)($ldetail_print['total'] ?? 0);
    }
    if (!isset($pay['other_deduction']) || (float)$pay['other_deduction'] <= 0) {
        $other_deduction = (float)($odetail_print['total'] ?? 0);
    }

    $total_deduction = (float)bcadd(
        bcadd(bcadd($cash_advance, $sss, 2), bcadd($pagibig, $philhealth, 2), 2),
        bcadd($loan, $other_deduction, 2),
        2
    );
    $netPay = (float)bcsub($salary, $total_deduction, 2);

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
      $dailyRate  = $rateAmount / 15;
      $hourlyRate = $dailyRate / 8;
    } elseif ($rateTypeLower === 'day') {
      $dailyRate  = $rateAmount;
      $hourlyRate = $rateAmount / 8;
    } else {
      $hourlyRate = $rateAmount;
      $dailyRate  = $hourlyRate * 8;
    }
    $otRate = $hourlyRate * 1.0;

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
    if (!$hasAnyData) continue;
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
          <p>Regular Time: <?= number_format($regHoursRaw, 2) ?> hrs × ₱<?= number_format($hourlyRate, 2) ?>/hr
            = <strong><?= number_format($regAmount, 2) ?></strong></p>
        <?php endif; ?>
        <?php if ($otHoursRaw > 0 && $otAmount > 0): ?>
          <p>Overtime: <?= number_format($otHoursRaw, 2) ?> hrs × ₱<?= number_format($otRate, 2) ?>/hr
            = <strong><?= number_format($otAmount, 2) ?></strong></p>
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
        <?php if ($cash_advance > 0): ?>
          <p>Cash Advance: ₱<?= number_format($cash_advance, 2) ?></p>
        <?php endif; ?>
        <?php if ($sss > 0): ?>
          <p>SSS (Gov’t):</p>
          <?php if (!empty($gdetail_print['by']['SSS'])): ?>
            <div style="margin-left:12px; margin-top:2px;">
              <?php foreach ($gdetail_print['by']['SSS'] as $it): ?>
                <div><?= htmlspecialchars($it->description ?: 'SSS') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($pagibig > 0): ?>
          <p>Pag-IBIG (Gov’t):</p>
          <?php if (!empty($gdetail_print['by']['Pag-IBIG'])): ?>
            <div style="margin-left:12px; margin-top:2px;">
              <?php foreach ($gdetail_print['by']['Pag-IBIG'] as $it): ?>
                <div><?= htmlspecialchars($it->description ?: 'Pag-IBIG') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($philhealth > 0): ?>
          <p>PHIC (Gov’t):</p>
          <?php if (!empty($gdetail_print['by']['PhilHealth'])): ?>
            <div style="margin-left:12px; margin-top:2px;">
              <?php foreach ($gdetail_print['by']['PhilHealth'] as $it): ?>
                <div><?= htmlspecialchars($it->description ?: 'PhilHealth') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($loan > 0): ?>
          <p>Personnel Loan/s:</p>
          <?php if (!empty($ldetail_print['rows'])): ?>
            <div style="margin-left:12px; margin-top:2px;">
              <?php foreach ($ldetail_print['rows'] as $it): ?>
                <div><?= htmlspecialchars($it->description ?: 'Loan') ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($other_deduction > 0): ?>
          <p>Other Deduction:</p>
        <?php endif; ?>
        <?php if (!empty($odetail_print['rows'])): ?>
          <div style="margin-left:12px; margin-top:2px;">
            <?php foreach ($odetail_print['rows'] as $it): ?>
              <div><?= htmlspecialchars($it->description) ?> — ₱<?= number_format((float)$it->amount, 2) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($total_deduction > 0): ?>
          <p><strong>Total Deduction: ₱<?= number_format($total_deduction, 2) ?></strong></p>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php $netClassCard = (bccomp((string)$netPay, '0', 2) < 0) ? 'neg' : ''; ?>
    <div style="margin-top: 10px; text-align: right;">
      <h4><strong>Net Pay:
        <span class="<?= $netClassCard ?>">₱<?= number_format($netPay, 2) ?></span>
      </strong></h4>
    </div>
  </div>
<?php endforeach; ?>

  </div>
</div>
<div id="bottomScroller" class="no-print" aria-hidden="true">
  <div class="sizer"></div>
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
  const root = document.getElementById(elementId);
  if (!root) return;

  const title = (root.querySelector('.modal-title')?.textContent || 'Payslip').trim();
  const card  = root.querySelector('.payslip-card') || root;

  const css = `
  @page { size: A5 portrait; margin: 10mm; }
  html, body { background:#f3f4f6; color:#111827; font: 12px/1.45 "Segoe UI","Calibri","Arial",sans-serif; }
  .slip { width: 120mm; margin: 0 auto; background:#fff; border:1px solid #d1d5db; border-radius:8px; padding:12mm; box-sizing:border-box; }
  .slip-header { display:flex; align-items:center; justify-content:space-between; gap: 8mm; margin-bottom: 8mm; }
  .slip-brand img { height: 28px; }
  .slip-title { font-weight:700; font-size:16px; }
  .slip-date  { color:#6b7280; font-size:11px; }

    /* Transform your existing markup into a tidy layout */
    .section-title {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .02em;
      color: #374151;
      margin: 10px 0 6px 0;
      padding-bottom: 4px;
      border-bottom: 1px solid #e5e7eb;
    }

    /* Info rows (Employee, Position, Period, Printed) */
    .info-row { 
      display: grid;
      grid-template-columns: max-content 1fr;
      gap: 4mm 6mm;
      margin: 2px 0;
    }
    .info-row .label { color:#6b7280; min-width: 90px; }
    .info-row .value { text-align: right; }

    /* Lists -> clean lines with aligned amounts */
    ul { list-style: none; padding: 0; margin: 0; }
    li { padding: 2px 0; }
    .line {
      display: grid;
      grid-template-columns: 1fr max-content;
      align-items: baseline;
      gap: 8mm;
    }
    .amt {
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
      font-weight: 600;
    }
    .sublist {
      margin: 2mm 0 2mm 0;
      padding-left: 3mm;
      border-left: 2px solid #f1f5f9;
    }
    .total-line { 
      border-top: 1px solid #e5e7eb; 
      margin-top: 3mm; padding-top: 3mm; 
    }
    .netpay-box {
      border-top: 2px solid #111827;
      margin-top: 6mm; padding-top: 4mm;
      display: grid; grid-template-columns: 1fr max-content; align-items: baseline;
    }
    .netpay-box .label { font-weight: 700; }
    .netpay-box .value { font-size: 14px; font-weight: 800; }
    
    /* Kill any screen-only UI that might slip in */
    .btn, .modal-backdrop { display: none !important; }
  `;

 const content = `
  <div class="slip">
    <div class="slip-header">
      <div class="slip-brand">
        <img src="<?= base_url('assets/images/pms-logo1.png') ?>" alt="PMS Logo">
      </div>
      <div class="slip-title">${title}</div>
      <div class="slip-date">${new Date().toLocaleDateString()}</div>
    </div>
    ${card.innerHTML}
  </div>
`;


  const win = window.open('', '_blank', 'width=900,height=1000');
  win.document.open();
  win.document.write(`
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <title>${title}</title>
        <style>${css}</style>
      </head>
      <body>${content}</body>
    </html>
  `);
  win.document.close();
  win.focus();
  win.print();
  win.close();
}
</script>

<script>
function printAllPayslips() {
  const originalContent = document.body.innerHTML;
  const payslips = document.getElementById("allPayslips").innerHTML;

  document.body.innerHTML = payslips;

  window.print();

  document.body.innerHTML = originalContent;
  location.reload();
}
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const real = document.getElementById('mainScroll');
  const fake = document.getElementById('bottomScroller');
  if (!real || !fake) return;
  const sizer = fake.querySelector('.sizer');

  let syncing = false;

  function setWidths() {
    sizer.style.width = real.scrollWidth + 'px';
    const needs = real.scrollWidth > real.clientWidth + 1;
    fake.style.display = needs ? 'block' : 'none';
    if (needs) fake.scrollLeft = real.scrollLeft;
  }

  function syncFromReal() {
    if (syncing) return;
    syncing = true;
    fake.scrollLeft = real.scrollLeft;
    syncing = false;
  }

  function syncFromFake() {
    if (syncing) return;
    syncing = true;
    real.scrollLeft = fake.scrollLeft;
    syncing = false;
  }

  real.addEventListener('scroll', syncFromReal, { passive: true });
  fake.addEventListener('scroll', syncFromFake, { passive: true });
  window.addEventListener('resize', setWidths);

  window.addEventListener('load', setWidths);
  setTimeout(setWidths, 50);
})();
</script>

</body>
</html>
