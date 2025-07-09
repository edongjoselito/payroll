<!DOCTYPE html>
<html lang="en">
<title>PMS - Attendance Records</title>
<?php include('includes/head.php'); ?>
<style>
  thead th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 2;
  }

  th:first-child,
  td:first-child {
    position: sticky;
    left: 0;
    background: #f8f9fa; 
    z-index: 1;
    box-shadow: 2px 0 5px rgba(0,0,0,0.05); 
  }
   @media print {
    .btn, .modal, .modal-backdrop, .navbar, .sidebar, .page-title, .alert, .modal-open body {
      display: none !important;
    }

    table {
      font-size: 12px;
    }

    th, td {
      border: 1px solid #000 !important;
    }
  }
</style>
<body>
<div id="wrapper">
  <?php include('includes/top-nav-bar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

  <div class="mb-3">
  <h4 class="page-title">View Saved Attendance</h4>
  <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#filterModal">
    <i class="mdi mdi-filter-outline"></i> View Saved Attendance
  </button>
</div>



        <!-- Filter Modal -->
        <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post">
              <div class="modal-content">
                <div class="modal-header">

                  <h5 class="modal-title" id="filterModalLabel"><i class="mdi mdi-calendar-month"></i> Select Attendance Records</h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>

                <div class="modal-body">
                  <div class="form-group">
                    <label for="project">Project</label>
                    <select name="project" id="project" class="form-control" required>
                      <option value="">Select Project</option>
                      <?php foreach ($projects as $proj): ?>
                        <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="from">From</label>
                      <input type="date" name="from" id="from" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="to">To</label>
                      <input type="date" name="to" id="to" class="form-control" required>
                    </div>
                  </div>
                </div>

                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="mdi mdi-eye"></i> View
                  </button>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Display Records -->
        <div class="card">
          <div class="card-body">

            <?php if (!empty($attendances)): ?>
              <div class="alert alert-info">
                <strong><i class="mdi mdi-briefcase-check"></i> Project:</strong> <?= $project->projectTitle ?>
              </div>
<div class="mb-3 text-end">
  <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
    <i class="mdi mdi-printer"></i> Print Attendance
  </button>
</div>
<!-- Warning Message -->
<div class="alert alert-warning">
  <strong>Note:</strong> Delete Attendance to avoid duplication of hours or do not generate again with the same date range.
</div>

<!-- Delete Button Form -->
<form action="<?= base_url('WeeklyAttendance/deleteAttendance') ?>" method="post" onsubmit="return confirm('Are you sure you want to delete all attendance records for this date range?')">
  <input type="hidden" name="projectID" value="<?= $projectID ?>">
  <input type="hidden" name="from" value="<?= $from ?>">
  <input type="hidden" name="to" value="<?= $to ?>">

  <button type="submit" class="btn btn-danger btn-sm mb-3">
    <i class="mdi mdi-delete"></i> Delete Attendance
  </button>
</form>

              <div class="table-responsive">
                <table class="table table-bordered table-striped nowrap" style="width: 100%;">
                  <thead class="thead-light">
                    <tr>
                      <th>Personnel</th>
                      <?php foreach ($dates as $d): ?>
                        <th><?= date('M d', strtotime($d)) ?></th>
                      <?php endforeach; ?>
                      <th>Total Hours</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($attendances as $pid => $person): ?>
                      <tr>
                        <td><?= $person['name'] ?></td>
                        <?php foreach ($dates as $d): ?>
                       <?php
  $status = isset($person['dates'][$d]) ? $person['dates'][$d] : 'Absent';
  $isAbsent = $status !== 'Present';
?>
<td class="text-center <?= $isAbsent ? 'bg-danger text-white font-weight-bold' : '' ?>">
  <?= $status === 'Present' ? '✔' : '✘' ?>
</td>

                        <?php endforeach; ?>
                   <td><?= isset($hours[$pid]) ? number_format($hours[$pid], 2) : '0.00' ?></td>

                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
               
              </div>
            <?php elseif (isset($project)): ?>
              <div class="alert alert-warning">
                <i class="mdi mdi-alert-circle-outline"></i>
                No attendance records found for this project and date range.
              </div>
            <?php endif; ?>

          </div>
        </div>

      </div>
    </div>
    <?php include('includes/footer.php'); ?>
  </div>
</div>

<!-- Bootstrap + App JS -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<!-- Focus field when modal opens -->
<script>
  $('#filterModal').on('shown.bs.modal', function () {
    $('#project').focus();
  });
</script>

</body>
</html>
