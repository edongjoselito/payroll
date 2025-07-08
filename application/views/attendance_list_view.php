<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<style>
    .table-sm td,
    .table-sm th {
        padding: 0.4rem !important;
        font-size: 13px;
        vertical-align: middle;
    }

    .table-danger td {
        background-color: #f8d7da !important;
    }

    .card-header {
        cursor: pointer;
    }
</style>

<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
<!-- Page Header -->
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h4 class="page-title mb-1">Attendance Logs</h4>
               <div class="text-muted small">Project: <strong><?= $project->projectTitle ?? '' ?></strong></div>

            </div>
            <a href="<?= base_url('project/project_view') ?>" class="btn btn-secondary btn-sm">
                 Back to Projects
            </a>
        </div>
        <hr class="mt-0">
    </div>
</div>

                    <!-- Attendance Records -->
                    <?php if (empty($attendance_logs)): ?>
                        <div class="alert alert-info text-center">No attendance records found.</div>
                    <?php else: ?>

                        <?php
                        // Group logs by attendance_date
                        $grouped_logs = [];
                        foreach ($attendance_logs as $log) {
                            $grouped_logs[$log->attendance_date][] = $log;
                        }
                        ?>

                        <?php foreach ($grouped_logs as $date => $logs): ?>
                            <div class="card mb-2">
                                <div class="card-header" data-toggle="collapse" data-target="#collapse<?= md5($date) ?>" aria-expanded="false">
                                    <h5 class="mb-0">
                                        <?= date('F d, Y', strtotime($date)) ?>
                                        <span class="float-right"><i class="mdi mdi-chevron-down"></i></span>
                                    </h5>
                                </div>
                                <div id="collapse<?= md5($date) ?>" class="collapse">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm table-hover mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Personnel</th>
                                                        <th>Status</th>
                                                        <th>Work Duration (hrs)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($logs as $log): ?>
                                                        <tr class="<?= $log->attendance_status == 'Absent' ? 'table-danger' : '' ?>">
                                                            <td><?= htmlspecialchars($log->first_name . ' ' . $log->last_name) ?></td>
                                                            <td><?= htmlspecialchars($log->attendance_status) ?></td>
                                                            <td><?= htmlspecialchars($log->workDuration) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div> <!-- container-fluid -->
            </div> <!-- content -->

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
        // Auto-expand latest group if needed
        document.addEventListener("DOMContentLoaded", function () {
            const collapseEls = document.querySelectorAll('.card .collapse');
            if (collapseEls.length > 0) {
                collapseEls[0].classList.add('show');
            }
        });
    </script>
</body>

</html>
