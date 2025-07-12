<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Attendance Logs</title>
    <style>
        @media print {
            .no-print { display: none; }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .date-group {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .status-present {
            color: green;
            font-weight: bold;
        }

        .status-absent {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right; margin-bottom:20px;">
        <button onclick="window.print()">🖨 Print</button>
    </div>

    <h2>Attendance Logs - <?= $project->projectTitle ?? 'Project' ?></h2>

    <?php if (empty($attendance_logs)): ?>
        <p style="text-align:center;">No attendance logs found for this project.</p>
    <?php else:
        $grouped = [];
        foreach ($attendance_logs as $log) {
            $grouped[$log->date][] = $log;
        }
        ksort($grouped);
    ?>

        <?php foreach ($grouped as $date => $logs): ?>
            <div class="date-group">
                <strong><?= date('F d, Y', strtotime($date)) ?></strong>
                <table>
                    <thead>
                        <tr>
                            <th>Personnel</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Work Duration (hrs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        usort($logs, function($a, $b) {
                            return strcmp($a->first_name, $b->first_name);
                        });
                        foreach ($logs as $log): ?>
                            <tr>
                                <td><?= ucwords($log->first_name . ' ' . $log->last_name) ?></td>
                                <td><?= date('F d, Y', strtotime($log->date)) ?></td>
                                <td class="<?= strtolower($log->status) === 'present' ? 'status-present' : 'status-absent' ?>">
                                    <?= ucfirst($log->status) ?>
                                </td>
                                <td><?= number_format($log->work_duration, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
