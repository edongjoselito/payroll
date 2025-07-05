<?php include('includes/head.php'); ?>
<body>
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

<?php $settingsID = $this->session->userdata('settingsID'); ?>

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
        <th>Action</th> <!-- Add this -->
    </tr>
</thead>

                  <tbody><tbody>
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
     <td>
    <!-- View Button: Opens payroll summary in new tab -->
   <a href="<?= base_url('project/payroll_summary/' . $settingsID . '/' . $log->projectID) ?>?start=<?= $log->date_from ?>&end=<?= $log->date_to ?>" 
   class="btn btn-primary btn-sm" target="_blank">
   View
</a>

    <!-- Delete Button -->
    <a href="<?= base_url('report/delete_log/' . $log->id) ?>" 
       onclick="return confirm('Are you sure you want to delete this log?')" 
       class="btn btn-danger btn-sm">
       Delete
    </a>
</td>

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
<script>
function viewPayroll(url) {
    window.open(url, '_blank', 'width=1200,height=800');
}
</script>

</body>
</html>
