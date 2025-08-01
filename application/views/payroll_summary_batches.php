<!DOCTYPE html>
<html lang="en">
<head>
  <title>Payroll Summary</title>
  <?php include('includes/head.php'); ?>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<style>
  .dataTables_length {
    display: none;
  }
  /* Smooth animation for all buttons */
.btn {
  transition: all 0.3s ease;
}

/* Slight zoom and shadow on hover */
.btn:hover {
  transform: scale(1.05);
  opacity: 0.95;
}

/* Icon button hover with colored glow */
.btn-outline-danger:hover {
  background-color: #dc3545;
  color: white;
  box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
  border-color: #dc3545;
}

.btn-primary:hover {
  box-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
  border-color: rgba(0, 123, 255, 0.3);
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
  <table id="payrollTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
    <thead class="thead-light">
      <tr>
        <th>Project Title</th>
        <th>Location</th>
        <th>Payroll Period</th>
        <th class="text-right">Payroll Gross</th>
        <th class="text-center">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($batch_summaries as $batch): ?>
        <tr>
          <td><?= $batch['projectTitle'] ?></td>
          <td><?= $batch['projectLocation'] ?></td>
          <td><?= date('M d, Y', strtotime($batch['start_date'])) ?> – <?= date('M d, Y', strtotime($batch['end_date'])) ?></td>
          <td class="text-right text-success">₱ <?= number_format($batch['gross_total'], 2) ?></td>
          <td class="text-center">
            <form method="post" action="<?= base_url('Project/delete_summary_batch') ?>" onsubmit="return confirm('Are you sure you want to delete this summary?');">
              <input type="hidden" name="projectID" value="<?= $batch['projectID'] ?>">
              <input type="hidden" name="start_date" value="<?= $batch['start_date'] ?>">
              <input type="hidden" name="end_date" value="<?= $batch['end_date'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Summary">
  <i class="mdi mdi-delete"></i>
</button>

            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" class="text-right font-weight-bold">Grand Totals</td>
        <td class="text-right font-weight-bold text-success">₱ <?= number_format($grand_total_gross, 2) ?></td>
        <td></td>
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
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
  $('#payrollTable').DataTable({
    paging: false,       // Disable pagination
    searching: true,     // Enable search
    ordering: true,      // Enable column sorting
    responsive: true     // Make it mobile-friendly
  });
});
</script>


</body>
</html>
