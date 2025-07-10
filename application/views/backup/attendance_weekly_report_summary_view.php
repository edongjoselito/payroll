<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
  <div class="content">
    <div class="container-fluid">
      <div class="page-title-box d-flex justify-content-between align-items-center">
        <h4 class="page-title">🗓 Weekly Attendance Summary</h4>
      </div>

      <div class="card">
        <div class="card-body">
          <p><strong>Period:</strong> <?= date('F d, Y', strtotime($start)) ?> to <?= date('F d, Y', strtotime($end)) ?></p>

          <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm text-center">
              <thead class="thead-light">
                <tr>
                  <th>👤 Personnel</th>
                  <?php foreach ($dates as $d): ?>
                      <th><?= date('M d', strtotime($d)) ?></th>
                  <?php endforeach; ?>
                  <th>⏱ Total Hours</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($personnels as $person): ?>
                <tr>
                  <td class="text-start"><?= $person->last_name ?>, <?= $person->first_name ?></td>
                  <?php foreach ($dates as $d): ?>
                    <?php
                      $key = $person->personnelID . '_' . $d;
                      $status = isset($attendance[$key]) ? $attendance[$key]->attendance_status : 'Absent';
                    ?>
                    <td><?= $status === 'Present' ? '✔️' : '❌' ?></td>
                  <?php endforeach; ?>
                  <td><?php foreach ($personnels as $person): ?>
    <tr>
        <td class="text-start"><?= $person->last_name ?>, <?= $person->first_name ?></td>
        <?php foreach ($dates as $d): ?>
            <?php
                $key = $person->personnelID . '_' . $d;
                $status = isset($attendance[$key]) ? $attendance[$key]->attendance_status : 'Absent';
            ?>
            <td><?= $status === 'Present' ? '✔️' : '❌' ?></td>
        <?php endforeach; ?>
        <td>
            <?php
                $pid = $person->personnelID;
                $salaryData = $this->session->flashdata('weekly_salary') ?? [];
                $duration = $durations[$pid] ?? '-';
                echo $duration;

                // Show salary if available
                if (isset($salaryData[$pid])) {
                    echo "<br><small><strong>₱" . number_format($salaryData[$pid]['salary'], 2) . "</strong> (" . $salaryData[$pid]['rate'] . "/hr)</small>";
                }
            ?>
        </td>
    </tr>
<?php endforeach; ?>
</td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($personnels)): ?>
                <tr><td colspan="<?= count($dates) + 2 ?>" class="text-muted text-center">No personnel found.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="text-end mt-3">
            <a href="<?= base_url('project/attendance_list/' . $settingsID . '?pid=' . $projectID) ?>" class="btn btn-secondary">Back to Attendance List</a>
          </div>
        </div>
      </div>

    </div>
  </div>
  <?php include('includes/footer.php'); ?>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>