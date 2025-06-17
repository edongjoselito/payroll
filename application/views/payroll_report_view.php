<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<body>
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h4 class="page-title mb-1">Gatekeep Payroll Report</h4>
            <p><strong>Project:</strong> <?= $project[0]->projectTitle ?? '' ?><br>
               <strong>Location:</strong> <?= $project[0]->location ?? '' ?><br>
               <strong>Period:</strong>
               <?php if (!empty($start) && !empty($end)): ?>
                   <?= date('F d, Y', strtotime($start)) ?> - <?= date('F d, Y', strtotime($end)) ?>
               <?php else: ?>
                   <em>No date range selected</em>
               <?php endif; ?>
            </p>

            <?php if (!empty($start) && !empty($end)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center">
                    <thead class="bg-light">
                        <tr>
                            <th rowspan="2">L/N</th>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Position</th>
                            <th rowspan="2">Rate</th>
                            <?php
                            $period = [];
                            $s = strtotime($start);
                            $e = strtotime($end);
                            while ($s <= $e) {
                                $period[] = date('Y-m-d', $s);
                                $s = strtotime('+1 day', $s);
                            }
                            foreach ($period as $date): ?>
                                <th colspan="2"><?= date('M d', strtotime($date)) ?></th>
                            <?php endforeach; ?>
                            <th rowspan="2">Total Days</th>
                            <th rowspan="2">Gross</th>
                            <th colspan="4">Deductions</th>
                            <th rowspan="2">Net Pay</th>
                        </tr>
                        <tr>
                            <?php foreach ($period as $d): ?>
                                <th>Reg</th>
                                <th>OT</th>
                            <?php endforeach; ?>
                            <th>WCA</th>
                            <th>SSS</th>
                            <th>PHIC</th>
                            <th>Hardhat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($personnel_logs as $person):
                            $rate = $person['rate'];
                            $gross = $rate * $person['total_days'];
                            $deductions = ['WCA' => 100, 'SSS' => 150, 'PHIC' => 100, 'Hardhat' => 50];
                            $net = $gross - array_sum($deductions);
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td class="text-left"><?= $person['name'] ?></td>
                            <td><?= $person['position'] ?></td>
                            <td><?= number_format($rate, 2) ?></td>
                            <?php foreach ($period as $day): ?>
                                <?php $log = $person['attendance'][$day] ?? null; ?>
                                <td><?= $log && $log->attendance_status == 'Present' ? '8' : '-' ?></td>
                                <td><?= $log && $log->attendance_status == 'Present' ? '0' : '-' ?></td>
                            <?php endforeach; ?>
                            <td><?= $person['total_days'] ?></td>
                            <td><?= number_format($gross, 2) ?></td>
                            <td><?= $deductions['WCA'] ?></td>
                            <td><?= $deductions['SSS'] ?></td>
                            <td><?= $deductions['PHIC'] ?></td>
                            <td><?= $deductions['Hardhat'] ?></td>
                            <td><?= number_format($net, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-info">Please select a valid date range.</div>
            <?php endif; ?>

            <div class="mt-4 text-right">
                <p>Prepared by: <strong>Kimmy T. Aban</strong> | OFC-Admin</p>
                <p>Checked by: <strong>Eloisa A. Cabanilla</strong> | Admin/Finance Mngr.</p>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>
</body>
</html>
