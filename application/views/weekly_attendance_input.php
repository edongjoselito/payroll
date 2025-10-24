<!DOCTYPE html>
<html lang="en">
<title>PMS - Attendance</title>

<?php include('includes/head.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php $flash = $this->session->flashdata('attendance_exists'); ?>
<?php $attendance_success = $this->session->flashdata('attendance_success'); ?>

<style>
  thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 2;
    text-align: center;
  }

  th,
  td {
    vertical-align: middle !important;
    text-align: center;
    font-size: 14px;
  }

  td:first-child,
  th:first-child {
    text-align: left;
    background: #f8f9fa;
    position: sticky;
    left: 0;
    z-index: 1;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
  }

  #attendanceTable tbody tr:hover {
    background-color: whitesmoke;
  }

  .attendance-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
  }

  input.is-invalid {
    border-color: #dc3545 !important;
    background-color: #fff !important;
    color: #212529 !important;
  }

  .card-body h5 {
    font-weight: 600;
  }

  .card-body p {
    font-size: 14px;
  }

  .attendance-select,
  .input-hours {
    width: 90px;
    min-width: 90px;
    max-width: 180px;
    height: 30px;
    font-size: 13px;
    padding: 2px 6px;
    text-align: center;
  }

  .select2-container--bootstrap4 .select2-selection--single {
    width: 90px !important;
    height: 30px !important;
    font-size: 13px !important;
    text-align: center;
  }

  .table td,
  .table th {
    vertical-align: middle;
    padding: 6px 8px;
  }

  .attendance-row:hover {
    background-color: #f9f9f9;
  }

  /* Page Title Styling */
  .page-title {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 1rem;
    border-left: 5px solid #007bff;
    padding-left: 10px;
  }

  /* Table Header */
  .table thead th {
    font-size: 13px;
    font-weight: 600;
    background-color: #f1f3f5;
    color: #343a40;
  }

  /* Alternating row color */
  .table-striped tbody tr:nth-of-type(odd) {
    background-color: #fafafa;
  }

  /* Buttons */
  .btn {
    border-radius: 0.375rem;
    font-size: 14px;
    padding: 8px 18px;
    font-weight: 500;
    transition: transform 0.5s ease;
  }

  .btn i {
    margin-right: 5px;
  }

  /* Modal Styling */
  .modal-content {
    border-radius: 5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  }

  .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
  }

  .modal-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
  }

  .modal-footer {
    border-top: 1px solid #dee2e6;
  }

  /* Alert Styling */
  .alert {
    font-size: 14px;
    border-radius: 5px;
    padding: 8px 16px;
  }

  /* Card Section Info */
  .card h5 {
    font-size: 18px;
    color: #343a40;
  }

  .card p {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 4px;
  }

  /* Smooth enlarge on hover */
  .btn:hover {
    transform: scale(1.05);
  }

  /* Optional: add focus effect for accessibility */
  .btn:focus {
    box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.25);
    outline: none;
  }

  /* ✅ Toast: Success Header */
  .toast-header-success {
    background-color: #28a745;
    color: #fff;
    font-weight: 500;
    font-size: 15px;
  }

  /* ✅ Toast: Success Body */
  .toast-body-success {
    background-color: #eaf9ef;
    color: #155724;
    font-size: 14px;
    padding: 10px 15px;
    border-left: 4px solid #28a745;
  }

  /* SweetAlert styles */
  .fb-style-popup {
    padding: 1.5rem 1.25rem !important;
    border-radius: 12px !important;
    font-family: 'Poppins', sans-serif;
    max-width: 480px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
  }

  .fb-style-title {
    font-size: 20px;
    font-weight: 600;
    margin-top: 8px;
    color: #1c1e21;
    display: block;
    text-align: center;
  }

  .fb-style-text {
    font-size: 14px;
    line-height: 1.6;
    color: #555;
    margin-top: 8px;
  }

  .fb-style-confirm {
    background-color: #1877f2 !important;
    color: white !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 6px 20px !important;
    font-weight: 500;
    font-size: 14px;
    transition: background-color 0.2s ease;
  }

  .fb-style-confirm:hover {
    background-color: #0f66d0 !important;
  }

  .fb-style-cancel {
    background-color: #e4e6eb !important;
    color: #050505 !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 6px 20px !important;
    font-weight: 500;
    font-size: 14px;
    transition: background-color 0.2s ease;
  }

  .fb-style-cancel:hover {
    background-color: #d0d2d5 !important;
  }

  .swal2-actions {
    gap: 10px;
    justify-content: center !important;
  }

  .swal2-popup .swal2-styled {
    box-shadow: none !important;
  }
</style>

<body>

  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

  <div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">
          <?php
          // role once
          $role = $this->session->userdata('position') ?: $this->session->userdata('level');

          // period (when generated)
          $periodFrom = !empty($from) ? date('M d, Y', strtotime($from)) : null;
          $periodTo   = !empty($to)   ? date('M d, Y', strtotime($to))   : null;
          ?>

          <?php if ($this->session->flashdata('msg')): ?>
            <?php
            $role = $this->session->userdata('position') ?: $this->session->userdata('level');
            ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <?= $this->session->flashdata('msg'); ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php endif; ?>

          <?php
          // 🔐 read role once for this view
          $role = $this->session->userdata('position') ?: $this->session->userdata('level');
          ?>

          <div class="card shadow-sm mb-3 border-0">
            <div class="card-body">
              <h4 class="page-title">Weekly Attendance</h4>

              <?php if ($this->session->flashdata('duplicate_msg')): ?>
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm border-left border-4 border-warning mt-2" role="alert" style="font-size: 14px;">
                  <i class="mdi mdi-alert-circle-outline mr-2" style="font-size: 20px;"></i>
                  <div class="flex-fill">
                    <?= $this->session->flashdata('duplicate_msg'); ?>
                  </div>
                  <button type="button" class="close ml-3" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              <?php endif; ?>
              <!-- ✅ Payroll User + Admin can generate WEEKLY (workers) -->
              <button type="button"
                class="btn btn-info btn-sm mt-2 mr-2 shadow-sm"
                data-toggle="modal"
                data-target="#generateModal">
                <i class="mdi mdi-calendar-search"></i> Generate Attendance
              </button>


              <!-- ❌ Hide Monthly for Payroll User -->
              <?php if ($role !== 'Payroll User'): ?>
                <button class="btn btn-primary btn-sm mt-2 shadow-sm" data-toggle="modal" data-target="#monthlyPayrollModal">
                  <i class="mdi mdi-calendar-month"></i> Generate Monthly
                </button>
              <?php endif; ?>

            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <?php if ($role !== 'Payroll User'): ?>
                <!-- Modal: Select Month for Monthly Payroll (always) -->
                <div class="modal fade" id="monthlyPayrollModal" tabindex="-1" role="dialog" aria-labelledby="monthlyPayrollModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <form method="post" action="<?= base_url('MonthlyPayroll/generate') ?>">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="monthlyPayrollModalLabel">
                            <i class="mdi mdi-calendar-month"></i> Select Month
                          </h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label for="from_date" class="font-weight-bold">From</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="<?= date('Y-m-01'); ?>" required>
                          </div>
                          <div class="form-group">
                            <label for="to_date" class="font-weight-bold">To</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="<?= date('Y-m-t'); ?>" required>
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
              <?php endif; ?>



              <!-- Attendance Table / Save form -->
              <?php if (isset($employees)): ?>

                <?php if (isset($project)): ?>
                  <?php
                  $fromFormatted = date("F d, Y", strtotime($from));
                  $toFormatted   = date("F d, Y", strtotime($to));
                  ?>
                  <div class="card shadow-sm mb-3 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                      <div>
                        <h5 class="mb-1">
                          <i class="mdi mdi-briefcase-outline text-primary mr-1"></i>
                          <strong><?= htmlspecialchars($project->projectTitle) ?></strong>
                        </h5>
                        <p class="mb-1 text-muted">
                          <i class="mdi mdi-map-marker text-danger mr-1"></i>
                          <?= htmlspecialchars($project->projectLocation ?? 'N/A') ?>
                        </p>
                        <p class="mb-0 text-muted">
                          <i class="mdi mdi-calendar text-info mr-1"></i>
                          <?= date('F j, Y', strtotime($from)) ?> to <?= date('F j, Y', strtotime($to)) ?>
                        </p>
                      </div>

                      <div class="mt-2 mt-sm-0">
                        <button type="button" class="btn btn-outline-dark btn-sm" title="View Attendance Notes" onclick="showNotesSweetAlert()">
                          <i class="mdi mdi-information-outline"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('WeeklyAttendance/save') ?>" onsubmit="return validateAttendanceForm()">
                  <input type="hidden" name="projectID" value="<?= $projectID ?>">
                  <input type="hidden" name="from" value="<?= $from ?>">
                  <input type="hidden" name="to" value="<?= $to ?>">

                  <?php foreach ($dates as $date): ?>
                    <input type="hidden" name="dates[]" value="<?= $date ?>">
                  <?php endforeach; ?>

                  <h5 class="text-dark font-weight-bold mb-2 mt-4"><i class="mdi mdi-table"></i> Attendance Input Table</h5>
                  <hr class="mb-3 mt-0">

                  <div class="table-responsive mt-3">
                    <table id="attendanceTable" class="table table-bordered table-hover table-striped nowrap" style="width:100%">
                      <thead class="thead-light sticky-top bg-light">
                        <tr>
                          <th>Personnel</th>
                          <?php foreach ($dates as $date): ?>
                            <th class="text-center align-middle" style="font-size: 13px;">
                              <?= date('M d', strtotime($date)) ?><br>
                              <small>Status / Hours</small>
                            </th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        usort($employees, function ($a, $b) {
                          return strcasecmp($a->last_name, $b->last_name);
                        });
                        foreach ($employees as $emp): ?>
                          <tr>
                            <td><?= $emp->last_name . ', ' . $emp->first_name ?></td>
                            <?php foreach ($dates as $date): ?>
                              <td class="text-center align-middle">
                                <div class="attendance-box">
                                  <select name="attendance_status[<?= $emp->personnelID ?>][<?= $date ?>]" class="form-control attendance-select" onchange="handleAttendanceChange(this)">
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Day Off">Day Off</option>
                                    <option value="Regular Holiday">Regular Holiday</option>
                                    <option value="Special Non-Working Holiday">Special Non-Working Holiday</option>
                                  </select>

                                  <input type="number" name="regular_hours[<?= $emp->personnelID ?>][<?= $date ?>]" class="form-control hours-input" min="0" max="8" step="0.25" placeholder="Reg Hrs">
                                  <input type="number" name="overtime_hours[<?= $emp->personnelID ?>][<?= $date ?>]" class="form-control hours-input mt-1" min="0" max="8" step="0.25" placeholder="OT Hrs">
                                </div>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>

                  <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-info shadow-sm px-4">
                      <i class="mdi mdi-content-save"></i> Save Attendance
                    </button>
                  </div>
                </form>
              <?php endif; ?>

            </div>
          </div>

        </div>
      </div>
      <!-- ✅ Generate Attendance modal: available for Admin & Payroll User -->
      <div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="generateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
          <form method="post" action="<?= base_url('WeeklyAttendance/generate') ?>">
            <div class="modal-content bg-white border-0 shadow-sm">
              <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="generateModalLabel">
                  <i class="mdi mdi-calendar-month"></i> Generate Attendance
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span>&times;</span>
                </button>
              </div>

              <div class="modal-body">
                <div class="form-group">
                  <label for="project" class="font-weight-bold">Project</label>
                  <select name="project" id="project" class="form-control" required>
                    <option value="" disabled selected>Select Project</option>
                    <?php foreach ($projects as $proj): ?>
                      <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="from" class="font-weight-bold">From</label>
                    <input type="date" name="from" id="from" class="form-control" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="to" class="font-weight-bold">To</label>
                    <input type="date" name="to" id="to" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="mdi mdi-check"></i> Generate
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <?php if ($flash): ?>
        <script>
          Swal.fire({
            title: `<span class="fb-style-title">⚠️ Attendance Already Exists</span>`,
            html: `
        <div class="fb-style-text text-center">
          <strong>Project:</strong> <?= htmlspecialchars($flash['projectTitle']) ?><br>
          <strong>Period:</strong> <?= date("F d, Y", strtotime($flash['from'])) ?> to <?= date("F d, Y", strtotime($flash['to'])) ?><br><br>
          This attendance has already been submitted. You may choose to view or delete it.
        </div>
      `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: 'View',
            cancelButtonText: 'Delete',
            reverseButtons: true,
            focusConfirm: false,
            width: '480px',
            customClass: {
              popup: 'fb-style-popup',
              title: 'text-dark',
              htmlContainer: 'text-muted',
              confirmButton: 'fb-style-confirm',
              cancelButton: 'fb-style-cancel'
            }
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = "<?= base_url('WeeklyAttendance/records?projectID=' . $flash['projectID'] . '&from=' . $flash['from'] . '&to=' . $flash['to']) ?>";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
              Swal.fire({
                title: `<span class="fb-style-title text-danger">🗑️ Confirm Deletion</span>`,
                html: `
            <div class="fb-style-text text-center">
              This will permanently delete the attendance record.<br>Are you sure?
            </div>
          `,
                icon: null,
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                  popup: 'fb-style-popup',
                  title: 'text-dark',
                  htmlContainer: 'text-muted',
                  confirmButton: 'fb-style-confirm btn-danger',
                  cancelButton: 'fb-style-cancel'
                }
              }).then((res) => {
                if (res.isConfirmed) {
                  const form = document.createElement('form');
                  form.method = 'POST';
                  form.action = "<?= base_url('WeeklyAttendance/deleteAttendance') ?>";
                  const inputs = {
                    projectID: "<?= $flash['projectID'] ?>",
                    from: "<?= $flash['from'] ?>",
                    to: "<?= $flash['to'] ?>"
                  };
                  for (const name in inputs) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = inputs[name];
                    form.appendChild(input);
                  }
                  document.body.appendChild(form);
                  form.submit();
                }
              });
            }
          });
        </script>
      <?php endif; ?>

      <?php include('includes/footer.php'); ?>
    </div>
  </div>

  <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/buttons.colVis.min.js"></script>
  <!-- <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script> -->
  <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
  <link href="<?= base_url(); ?>assets/libs/select2/select2.min.css" rel="stylesheet" />
  <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Focus 'from' when Generate Attendance modal shows (safe if missing)
      $('#generateModal').on('shown.bs.modal', function() {
        $('#from').trigger('focus');
      });

      <?php if ($attendance_success): ?>
        Swal.fire({
          title: `<span class="fb-style-title text-success">✅ Attendance Saved</span>`,
          html: `
      <div class="fb-style-text text-center">
        <strong>Project:</strong> <?= htmlspecialchars($attendance_success['projectTitle']) ?><br>
        <strong>Period:</strong> <?= date("F d, Y", strtotime($attendance_success['from'])) ?> to <?= date("F d, Y", strtotime($attendance_success['to'])) ?>
      </div>
    `,
          icon: null,
          showCancelButton: true,
          confirmButtonText: 'View Attendance',
          cancelButtonText: 'Close',
          reverseButtons: true,
          focusConfirm: false,
          width: '480px',
          customClass: {
            popup: 'fb-style-popup',
            title: 'text-dark',
            htmlContainer: 'text-muted',
            confirmButton: 'fb-style-confirm',
            cancelButton: 'fb-style-cancel'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "<?= base_url('WeeklyAttendance/records?projectID=' . $attendance_success['projectID'] . '&from=' . $attendance_success['from'] . '&to=' . $attendance_success['to']) ?>";
          }
        });
      <?php endif; ?>

      // Inputs in table (safe if table not present)
      document.querySelectorAll('#attendanceTable td').forEach(function(cell) {
        const checkbox = cell.querySelector('input[type="checkbox"]');
        const input = cell.querySelector('input[name*="[hours]"]');
        if (checkbox && input) {
          toggleState(checkbox, input);
          checkbox.addEventListener('change', function() {
            toggleState(this, input);
          });
        }

        function toggleState(checkbox, input) {
          if (checkbox.checked) {
            input.removeAttribute('readonly');
            input.style.pointerEvents = 'auto';
            input.style.backgroundColor = '';
            input.style.borderColor = '';
          } else {
            input.setAttribute('readonly', 'readonly');
            input.style.pointerEvents = 'none';
            input.style.backgroundColor = '#e9ecef';
            input.style.borderColor = 'red';
            input.value = '';
          }
        }
      });

      // Validate hours on submit (safe if form not present)
      const form = document.querySelector('form[action$="WeeklyAttendance/save"]');
      if (form) {
        form.addEventListener('submit', function(e) {
          let isValid = true,
            firstInvalid = null;
          document.querySelectorAll('.attendance-box').forEach(function(box) {
            const select = box.querySelector('.attendance-select');
            const regularHoursInput = box.querySelector('input[name^="regular_hours"]');
            const overtimeHoursInput = box.querySelector('input[name^="overtime_hours"]');

            // 🔁 Use readOnly (not disabled) since readOnly inputs still post
            if (regularHoursInput && !regularHoursInput.readOnly &&
              parseFloat(regularHoursInput.value || 0) > parseFloat(regularHoursInput.max || 8)) {
              alert(`Regular Hours exceeds max for ${select ? select.value : 'this row'}. Max allowed: ${regularHoursInput.max || 8}`);
              if (!firstInvalid) firstInvalid = regularHoursInput;
              isValid = false;
            }
            if (overtimeHoursInput && !overtimeHoursInput.readOnly &&
              parseFloat(overtimeHoursInput.value || 0) > parseFloat(overtimeHoursInput.max || 8)) {
              alert(`Overtime Hours exceeds max for ${select ? select.value : 'this row'}. Max allowed: ${overtimeHoursInput.max || 8}`);
              if (!firstInvalid) firstInvalid = overtimeHoursInput;
              isValid = false;
            }
          });
          if (!isValid) {
            e.preventDefault();
            Swal.fire({
              icon: 'warning',
              title: 'Missing Input',
              text: '⚠ Please input hours for all personnel.'
            });
            if (firstInvalid) firstInvalid.focus();
          }
        });
      }

      // Select2 (safe if no selects present)
      $('.attendance-select').select2({
        width: '180px',
        minimumResultsForSearch: Infinity
      });

      // Auto-hide toast
      setTimeout(() => {
        $('.toast').toast('hide');
      }, 6000);
    });

    function handleAttendanceChange(select) {
      const box = select.closest('.attendance-box');
      const regular = box.querySelector('input[name^="regular_hours"]');
      const ot = box.querySelector('input[name^="overtime_hours"]');
      const val = select.value;

      // default state
      [regular, ot].forEach(el => {
        if (!el) return;
        el.readOnly = false;
        el.max = 8;
        // don't force value here; user might have typed already
      });

      if (val === 'Absent' || val === 'Day Off') {
        if (regular) {
          regular.value = '0';
          regular.readOnly = true;
        }
        if (ot) {
          ot.value = '0';
          ot.readOnly = true;
        }
      } else if (val === 'Regular Holiday') {
        if (regular) regular.max = 16;
        if (ot) ot.max = 16;
      } else {
        // Present or Special Non-Working Holiday -> default max 8
        if (regular) regular.max = 8;
        if (ot) ot.max = 8;
      }
    }

    // Attendance Notes popup
    function showNotesSweetAlert() {
      Swal.fire({
        title: '<span style="font-size: 18px;"><span style="font-size: 22px;">📝</span> Attendance Notes</span>',
        html: `
          <div style="text-align: left; font-size: 14px; line-height: 1.6;">
              <span>⏱️ <strong>Work duration</strong> must be entered in <strong>hours</strong>.</span><br>
              <span>✍️ Use decimal values:</span><br>
              <ul style="margin-left: 1.2em; padding-left: 0;">
                  <li>0.25 = 15 minutes</li>
                  <li>0.50 = 30 minutes</li>
                  <li>0.75 = 45 minutes</li>
              </ul>
              <span>⚠️ <strong>You cannot save without entering hours.</strong></span>
          </div>
      `,
        icon: null,
        showConfirmButton: true,
        confirmButtonText: 'Understood',
        width: '420px',
        padding: '1.2em',
        customClass: {
          popup: 'shadow-sm rounded'
        }
      });
    }
  </script>

  <script>
    $(function() {
      if (!$.fn.DataTable) return;

      // Init with paging ON (keep your preferred settings)
      var table = $('#attendanceTable').DataTable({
        responsive: true,
        dom: 'frtip',
        // optional: control page size
        // pageLength: 10,
        // ordering: false
      });

      // On submit, normalize & include inputs from ALL pages (not just the current DOM)
      const $form = $('form[action$="WeeklyAttendance/save"]');
      if ($form.length) {
        $form.on('submit', function() {
          // 1) Normalize blanks to "0" and clamp to min/max
          $(this).find('input[name^="regular_hours"], input[name^="overtime_hours"]').each(function() {
            const min = parseFloat(this.min || '0');
            const max = parseFloat(this.max || '8');
            let v = parseFloat(this.value);
            if (isNaN(v)) v = 0;
            if (v < min) v = min;
            if (v > max) v = max;
            this.value = String(v);
          });

          // 2) Clone fields from non-visible pages so DataTables submits everything
          table.$('input, select, textarea').each(function() {
            if (!$.contains(document, this)) {
              const $hidden = $('<input type="hidden">')
                .attr('name', this.name)
                .val($(this).val());
              $form.append($hidden);
            }
          });
        });
      }
    });
  </script>


</body>

</html>