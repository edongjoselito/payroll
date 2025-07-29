<!DOCTYPE html>
<html lang="en">
<title>PMS - Monthly Payroll</title>
<?php include('includes/head.php'); ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        background-color: #f8f9fa;
    }

    h4.page-title {
        font-weight: 600;
        color: #343a40;
        margin-bottom: 1.5rem;
    }

    .attendance-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .attendance-select,
    .input-hours {
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 4px;
        width: 100%;
    }

    .attendance-select {
        background-color: #fff;
        border: 1px solid #ced4da;
    }

    .input-hours {
        background-color: #fff;
        border: 1px solid #ced4da;
    }

    .attendance-box input::placeholder {
        
        font-size: 12px;
        color: #999;
    }

    table#payrollTable th,
    table#payrollTable td {
        vertical-align: middle !important;
        text-align: center;
        white-space: nowrap;
    }

    table#payrollTable thead th {
        position: sticky;
        top: 0;
        background-color: #e9ecef;
        z-index: 10;
        font-size: 13px;
    }

    table#payrollTable th small {
        font-weight: normal;
        font-size: 11px;
        color: #666;
    }

    .btn-success {
        font-weight: 500;
    }

    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow-x: auto;
        background-color: #fff;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }

    @media screen and (max-width: 768px) {
        .attendance-box {
            flex-direction: column;
        }

        .attendance-select,
        .input-hours {
            font-size: 12px;
        }

        table#payrollTable th,
        table#payrollTable td {
            font-size: 12px;
        }
    }
</style>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
<?php if ($this->session->flashdata('msg')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('msg'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

                
<?php if ($this->session->flashdata('duplicate_msg')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('duplicate_msg'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

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
        <table id="payrollTable" class="table table-bordered table-striped" style="width:100%">
            <thead class="thead-light">
                <tr>
                    <th>Personnel</th>
                    <?php foreach ($dates as $date): ?>
                        <th style="min-width:120px;">
    <strong><?= date('M d', strtotime($date)) ?></strong><br>
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
    min="0" max="8" step="0.25" placeholder="Regular Hrs"
    value="8"
>

                            <input type="number"
                                name="overtime_hours[<?= $emp->personnelID ?>][<?= $date ?>]"
                                class="form-control input-hours mt-1"
                                min="0" max="8" step="0.25" placeholder="Overtime Hrs">
                        </div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
   <div class="d-flex justify-content-end mt-3">
    <button type="submit" class="btn btn-success shadow px-4 py-2">
        <i class="mdi mdi-content-save"></i> Save Payroll
    </button>
</div>

</form>


                    <?php endif; ?>

                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<!-- Select2 Assets -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Styles -->
<style>
   
    .select2-container {
        max-width: 100px !important;
        width: 100% !important;
    }
    .select2-dropdown {
        max-width: 200px !important;
        font-size: 13px;
    }
</style>

<!-- JS Logic -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Select2
    $('.attendance-select').select2({
        width: 'resolve',
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth: true
    });

    // Apply status behavior on load
    document.querySelectorAll('.attendance-select').forEach(function (select) {
        handleAttendanceChange(select);
    });

    // Limit regular hours to 8 and warn
    let warned = false;
    document.querySelectorAll('input[name^="regular_hours"]').forEach(function (input) {
        input.addEventListener('input', function () {
            let val = parseFloat(this.value);
            if (val > 8) {
                this.value = 8;
                if (!warned) {
                    alert("⚠ Maximum Regular Hours is 8.");
                    warned = true;
                    setTimeout(() => warned = false, 1000); // allow re-showing after delay
                }
            }
        });
    });
});

// FORM VALIDATION
function validatePayrollForm() {
    let isValid = true;
    let warned = false;

    document.querySelectorAll('.attendance-box').forEach(box => {
        const select = box.querySelector('.attendance-select');
        const regHours = box.querySelector('input[name^="regular_hours"]');

        if (select.value === 'Present' && regHours.value.trim() === '') {
            regHours.classList.add('is-invalid');
            if (!warned) {
                alert('⚠ Please input Regular Hours for all Present days.');
                warned = true;
            }
            isValid = false;
        } else {
            regHours.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// ON DROPDOWN CHANGE
function handleAttendanceChange(select) {
    const box = select.closest('.attendance-box');
    const regHours = box.querySelector('input[name^="regular_hours"]');
    const otHours = box.querySelector('input[name^="overtime_hours"]');

    const isReadonlyReg = ['Absent', 'Day Off'].includes(select.value);

    // REGULAR HOURS
    regHours.readOnly = isReadonlyReg;
    regHours.classList.toggle('bg-light', isReadonlyReg);
    if (isReadonlyReg) {
        regHours.value = '';
    } else if (select.value === 'Present' && (regHours.value === '' || regHours.value === '0' || regHours.value === '0.00')) {
        regHours.value = '8';
    }

    // OVERTIME
    otHours.readOnly = false;
    otHours.classList.remove('bg-light');
}
</script>


</body>
</html>
