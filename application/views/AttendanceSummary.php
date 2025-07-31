<!DOCTYPE html>
<html lang="en">
<head>
<title>PMS - Attendance Summary</title>
<?php include('includes/head.php'); ?>

<style>
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
.emoji-bounce {
  width: 80px;
  height: 80px;
  animation: bounce 2s infinite;
}
</style>

<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">
</head>

<body>
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
<div class="content">
<div class="container-fluid">

    <div class="page-title-box d-flex justify-content-between align-items-center">
        <h4 class="page-title">Personnel Attendance Summary</h4>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php elseif ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="mb-2">
      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#filterModal">
    <i class="fas fa-clock"></i> Select by Period
</button>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span><strong>All Attendance Records</strong></span>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-toggle="collapse" data-target="#attendanceTable" aria-expanded="true">
                Hide / View Table
            </button>
        </div>
        <div class="collapse show" id="attendanceTable">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>Personnel Name</th>
                                <th>Total Absent</th>
                                <th>Absent Dates</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $hasAbsent = false;
                        foreach ($summary as $row):
                            if ($row->absent_count > 0):
                                $hasAbsent = true;
                        ?>
                            <tr>
                                <td><?= $row->full_name ?></td>
                                <td><?= $row->absent_count ?></td>
                                <td>
                                    <?php foreach (explode(',', $row->absent_dates ?? '') as $date): ?>
                                        <span class="badge badge-light border" style="opacity: 0.9;">
                                            <?= date('M d, Y', strtotime($date)) ?>
                                        </span><br>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php
                            endif;
                        endforeach;
                        ?>

                        <?php if (!$hasAbsent): ?>
                            <tr>
                              <td colspan="3" class="text-center">
    <img src="https://em-content.zobj.net/source/apple/391/hugging-face_1f917.png" alt="Hugging Emoji" class="emoji-bounce"><br>
    <span class="text-muted" style="font-size: 1.2rem;">
        Everyone’s been attending? That’s awesome!
        <img src="https://em-content.zobj.net/source/apple/391/smiling-face-with-smiling-eyes_1f60a.png" alt="Happy Face" style="width: 24px; vertical-align: middle;">
    </span>
</td>

                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<?php include('includes/footer.php'); ?>
</div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="<?= base_url('OtherDeduction/filter_attendance_summary') ?>" method="get">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Absent Records</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="filter_type">Select Period</label>
                    <select class="form-control" name="filter_type" required>
                        <option value="">Select</option>
                        <option value="weekly">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="yearly">This Year</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-sm">Proceed</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- JS -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
