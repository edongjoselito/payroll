<!DOCTYPE html>
<html lang="en">
<head>
  <title>Generate Payroll</title>
  <?php include('includes/head.php'); ?>
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
                  <div class="form-group">
                    <label for="pid">Project</label>
                    <select name="pid" id="pid" class="form-control" required>
                      <option value="" disabled selected>Select project</option>
                      <?php foreach ($projects as $proj): ?>
                        <option value="<?= $proj->projectID ?>"><?= $proj->projectTitle ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="start">Start Date</label>
                      <input type="date" name="start" id="start" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="end">End Date</label>
                      <input type="date" name="end" id="end" class="form-control" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="rateType">Salary Type</label>
                    <select name="rateType" id="rateType" class="form-control" required>
                      <option value="" disabled selected>Select salary type</option>
                      <option value="Hour">Per Hour</option>
                      <option value="Day">Per Day</option>
                      <option value="Month">Per Month</option>
                    </select>
                  </div>
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

      </div>
    </div>

    <?php include('includes/footer.php'); ?>
  </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>
