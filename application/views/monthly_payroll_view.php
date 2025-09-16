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

   if (strcasecmp($row->rateType, 'Hour') === 0) {
    $base = (float)$row->rateAmount;          // per-hour as is
} else {
    // Treat Day, Month, Bi-Month as PER-DAY -> convert to per-hour
    $base = ((float)$row->rateAmount) / 8.0;  // per-hour
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

    // --- EXTRA GUARD: exclude any gov-looking lines that slipped into type='Others' ---
    // Matches: SSS, PAG-IBIG, Pagibig, HDMF, PhilHealth, PHIC, etc. (case-insensitive)
    $govPattern = '/\b(SSS|PAG\s*[-]?\s*IBIG|PAGIBIG|HDMF|PHIL\s*HEALTH|PHILHEALTH|PHIC)\b/i';

    $cleanRows = [];
    $total = 0.0;
    foreach ($rows as $r) {
        $desc = (string)($r->description ?? '');
        if (preg_match($govPattern, $desc)) {
            // skip anything that looks like a government deduction
            continue;
        }
        $cleanRows[] = $r;
        $total += (float)$r->amount;
    }

    return $cache[$key] = ['rows' => $cleanRows, 'total' => $total];
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
function pick_other_amount($legacy, array $odetail, array $gdetail): float {
    $other_calc = (float)($odetail['total'] ?? 0.0);
    if ($other_calc > 0) return $other_calc;

    $legacy = (float)$legacy;

    // Compare against each gov total with a small tolerance
    $govs = [
        (float)($gdetail['totals']['SSS']        ?? 0.0),
        (float)($gdetail['totals']['PhilHealth'] ?? 0.0),
        (float)($gdetail['totals']['Pag-IBIG']   ?? 0.0),
    ];
    foreach ($govs as $g) {
        if (abs($legacy - $g) < 0.005) return 0.0; // treat as duplicate gov, not "Others"
    }
    return $legacy;
}
function getPrintSlices($start, $end) {
    $startTs = strtotime($start);
    $endTs   = strtotime($end);
    if ($startTs === false || $endTs === false || $startTs > $endTs) return [];

    // cursor at first of the start month
    $monthCur = strtotime(date('Y-m-01', $startTs));
    $lastMon  = strtotime(date('Y-m-01', $endTs));

    $out = [];
    while ($monthCur <= $lastMon) {
        $y = (int)date('Y', $monthCur);
        $m = (int)date('m', $monthCur);
        $eom = cal_days_in_month(CAL_GREGORIAN, $m, $y);

        $ranges = [
            ["$y-$m-01", "$y-$m-10"],
            ["$y-$m-11", "$y-$m-20"],
            ["$y-$m-21", "$y-$m-$eom"],
        ];

        foreach ($ranges as [$rs, $re]) {
            $s = max(strtotime($rs), $startTs);
            $e = min(strtotime($re), $endTs);
            if ($s <= $e) {
                $out[] = [
                    'label' => date('M d', $s) . ' – ' . date('M d, Y', $e),
                    'start' => date('Y-m-d', $s),
                    'end'   => date('Y-m-d', $e),
                ];
            }
        }
        $monthCur = strtotime('+1 month', $monthCur);
    }
    return $out;
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

<style>
  /* Show/hide by media */
  @media screen { .print-only { display: none !important; } }
  @media print  { .screen-only { display: none !important; } .print-only { display: block !important; } }

  @media print {
    /* One-page fit (A4 landscape is safest) */
    @page { size: A4 landscape; margin: 8mm; }

    /* Tight, fixed layout so widths don't jump */
    .payroll-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: 10.5px;            /* ↑ a little so “upper were too small” */
    }
    .payroll-table th,
    .payroll-table td {
      padding: 1px 3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Column widths (used via <colgroup>) */
    .col-ln     { width: 24px; }
    .col-name   { width: 150px; }  /* <— tighten here if needed (e.g. 135px) */
    .col-pos    { width: 90px; }
    .col-rate   { width: 80px; }
    .col-ratehr { width: 64px; }
    .col-day    { width: 36px; }   /* applies to each Reg/OT day cell */
    .col-time   { width: 60px; }   /* Reg/OT/Days (totals) */

    /* Slice title compact */
    .slice-title { margin: 4px 0 3px; font-weight: 700; font-size: 12px; }

    /* Slight zoom to help one-page fit; tweak 0.88 ↔ 0.92 if needed */
    #printSliced { zoom: 0.90; }

    /* Firefox fallback for zoom */
    @supports (-moz-appearance: none) {
      #printSliced {
        transform: scale(0.90);
        transform-origin: top left;
        width: 111%;
      }
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
$showCA = $showSSS = $showPHIC = $showPAGIBIG = false;
$showLoan = $showOther = false;

$settingsID = isset($project->settingsID) ? $project->settingsID : null;

/* 1) Legacy field scan (> 0 only) */
foreach ($attendance_data as $row) {
    if (!empty($row->ca_cashadvance) && (float)$row->ca_cashadvance > 0) $showCA = true;
    if (!empty($row->gov_sss)        && (float)$row->gov_sss        > 0) $showSSS = true;
    if (!empty($row->gov_philhealth) && (float)$row->gov_philhealth > 0) $showPHIC = true;
    if (!empty($row->gov_pagibig)    && (float)$row->gov_pagibig    > 0) $showPAGIBIG = true;
}

/* 2) Backfill gov flags from detailed lines if needed */
if (!$showSSS || !$showPHIC || !$showPAGIBIG) {
    foreach ($attendance_data as $r0) {
        $g0 = fetch_gov_deduction_lines($r0->personnelID, $start, $end, $settingsID);
        if (!$showSSS     && (float)($g0['totals']['SSS']        ?? 0) > 0) $showSSS = true;
        if (!$showPHIC    && (float)($g0['totals']['PhilHealth'] ?? 0) > 0) $showPHIC = true;
        if (!$showPAGIBIG && (float)($g0['totals']['Pag-IBIG']   ?? 0) > 0) $showPAGIBIG = true;
        if ($showSSS && $showPHIC && $showPAGIBIG) break;
    }
}

/* 3) Strict detection for Loan/Other (totals > 0 only) */
foreach ($attendance_data as $r0) {
    $legacyLoan  = (float)($r0->loan ?? 0);
    $ld0         = fetch_loan_lines($r0->personnelID, $start, $end, $settingsID);
    $loanTotal   = (float)($ld0['total'] ?? 0);
    if ($legacyLoan > 0 || $loanTotal > 0) $showLoan = true;

    $legacyOther = (float)($r0->other_deduction ?? 0);
    $od0         = fetch_other_deduction_lines($r0->personnelID, $start, $end, $settingsID);
    $otherTotal  = (float)($od0['total'] ?? 0);
    if ($legacyOther > 0 || $otherTotal > 0) $showOther = true;

    if ($showLoan && $showOther) break;
}

/* 4) Column visibility for LOAN column ONLY */
$showLoanCol = $showLoan;

/* 5) Make Total Deduction show if ANY row has a positive sum of deductions */
$showTotalDeduction = ($showCA || $showSSS || $showPHIC || $showPAGIBIG || $showLoanCol || $showOther);
if (!$showTotalDeduction) {
    foreach ($attendance_data as $r0) {
        $cash_advance = (float)($r0->ca_cashadvance ?? 0);

        $g = fetch_gov_deduction_lines($r0->personnelID, $start, $end, $settingsID);
        $sss        = (float)($r0->gov_sss        ?? 0); $sss        = $sss        > 0 ? $sss        : (float)($g['totals']['SSS']        ?? 0);
        $philhealth = (float)($r0->gov_philhealth ?? 0); $philhealth = $philhealth > 0 ? $philhealth : (float)($g['totals']['PhilHealth'] ?? 0);
        $pagibig    = (float)($r0->gov_pagibig    ?? 0); $pagibig    = $pagibig    > 0 ? $pagibig    : (float)($g['totals']['Pag-IBIG']   ?? 0);

        $ld = fetch_loan_lines($r0->personnelID, $start, $end, $settingsID);
        $loan = max((float)($r0->loan ?? 0), (float)($ld['total'] ?? 0));

        $od = fetch_other_deduction_lines($r0->personnelID, $start, $end, $settingsID);
        $other = max((float)($r0->other_deduction ?? 0), (float)($od['total'] ?? 0));

        $rowTotalDed = $cash_advance + $sss + $philhealth + $pagibig + $loan + $other;
        if ($rowTotalDed > 0) { $showTotalDeduction = true; break; }
    }
}
?>




<div class="screen-only" id="unslicedPayroll">
<table class="payroll-table">
<colgroup>
  <col class="col-ln">
  <col class="col-name">
  <col class="col-pos">
  <col class="col-rate">
  <col class="col-ratehr">
  <?php
    $__d = strtotime($start); $__e = strtotime($end);
    while ($__d !== false && $__e !== false && $__d <= $__e):
  ?>
    <col class="col-day"><col class="col-day">
  <?php $__d = strtotime('+1 day', $__d); endwhile; ?>
  <col class="col-time"><col class="col-time"><col class="col-time">
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

<?php if ($showLoanCol): ?>
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
    $dateColumnCount += 2; // 2 columns per day (Reg. & OT)
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
$perHour = (strcasecmp($row->rateType, 'Hour') === 0)
    ? (float)$row->rateAmount
    : ((float)$row->rateAmount) / 8.0;
?>
    <td><?= $ln++ ?></td>
<?php $fullName = $row->last_name . ', ' . $row->first_name; ?>
<td><span title="<?= htmlspecialchars($fullName) ?>"><?= htmlspecialchars($fullName) ?></span></td>

    <td><?= htmlspecialchars($row->position) ?></td>
<td>
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
<td><?= number_format($perHour, 2) ?></td>


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
$base = $perHour; // use the unified per-hour computed once

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

     $base = $perHour; // use the unified per-hour computed once


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
$salary = bcadd(
    bcadd($regAmount, $otAmount, 2),
    bcadd($amountRegularHoliday, $amountSpecialHoliday, 2),
    2
);

$settingsID = isset($project->settingsID) ? $project->settingsID : null;

$odetail = fetch_other_deduction_lines($row->personnelID, $start, $end, $settingsID);
$gdetail = fetch_gov_deduction_lines($row->personnelID, $start, $end, $settingsID);
$ldetail = fetch_loan_lines($row->personnelID, $start, $end, $settingsID);

$g_by_type = $gdetail['by'];
$g_totals  = $gdetail['totals'];

$cash_advance = (float) ($row->ca_cashadvance ?? 0);

$sss_legacy   = (float) ($row->gov_sss ?? 0);
$phi_legacy   = (float) ($row->gov_philhealth ?? 0);
$pag_legacy   = (float) ($row->gov_pagibig ?? 0);

$sss        = $sss_legacy   > 0 ? $sss_legacy   : (float) ($g_totals['SSS']        ?? 0);
$philhealth = $phi_legacy   > 0 ? $phi_legacy   : (float) ($g_totals['PhilHealth'] ?? 0);
$pagibig    = $pag_legacy   > 0 ? $pag_legacy   : (float) ($g_totals['Pag-IBIG']   ?? 0);

$loan_legacy       = (float) ($row->loan ?? 0);
$loan_calc         = (float) ($ldetail['total'] ?? 0);
$loan_for_calc     = $loan_legacy > 0 ? $loan_legacy : $loan_calc;
$loan_for_display  = !empty($ldetail['rows']) ? $loan_calc : $loan_for_calc;

$other_legacy = (float)($row->other_deduction ?? 0);
$other_calc         = (float) ($odetail['total'] ?? 0);
$other_for_calc = pick_other_amount($other_legacy, $odetail, $gdetail);
$other_for_display = $other_for_calc;

$total_deduction = bcadd(
    bcadd(
        bcadd((string)$cash_advance, (string)$sss,        2),
        bcadd((string)$pagibig,      (string)$philhealth, 2),
        2
    ),
    bcadd((string)$loan_for_calc, (string)$other_for_calc, 2),
    2
);

$netPay = bcsub($salary, $total_deduction, 2);

$totalGross     = bcadd($totalGross,     $salary,         2);
$totalCA        = bcadd($totalCA,        (string)$cash_advance,   2);
$totalSSS       = bcadd($totalSSS,       (string)$sss,            2);
$totalPHIC      = bcadd($totalPHIC,      (string)$philhealth,     2);
$totalPAGIBIG   = bcadd($totalPAGIBIG,   (string)$pagibig,        2);
$totalLoan      = bcadd($totalLoan,      (string)$loan_for_calc,  2);
$totalOther     = bcadd($totalOther,     (string)$other_for_calc, 2);
$totalDeduction = bcadd($totalDeduction, $total_deduction,        2);
$totalNet       = bcadd($totalNet,       $netPay,                 2);

if (bccomp($netPay, '0', 2) > 0) {
    $totalPayroll = bcadd($totalPayroll, $netPay, 2);
}

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
      <?= displayAmount($loan_for_display) ?>
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
      <?= displayAmount($other_for_display) ?>
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

      if (in_array($rateTypeLower, ['month','bi-month','bi-monthly','bimonth','bi-month '], true)) {
  $dailyRate  = (float)$rateAmountNum;       // treat as per-day
  $hourlyRate = $dailyRate / 8.0;
} elseif ($rateTypeLower === 'day') {
  $dailyRate  = (float)$rateAmountNum;       // per-day
  $hourlyRate = $dailyRate / 8.0;
} else { // 'hour'
  $hourlyRate = (float)$rateAmountNum;       // per-hour
  $dailyRate  = $hourlyRate * 8.0;
}


        $otRate = $hourlyRate * 1.0;

       $regHours = (float)($row->reg ?? 0);
$otHours  = (float)($row->ot ?? 0);


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

          <!-- Top info -->
        
          <div class="info-row">
            <div class="label">Position</div>
            <div class="value"><?= htmlspecialchars($row->position) ?></div>
          </div>
          <div class="info-row">
            <div class="label">Period</div>
            <div class="value"><?= date('F d', strtotime($start)) ?> – <?= date('F d, Y', strtotime($end)) ?></div>
          </div>
        

          <!-- Rate block -->
          <div class="section-title">Rates</div>
          <ul>
            <li class="line">
              <span>Base Rate (<?= htmlspecialchars(ucfirst($rateTypeLower === 'bi-month' ? 'bi-month' : $rateTypeLower)) ?>)</span>
              <span class="amt">₱<?= number_format($rateAmountNum, 2) ?></span>
            </li>
            <?php if ($dailyRate > 0): ?>
              <li class="line"><span>Daily Rate</span><span class="amt">₱<?= number_format($dailyRate, 2) ?></span></li>
            <?php endif; ?>
            <?php if ($hourlyRate > 0): ?>
              <li class="line"><span>Hourly Rate</span><span class="amt">₱<?= number_format($hourlyRate, 2) ?></span></li>
            <?php endif; ?>
            <?php if ($otRate > 0): ?>
              <li class="line"><span>Overtime Rate</span><span class="amt">₱<?= number_format($otRate, 2) ?></span></li>
            <?php endif; ?>
          </ul>

          <!-- Earnings & Deductions -->
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
  <li class="line"><span>Loan</span><span class="amt">₱<?= number_format($loan, 2) ?></span></li>
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

          <?php if ($netPay > 0): ?>
            <div class="text-right netpay-box">
              <h5 class="mb-2"><strong>Net Pay: <span class="amt">₱<?= number_format($netPay, 2) ?></span></strong></h5>
              <button onclick="printPayslip('printablePayslip<?= $ln ?>')" class="btn btn-sm btn-secondary">
                <i class="fas fa-print"></i> Print this payslip
              </button>
            </div>
          <?php endif; ?>

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

<?php if ($showLoanCol): ?>
    <td><?= number_format((float)$totalLoan, 2) ?></td>
  <?php endif; ?>
  <?php if ($showOther): ?>
    <td><?= number_format((float)$totalOther, 2) ?></td>
  <?php endif; ?>
  <?php if ($showTotalDeduction): ?>
    <td><?= number_format((float)$totalDeduction, 2) ?></td>
  <?php endif; ?>
  <td><?= number_format((float)$totalNet, 2) ?></td>
  <?php if (empty($is_summary)): ?>
    <td colspan="3"></td>
  <?php endif; ?>
</tr>


</tbody>
</table>
</div><!-- /#unslicedPayroll -->
<!-- ===== PRINT-ONLY SLICED VIEW (template-style) ===== -->
<div class="print-only" id="printSliced">
<?php
$__origStart = $start;
$__origEnd   = $end;

$slices = getPrintSlices($start, $end);
/* fallback: if no slices returned, use the full range */
if (empty($slices)) { $slices = [['start' => $start, 'end' => $end]]; }

$__sliceIndex = 0;
foreach ($slices as $__slice) {
    $__sliceIndex++;

    // ✅ define per-slice range with safe fallbacks
    $ps = $__slice['start'] ?? $start;
    $pe = $__slice['end']   ?? $end;

    // (optional ultra-guard: skip if still bad)
    if (empty($ps) || empty($pe) || strtotime($ps) === false || strtotime($pe) === false) {
        continue;
    }

    $projName = $project->projectTitle ?? '';
    ?>
    <h5 class="slice-title">
      <?= htmlspecialchars($projName) ?> — <?= date('M d', strtotime($ps)) ?> – <?= date('M d, Y', strtotime($pe)) ?>
    </h5>

    <table class="payroll-table">
      <colgroup>
        <col class="col-ln">
        <col class="col-name">
        <col class="col-pos">
        <col class="col-rate">
        <col class="col-ratehr">
        <?php
        $__d = strtotime($ps); $__e = strtotime($pe);
        while ($__d !== false && $__e !== false && $__d <= $__e): ?>
          <col class="col-day"><col class="col-day">
        <?php $__d = strtotime('+1 day', $__d); endwhile; ?>
        <col class="col-time"><col class="col-time"><col class="col-time">
      </colgroup>


  <thead>
    <tr>
      <th rowspan="3">L/N</th>
      <th rowspan="3">NAME</th>
      <th rowspan="3">POSITION</th>
      <th rowspan="3">RATE</th>
      <th rowspan="3">Rate / Hour</th>
      <?php
      $d = strtotime($ps); $e = strtotime($pe);
      while ($d !== false && $e !== false && $d <= $e): ?>
        <th colspan="2"><?= date('M d', $d) ?></th>
      <?php $d = strtotime('+1 day', $d); endwhile; ?>
      <th colspan="3">TOTAL TIME</th>
    </tr>
    <tr>
      <?php
      $d = strtotime($ps); $e = strtotime($pe);
      while ($d !== false && $e !== false && $d <= $e): ?>
        <th colspan="2" class="center"><?= date('D', $d) ?></th>
      <?php $d = strtotime('+1 day', $d); endwhile; ?>
      <th rowspan="2">Reg.</th>
      <th rowspan="2">O.T</th>
      <th rowspan="2">Days</th>
    </tr>
    <tr>
      <?php
      $d = strtotime($ps); $e = strtotime($pe);
      while ($d !== false && $e !== false && $d <= $e): ?>
        <th>Reg.</th><th>O.T</th>
      <?php $d = strtotime('+1 day', $d); endwhile; ?>
    </tr>
  </thead>

  <tbody>
  <?php
  $ln = 1;
  foreach ($attendance_data as $row):
      // per-hour rate (display only)
      $perHour = (strcasecmp($row->rateType, 'Hour') === 0)
        ? (float)$row->rateAmount
        : ((float)$row->rateAmount) / 8.0;

      // slice totals per employee
      $regTotalMin = 0;
      $otTotalMin  = 0;
      $daysTotal   = 0;
  ?>
    <tr>
      <td><?= $ln++ ?></td>
      <?php $fullName = $row->last_name . ', ' . $row->first_name; ?>
      <td><span title="<?= htmlspecialchars($fullName) ?>"><?= htmlspecialchars($fullName) ?></span></td>
      <td><?= htmlspecialchars($row->position) ?></td>
      <td>
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
      <td><?= number_format($perHour, 2) ?></td>

      <?php
      $d = strtotime($ps); $e = strtotime($pe);
      while ($d !== false && $e !== false && $d <= $e):
          $curDate = date('Y-m-d', $d);
          $raw = $row->reg_hours_per_day[$curDate] ?? '-';

          $regH = 0.0; $otH = 0.0;

          if (is_array($raw)) {
              $status = strtolower(trim((string)($raw['status'] ?? '')));
              $regH   = (float)($raw['hours'] ?? 0);
              $otH    = (float)($raw['overtime_hours'] ?? 0);

              if ($status === 'day off' || $status === 'dayoff') {
                  echo "<td colspan='2' class='text-info font-bold text-center'>Day Off</td>";
              } elseif (in_array($status, ['absent', 'absentee'])) {
                  if ($regH <= 0 && $otH <= 0) {
                      echo "<td colspan='2' class='absent text-center' style='background:#f8d7da;color:red;'>Absent</td>";
                  } else {
                      echo "<td class='text-danger text-center font-weight-bold'>A</td>";
                      echo "<td>" . number_format($otH, 2) . "</td>";
                  }
              } else {
                  echo '<td class="num">' . (($regH == 0.0) ? '––' : number_format($regH, 2)) . '</td>';
                  echo '<td class="num">' . (($otH  == 0.0) ? '––' : number_format($otH,  2)) . '</td>';
              }
          } elseif (is_numeric($raw)) {
              $dec    = (float)$raw;
              $regMin = min($dec * 60, 480);
              $otMin  = max(0, ($dec * 60) - 480);
              $regH   = $regMin / 60;
              $otH    = $otMin  / 60;
              echo '<td class="num">' . (($regH == 0.0) ? '––' : number_format($regH, 2)) . '</td>';
              echo '<td class="num">' . (($otH  == 0.0) ? '––' : number_format($otH,  2)) . '</td>';
          } else {
              echo "<td colspan='2' class='absent text-center' style='background:#f8d7da;color:red;'>Absent</td>";
          }

          // accumulate totals
          $regTotalMin += $regH * 60;
          $otTotalMin  += $otH  * 60;
          if ($regH > 0) $daysTotal += $regH / 8.0;

          $d = strtotime('+1 day', $d);
      endwhile;
      ?>

      <td><?= number_format($regTotalMin / 60, 2) ?></td>
      <td><?= number_format($otTotalMin  / 60, 2) ?></td>
      <td><?= number_format($daysTotal, 2) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>


    <!-- <div class="page-break"></div> -->
<?php } // end foreach slice ?>

<?php
// ===== Print summary for FULL original period (Gross/Net only) =====
$start = $__origStart; $end = $__origEnd;
?>
<h5 class="slice-title">Deductions &amp; Net Pay — <?= date('M d', strtotime($start)) ?> – <?= date('M d, Y', strtotime($end)) ?></h5>

<table class="payroll-table">
  <colgroup>
    <col class="col-ln">
    <col class="col-name">
    <col style="width: 95px">
    <col style="width: 95px">
    <?php if (empty($is_summary)): ?><col style="width: 130px"><?php endif; ?>
  </colgroup>
  <thead>

    <tr>
      <th style="width:28px">L/N</th>
      <th>NAME</th>
      <th style="width:120px">Gross</th>
      <th style="width:120px">Net Pay</th>
      <?php if (empty($is_summary)): ?><th style="width:180px">Signature</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php
    $ln = 1; $totalGross = '0'; $totalNet = '0';
    foreach ($attendance_data as $row):
        $p = computePayroll($row, $start, $end);
        $gross = (string)($p['salary'] ?? '0');
        $net   = (string)($p['netPay'] ?? '0');
        $totalGross = bcadd($totalGross, $gross, 2);
        $totalNet   = bcadd($totalNet,   $net,   2);
    ?>
      <tr>
        <td><?= $ln++ ?></td>
<?php $fullName = $row->last_name . ', ' . $row->first_name; ?>
<td><span title="<?= htmlspecialchars($fullName) ?>"><?= htmlspecialchars($fullName) ?></span></td>
        <td><?= number_format((float)$gross, 2) ?></td>
        <td><?= number_format((float)$net,   2) ?></td>
        <?php if (empty($is_summary)): ?><td></td><?php endif; ?>
      </tr>
    <?php endforeach; ?>
    <tr style="background:#f3f3f3; font-weight:600;">
      <td colspan="2" class="text-right">TOTAL</td>
      <td><?= number_format((float)$totalGross, 2) ?></td>
      <td><?= number_format((float)$totalNet,   2) ?></td>
      <?php if (empty($is_summary)): ?><td></td><?php endif; ?>
    </tr>
  </tbody>
</table>

</div>
<!-- ===== END PRINT-ONLY SLICED VIEW ===== -->

<!-- === PRINTABLE ALL PAYSLIPS SECTION (hidden by default) === -->
<div id="allPayslips" class="no-print-payroll d-none">
  <div id="print-all-payslips-container">
<?php foreach ($attendance_data as $ln => $row): ?>
  <?php if (!in_array($row->rateType, ['Month', 'Bi-Month'])) continue; ?>
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
$other_deduction = pick_other_amount(($pay['other_deduction'] ?? 0), $odetail_print, $gdetail_print);

    $g_totals_print = $gdetail_print['totals'] ?? ['SSS'=>0.0,'Pag-IBIG'=>0.0,'PhilHealth'=>0.0];

    $sss        = (float)((isset($pay['sss'])        && (float)$pay['sss']        > 0) ? $pay['sss']        : $g_totals_print['SSS']);
    $pagibig    = (float)((isset($pay['pagibig'])    && (float)$pay['pagibig']    > 0) ? $pay['pagibig']    : $g_totals_print['Pag-IBIG']);
    $philhealth = (float)((isset($pay['philhealth']) && (float)$pay['philhealth'] > 0) ? $pay['philhealth'] : $g_totals_print['PhilHealth']);

    if (!isset($pay['loan']) || (float)$pay['loan'] <= 0) {
        $loan = (float)($ldetail_print['total'] ?? 0);
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

   if (in_array($rateTypeLower, ['month','bi-month','bi-monthly','bimonth','bi-month '], true)) {
  $dailyRate  = (float)$rateAmount;      // per-day
  $hourlyRate = $dailyRate / 8.0;
} elseif ($rateTypeLower === 'day') {
  $dailyRate  = (float)$rateAmount;      // per-day
  $hourlyRate = $dailyRate / 8.0;
} else { // hour
  $hourlyRate = (float)$rateAmount;      // per-hour
  $dailyRate  = $hourlyRate * 8.0;
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

    <?php if ($netPay > 0): ?>
    <div style="margin-top: 10px; text-align: right;">
      <h4><strong>Net Pay: ₱<?= number_format($netPay, 2) ?></strong></h4>
    </div>
    <?php endif; ?>
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
    /* Page setup — A5 portrait, tweak to A4 by changing size */
    @page { size: A5 portrait; margin: 10mm; }
    html, body {
      background: #f3f4f6;
      color: #111827;
      font: 12px/1.45 "Segoe UI","Calibri","Arial",sans-serif;
    }

    .slip {
      width: 120mm;  
      margin: 0 auto;
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 12mm;
      box-sizing: border-box;
    }

    /* Header */
    .slip-header {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 8mm;
    }
    .slip-title { font-weight: 700; font-size: 16px; }
    .slip-date  { color: #6b7280; font-size: 11px; }

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
    // Match proxy width to real content width
    sizer.style.width = real.scrollWidth + 'px';
    // Show/hide proxy if not needed
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

  // Wire up events
  real.addEventListener('scroll', syncFromReal, { passive: true });
  fake.addEventListener('scroll', syncFromFake, { passive: true });
  window.addEventListener('resize', setWidths);

  // Initial pass (after layout)
  window.addEventListener('load', setWidths);
  // In case fonts/layout shift shortly after load
  setTimeout(setWidths, 50);
})();
</script>

</body>
</html>
