<?php include('includes/head.php'); ?>
<body>
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <h4 class="mb-3">Payroll Logs</h4>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
            <?php endif; ?>

            <div class="card-box table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Location</th>
                            <th>Period</th>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>Payroll Date</th>
                            <th>Total Gross</th>
                            <th>Date Saved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log->project_title ?></td>
                                <td><?= $log->location ?></td>
                                <td><?= $log->period ?></td>
                                <td><?= $log->date_from ?></td>
                                <td><?= $log->date_to ?></td>
                                <td><?= $log->payroll_date ?></td>
                                <td>₱<?= number_format($log->total_gross, 2) ?></td>
                                <td><?= $log->date_saved ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
</div>
</body>
</html>
