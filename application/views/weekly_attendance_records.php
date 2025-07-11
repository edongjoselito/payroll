<!DOCTYPE html>
<html lang="en">
  <?php
function formatHoursAndMinutes($decimal) {
    $hours = floor($decimal);
    $minutes = round(($decimal - $hours) * 60);
    return "{$hours} hr" . ($hours != 1 ? "s" : "") . " and {$minutes} mins";
}
?>

<title>PMS - Attendance Records</title>
<?php include('includes/head.php'); ?>
<style>
  thead th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 2;
  }
td, th {
  vertical-align: middle !important;
  text-align: center;
}

td {
  min-height: 40px;
  height: 40px;
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
    font-size: 11px;
    border-collapse: collapse !important;
  }

  th, td {
    border: 1px solid black !important;
  }

  body {
    margin: 20px;
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


  <div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="font-weight-bold text-primary">
      <i class="mdi mdi-calendar-check-outline mr-2"></i> Attendance Records
    </h3>
    <p class="text-dark mb-0">Saved logs by project and date range</p>
  </div>
  <button class="btn btn-success" data-toggle="modal" data-target="#filterModal">
    <i class="mdi mdi-filter-outline"></i> View Records
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
              <?php
// Sort attendances array by name alphabetically
uasort($attendances, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="mb-1">
      <i class="mdi mdi-office-building text-info mr-1"></i> 
      <strong><?= $project->projectTitle ?></strong>
    </h5>
    <p class="text-dark small mb-0">
      <?= date('F j, Y', strtotime($from)) ?> to <?= date('F j, Y', strtotime($to)) ?>
    </p>
  </div>
</div>

  
  <div class="text-end">
    <button class="btn btn-outline-secondary btn-sm mr-1" onclick="window.print()">
      <i class="mdi mdi-printer"></i> Print
    </button>

    <form action="<?= base_url('WeeklyAttendance/deleteAttendance') ?>" method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete all attendance records for this date range?')">
      <input type="hidden" name="projectID" value="<?= $projectID ?>">
      <input type="hidden" name="from" value="<?= $from ?>">
      <input type="hidden" name="to" value="<?= $to ?>">
      <button type="submit" class="btn btn-danger btn-sm">
        <i class="mdi mdi-delete"></i> Delete
      </button>
    </form>
  </div>
</div>



             <div class="table-responsive">
  <table class="table table-hover table-bordered table-sm text-center">
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
          <td class="text-left font-weight-bold"><?= $person['name'] ?></td>
          <?php foreach ($dates as $d): ?>
            <?php
              $status = $person['dates'][$d] ?? 'Absent';
              $color = ($status === 'Present') ? 'success' : 'danger';
            ?>
            <td class="text-<?= $color ?>">
              <?= $status ?>
              <?php if ($status === 'Present' && isset($person['hours'][$d])): ?>
                <br><small>(<?= number_format($person['hours'][$d], 2) ?> hrs)</small>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td><strong><?= formatHoursAndMinutes($hours[$pid] ?? 0) ?></strong></td>
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
<?php if ($this->session->flashdata('error')): ?>
<!-- Error Modal -->
<!-- Bootstrap Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="errorModalLabel">
          <i class="mdi mdi-alert-circle-outline mr-2"></i> Error
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body text-dark">
        <div class="d-flex align-items-center">
          <i class="mdi mdi-alert mr-2 text-warning" style="font-size: 24px;"></i>
          <div>
            <strong>Some selected dates have no data.</strong><br>
            Only dates with existing attendance records will be shown.
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="mdi mdi-close"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>


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
<script>
  $(document).ready(function() {
    <?php if ($this->session->flashdata('error')): ?>
      $('#errorModal').modal('show');
    <?php endif; ?>
  });
</script>


</body>
</html>
