<!DOCTYPE html>
<html lang="en">
<head>
  <title>Saved Bi-Month Payroll</title>
  <?php include('includes/head.php'); ?>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
  <style>
    .dataTables_length { display: none; } 
    .btn { transition: all 0.3s ease; }
    .btn:hover { transform: scale(1.05); opacity: 0.95; }
    .btn-dark:hover { box-shadow: 0 0 8px rgba(0,0,0,0.3); }
    .gap-2 { gap: .5rem; }

    #payrollTable td, #payrollTable th {
      vertical-align: middle;
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

        <!-- Top Nav Title -->
        <div class="page-title-box d-flex justify-content-between align-items-center mb-3">
          <h4 class="page-title mb-0">Summary & Saved Bi-Month Payroll</h4>
          <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('Generatepayroll/form'); ?>" class="btn btn-primary">
              <i class="mdi mdi-arrow-left"></i> Back
            </a>
          </div>
        </div>
          <em>Click <strong>Open</strong> to view saved bi-month/month payroll</em>

        <?php if (!empty($this->session->flashdata('msg'))): ?>
          <div class="alert alert-info"><?= $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="table-responsive">
              <table id="payrollTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th>#</th>
                    <th>Period</th>
                    <th>Created</th>
                    <th class="text-right">Gross</th>
                    <th class="text-right">Deduction</th>
                    <th class="text-right">Net</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($batches)): ?>
                    <?php $i=1; foreach ($batches as $b): $tot = json_decode($b->totals_json,true) ?: []; ?>
                      <tr>
                        <td><?= $i++; ?></td>
                        <td><?= date('M d, Y', strtotime($b->start_date)) ?> – <?= date('M d, Y', strtotime($b->end_date)) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($b->created_at)) ?></td>
                        <td class="text-right">₱<?= number_format((float)($tot['sum_gross'] ?? 0), 2) ?></td>
                        <td class="text-right">₱<?= number_format((float)($tot['sum_ded'] ?? 0), 2) ?></td>
                        <td class="text-right">₱<?= number_format((float)($tot['sum_net'] ?? 0), 2) ?></td>
                        <td class="text-center">
                          <a target="_blank"
                             href="<?= base_url('MonthlyPayroll/view_formatted?start='.$b->start_date.'&end='.$b->end_date) ?>"
                             class="btn btn-sm btn-dark">
                            Open (saved)
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">No saved bi-month payroll yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
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
      paging: false,
      searching: true,
      ordering: true,
      responsive: true
    });
  });
</script>

</body>
</html>
