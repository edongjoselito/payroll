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
            <th>L/N</th>
            <th>NAME</th>
            <th>POSITION</th>
            <th>RATE</th>
            <th>Rate / Hour</th>
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
