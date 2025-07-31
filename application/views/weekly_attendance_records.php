<!DOCTYPE html>
<html lang="en">
<?php
function formatHoursAndMinutes($decimal) {
    $hours = floor($decimal);
    $minutes = round(($decimal - $hours) * 60);
    return "{$hours} hr" . ($hours != 1 ? "s" : "") . " and {$minutes} mins";
}
?>

<title>PMS - Attendance Records</title>
<?php include('includes/head.php'); ?>
<style>
thead th {
     position: sticky;
     top: 0;
     background-color: #fff;
     z-index: 2;
}

td,
th {
     
     vertical-align: middle !important;
     text-align: center;
}

td {
     min-height: 40px;
     height: 40px;
}


th:first-child,
td:first-child {
     position: sticky;
     left: 0;
     background: #f8f9fa;
     z-index: 1;
     box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
}

.edit-attendance:hover {
     background-color: #f5f5f5;
     cursor: pointer;
}

.toast-header.bg-success {
     background-color: #28a745 !important;
}

/* Match global button appearance */
.btn {
    border-radius: 0.375rem;
    font-size: 14px;
    padding: 8px 18px;
    font-weight: 500;
    transition: transform 0.5s ease;
}
.btn:hover {
    transform: scale(1.05);
}
.btn i {
    margin-right: 5px;
}

/* Match section title */
h4.page-title, h4.text-dark.font-weight-bold {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    border-left: 5px solid #007bff;
    padding-left: 10px;
    margin-bottom: 1rem;
}

/* Modal consistency */
.modal-content {
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.modal-footer {
    border-top: 1px solid #dee2e6;
}

/* Table consistency */
.table thead th {
    font-size: 13px;
    font-weight: 600;
    background-color: #f1f3f5;
    color: #343a40;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #fafafa;
}

@media print {

     .btn,
     .modal,
     .modal-backdrop,
     .navbar,
     .sidebar,
     .page-title,
     .alert,
     .modal-open body {
          display: none !important;
     }

     table {
          font-size: 11px;
          border-collapse: collapse !important;
     }

     th,
     td {
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
               <div class="content">
                    <div class="container-fluid">


                         <div class="card">
                              <div class="card-body">
                               <h4 class="page-title">Attendance Records</h4>

<!-- 🔘 Filter Button -->
<div class="mb-3">
    <button class="btn btn-info btn-sm mt-2 mr-2 shadow-sm" data-toggle="modal" data-target="#filterModal">
        <i class="mdi mdi-filter-variant"></i> View Attendance
    </button>
    <button class="btn btn-primary btn-sm mt-2 shadow-sm" data-toggle="modal" data-target="#viewPayrollModal">
        <i class="mdi mdi-eye"></i> View Monthly
    </button>
</div>


<!-- View Monthly Modal -->
<div class="modal fade" id="viewPayrollModal" tabindex="-1" role="dialog" aria-labelledby="viewPayrollModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <form method="post" action="<?= base_url('MonthlyPayroll/view_record') ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPayrollModalLabel">
                        <i class="mdi mdi-calendar-month"></i> Select Payroll Range to View
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="view_payroll_month" class="font-weight-bold">Payroll Month</label>
                        <select class="form-control" id="view_payroll_month" name="payroll_month" required>
                            <option value="">Select Month</option>
                            <?php if (!empty($saved_months)): ?>
                                <?php foreach ($saved_months as $row): ?>
                                    <option value="<?= $row->payroll_month ?>">
                                        <?= date('F Y', strtotime($row->payroll_month . '-01')) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option disabled>No saved payroll months</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="from_date" class="font-weight-bold">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="to_date" class="font-weight-bold">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">View</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View Attendance Records -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <form method="get" action="<?= base_url('WeeklyAttendance/records') ?>">
      <div class="modal-content shadow-sm border-0">
        <div class="modal-header text-white">
          <h5 class="modal-title" id="filterModalLabel">
            <i class="mdi mdi-calendar-search mr-2"></i> View Attendance Records
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body text-dark">
          <div class="form-group">
            <label for="project" class="font-weight-bold">Project</label>
            <select name="projectID" id="project" class="form-control" required>
              <option value="">Select Project</option>
              <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="attendanceBatch" class="font-weight-bold">Attendance Batch</label>
            <select name="batchRange" id="attendanceBatch" class="form-control" required>
              <option value="">Select Batch</option>
              <?php foreach ($attendance_periods as $batch): ?>
                <option data-project="<?= $batch->projectID ?>"
                        data-start="<?= $batch->start ?>"
                        data-end="<?= $batch->end ?>">
                  <?= date('F j, Y', strtotime($batch->start)) ?> - <?= date('F j, Y', strtotime($batch->end)) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <input type="hidden" name="from" id="from">
          <input type="hidden" name="to" id="to">
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="mdi mdi-check"></i> View Records
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="mdi mdi-close-circle-outline"></i> Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


<?php
$selectedProjectID = $this->input->get('projectID');
$selectedFrom = $this->input->get('from');
$selectedTo = $this->input->get('to');

$projectID = $selectedProjectID ?? '';
$from = $selectedFrom ?? '';
$to = $selectedTo ?? '';
?>


<?php if (!empty($batches) && $selectedProjectID && $selectedFrom && $selectedTo): ?>
<div class="row justify-content-center">
  <div class="col-md-12">
 <div class="card shadow mb-4">

      <div class="card-body">

<?php foreach ($batches as $batch): ?>

        <?php
            if (
                $batch['projectID'] != $selectedProjectID ||
                $batch['from'] != $selectedFrom ||
                $batch['to'] != $selectedTo
            ) {
                continue;
            }

            $project = $batch['project'];
            $projectID = $batch['projectID'];
            $from = $batch['from'];
            $to = $batch['to'];
            $dates = $batch['dates'];
            $attendances = $batch['attendances'];
        ?>

        <?php
            $project = $batch['project'];
            $projectID = $batch['projectID'];
            $from = $batch['from'];
            $to = $batch['to'];
            $dates = $batch['dates'];
            $attendances = $batch['attendances'];
        ?>
<a id="batch-<?= $projectID ?>-<?= $from ?>-<?= $to ?>"></a>
<h5 class="text-dark font-weight-bold mb-3">
  🗂 Attendance Batch: <?= date('F j', strtotime($from)) ?> - <?= date('F j, Y', strtotime($to)) ?>
</h5>


        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-1">
                        <i class="mdi mdi-office-building text-info mr-1"></i>
                        <strong><?= $project->projectTitle ?></strong>
                    </h5>
                    <p class="text-dark small mb-0">
                        <?= date('F j, Y', strtotime($from)) ?> to
                        <?= date('F j, Y', strtotime($to)) ?>
                    </p>
                </div>
            </div>

          <div class="d-flex justify-content-end mb-3">
  <button class="btn btn-outline-primary btn-sm mr-2" onclick="window.print()">
    <i class="mdi mdi-printer"></i> Print
  </button>

  <form action="<?= base_url('WeeklyAttendance/deleteAttendance') ?>" method="post" onsubmit="return confirm('Are you sure you want to delete...?')" style="display: inline;">
    <input type="hidden" name="projectID" value="<?= $projectID ?>">
    <input type="hidden" name="from" value="<?= $from ?>">
    <input type="hidden" name="to" value="<?= $to ?>">
    <button type="submit" class="btn btn-danger btn-sm">
      <i class="mdi mdi-delete"></i> Delete
    </button>
  </form>
</div>

        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover">

              <th style="font-size: 13px; font-weight: 600;">

                <tr>
                    <th>Personnel</th>
                    <?php foreach ($dates as $d): ?>
                        <th><?= date('M d', strtotime($d)) ?></th>
                    <?php endforeach; ?>
                    <th>Total Hours</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($attendances as $pid => $person): ?>
                    <tr>
                        <td class="text-left font-weight-bold"><?= $person['name'] ?></td>
                        <?php foreach ($dates as $d): ?>
                            <?php
                            $status = $person['dates'][$d] ?? 'Absent';
                            $workHrs = $person['hours'][$d] ?? 0;
                            $holidayHrs = $person['holiday'][$d] ?? 0;
                            $overtimeHrs = $person['overtime'][$d] ?? 0;


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
                            <td class="text-<?= $color ?> edit-attendance"
                                data-personnel="<?= $pid ?>" data-date="<?= $d ?>"
                                data-status="<?= $status ?>" data-hours="<?= $workHrs ?>"
                                data-overtime="<?= $overtimeHrs ?>"
                                data-holiday="<?= $holidayHrs ?>"
                                style="cursor:pointer; min-width: 130px;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-pencil text-muted mr-1" style="font-size:12px;"></i>
                                    <div class="text-wrap text-center" style="white-space: normal;">
                                        <?= $statusLabel ?>
                                     <?php
if (
    stripos($status, 'Present') !== false ||
    stripos($status, 'Regular') !== false ||
    stripos($status, 'Special') !== false ||
    stripos($status, 'Absent') !== false ||
    stripos($status, 'Day Off') !== false
)
 {
    if ($workHrs > 0 || $holidayHrs > 0 || $overtimeHrs > 0) {
        echo "<br><small>";
        if ($workHrs > 0) echo number_format($workHrs, 2) . ' hr' . ($workHrs != 1 ? 's' : '');
        if ($holidayHrs > 0) {
            if ($workHrs > 0) echo ' + ';
            echo number_format($holidayHrs, 2) . ' hr' . ($holidayHrs != 1 ? 's' : '') . ' (holiday)';
        }
        if ($overtimeHrs > 0) {
            if ($workHrs > 0 || $holidayHrs > 0) echo ' + ';
            echo number_format($overtimeHrs, 2) . ' hr' . ($overtimeHrs != 1 ? 's' : '') . ' (OT)';
        }
        echo "</small>";
    }
}
?>

                                    </div>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <?php
                        $reg = array_sum(array_column($person['hours'], null));
                        $hol = array_sum(array_column($person['holiday'], null));
                        $ot = array_sum(array_column($person['overtime'] ?? [], null));

                        ?>
                        <td class="text-center">
                          <?php if ($reg > 0 || $hol > 0 || $ot > 0): ?>

                                <span class="text-dark">
                                   <?php if ($reg > 0): ?><span class="text-primary">Regular: <?= formatHoursAndMinutes($reg); ?></span><br><?php endif; ?>
<?php if ($hol > 0): ?><span class="text-info">Holiday: <?= formatHoursAndMinutes($hol); ?></span><br><?php endif; ?>
<?php if ($ot > 0): ?><span class="text-danger">Overtime: <?= formatHoursAndMinutes($ot); ?></span><?php endif; ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
     </div>
    </div>
  </div>
</div>
<?php endif; ?>


<?php if (isset($project) && empty($attendances)): ?>
<div class="row justify-content-center">
  <div class="col-md-12">
    <div class="alert alert-warning text-center">
      <i class="mdi mdi-alert-circle-outline"></i>
      No attendance records found for this project and date range.
    </div>
  </div>
</div>
<?php endif; ?>



                         <?php if ($this->session->flashdata('view_error')): ?>
<!-- Bootstrap Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog"
     aria-labelledby="errorModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0">

               <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">
                         <i class="mdi mdi-alert-circle-outline mr-2"></i> Error
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                    </button>
               </div>
               <div class="modal-body text-dark">
                    <div class="d-flex align-items-center">
                         <i class="mdi mdi-alert mr-2 text-warning" style="font-size: 24px;"></i>
                         <div>
                              <strong>Some selected dates have no data.</strong><br>
                              Only dates with existing attendance records will be shown.
                         </div>
                    </div>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                         <i class="mdi mdi-close"></i> Close
                    </button>
               </div>
          </div>
     </div>
</div>
<?php endif; ?>



                    </div>
               </div>
               <?php include('includes/footer.php'); ?>
          </div>
     </div>
     <!-- ❌ No Attendance Data Modal -->
     <div class="modal fade" id="noAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="noAttendanceModalLabel"
          aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
               <div class="modal-content border-0 shadow-sm">
                    <div class="modal-header bg-danger text-white">
                         <h5 class="modal-title" id="noAttendanceModalLabel">
                              <i class="mdi mdi-alert-circle-outline mr-2"></i> No Attendance Found
                         </h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>

                    <div class="modal-body text-dark">
                         <p><strong>❌ No attendance has been generated for this project yet.</strong></p>
                    </div>

                    <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" data-dismiss="modal">
                              <i class="mdi mdi-close-circle-outline"></i> Close
                         </button>
                    </div>
               </div>
          </div>
     </div>
     
     <div class="modal fade" id="editAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
          aria-hidden="true">
          <div class="modal-dialog" role="document">
               <form method="post" action="<?= base_url('WeeklyAttendance/updateAttendance') ?>">
                    <input type="hidden" name="projectID" value="<?= $projectID ?>">
<input type="hidden" name="from" value="<?= $from ?>">
<input type="hidden" name="to" value="<?= $to ?>">

                    <div class="modal-content">
                         <div class="modal-header bg-primary text-white">
                              <h5 class="modal-title" id="editModalLabel">Edit Attendance</h5>
                              <button type="button" class="close text-white"
                                   data-dismiss="modal"><span>&times;</span></button>
                         </div>
                         <div class="modal-body">
                              <input type="hidden" name="personnelID" id="editPersonnelID">
                              <input type="hidden" name="date" id="editDate">
                            <input type="hidden" name="projectID" value="<?= $projectID ?>">

                              <input type="hidden" name="from" value="<?= $from ?>">
                              <input type="hidden" name="to" value="<?= $to ?>">
                              <div class="form-group">
                                   <label>Attendance Type</label>
                                   <select name="status" id="editStatus" class="form-control" required>
                                        <option value="Present">Present</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Day Off">Day Off</option>
                                        <option value="Regular Holiday">Regular Holiday</option>
                                        <option value="Special Non-Working Holiday">Special Non-Working Holiday</option>
                                   </select>
                              </div>
<div class="form-group" id="regularHoursWrapper">
    <label>Regular Hours <small class="text-muted">(Max: 8 hrs)</small></label>
    <input type="number" step="0.01" max="8" name="hours" id="editHours" class="form-control">
</div>


                              <div class="form-group">
                                   <label>Holiday Hours</label>
                                   <input type="number" step="0.01" name="holiday" id="editHoliday"
                                        class="form-control">
                              </div>
                              <div class="form-group" id="overtimeWrapper">
    <label>Overtime Hours</label>
    <input type="number" step="0.01" name="overtime" id="editOvertime" class="form-control">
</div>
                         </div>

                         <div class="modal-footer">
                             <?php if (!empty($batches) && $selectedProjectID && $selectedFrom && $selectedTo): ?>
    <button type="submit" class="btn btn-primary">Update</button>
   
<?php endif; ?>

                         </div>
                    </div>
               </form>
          </div>
     </div>
   <?php if ($this->session->flashdata('update_success')): ?>
     <div aria-live="polite" aria-atomic="true" style="
     position: fixed;
     top: 70px;
     left: 50%;
     transform: translateX(-50%);
     z-index: 1055;
">

          <div class="toast show" id="successToast" role="alert" data-delay="3000" style="min-width: 250px;">
               <div class="toast-header bg-success text-white">
                    <strong class="mr-auto"><i class="mdi mdi-check-circle-outline"></i> Success</strong>
                    <small>Just now</small>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                    </button>
               </div>
               <div class="toast-body text-dark">
                    <?= $this->session->flashdata('update_success'); ?>
               </div>
          </div>
     </div>
<?php endif; ?>



     <!-- Bootstrap + App JS -->
     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

  <script>
$(document).ready(function () {
    // Show toast if available
    $('.toast').toast({ delay: 2500 }).toast('show');

    // Show error modal if session has flashdata
    <?php if ($this->session->flashdata('view_error')): ?>
    $('#errorModal').modal('show');
    <?php endif; ?>

    // Focus field when filter modal opens
    $('#filterModal').on('shown.bs.modal', function () {
        $('#project').focus();
    });

    // Attendance batch filtering based on project
    const projectSelect = document.getElementById('project');
    const batchSelect = document.getElementById('attendanceBatch');

    projectSelect.addEventListener('change', function () {
        const selectedProject = this.value;
        const options = batchSelect.querySelectorAll('option');
        batchSelect.value = "";

        options.forEach(option => {
            if (option.value === "") {
                option.style.display = 'block';
            } else if (option.getAttribute('data-project') === selectedProject) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });

    batchSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('from').value = selected.getAttribute('data-start');
        document.getElementById('to').value = selected.getAttribute('data-end');
    });

    // ✅ Dynamic field logic based on status
   function toggleHourFields() {
    const status = $('#editStatus').val();

    // Always show regular + overtime, hide holiday
    $('#regularHoursWrapper').show();
    $('#overtimeWrapper').show();
    $('#editHoliday').closest('.form-group').hide(); // always hide holiday hours

    // Overtime is always editable
    $('#editOvertime').prop('readonly', false);

    if (status === 'Absent' || status === 'Day Off') {
        // Lock regular hours to 0.00
        $('#editHours').val('0.00').prop('readonly', true);
    } else {
        // Allow user to edit, but don't blank it
        $('#editHours').prop('readonly', false);
        // Do not change the value here — retain what's already loaded
    }
}


    // Edit modal trigger
    $('.edit-attendance').on('click', function () {
        $('#editPersonnelID').val($(this).data('personnel'));
        $('#editDate').val($(this).data('date'));
        $('#editStatus').val($(this).data('status'));
        $('#editHoliday').val($(this).data('holiday'));
        $('#editHours').val($(this).data('hours'));
        $('#editOvertime').val($(this).data('overtime'));

        // Add validation for Regular Hours
        $('#editHours').off('input').on('input', function () {
            let val = parseFloat($(this).val());
            if (val > 8) {
                $(this).val(8);
                alert('Regular hours cannot exceed 8.00 hours.');
            } else if (val < 0 || isNaN(val)) {
                $(this).val('');
            }
        });

        $('#editAttendanceModal').modal('show');
        toggleHourFields();
    });

    $('#editStatus').on('change', toggleHourFields);
});
</script>



</body>

</html>