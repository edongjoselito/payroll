<?php
function formatHoursAndMinutes($decimal) {
    $hours = floor($decimal);
    $minutes = round(($decimal - $hours) * 60);
    return "{$hours} hr" . ($hours != 1 ? "s" : "") . " and {$minutes} mins";
}
?>

<!DOCTYPE html>
<html lang="en">
<title>PMS - Monthly Attendance Records</title>
<?php include('includes/head.php'); ?>
<style>
thead th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 2;
}
td, th {
    vertical-align: middle !important;
    text-align: center;
}
th:first-child, td:first-child {
    position: sticky;
    left: 0;
    background: #f8f9fa;
    z-index: 1;
    text-align: left;
}
.edit-attendance:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}
@media print {
    .btn, .navbar, .sidebar, .modal, .modal-backdrop {
        display: none !important;
    }
    table {
        font-size: 11px;
        border-collapse: collapse !important;
    }
    th, td {
        border: 1px solid black !important;
    }
    body {
        margin: 20px;
    }
}
</style>

<body>
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
    <div class="content container-fluid">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="text-dark font-weight-bold mb-3">
                    Monthly Attendance Records – <?= date('F Y', strtotime($month)) ?>
                </h4>

                <?php if (!empty($personnel)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Personnel</th>
                                    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                        <th><?= date('M d', strtotime("$month-" . str_pad($d, 2, '0', STR_PAD_LEFT))) ?></th>
                                    <?php endfor; ?>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($personnel as $emp): ?>
                                    <tr>
                                        <td><?= $emp->last_name . ', ' . $emp->first_name ?></td>
                                        <?php
                                            $totalReg = $totalOT = $totalHol = 0;
                                            for ($d = 1; $d <= $daysInMonth; $d++):
                                                $dayData = $records[$emp->personnelID][$d] ?? null;
                                                $status = $dayData['status'] ?? '-';
                                                $reg = $dayData['reg'] ?? 0;
                                                $ot = $dayData['ot'] ?? 0;
                                                $hol = $dayData['holiday'] ?? 0;

                                                $totalReg += $reg;
                                                $totalOT += $ot;
                                                $totalHol += $hol;

                                                $color = 'secondary';
                                                if ($status === 'Present') $color = 'success';
                                                elseif ($status === 'Absent') $color = 'danger';
                                                elseif ($status === 'Day Off') $color = 'info';
                                                elseif ($status === 'Regular Holiday') $color = 'warning';
                                                elseif ($status === 'Special Non-Working Holiday') $color = 'dark';

                                                $statusLabel = $status;
                                                if ($status === 'Regular Holiday') $statusLabel = 'R. Holiday';
                                                elseif ($status === 'Special Non-Working Holiday') $statusLabel = 'S. Holiday';
                                        ?>
                                        <td class="edit-attendance text-<?= $color ?>"
                                            data-personnel="<?= $emp->personnelID ?>"
                                            data-date="<?= "$month-" . str_pad($d, 2, '0', STR_PAD_LEFT) ?>"
                                            data-status="<?= $status ?>"
                                            data-hours="<?= $reg ?>"
                                            data-overtime="<?= $ot ?>"
                                            data-holiday="<?= $hol ?>">
                                            <?= $statusLabel ?>
                                            <?php if ($reg > 0 || $ot > 0 || $hol > 0): ?>
                                                <br><small>
                                                    <?= $reg > 0 ? number_format($reg, 2) . 'h' : '' ?>
                                                    <?= $hol > 0 ? ($reg > 0 ? ' + ' : '') . number_format($hol, 2) . 'h (H)' : '' ?>
                                                    <?= $ot > 0 ? (($reg > 0 || $hol > 0) ? ' + ' : '') . number_format($ot, 2) . 'h (OT)' : '' ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <?php endfor; ?>
                                        <td class="text-center">
                                            <?php if ($totalReg || $totalHol || $totalOT): ?>
                                                <?php if ($totalReg): ?><div class="text-primary">Regular: <?= formatHoursAndMinutes($totalReg) ?></div><?php endif; ?>
                                                <?php if ($totalHol): ?><div class="text-info">Holiday: <?= formatHoursAndMinutes($totalHol) ?></div><?php endif; ?>
                                                <?php if ($totalOT): ?><div class="text-danger">Overtime: <?= formatHoursAndMinutes($totalOT) ?></div><?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-right d-print-none">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="mdi mdi-printer"></i> Print
                        </button>
                        <form action="<?= base_url('Monthly/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete attendance for this month?');">
                            <input type="hidden" name="month" value="<?= $month ?>">
                            <button class="btn btn-danger">Delete Month</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle-outline"></i>
                        No personnel or attendance data found for this month.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="post" action="<?= base_url('Monthly/update') ?>">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Attendance</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="personnelID" id="editPersonnelID">
                    <input type="hidden" name="date" id="editDate">

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-control" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Day Off">Day Off</option>
                            <option value="Regular Holiday">Regular Holiday</option>
                            <option value="Special Non-Working Holiday">Special Non-Working Holiday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Regular Hours</label>
                        <input type="number" step="0.01" max="8" name="reg" id="editHours" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Holiday Hours</label>
                        <input type="number" step="0.01" name="holiday" id="editHoliday" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Overtime Hours</label>
                        <input type="number" step="0.01" name="ot" id="editOT" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">Update</button>
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
$(function () {
    $('.edit-attendance').on('click', function () {
        $('#editPersonnelID').val($(this).data('personnel'));
        $('#editDate').val($(this).data('date'));
        $('#editStatus').val($(this).data('status'));
        $('#editHours').val($(this).data('hours'));
        $('#editHoliday').val($(this).data('holiday'));
        $('#editOT').val($(this).data('overtime'));
        $('#editAttendanceModal').modal('show');
    });
});
</script>
</body>
</html>
