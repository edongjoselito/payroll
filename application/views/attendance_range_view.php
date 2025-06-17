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

                <!-- Page Header -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="page-title-box d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="page-title mb-1">Payroll Attendance Logs</h4>
                                <div class="text-muted">Project: <strong><?= $project[0]->projectTitle ?? '' ?></strong></div>
                            </div>
                            <a href="<?= base_url('project/project_view') ?>" class="btn btn-secondary btn-sm">← Back to Projects</a>
                        </div>
                        <hr>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Filter by Date Range</strong>
            </div>
            <div class="card-body">
                <form method="get">
                    <input type="hidden" name="pid" value="<?= $projectID ?>">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="start">Start Date</label>
                            <input type="date" name="start" id="start" class="form-control" value="<?= $start ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="end">End Date</label>
                            <input type="date" name="end" id="end" class="form-control" value="<?= $end ?>" required>
                        </div>
                        <div class="form-group col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

                <!-- Attendance Table -->
                <?php if (!empty($attendance_logs)): ?>
              <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="width:100%">
                            <thead class="text-center bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Personnel</th>
                                    <th>Status</th>
                                    <th>Rate Type</th>
                                    <th>Work Duration (hrs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_logs as $log): ?>
                                    <tr class="text-center align-middle <?= $log->attendance_status == 'Absent' ? 'table-danger' : '' ?>">
                                        <td><?= date('F d, Y', strtotime($log->attendance_date)) ?></td>
                                        <td class="text-left"><?= htmlspecialchars($log->first_name . ' ' . $log->last_name) ?></td>
                                        
                                        <td>
                                            <?php if ($log->attendance_status == 'Present'): ?>
                                                <span class="badge badge-success">Present</span>
                                            <?php elseif ($log->attendance_status == 'Absent'): ?>
                                                <span class="badge badge-danger">Absent</span>
                                            <?php elseif ($log->attendance_status == 'Leave'): ?>
                                                <span class="badge badge-info">Leave</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?= htmlspecialchars($log->attendance_status) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
<?= htmlspecialchars($log->workDuration ?? '-') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($start && $end): ?>
                    <div class="alert alert-info text-center">No records found for the selected date range.</div>
                <?php endif; ?>

            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>
