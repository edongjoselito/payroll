<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Report</title>
    <?php include('includes/head.php'); ?>
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
        th {
            background-color: #d9d9d9;
            font-weight: bold;
        }
        .absent {
            background-color: #f4cccc;
            color: #000;
        }
        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }
        .signature div {
            width: 45%;
            text-align: center;
        }
        .signature strong {
            margin-top: 10px;
            display: block;
            font-size: 14px;
        }
   @media print {
    body {
        margin: 0;
        font-size: 12px;
        -webkit-print-color-adjust: exact !important;
    }

    @page {
        size: 8.5in 13in landscape;
        margin: 1cm;
    }

    .btn, .modal, .no-print {
        display: none !important;
    }

    .payroll-table th,
    .payroll-table td {
        font-size: 11px;
        padding: 5px;
    }

    .signature {
        page-break-inside: avoid;
    }
}

/* Formal table headers and clean font */
table {
    font-family: 'Calibri', 'Arial', sans-serif;
    font-size: 12px;
    border-collapse: collapse;
    width: 100%;
}
th, td {
    border: 1px solid #000;
    text-align: center;
    vertical-align: middle;
    padding: 6px 8px;
}
th {
    background-color: #f1f1f1;
}

/* Payslip Modal Styling */
.modal-content {
    width: 100%;
    max-width: 600px;
    margin: auto;
    font-family: 'Calibri', sans-serif;
    font-size: 12px;
    padding: 20px;
    border: 1px solid #333;
}

.modal-body {
    padding: 10px;
}

.modal-body ul {
    padding-left: 20px;
}

.modal-header h5 {
    font-size: 16px;
}

/* Payslip Print Layout (A5 portrait - 1/2 A4) */
@media print {
    .modal-content {
        width: 100%;
        max-width: 5.8in;
        height: 8.3in;
        margin: auto;
        border: none;
        box-shadow: none;
        font-size: 12px;
    }

    .modal-header,
    .modal-footer {
        display: none;
    }
}

    </style>
</head>
<body>

<div class="header">
    <h2>PROJECT: <?= $project->projectTitle ?? 'N/A' ?></h2>
    <p>LOCATION: <?= $project->projectLocation ?? 'Unknown' ?></p>
    <p>PERIOD: <?= date('F d, Y', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></p>
    <?php if (!empty($_GET['rateType'])): ?>
        <p>SALARY TYPE: Per <?= htmlspecialchars($_GET['rateType']) ?></p>
    <?php endif; ?>
</div>

<table class="payroll-table">
  <thead>
<tr>
    <th rowspan="2">L/N</th>
    <th rowspan="2">NAME</th>
    <th rowspan="2">POSITION</th>
    <th rowspan="2">RATE</th>
    <th rowspan="2">Rate / Hour</th>

    <?php
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    while ($startDate <= $endDate):
        echo '<th colspan="2">' . date('M d', $startDate) . '</th>';
        $startDate = strtotime('+1 day', $startDate);
    endwhile;
    ?>

    <th colspan="3">TOTAL TIME</th>
    <th rowspan="2">Gross Salary</th>
    <th rowspan="2">W.C.A</th>
    <th rowspan="2">HARDHAT</th>
    <th rowspan="2">SSS Loan</th>
    <?php $monthYear = date('F Y', strtotime($start)); ?>
    <th rowspan="2">SSS (<?= $monthYear ?>)</th>
    <th rowspan="2">PHIC (<?= $monthYear ?>)</th>
    <th rowspan="2">Pondo</th>
    <th rowspan="2">HARDWARE</th>
    <th rowspan="2">Safety Shoes</th>
    <th rowspan="2">Loan</th>
    <th rowspan="2">Total Deduction</th>
    <th rowspan="2">Net Pay</th>
    <th rowspan="2" colspan="3">Signature</th>
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
    <th>Reg.</th>
    <th>O.T</th>
    <th>Days</th>
</tr>
</thead>


    <tbody>
    <?php $ln = 1; foreach ($attendance_data as $row): ?>
     <?php
$regTotalMinutes = 0;
$otTotalMinutes = 0;
?>
<tr>
    <td><?= $ln++ ?></td>
    <td><?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?></td>
    <td><?= htmlspecialchars($row->position) ?></td>
    <td><?= $row->rateType === 'Day' ? number_format($row->rateAmount, 2) : '' ?></td>
    <td><?= $row->rateType === 'Hour' ? number_format($row->rateAmount, 2) : '' ?></td>

    <?php
    $totalMinutes = 0;
    $totalDays = 0;
    $startDate = strtotime($start);
    $endDate = strtotime($end);

    while ($startDate <= $endDate):
        $curDate = date('Y-m-d', $startDate);
        $log = $row->logs[$curDate] ?? null;

        if ($log && $log->attendance_status === 'Present') {
            $parts = explode(':', $log->workDuration);
            $hours = isset($parts[0]) ? (int)$parts[0] : 0;
            $minutes = isset($parts[1]) ? (int)$parts[1] : 0;
            $workMinutes = ($hours * 60) + $minutes;
            $totalMinutes += $workMinutes;
            $totalDays++;

            // Cap regular at 480 minutes (8 hours)
            $regMinutes = min(480, $workMinutes);
            $otMinutes = max(0, $workMinutes - 480);

            $regTotalMinutes += $regMinutes;
            $otTotalMinutes += $otMinutes;

            $regH = floor($regMinutes / 60);
            $regM = $regMinutes % 60;
            $otH = floor($otMinutes / 60);
            $otM = $otMinutes % 60;

            $regDisplay = $regM > 0 ? "{$regH}:" . str_pad($regM, 2, '0', STR_PAD_LEFT) : "{$regH}";
            $otDisplay = $otM > 0 ? "{$otH}:" . str_pad($otM, 2, '0', STR_PAD_LEFT) : "{$otH}";

            echo "<td>{$regDisplay}</td><td>{$otDisplay}</td>";
        } elseif ($log && $log->attendance_status === 'Absent') {
            echo "<td class='absent' colspan='2'>Absent</td>";
        } else {
            echo "<td colspan='2'>-</td>";
        }

        $startDate = strtotime('+1 day', $startDate);
    endwhile;

    $regH = floor($regTotalMinutes / 60);
    $regM = $regTotalMinutes % 60;
    $otH = floor($otTotalMinutes / 60);
    $otM = $otTotalMinutes % 60;

    $regTotalFormatted = $regM > 0 ? "{$regH}:" . str_pad($regM, 2, '0', STR_PAD_LEFT) : "{$regH}";
    $otTotalFormatted  = $otM > 0 ? "{$otH}:" . str_pad($otM, 2, '0', STR_PAD_LEFT) : "{$otH}";

    $totalHours = floor($totalMinutes / 60);
    $remainingMinutes = $totalMinutes % 60;
    $totalTimeFormatted = $totalHours . ':' . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT);

    if ($row->rateType === 'Hour') {
        $salary = ($totalMinutes / 60) * $row->rateAmount;
    } elseif ($row->rateType === 'Day') {
        $salary = $totalDays * $row->rateAmount;
    } elseif ($row->rateType === 'Month') {
        $salary = ($row->rateAmount / 22) * $totalDays;
    } else {
        $salary = 0;
    }

    $cash_advance = $row->ca_cashadvance ?? 0;
    $hardhat = $row->ca_hardhat ?? 0;
    $pondo = $row->ca_pondo ?? 0;
    $hardware = $row->ca_hardware ?? 0;
    $safety = $row->ca_safety_shoes ?? 0;
    $sss = $row->sss ?? 0;
    $pagibig = $row->pagibig ?? 0;
    $philhealth = $row->philhealth ?? 0;
    $loan = $row->loan ?? 0;

    $total_deduction = $cash_advance + $hardhat + $pondo + $hardware + $safety + $sss + $pagibig + $philhealth + $loan;
    $netPay = $salary - $total_deduction;
    ?>

    <td><?= $regTotalFormatted ?></td>
    <td><?= $otTotalFormatted ?></td>
    <td><?= $totalDays ?></td>

    <td><?= number_format($salary, 2) ?></td>
    <td><?= number_format($cash_advance, 2) ?></td>
    <td><?= number_format($hardhat, 2) ?></td>
    <td>0.00</td>
    <td><?= number_format($sss, 2) ?></td>
    <td><?= number_format($philhealth, 2) ?></td>
    <td><?= number_format($pondo, 2) ?></td>
    <td><?= number_format($hardware, 2) ?></td>
    <td><?= number_format($safety, 2) ?></td>
    <td><?= number_format($loan, 2) ?></td>
    <td><?= number_format($total_deduction, 2) ?></td>
    <td>
    <a href="#" class="btn btn-link btn-sm" data-toggle="modal" data-target="#payslipModal<?= $ln ?>">
        <?= number_format($netPay, 2) ?>
    </a>
</td>

    <td></td>
</tr>


    <?php endforeach; ?>
    </tbody>
</table>

<div class="signature">
    <div><br><br><br>
        Prepared by:<br><br><br>
        <strong>Kimmy T. Aban</strong><br>
        OFC-Admin
    </div>
    <div><br><br><br>
        Checked by:<br><br><br>
        <strong>Eloisa A. Cabanilla</strong><br>
        Admin/Finance Mngr.
    </div>
</div>

<div class="modal fade" id="payslipModal<?= $ln ?>" tabindex="-1" role="dialog" aria-labelledby="payslipLabel<?= $ln ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" id="printablePayslip<?= $ln ?>">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="payslipLabel<?= $ln ?>">Payslip - <?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?></h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Employee:</strong> <?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?></p>
            <p><strong>Position:</strong> <?= htmlspecialchars($row->position) ?></p>
            <p><strong>Rate:</strong> <?= number_format($row->rateAmount, 2) ?> / <?= $row->rateType ?></p>
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
              <li>Regular Time: <?= $regTotalFormatted ?> hrs</li>
              <li>Overtime: <?= $otTotalFormatted ?> hrs</li>
              <li>Total Days: <?= $totalDays ?></li>
              <li><strong>Gross Salary: <?= number_format($salary, 2) ?></strong></li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>Deductions</h6>
            <ul class="list-unstyled">
              <li>Cash Advance: <?= number_format($cash_advance, 2) ?></li>
              <li>Hardhat: <?= number_format($hardhat, 2) ?></li>
              <li>SSS: <?= number_format($sss, 2) ?></li>
              <li>PHIC: <?= number_format($philhealth, 2) ?></li>
              <li>Pag-IBIG: <?= number_format($pagibig, 2) ?></li>
              <li>Pondo: <?= number_format($pondo, 2) ?></li>
              <li>Hardware: <?= number_format($hardware, 2) ?></li>
              <li>Safety Shoes: <?= number_format($safety, 2) ?></li>
              <li>Loan: <?= number_format($loan, 2) ?></li>
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
<script>
  function printPayslip(elementId) {
    var printContents = document.getElementById(elementId).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // to restore modal functionality
  }
</script>


<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>