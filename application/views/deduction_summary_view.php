<!DOCTYPE html>
<html lang="en">
<title>PMS - Deduction Summary</title>

<?php include('includes/head.php'); ?>
<style>
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.emoji-bounce {
  width: 80px;
  height: 80px;
  animation: bounce 2s infinite;
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

    <div class="page-title-box d-flex justify-content-between align-items-center">
        <h4 class="page-title">Deduction Summary</h4>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php elseif ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php
    $has_ca = false;
    $has_gd = false;
    foreach ($summary as $row) {
        if (!empty($row->ca_amount) && $row->ca_amount > 0) $has_ca = true;
        if (!empty($row->gd_amount) && $row->gd_amount > 0) $has_gd = true;
    }
    ?>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span><strong>All Record of Deductions</strong></span>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-toggle="collapse" data-target="#deductionTable" aria-expanded="true">
                Hide / View Table
            </button>
        </div>
        <div class="collapse show" id="deductionTable">
            <div class="card-body">
                <div class="table-responsive">
                   <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
  <thead class="thead-light">
    <tr>
      <th style="width:120px;">Date</th>
      <th>Personnel Name</th>
      <th>Type</th>
      <th>Description</th>
      <th class="text-right" style="width:140px;">Amount</th>
    </tr>
  </thead>

  <tbody>
    <?php
      $hasData  = !empty($summary);
      $total_ca = 0.0;
      $total_od = 0.0; // Other Deduction
      $total_gd = 0.0;

      if ($hasData):
        foreach ($summary as $row):
          $amt = (float)$row->amount;

          if ($row->d_type === 'Cash Advance') {
            $total_ca += $amt;
          } elseif ($row->d_type === 'Other Deduction') {
            $total_od += $amt;
          } elseif ($row->d_type === "Gov't Deduction") {
            $total_gd += $amt;
          }
    ?>
      <tr>
        <!-- sort strictly by DATE (oldest -> newest) -->
        <td data-order="<?= date('Y-m-d', strtotime($row->date)) ?>">
          <?= date('Y-m-d', strtotime($row->date)) ?>
        </td>
        <td><?= $row->full_name ?></td>
        <td><?= $row->d_type ?></td>
        <td><?= $row->description ?></td>
        <td class="text-right">₱<?= number_format($amt, 2) ?></td>
      </tr>
    <?php
        endforeach;
      else:
    ?>
      <tr>
        <td colspan="5" class="text-center text-muted">No deductions found.</td>
      </tr>
    <?php endif; ?>
  </tbody>

  <?php if ($hasData): ?>
  <tfoot>
    <tr class="font-weight-bold bg-white border-top">
      <td colspan="4" class="text-right">TOTAL CASH ADVANCE:</td>
      <td class="text-right">₱<?= number_format($total_ca, 2) ?></td>
    </tr>
    <tr class="font-weight-bold bg-white">
      <td colspan="4" class="text-right">TOTAL OTHER DEDUCTIONS:</td>
      <td class="text-right">₱<?= number_format($total_od, 2) ?></td>
    </tr>
    <tr class="font-weight-bold bg-white">
      <td colspan="4" class="text-right">TOTAL GOV’T DEDUCTIONS:</td>
      <td class="text-right">₱<?= number_format($total_gd, 2) ?></td>
    </tr>
    <tr class="font-weight-bold bg-light">
      <td colspan="4" class="text-right">GRAND TOTAL:</td>
      <td class="text-right">₱<?= number_format($total_ca + $total_od + $total_gd, 2) ?></td>
    </tr>
  </tfoot>
  <?php endif; ?>
</table>

                </div>
            </div>
        </div>
    </div>

</div>
</div>
<?php include('includes/footer.php'); ?>
</div>
</div>

<!-- JS -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
$(function () {
  if ($.fn.DataTable.isDataTable('#datatable')) {
    $('#datatable').DataTable().destroy();
  }
 $('#datatable').DataTable({
  // 0 Date | 1 Name | 2 Type | 3 Description | 4 Amount
  order: [[1, 'asc'], [0, 'asc']],   // Name first, then Date
  columnDefs: [
    { targets: 0, type: 'date' },
    { targets: 4, className: 'text-right' }
  ],
  pageLength: 25
});

});
</script>

</body>
</html>
