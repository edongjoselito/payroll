<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<style>
    .separated-table {
        margin-bottom: 20px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .separated-table th,
    .separated-table td {
        padding: 6px 10px !important;
        font-size: 14px;
        vertical-align: middle;
    }

    .separated-table th {
        background-color: #f8f9fa;
    }

    .table-section {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
    }

    h4 {
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
        font-size: 20px;
    }

    .form-section-header {
        font-weight: 600;
        font-size: 15px;
    }

    .table td,
    .table th {
        padding: 6px !important;
    }

    .btn-group-toggle .btn {
        padding: 4px 10px;
        font-size: 13px;
    }
</style>

<body>
<div id="wrapper">

    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid pt-2">

            <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php elseif ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>


                <!-- Page Header -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-title-box mb-2">
                            <h4 class="page-title">📝 <?php echo $project[0]->projectTitle; ?></h4>
                        </div>
                    </div>
                </div>

                <!-- Attendance Section -->
                <div class="row table-section">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body p-3">
                                <form method="post" action="<?= base_url('project/save_attendance') ?>" id="attendanceFormTop">
                                    <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
                                    <input type="hidden" name="projectID" value="<?= $projectID ?>">

                                    <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="form-section-header">
                                            📅 Attendance Date: <strong><?= date('F d, Y') ?></strong>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-sm me-1">
                                                <i class="bi bi-save"></i> Save Attendance
                                            </button>
                                            <a href="<?= base_url('project/project_view') ?>" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-arrow-left"></i> Back
                                            </a>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th style="width: 60%;">👤 Personnel</th>
                                                    <th style="width: 40%;">📌 Attendance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                              <?php foreach ($personnels as $p): ?>
                                                <?php
                                                    $saved_status = isset($attendance_records[$p->personnelID]) ? $attendance_records[$p->personnelID] : '';
                                                ?>
                                                <tr>
                                                    <td><?= $p->first_name . ' ' . $p->last_name ?></td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                            <label class="btn btn-outline-success btn-sm <?= $saved_status == 'Present' ? 'active' : '' ?>">
                                                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="Present" <?= $saved_status == 'Present' ? 'checked' : '' ?>> Present
                                                            </label>
                                                            <label class="btn btn-outline-danger btn-sm <?= $saved_status == 'Absent' ? 'active' : '' ?>">
                                                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="Absent" <?= $saved_status == 'Absent' ? 'checked' : '' ?>> Absent
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>

                                                <?php if (empty($personnels)): ?>
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No personnel records found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div> <!-- content -->

        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- JS Resources -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>

</body>
</html>
