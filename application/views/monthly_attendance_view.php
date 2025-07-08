<!DOCTYPE html>
<html lang="en">
<title>PMS - Monthly Attendance & Payroll</title>
<?php include('includes/head.php'); ?>

<body>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Monthly Attendance & Payroll</h4>
                    <form method="get" class="form-inline">
                        <input type="month" name="month" value="<?= $month ?>" class="form-control mr-2" />
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </form>
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

                <div class="card">
                    <div class="card-body">
                        <h5 class="page-title">Payroll Summary for <?= date('F Y', strtotime($month)) ?></h5>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Personnel</th>
                                        <th>Days Present</th>
                                        <th>Total Salary (₱)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                           <tbody>
<?php if (empty($salaries)): ?>
    <tr><td colspan="5" class="text-center">No attendance records found.</td></tr>
<?php else: ?>
    <?php $i = 1; foreach ($salaries as $index => $row): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $row->fullname ?></td>
            <td><?= $row->present_days ?></td>
            <td>₱<?= number_format($row->total_salary, 2) ?></td>
            <td>
                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#payslipModal<?= $index ?>">
                    Payslip
                </button>
            </td>
        </tr>

        <!-- Modal per personnel -->
        <div class="modal fade" id="payslipModal<?= $index ?>" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Payslip - <?= $row->fullname ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
           <div class="modal-body">
    <p><strong>Total Days Worked:</strong> <?= $row->present_days ?></p>
    <p><strong>Total Hours Worked:</strong> <?= $row->total_hours ?></p>
    <p><strong>Rate per Hour:</strong> ₱<?= number_format($row->per_hour, 2) ?></p>
    <hr>
    <p><strong>1st Cutoff (1–15):</strong> ₱<?= number_format($row->first_half, 2) ?></p>
    <p><strong>2nd Cutoff (16–<?= date('t', strtotime($month)) ?>):</strong> ₱<?= number_format($row->second_half, 2) ?></p>
    <hr>
    <h5><strong>Total Salary:</strong> ₱<?= number_format($row->total_salary, 2) ?></h5>
</div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>

                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- DataTable Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>