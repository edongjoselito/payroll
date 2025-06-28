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
            th, .absent {
                -webkit-print-color-adjust: exact;
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
            echo '<th>' . date('M d', $startDate) . '</th>';
            $startDate = strtotime('+1 day', $startDate);
        endwhile;
        ?>
        <th colspan="2">TOTAL TIME/DAYS</th>
        <th>SSS</th>
        <th>Pag-IBIG</th>
        <th>PhilHealth</th>
        <th>Cash Advance</th>
        <th>Loan</th>
        <th>Total Deduction</th>
        <th>Gross Salary</th>
        <th>Net Pay</th>
        <th rowspan="2">Signature</th>
    </tr>
    <tr>
        <?php
        $startDate = strtotime($start);
        $endDate = strtotime($end);
        while ($startDate <= $endDate):
            echo '<th>' . date('l', $startDate) . '</th>';
            $startDate = strtotime('+1 day', $startDate);
        endwhile;
        ?>
        <th>Time</th>
        <th>Days</th>
        <th colspan="7"></th>
    </tr>
    </thead>

    <tbody>
    <?php $ln = 1; foreach ($attendance_data as $row): ?>
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
                    $totalMinutes += ($hours * 60) + $minutes;
                    $totalDays++;
                    echo "<td>{$log->workDuration}</td>";
                } elseif ($log && $log->attendance_status === 'Absent') {
                    echo "<td class='absent'>Absent</td>";
                } else {
                    echo "<td>-</td>";
                }

                $startDate = strtotime('+1 day', $startDate);
            endwhile;

            $totalHours = floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;
            $totalTimeFormatted = $totalHours . ':' . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT);

            // Salary computation
            if ($row->rateType === 'Hour') {
                $salary = ($totalMinutes / 60) * $row->rateAmount;
            } elseif ($row->rateType === 'Day') {
                $salary = $totalDays * $row->rateAmount;
            } elseif ($row->rateType === 'Month') {
                $salary = ($row->rateAmount / 22) * $totalDays;
            } else {
                $salary = 0;
            }

            // Deductions
            $ca = $row->cash_advance ?? 0;
            $sss = $row->sss ?? 0;
            $pagibig = $row->pagibig ?? 0;
            $philhealth = $row->philhealth ?? 0;

           $loan = $personnel_loans[$row->personnelID] ?? 0;
             $total_deduction = $ca + $sss + $pagibig + $philhealth + $loan;
            $netPay = $salary - $total_deduction;

            ?>

            <td><?= $totalTimeFormatted ?></td>
            <td><?= $totalDays ?></td>
          <td><?= number_format($sss, 2) ?></td>
<td><?= number_format($pagibig, 2) ?></td>
<td><?= number_format($philhealth, 2) ?></td>
<td><?= number_format($ca, 2) ?></td>
<?php
$loanDisplay = number_format($loan, 2);

?>
<td><?= $loanDisplay ?></td>

<td><?= number_format($total_deduction, 2) ?></td>
<td><?= number_format($salary, 2) ?></td>
<td><?= number_format($netPay, 2) ?></td>
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

</body>
</html>
