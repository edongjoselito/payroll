<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<body>

<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">🗓 Weekly Attendance Input</h4>
                    <span class="badge badge-light">Period: <?= date('F d, Y', strtotime($start)) ?> to <?= date('F d, Y', strtotime($end)) ?></span>
                </div>

                <div class="alert alert-info mb-3">
                    <strong>Note:</strong> ✔️ All checkboxes are <strong>checked</strong> by default. Uncheck to mark a personnel as <strong>Absent</strong>.
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="post" action="<?= base_url('project/save_weekly_attendance') ?>">
                            <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
                            <input type="hidden" name="projectID" value="<?= $projectID ?>">
                            <input type="hidden" name="start" value="<?= $start ?>">
                            <input type="hidden" name="end" value="<?= $end ?>">

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped dt-responsive nowrap text-center" style="width:100%">
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
                                                <td class="text-left"><?= $person->last_name ?>, <?= $person->first_name ?></td>
                                                <?php foreach ($dates as $d): ?>
                                                    <td>
                                                        <input type="checkbox" name="attendance[<?= $person->personnelID ?>][<?= $d ?>]" checked>
                                                    </td>
                                                <?php endforeach; ?>
                                                <td>
                                                    <input type="text" name="work_duration[<?= $person->personnelID ?>]" class="form-control form-control-sm" placeholder="e.g., 08:00">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($personnels)): ?>
                                            <tr><td colspan="<?= count($dates) + 2 ?>" class="text-center text-muted">No personnel records found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Weekly Attendance
                                </button>
                                <a href="<?= base_url('project/attendance_list/' . $settingsID . '?pid=' . $projectID) ?>" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>
