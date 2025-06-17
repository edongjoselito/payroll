<?php
// payroll_report_view.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 10px;
        }
        .payroll-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .payroll-table th,
        .payroll-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }
        .signature {
            margin-top: 30px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            width: 45%;
            text-align: center;
        }
        .absent {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
<div class="header">
    <h2>PROJECT: <?= $project->projectTitle ?? 'N/A' ?></h2>
    <p>LOCATION: <?= $project->projectLocation ?? 'Unknown' ?></p>
    <p>PERIOD: <?= date('F d, Y', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?></p>
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
        <th rowspan="2">Total Amount</th>
        <th rowspan="2">signature</th>

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

        // Calculate total amount
        if ($row->rateType === 'Hour') {
            $amount = ($totalMinutes / 60) * $row->rateAmount;
        } elseif ($row->rateType === 'Day') {
            $amount = $totalDays * $row->rateAmount;
        } else {
            $amount = 0;
        }
        ?>
        <td><?= $totalTimeFormatted ?></td>
        <td><?= $totalDays ?></td>
        <td><?= number_format($amount, 2) ?></td>
        <td></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="signature">
    <div>
        Prepared by:<br><br>
        <strong>Kimmy T. Aban</strong><br>
        OFC-Admin
    </div>
    <div>
        Checked by:<br><br>
        <strong>Eloisa A. Cabanilla</strong><br>
        Admin/Finance Mngr.
    </div>
</div>
</body>
</html>
