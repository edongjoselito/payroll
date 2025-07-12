<!DOCTYPE html>
<html lang="en">
<head>
  <title>Generate Payroll</title>
  <?php include('includes/head.php'); ?>
  <!-- Include Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container--default .select2-selection--single {
      height: 38px;
      padding: 6px 12px;
      font-size: 14px;
      border: 1px solid #ced4da;
      border-radius: 0.25rem;
      display: flex;
      align-items: center;
    }
    .select2-results__option {
      padding: 10px;
      font-size: 14px;
    }
  </style>
</head>
<body>

<div id="wrapper">
  <?php include('includes/top-nav-bar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="page-title mb-4">Payroll Generation</h4>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#generatePayrollModal">
              <i class="mdi mdi-calculator-variant-outline"></i> Generate Payroll
            </button>
          </div>
        </div>

        <!-- Modal Section -->
        <div class="modal fade" id="generatePayrollModal" tabindex="-1" role="dialog" aria-labelledby="generatePayrollLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <form method="get" action="<?= base_url('project/payroll_report') ?>" target="_blank" id="payrollForm">
              <div class="modal-content border-0 shadow-sm">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title" id="generatePayrollLabel">
                    <i class="mdi mdi-file-document-box"></i> Generate Payroll Report
                  </h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                  </button>
                </div>
                
                <div class="modal-body">
                  <div class="form-row">
                    <div class="form-group col-md-12">
                      <label for="pid" class="font-weight-bold">Project</label>
                      <select name="pid" id="pid" class="form-control select2" required>
                        <option value="" disabled selected>Select project</option>
                        <?php foreach ($projects as $proj): ?>
                          <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  
                  <div class="form-row">
                    <div class="form-group col-md-12">
                      <label for="attendanceBatch" class="font-weight-bold">Attendance Records</label>
                      <select name="attendanceID" id="attendanceBatch" class="form-control select2" required>
                        <option value="" disabled selected>Select Saved Attendance</option>
                        <?php foreach ($attendance_periods as $batch): ?>
                          <option 
                            value="<?= $batch->projectID ?>-<?= $batch->start ?>-<?= $batch->end ?>"
                            data-project="<?= $batch->projectID ?>"
                            data-start="<?= $batch->start ?>"
                            data-end="<?= $batch->end ?>">
                            <?= date('F d', strtotime($batch->start)) ?> to <?= date('F d, Y', strtotime($batch->end)) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  
                  <input type="hidden" name="start" id="start">
                  <input type="hidden" name="end" id="end">
                </div>
                
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-check"></i> Generate
                  </button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <!-- End Modal Section -->

      </div>
    </div>
    <?php include('includes/footer.php'); ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const pidSelect = document.getElementById('pid');
  const batchSelect = document.getElementById('attendanceBatch');

  pidSelect.addEventListener('change', function () {
    const selectedProjectID = this.value;
    const options = batchSelect.querySelectorAll('option');

    batchSelect.value = "";
    options.forEach(option => {
      if (option.value === "") {
        option.style.display = 'block';
      } else if (option.getAttribute('data-project') === selectedProjectID) {
        option.style.display = 'block';
      } else {
        option.style.display = 'none';
      }
    });
  });

  batchSelect.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    document.getElementById('start').value = selected.getAttribute('data-start');
    document.getElementById('end').value = selected.getAttribute('data-end');
  });
});

$(document).ready(function () {
  $('.select2').select2({
    width: '100%',
    dropdownParent: $('#generatePayrollModal')
  });
});
</script>

</body>
</html>
