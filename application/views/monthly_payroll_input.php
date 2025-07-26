<!DOCTYPE html>
<html lang="en">
<title>PMS - Monthly Payroll</title>
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="mb-3">
                        <h4 class="page-title">Monthly Payroll Generation</h4>
                       
                    </div>

                    <div class="modal fade" id="monthModal" tabindex="-1" role="dialog" aria-labelledby="monthModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                            <form method="post" action="<?= base_url('MonthlyPayroll/generate') ?>">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="monthModalLabel">
                                            <i class="mdi mdi-calendar-month"></i> Select Month
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="payroll_month" class="font-weight-bold">Month</label>
                                            <input type="month" class="form-control" id="payroll_month" name="payroll_month"
                                                value="<?= date('Y-m'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Proceed</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payroll Input Table loads here after submit -->
                    <?php if (isset($personnel) && isset($dates)): ?>
                       <form method="post" action="<?= base_url('MonthlyPayroll/save') ?>" onsubmit="return validatePayrollForm()">
    <input type="hidden" name="payroll_month" value="<?= htmlspecialchars($month) ?>">
    <div class="table-responsive">
        <table id="payrollTable" class="table table-bordered table-striped table-hover nowrap" style="width:100%">
            <thead class="thead-light">
                <tr>
                    <th>Personnel</th>
                    <?php foreach ($dates as $date): ?>
                        <th style="min-width:120px;">
                            <?= date('M d', strtotime($date)) ?><br>
                            <small>Status / Reg Hrs / OT</small>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Sort personnel by last name
                usort($personnel, function($a, $b) {
                    return strcasecmp($a->last_name, $b->last_name);
                });
                foreach ($personnel as $emp): ?>
                <tr>
                    <td>
                        <?= $emp->last_name . ', ' . $emp->first_name . ($emp->name_ext ? ' ' . $emp->name_ext : '') ?>
                        <input type="hidden" name="personnelID[]" value="<?= $emp->personnelID ?>">
                    </td>
                    <?php foreach ($dates as $date): ?>
                    <td>
                        <div class="attendance-box">
                            <select
                                name="attendance_status[<?= $emp->personnelID ?>][<?= $date ?>]"
                                class="form-control attendance-select"
                                onchange="handleAttendanceChange(this)">
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Day Off">Day Off</option>
                                <option value="Regular Holiday">Regular Holiday</option>
                                <option value="Special Non-Working Holiday">Special Non-Working Holiday</option>
                            </select>
                           <input type="number"
    name="regular_hours[<?= $emp->personnelID ?>][<?= $date ?>]"
    class="form-control input-hours"
    min="0" max="8" step="0.25" placeholder="Reg Hrs"
    value="8"
>

                            <input type="number"
                                name="overtime_hours[<?= $emp->personnelID ?>][<?= $date ?>]"
                                class="form-control input-hours mt-1"
                                min="0" max="8" step="0.25" placeholder="OT Hrs">
                        </div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-success shadow-sm px-4">
            <i class="mdi mdi-content-save"></i> Save Payroll
        </button>
    </div>
</form>
<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('msg'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

                    <?php endif; ?>

                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.attendance-select').forEach(function(select) {
        if (select.value === 'Present') {
            const box = select.closest('.attendance-box');
            const regHours = box.querySelector('input[name^="regular_hours"]');
            if (regHours.value === '' || regHours.value === '0' || regHours.value === '0.00') {
                regHours.value = '8';
            }
        }
    });
});
</script>

    <script>
function validatePayrollForm() {
    let isValid = true;
    document.querySelectorAll('.attendance-box').forEach(box => {
        const select = box.querySelector('.attendance-select');
        const regHours = box.querySelector('input[name^="regular_hours"]');
        const otHours = box.querySelector('input[name^="overtime_hours"]');
        if (select.value === 'Present' && regHours.value === '') {
            regHours.classList.add('is-invalid');
            isValid = false;
        } else {
            regHours.classList.remove('is-invalid');
        }
        // You can add further checks as needed
    });
    if (!isValid) {
        alert('⚠ Please input regular hours for all Present days.');
    }
    return isValid;
}

function handleAttendanceChange(select) {
    const box = select.closest('.attendance-box');
    const regHours = box.querySelector('input[name^="regular_hours"]');
    const otHours = box.querySelector('input[name^="overtime_hours"]');
    if (select.value === 'Absent' || select.value === 'Day Off') {
        regHours.value = '';
        regHours.disabled = true;
        otHours.value = '';
        otHours.disabled = true;
    } else if (select.value === 'Present') {
        regHours.disabled = false;
        if (regHours.value === '' || regHours.value === '0' || regHours.value === '0.00') {
            regHours.value = '8';
        }
        otHours.disabled = false;
    } else {
        regHours.disabled = false;
        regHours.value = '';
        otHours.disabled = false;
    }
}

</script>

</body>
</html>
