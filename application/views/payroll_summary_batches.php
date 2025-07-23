<!DOCTYPE html>
<html lang="en">
<head>
  <title>Payroll Summary</title>
  <?php include('includes/head.php'); ?>
</head>

<body>
<div id="wrapper">
  <?php include('includes/top-nav-bar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <div class="page-title-box d-flex justify-content-between align-items-center mb-3">
          <h4 class="page-title mb-0">Payroll Summary</h4>
          <a href="<?= base_url('Generatepayroll/form') ?>" class="btn btn-primary">
            <i class="mdi mdi-arrow-left"></i> Back to Payroll
          </a>
        </div>
<?php if ($this->session->flashdata('success')): ?>
  <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
  <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
<?php endif; ?>

        <div class="card border-0 shadow-sm">
          <div class="card-body">

            <?php if (empty($batch_summaries)): ?>
              <div class="alert alert-warning">No payroll summaries found.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead class="thead-light">
                    <tr>
                      <th>Project Title</th>
                      <th>Location</th>
                      <th>Payroll Period</th>
                      <th class="text-right">Total Payroll (₱)</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
  <?php foreach ($batch_summaries as $batch): ?>
    
    <tr>
  <td><?= $batch['projectTitle'] ?></td>
  <td><?= $batch['projectLocation'] ?></td>
  <td><?= date('M d, Y', strtotime($batch['start_date'])) ?> – <?= date('M d, Y', strtotime($batch['end_date'])) ?></td>
  <td class="text-right font-weight-bold text-success">₱ <?= number_format($batch['total_payroll'], 2) ?></td>
  <td class="text-center">
    <form method="post" action="<?= base_url('project/delete_summary_batch') ?>" onsubmit="return confirm('Are you sure you want to delete this summary?');">
      <input type="hidden" name="projectID" value="<?= $batch['projectID'] ?>">
      <input type="hidden" name="start_date" value="<?= $batch['start_date'] ?>">
      <input type="hidden" name="end_date" value="<?= $batch['end_date'] ?>">
      <button type="submit" class="btn btn-sm btn-danger">
        <i class="mdi mdi-delete"></i> Delete
      </button>
    </form>
  </td>
</tr>

  <?php endforeach; ?>
</tbody>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3" class="text-right font-weight-bold">Grand Total</td>
                      <td class="text-right font-weight-bold text-primary">₱ <?= number_format($grand_total, 2) ?></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            <?php endif; ?>

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
