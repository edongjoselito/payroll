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
    <h2>PROJECT: <?= $project[0]->projectTitle ?? 'N/A' ?></h2>
    <p>LOCATION: <?= $project[0]->location ?? 'Unknown' ?></p>
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
        $startDate = strtotime($start);
        $endDate = strtotime($end);
        while ($startDate <= $endDate):
            $curDate = date('Y-m-d', $startDate);
            $log = $row->logs[$curDate] ?? null;

            if ($log) {
                echo $log->attendance_status === 'Present'
                    ? "<td>{$log->workDuration}</td>"
                    : "<td class='absent'>Absent</td>";
            } else {
                echo "<td>-</td>";
            }

            $startDate = strtotime('+1 day', $startDate);
        endwhile;
        ?>
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
