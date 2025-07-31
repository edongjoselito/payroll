<!DOCTYPE html>
<html lang="en">
<head>
    <title>Attendance List</title>
    <?php include('includes/head.php'); ?>
    <style>
        .print-btn {
            float: right;
        }
/* Button Enhancements */
.btn {
    padding: 6px 12px !important;
    font-size: 15px;
    border-radius: 6px;
    margin-right: 6px;
    transition: all 0.25s ease-in-out;
}
.btn:last-child {
    margin-right: 0;
}

.btn:hover {
    transform: scale(1.07);
    opacity: 0.95;
}

.btn-info:hover {
    box-shadow: 0 0 8px rgba(23, 162, 184, 0.4);
}
.btn-success:hover {
    box-shadow: 0 0 8px rgba(40, 167, 69, 0.4);
}
.btn-outline-danger:hover,
.btn-danger:hover {
    box-shadow: 0 0 8px rgba(220, 53, 69, 0.4);
}

/* Print/Export Buttons Container */
.page-title .d-flex .btn {
    margin-left: 8px;
}
</style>

    </style>
</head>
<body>
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <h4 class="page-title d-flex justify-content-between">
                    Attendance Logs - <?= $project->projectTitle ?? 'Project' ?>
                    <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-info btn-sm print-btn mr-2" data-toggle="modal" data-target="#printModal">
        <i class="fa fa-print"></i> Print
    </button>
    <a href="<?= base_url('project/export_attendance_csv/' . $settingsID . '?pid=' . $projectID) ?>"
       class="btn btn-success btn-sm" target="_blank">
        <i class="fa fa-file-excel"></i> Export to Excel (CSV)
    </a>
</div>

                    
                </h4>
                <hr>


                <?php if (empty($attendance_logs)): ?>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-center">No attendance logs found for this project.</p>
                        </div>
                    </div>
                    
                <?php else:
                    $grouped = [];
                    foreach ($attendance_logs as $log) {
                        $grouped[$log->date][] = $log;
                    }

                    krsort($grouped);
                    $groupCount = 1;
                    foreach ($grouped as $date => $logs): ?>
                    
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-toggle="collapse" data-target="#group<?= $groupCount ?>">
                                <strong><?= date('F d, Y', strtotime($date)) ?></strong>
                                <form method="post" action="<?= base_url('project/delete_attendance_group') ?>" onsubmit="return confirm('Delete logs for <?= date('F d, Y', strtotime($date)) ?>?')" class="m-0">
                                    <input type="hidden" name="projectID" value="<?= $projectID ?>">
                                    <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
                                    <input type="hidden" name="date" value="<?= $date ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>

                            <div class="collapse" id="group<?= $groupCount ?>">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="thead-light">
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
                                                    <td>
                                                        <?php if (strtolower($log->status) === 'present'): ?>
                                                            <span class="text-success font-weight-bold">Present</span>
                                                        <?php else: ?>
                                                            <span class="text-danger font-weight-bold">Absent</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= number_format($log->work_duration, 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php $groupCount++; endforeach;
                endif; ?>
            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- ✅ Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form target="_blank" action="<?= base_url('project/print_attendance') ?>" method="get" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="printModalLabel">Print Attendance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="projectID" value="<?= $projectID ?>">
        <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
        <div class="form-group">
          <label>Select Date (leave blank to print all):</label>
          <input type="date" name="date" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info"><i class="fa fa-print"></i> Print</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
