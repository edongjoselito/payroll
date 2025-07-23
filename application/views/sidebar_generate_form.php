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
    .select2-selection--single {
  height: 38px !important;
  padding: 6px 12px !important;
  font-size: 14px;
  border: 1px solid #ced4da !important;
  border-radius: 0.25rem !important;
  display: flex !important;
  align-items: center !important;
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
    
<button type="button" class="btn btn-info" data-toggle="modal" data-target="#viewSavedPayrollModal">
  View Saved Payroll
</button>
<button type="button" class="btn btn-success" data-toggle="modal" data-target="#payrollSummaryModal">
  <i class="mdi mdi-chart-bar"></i> Payroll Summary
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
<!-- View Saved Payroll Modal -->
<div class="modal fade" id="viewSavedPayrollModal" tabindex="-1" role="dialog" aria-labelledby="viewSavedPayrollModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <form action="<?= base_url('project/view_payroll_batch') ?>" method="get">
      <div class="modal-content border-0 shadow-sm">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="viewSavedPayrollModalLabel">
            <i class="mdi mdi-clipboard-text"></i> View Saved Payroll Batch
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label for="savedProjectSelect" class="font-weight-bold">Project</label>
            <select id="savedProjectSelect" class="form-control select2" required>
              <option disabled selected>Select project</option>
              <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Please select a project.</div>
          </div>

          <div class="form-group mt-3">
            <label for="savedBatchSelect" class="font-weight-bold">Saved Payroll</label>
           <select name="batch_id" id="savedBatchSelect" class="form-control select2" required>
  <option disabled selected>Choose saved payroll</option>
  <?php foreach ($batches as $batch): ?>
    <option 
      value="<?= $batch->projectID . '|' . $batch->start_date . '|' . $batch->end_date ?>"
      data-project="<?= $batch->projectID ?>">
      <?= date('M d, Y', strtotime($batch->start_date)) ?> - <?= date('M d, Y', strtotime($batch->end_date)) ?>
    </option>
  <?php endforeach; ?>
</select>

            <div class="invalid-feedback">Please select.</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-info">
            <i class="mdi mdi-eye"></i> View Payroll
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- Payroll Summary Modal -->
<div class="modal fade" id="payrollSummaryModal" tabindex="-1" role="dialog" aria-labelledby="payrollSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <form action="<?= base_url('project/view_payroll_summary_batches') ?>" method="get">
      <div class="modal-content border-0 shadow-sm">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="payrollSummaryModalLabel">
            <i class="mdi mdi-chart-bar"></i> Select Project for Payroll Summary
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label for="summary_project_id" class="font-weight-bold">Project</label>
            <select name="project_id" id="summary_project_id" class="form-control select2" required>
              <option disabled selected>Select Project</option>
              <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="mdi mdi-eye"></i> View Summary
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>



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
  // === GENERATE PAYROLL MODAL ===
  const pidSelect = document.getElementById('pid');
  const batchSelect = document.getElementById('attendanceBatch');

  if (batchSelect) {
    // Hide all batch options initially
    batchSelect.querySelectorAll('option').forEach(opt => {
      if (opt.value !== '') opt.style.display = 'none';
    });

    pidSelect.addEventListener('change', function () {
      const selectedProjectID = this.value;
      batchSelect.value = '';

      batchSelect.querySelectorAll('option').forEach(option => {
        const project = option.getAttribute('data-project');
        if (!project || project === selectedProjectID) {
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
  }

  // === VIEW SAVED PAYROLL MODAL ===
  const savedProject = document.getElementById('savedProjectSelect');
  const savedBatch = document.getElementById('savedBatchSelect');
  const submitBtn = document.querySelector('#viewSavedPayrollModal button[type="submit"]');

  if (savedBatch) {
    // Hide all saved batches initially
    savedBatch.querySelectorAll('option').forEach(opt => {
      if (opt.value !== '') opt.style.display = 'none';
    });

    savedProject.addEventListener('change', function () {
      const selectedPID = this.value;
      savedBatch.value = '';
      submitBtn.disabled = true;

      savedBatch.querySelectorAll('option').forEach(opt => {
        const project = opt.getAttribute('data-project');
        if (!project || project === selectedPID) {
          opt.style.display = 'block';
        } else {
          opt.style.display = 'none';
        }
      });
    });

    // Enable View button only when a batch is selected
    savedBatch.addEventListener('change', function () {
      submitBtn.disabled = !this.value;
    });

    // Start with disabled button
    submitBtn.disabled = true;
  }
});
</script>

<script>
$(document).ready(function () {
  $('.select2').select2({
    width: '100%',
    dropdownParent: $('.modal') 
  });
});
</script>

</body>
</html>
