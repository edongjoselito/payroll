<!DOCTYPE html>
<html lang="en">
<title>PMS - Attendance</title>

<?php include('includes/head.php'); ?>
<?php $flash = $this->session->flashdata('attendance_exists'); ?>

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
</style>


<body>
     
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
  <?php include('includes/top-nav-bar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <!-- Title and Button -->
        <div class="mb-3">
          <h4 class="page-title">Weekly Attendance</h4>
          <button class="btn btn-info mt-2 shadow-sm" data-toggle="modal" data-target="#generateModal">
            <i class="mdi mdi-calendar-search"></i> Generate Attendance
          </button>
        </div>

        <?php if ($this->session->flashdata('msg')): ?>
          
          <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('msg') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>
<!-- <?php if (isset($flash['projectID'], $flash['from'], $flash['to'])): ?>
  <div class="alert alert-warning">
    <strong>⚠ Attendance already exists for this date range.</strong><br>
    Would you like to view or delete the existing records?

    <div class="mt-2">
      <a href="<?= base_url('WeeklyAttendance/records?project=' . $flash['projectID'] . '&from=' . $flash['from'] . '&to=' . $flash['to']) ?>" class="btn btn-info btn-sm">
        <i class="mdi mdi-eye"></i> View Records
      </a>

      <form action="<?= base_url('WeeklyAttendance/deleteAttendance') ?>" method="post" style="display:inline;">
        <input type="hidden" name="projectID" value="<?= $flash['projectID'] ?>">
        <input type="hidden" name="from" value="<?= $flash['from'] ?>">
        <input type="hidden" name="to" value="<?= $flash['to'] ?>">
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this attendance data?');">
          <i class="mdi mdi-delete"></i> Delete
        </button>
      </form>
    </div>
  </div>
<?php endif; ?> -->


        <div class="card">
          <div class="card-body">

            <!-- Modal: Generate Attendance -->
            <div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="generateModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <form method="post" action="<?= base_url('WeeklyAttendance/generate') ?>">
                  <div class="modal-content bg-white border-0 shadow-sm">
                    <div class="modal-header">
                      <h5 class="modal-title font-weight-bold" id="generateModalLabel">
                        <i class="mdi mdi-calendar-month"></i> Generate Attendance
                      </h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                      </button>
                    </div>

                    <div class="modal-body">
                      <div class="form-group">
                        <label for="project" class="font-weight-bold">Project</label>
                        <select name="project" id="project" class="form-control" required>
                          <option value="" disabled selected>Select Project</option>
                          <?php foreach ($projects as $proj): ?>
                            <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="form-row">
                        <div class="form-group col-md-6">
                          <label for="from" class="font-weight-bold">From</label>
                          <input type="date" name="from" id="from" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                          <label for="to" class="font-weight-bold">To</label>
                          <input type="date" name="to" id="to" class="form-control" required>
                        </div>
                      </div>
                    </div>

                    <div class="modal-footer">
                      <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-check"></i> Generate
                      </button>
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Attendance Table -->
            <?php if (isset($employees)): ?>

              <?php if (isset($project)): ?>
                <div class="alert alert-info mb-3">
                  <strong><i class="mdi mdi-briefcase-check"></i> Selected Project:</strong> <?= htmlspecialchars($project->projectTitle) ?>
                </div>
 <small style="color: #000; font-size: 13px; display: block; line-height: 1.5;">
  ✅ All checkboxes are marked as <strong>Present</strong> by default. 
  <br>❌ <strong>Uncheck a box</strong> to mark as <strong>Absent</strong>.
  <br>🕒 <strong>Work duration</strong> in hours is required per date.
  <br> ✍️ <strong>Hours computation:</strong> 1 = hour , .25 = 15 minutes, .50 = 30 minutes , .75 = 45 minutes
</small>


              <?php endif; ?>

              <form method="post" action="<?= base_url('WeeklyAttendance/save') ?>">
                <input type="hidden" name="projectID" value="<?= $projectID ?>">
                <input type="hidden" name="from" value="<?= $from ?>">
                <input type="hidden" name="to" value="<?= $to ?>">

                <?php foreach ($dates as $date): ?>
                  <input type="hidden" name="dates[]" value="<?= $date ?>">
                <?php endforeach; ?>

                <div class="table-responsive mt-3">
               <table id="attendanceTable" class="table table-bordered table-hover table-striped nowrap" style="width:100%">

                    <thead class="thead-light sticky-top bg-light">
                      <tr>
                        <th>Personnel</th>
                        <?php foreach ($dates as $date): ?>
                          <th class="text-center"><?= date('M d', strtotime($date)) ?><br><small>Status / Hours</small></th>
                        <?php endforeach; ?>
                      </tr>
                    </thead>
                    <tbody>
                      
                    <?php
usort($employees, function($a, $b) {
    return strcasecmp($a->last_name, $b->last_name);
});
foreach ($employees as $emp): ?>

                        <tr>
                          <td><?= $emp->last_name . ', ' . $emp->first_name ?></td>
                          <?php foreach ($dates as $date): ?>
                            <td class="text-center">
                              <input type="checkbox"
                                name="attendance[<?= $emp->personnelID ?>][<?= $date ?>][status]"
                                value="Present" checked>
                              <br>
                           <input type="number"
    name="attendance[<?= $emp->personnelID ?>][<?= $date ?>][hours]"
    placeholder="   hours"
    step="0.25" min="0" max="24"
    style="width: 70px; margin-top: 3px;">


                            </td>
                          <?php endforeach; ?>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>

                <div class="text-right mt-3">
                  <button type="submit" class="btn btn-info">
                    <i class="mdi mdi-content-save"></i> Save Attendance
                  </button>
                </div>
              </form>
            <?php endif; ?>

          </div>
        </div>

      </div>
    </div>
<?php if ($flash): ?>
<!-- Modal -->
<div class="modal fade" id="existingAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="existingAttendanceLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="existingAttendanceLabel">
          ⚠ Attendance Already Exists
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
     <?php
  $fromFormatted = date("F d, Y", strtotime($flash['from']));
  $toFormatted = date("F d, Y", strtotime($flash['to']));
  $projectTitle = $flash['projectTitle'] ?? 'N/A';
?>
<div class="modal-body text-dark">
  <strong>Attendance already exists for this date range:</strong>
  <p>
    📁 <strong>Project:</strong> <?= htmlspecialchars($projectTitle) ?><br>
    📅 <strong>Period:</strong> <?= $fromFormatted ?> to <?= $toFormatted ?>
  </p>
  <small>You can either <b>view</b> or <b>delete</b> this data.</small>
</div>

<div class="modal-footer">
  <a href="<?= base_url('WeeklyAttendance/records?project=' . $flash['projectID'] . '&from=' . $flash['from'] . '&to=' . $flash['to']) ?>" class="btn btn-info">
    <i class="mdi mdi-eye"></i> View Records
  </a>
  <form action="<?= base_url('WeeklyAttendance/deleteAttendance') ?>" method="post" style="display:inline;">
    <input type="hidden" name="projectID" value="<?= $flash['projectID'] ?>">
    <input type="hidden" name="from" value="<?= $flash['from'] ?>">
    <input type="hidden" name="to" value="<?= $flash['to'] ?>">
    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this attendance data?');">
      <i class="mdi mdi-delete"></i> Delete
    </button>
  </form>
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>

    </div>
  </div>
</div>
<?php endif; ?>

    <?php include('includes/footer.php'); ?>
  </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
  $('#generateModal').on('shown.bs.modal', function () {
    $('#from').trigger('focus');
  });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('#attendanceTable td').forEach(function (cell) {
    const checkbox = cell.querySelector('input[type="checkbox"]');
    const input = cell.querySelector('input[name*="[hours]"]'); // Match any input named like [hours]

    if (checkbox && input) {
      toggleState(checkbox, input);

      checkbox.addEventListener('change', function () {
        toggleState(this, input);
      });
    }

    function toggleState(checkbox, input) {
      if (checkbox.checked) {
        input.removeAttribute('readonly');
        input.style.pointerEvents = 'auto';
        input.style.backgroundColor = '';
        input.style.borderColor = '';
      } else {
        input.setAttribute('readonly', 'readonly');
        input.style.pointerEvents = 'none';
        input.style.backgroundColor = '#e9ecef';
        input.style.borderColor = 'red';
        input.value = '';
      }
    }
  });
});
</script>
<script>
  <?php if ($flash): ?>
    $(document).ready(function() {
      $('#existingAttendanceModal').modal('show');
    });
  <?php endif; ?>
</script>



</body>
</html>
