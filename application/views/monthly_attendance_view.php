<!DOCTYPE html>
<html lang="en">
<title>PMS - Monthly Attendance</title>

<?php include('includes/head.php'); ?>

<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/select2/select2.min.css">

<style>
thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 2;
    text-align: center;
}
th, td {
    vertical-align: middle !important;
    text-align: center;
    font-size: 14px;
}
td:first-child, th:first-child {
    text-align: left;
    background: #f8f9fa;
    position: sticky;
    left: 0;
    z-index: 1;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
}
.attendance-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.attendance-select, .hours-input {
    width: 90px;
    height: 30px;
    font-size: 13px;
    padding: 2px 6px;
    text-align: center;
}
</style>

<body>
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content container-fluid">
            <h4 class="page-title mt-2">Monthly Attendance</h4>

            <!-- Month Picker Modal -->
            <button class="btn btn-info shadow-sm mb-3" data-toggle="modal" data-target="#generateModal">
                <i class="mdi mdi-calendar-search"></i> Generate Monthly Attendance
            </button>

            <div class="modal fade" id="generateModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <form method="get" action="<?= base_url('Monthly') ?>">
                        <div class="modal-content border-0 shadow-sm">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="mdi mdi-calendar-month"></i> Select Month</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <label>Select Month:</label>
                                <input type="month" name="month" class="form-control" value="<?= $month ?>" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Generate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($personnel)): ?>
            <form method="post" action="<?= base_url('Monthly/save') ?>" onsubmit="return validateAttendanceForm();">
                <input type="hidden" name="month" value="<?= $month ?>">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped nowrap" id="attendanceTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Personnel</th>
                                <?php
                                $daysInMonth = date('t', strtotime($month . '-01'));
                                for ($d = 1; $d <= $daysInMonth; $d++):
                                    $date = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                ?>
                                <th><?= date('M d', strtotime($date)) ?><br><small>Status / Hours</small></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $emp): ?>
                            <tr>
                                <td><?= $emp->last_name . ', ' . $emp->first_name ?></td>
                                <?php for ($d = 1; $d <= $daysInMonth; $d++):
                                    $date = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                ?>
                                <td class="text-center align-middle">
                                    <div class="attendance-box">
                                        <select name="attendance_status[<?= $emp->personnelID ?>][<?= $date ?>]" class="form-control attendance-select" onchange="handleAttendanceChange(this)">
                                            <option value="Present">Present</option>
                                            <option value="Absent">Absent</option>
                                            <option value="Day Off">Day Off</option>
                                            <option value="Regular Holiday">Regular Holiday</option>
                                            <option value="Special Non-Working Holiday">Special Holiday</option>
                                        </select>
                                        <input type="number" name="regular_hours[<?= $emp->personnelID ?>][<?= $date ?>]" class="form-control hours-input" min="0" max="8" step="0.25" placeholder="Reg Hrs">
                                        <input type="number" name="overtime_hours[<?= $emp->personnelID ?>][<?= $date ?>]" class="form-control hours-input mt-1" min="0" max="8" step="0.25" placeholder="OT Hrs">
                                    </div>
                                </td>
                                <?php endfor; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-success shadow-sm px-4">
                        <i class="mdi mdi-content-save"></i> Save Attendance
                    </button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-info mt-3">Please select a month to display personnel attendance input.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
<script>
$(document).ready(function () {
    $('.attendance-select').select2({ width: '100%', minimumResultsForSearch: Infinity });
});

// Attendance logic
function handleAttendanceChange(select) {
    const container = select.closest('.attendance-box');
    const reg = container.querySelector('input[name^="regular_hours"]');
    const ot = container.querySelector('input[name^="overtime_hours"]');
    const val = select.value;

    reg.disabled = false;
    ot.disabled = false;

    if (val === 'Absent' || val === 'Day Off') {
        reg.disabled = true; reg.value = '';
        ot.disabled = true; ot.value = '';
    } else if (val === 'Regular Holiday' || val === 'Special Non-Working Holiday') {
        reg.max = 16; reg.value = '0';
        ot.value = '';
    } else {
        reg.max = 8;
    }
}

function validateAttendanceForm() {
    let valid = true;
    document.querySelectorAll('.attendance-box').forEach(box => {
        const reg = box.querySelector('input[name^="regular_hours"]');
        const ot = box.querySelector('input[name^="overtime_hours"]');
        if (reg && !reg.disabled && (reg.value < 0 || reg.value > parseFloat(reg.max))) {
            alert('Invalid Regular Hours'); reg.focus(); valid = false;
        }
        if (ot && !ot.disabled && (ot.value < 0 || ot.value > parseFloat(ot.max))) {
            alert('Invalid OT Hours'); ot.focus(); valid = false;
        }
    });
    return valid;
}
</script>
<script>
    // Autofill all attendance rows on page load
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.attendance-box').forEach(box => {
        const select = box.querySelector('select');
        const regInput = box.querySelector('input[name^="regular_hours"]');
        const otInput = box.querySelector('input[name^="overtime_hours"]');

        // Autofill
        if (select) select.value = 'Present';
        if (regInput) {
            regInput.value = '8';
            regInput.disabled = false;
        }
        if (otInput) {
            otInput.value = '0';
            otInput.disabled = false;
        }
    });

    // Trigger change to apply logic for status (in case you add conditions later)
    $('.attendance-select').trigger('change.select2');
});

</script>
</body>
</html>
