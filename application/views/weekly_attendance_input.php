<!DOCTYPE html>
<html lang="en">
<title>PMS - Weekly Attendance</title>

<?php include('includes/head.php'); ?>

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
                <div class="alert alert-light py-1 px-3 mb-0"></div>
                <small class="text-muted">
                  ✅ All checkboxes are marked as <strong>Present</strong> by default. 
                  <br>❌ <strong>Uncheck a box</strong> to mark the personnel as <strong>Absent</strong> for that day.
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
                  <table class="table table-bordered table-hover table-striped nowrap" style="width:100%">
                    <thead class="thead-light sticky-top bg-light">
                      <tr>
                        <th>Personnel</th>
                        <?php foreach ($dates as $date): ?>
                          <th><?= date('M d', strtotime($date)) ?></th>
                        <?php endforeach; ?>
                        <th>Total Hours</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($employees as $emp): ?>
                        <tr>
                          <td><?= $emp->last_name . ', ' . $emp->first_name ?></td>
                          <?php foreach ($dates as $date): ?>
                            <td class="text-center">
                              <input type="checkbox" name="personnel[<?= $emp->personnelID ?>][present][<?= $date ?>]" checked>
                            </td>
                          <?php endforeach; ?>
                          <td>
                            <input type="number" name="personnel[<?= $emp->personnelID ?>][hours]" step="0.1" min="0" class="form-control" required>
                          </td>
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

</body>
</html>
