<!DOCTYPE html>
<html lang="en">
<head>
  <title>PMS - Weekly Attendance Input</title>
  <?php include('includes/head.php'); ?>
</head>

<body>
<div id="wrapper">

  <?php include('includes/top-nav-bar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <div class="page-title-box d-flex justify-content-between align-items-center">
          <h4 class="page-title">Weekly Attendance Input</h4>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php elseif ($this->session->flashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <div class="row">
          <!-- Sidebar Date Range Form -->
          <div class="col-md-3">
            <div class="card">
              <div class="card-header bg-primary text-white">Generate Attendance</div>
              <div class="card-body">
                <form method="GET">
                  <div class="form-group mb-3">
                    <label for="from">Date From:</label>
                    <input type="date" name="from" id="from" class="form-control" required value="<?= isset($_GET['from']) ? $_GET['from'] : '' ?>">
                  </div>
                  <div class="form-group mb-3">
                    <label for="to">Date To:</label>
                    <input type="date" name="to" id="to" class="form-control" required value="<?= isset($_GET['to']) ? $_GET['to'] : '' ?>">
                  </div>
                  <button type="submit" class="btn btn-success w-100">Generate</button>
                </form>
              </div>
            </div>
          </div>

          <!-- Attendance Table -->
          <div class="col-md-9">
            <?php if (!empty($dates)): ?>
              <form method="POST" action="<?= base_url('project/save_weekly_attendance') ?>">
                <input type="hidden" name="from" value="<?= $_GET['from'] ?>">
                <input type="hidden" name="to" value="<?= $_GET['to'] ?>">

                <div class="card">
                  <div class="card-header bg-secondary text-white">
                    Attendance: <?= date('F j', strtotime($_GET['from'])) ?> to <?= date('F j, Y', strtotime($_GET['to'])) ?>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                      <thead class="bg-light">
                        <tr>
                          <th>👤 Personnel</th>
                          <?php foreach ($dates as $date): ?>
                            <th><?= date('M d', strtotime($date)) ?></th>
                          <?php endforeach; ?>
                          <th>⏱ Total Hours</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($personnel as $person): ?>
                          <tr>
                        <td><?= $person->last_name ?>, <?= $person->first_name ?> <?= $person->middle_name ?> <?= $person->name_ext ?>


                              <input type="hidden" name="personnelID[]" value="<?= $person->personnelID ?>">
                            </td>
                            <?php foreach ($dates as $date): ?>
                              <td class="text-center">
                                <input type="checkbox" name="attendance[<?= $person->personnelID ?>][<?= $date ?>]" checked>
                              </td>
                            <?php endforeach; ?>
                            <td>
                              <input type="text" class="form-control form-control-sm" name="hours[<?= $person->personnelID ?>]" placeholder="e.g., 40">
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
                  </div>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </div>

      </div> <!-- /.container-fluid -->
    </div> <!-- /.content -->
    <?php include('includes/footer.php'); ?>
  </div> <!-- /.content-page -->

</div> <!-- /#wrapper -->

<!-- JS Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
