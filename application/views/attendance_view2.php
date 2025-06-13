<?php include('includes/head.php'); ?>
<body>
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid d-flex flex-column" style="min-height: 100vh; padding-bottom: 100px;">

            <!-- Page Header -->
            <div class="page-title-box d-flex justify-content-between align-items-center mb-3">
                <h4 class="page-title">📝 Project Attendance</h4>
                <div>
                    <form method="post" action="<?= base_url('project/save_attendance') ?>" class="d-inline-block" id="attendanceFormTop">
                        <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
                        <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">
                        <button type="submit" class="btn btn-primary btn-sm me-1">
                            <i class="bi bi-save"></i> Save Attendance
                        </button>
                    </form>
                    <a href="<?= base_url('project/project_view') ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-3">
                    <div class="mb-3 fw-semibold">
                        📅 Attendance Date: <strong><?= date('F d, Y') ?></strong>
                    </div>

                    <form method="post" action="<?= base_url('project/save_attendance') ?>">
                        <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
                        <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">

                       <div style="max-height: 70vh; overflow-y: auto; position: relative;">
    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center mb-0 align-middle">
            <thead class="table-primary sticky-top">
                <tr>
                    <th>👤 Personnel</th>
                    <th>📌 Status</th>
                    <th>🕒 Work Hours (Hrs:Mins)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($personnels as $p): ?>
                <tr>
                    <td class="text-start fw-semibold"><?= $p->first_name . ' ' . $p->last_name ?></td>
                    <td>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-success btn-sm">
                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="Present" required> Present
                            </label>
                            <label class="btn btn-outline-danger btn-sm">
                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="Absent"> Absent
                            </label>
                            <label class="btn btn-outline-warning btn-sm">
                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="On Leave"> On Leave
                            </label>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="workDuration[<?= $p->personnelID ?>]" class="form-control form-control-sm text-center" placeholder="e.g. 8:30" required>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-save-fill"></i> Save Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
        <?php include('includes/footer.php'); ?>

</div>

</div>

<!-- Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
