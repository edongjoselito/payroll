<!DOCTYPE html>
<html lang="en">
<title>PMS - Office Attendance</title>
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

                <!-- Title and Date Filter -->
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Office Attendance</h4>
                    <form method="get" class="form-inline">
                        <input type="date" name="date" value="<?= $date ?>" class="form-control mr-2" />
                        <button class="btn btn-primary">Load</button>
                    </form>
                </div>

                <!-- Flash Messages -->
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

                <!-- Attendance Form -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="page-title mb-3">Attendance for <?= date('F d, Y', strtotime($date)) ?></h5>

                        <form method="post" action="<?= base_url('Monthly/saveOfficeAttendance') ?>">
                            <input type="hidden" name="attendance_date" value="<?= $date ?>">

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Work Duration</th>
                                        </tr>
                                    </thead>
                                  <tbody>
<?php foreach ($personnel as $p): 
    $status = '';
    $duration = '8';
    foreach ($existing as $e) {
        if ($e['personnelID'] == $p->personnelID) {
            $status = $e['attendance_status'];
            $duration = $e['workDuration'];
        }
    }
?>
<tr>
    <td><?= "$p->first_name $p->last_name" ?></td>
    <td>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-sm btn-outline-primary present-btn <?= $status == 'Present' ? 'active' : '' ?>" onclick="setStatus(<?= $p->personnelID ?>, 'Present')">
                <input type="radio"> Present
            </label>
            <label class="btn btn-sm btn-outline-warning halfday-btn <?= $status == 'Half-Day' ? 'active' : '' ?>" onclick="setStatus(<?= $p->personnelID ?>, 'Half-Day')">
                <input type="radio"> Half Day
            </label>
            <label class="btn btn-sm btn-outline-danger absent-btn <?= $status == 'Absent' ? 'active' : '' ?>" onclick="setStatus(<?= $p->personnelID ?>, 'Absent')">
                <input type="radio"> Absent
            </label>
        </div>

        <!-- hidden input for status -->
        <input type="hidden" name="attendance[<?= $p->personnelID ?>][status]" id="status_<?= $p->personnelID ?>" value="<?= $status ?>">
    </td>

    <td>
        <input 
            type="number" 
            name="attendance[<?= $p->personnelID ?>][duration]" 
            class="form-control workDurationInput" 
            id="duration_<?= $p->personnelID ?>" 
            value="<?= $duration ?>" 
            step="0.5" min="0" max="8" 
            style="<?= ($status == 'Present' || $status == 'Half-Day') ? '' : 'display:none;' ?>">
    </td>
</tr>
<?php endforeach; ?>
</tbody>

                                </table>
                            </div>

                            <div class="text-right mt-3">
                                <button type="submit" class="btn btn-success">Save Attendance</button>
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
<script>
function setStatus(personnelID, status) {
    // Set hidden value
    document.getElementById('status_' + personnelID).value = status;

    // Toggle work duration input
    let input = document.getElementById('duration_' + personnelID);
    if (status === 'Present' || status === 'Half-Day') {
        input.style.display = 'block';
    } else {
        input.style.display = 'none';
        input.value = ''; // clear value if absent
    }
}
</script>

</body>
</html>
