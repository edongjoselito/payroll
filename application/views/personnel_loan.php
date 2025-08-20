<!DOCTYPE html>
<html lang="en">
<head>
  <title>PMS - Personnel Loan</title>
  <?php include('includes/head.php'); ?>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">
</head>
<style>
.btn {
  transition: all 0.25s ease-in-out;
}

.btn:hover {
  transform: scale(1.05);
  opacity: 0.95;
}

.btn-primary:hover,
.btn-success:hover,
.btn-info:hover,
.btn-danger:hover {
  box-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
  border-color: rgba(0, 123, 255, 0.3);
}
.toast-header-success {
    background-color: #28a745 !important;
    color: #fff;
    border-radius: 4px 4px 0 0;
}

.toast-body-success {
    background-color: #eaf9ef;
    color: #155724;
}

.toast-header-danger {
    background-color: #dc3545 !important;
    color: #fff;
    border-radius: 4px 4px 0 0;
}

.toast-body-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.toast-header i {
    font-size: 1.1rem;
    margin-right: 0.6rem;
}

.toast-header strong {
    font-weight: 600;
    font-size: 0.95rem;
    margin-right: auto;
}

.toast .close,
.toast .btn-close {
    color: white;
    font-size: 1rem;
    opacity: 0.85;
    margin-left: 0.5rem;
}

.toast .close:hover,
.toast .btn-close:hover {
    opacity: 1;
}
.print-header,
.print-footer,
.print-header-space,
.print-footer-space { display: none; }

@media print {
  .no-print,
  .page-title-box,
  .toast, #sessionToast,
  .modal,
  .btn,
  .dataTables_length,
  .dataTables_filter,
  .dataTables_info,
  .dataTables_paginate,
  .dataTables_wrapper .dt-buttons { display: none !important; }

  .left-side-menu, .navbar-custom, .footer, .card-header { display: none !important; }

  .content-page, .content, .container-fluid, .card, .card-body {
    padding: 0 !important; margin: 0 !important; box-shadow: none !important; border: 0 !important;
  }

  .print-header,
  .print-footer { display: block; position: fixed; left: 0; right: 0; color: #000; }
  .print-header { top: 0; padding: 8px 0; }
  .print-footer { bottom: 0; font-size: 12px; text-align: right; padding: 6px 12px; }

  .print-header-space { height: 110px; display: block; }
  .print-footer-space { height: 40px; display: block; }

  .lh-wrap { display: flex; align-items: center; gap: 12px; padding: 0 12px; }
  .lh-logo { height: 60px; width: auto; }
  .lh-text h2 { font-size: 18px; margin: 0; }
  .lh-text .sub { font-size: 12px; margin: 2px 0 0; }
  .lh-meta { font-size: 12px; margin-top: 4px; }
  .lh-line { height: 2px; background: #000; margin: 6px 12px 0; }

  #datatable thead th,
  #datatable th.sorting_1,#datatable th.sorting_2,#datatable th.sorting_3,
  #datatable td.sorting_1,#datatable td.sorting_2,#datatable td.sorting_3 {
    background: #fff !important; color: #000 !important;
  }
  .table-striped tbody tr:nth-of-type(odd),
  .table-hover tbody tr:hover { background: #fff !important; }

  #datatable, #datatable th, #datatable td { border-color: #000 !important; }

  .badge { background: #fff !important; color: #000 !important; border: 1px solid #000 !important; }

  thead { display: table-header-group; }
  tfoot { display: table-footer-group; }
  tr, img { page-break-inside: avoid; }

  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}

@page { size: A4 portrait; margin: 15mm; }

</style>

<body>

<div id="wrapper">
  <?php include('includes/top-nav-bar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <div class="page-title-box d-flex justify-content-between align-items-center">
  <h4 class="page-title">Personnel's Loan</h4>
  <div>
    <button class="btn btn-primary btn-md no-print" data-toggle="modal" data-target="#assignLoanModal">
      <i class="mdi mdi-plus"></i> Add Loan
    </button>
    <button id="printBtn" class="btn btn-outline-secondary btn-md no-print" type="button">
      <i class="fas fa-print"></i> Print
    </button>
  </div>
</div>


 <?php
$success = $this->session->flashdata('success');
$error = $this->session->flashdata('error');

if ($success || $error):
    $message = $success ?: $error;
    $isDelete = stripos($message, 'deleted') !== false;
    $type = $error || $isDelete ? 'danger' : 'success';
    $icon = $type === 'success' ? 'check-circle' : 'trash-alt';
    $title = $type === 'success' ? 'Success' : 'Deleted';
?>
<div aria-live="polite" aria-atomic="true" style="position: fixed; top: 75px; left: 50%; transform: translateX(-50%); z-index: 1055;">
    <div class="toast fade" id="sessionToast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3500" style="min-width: 320px;">
        <div class="toast-header toast-header-<?= $type ?>">
            <i class="fas fa-<?= $icon ?> me-2"></i>
            <strong class="me-auto"><?= $title ?></strong>
            <button type="button" class="close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body toast-body-<?= $type ?>">
            <?= $message ?>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- PRINT HEADER (print only) -->
<div class="print-header">
  <div class="lh-wrap">
    <img class="lh-logo" src="<?= base_url('assets/images/pms-logo1.png') ?>" alt="PMS Logo">
    <div class="lh-text">
      <h2>Payroll Management System</h2>
      <div class="sub">Personnel Loan Report</div>
      <div class="lh-meta">Printed on: <span id="printTimestamp"></span></div>
    </div>
  </div>
  <div class="lh-line"></div>
</div>
<div class="print-header-space"></div>

<!-- (optional) PRINT FOOTER -->
<div class="print-footer">Generated by PMS</div>
<div class="print-footer-space"></div>


       <div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
        <thead class="thead-light">
          <tr>
            <th>Personnel Name</th>
            <th>Position</th>
            <th>Loan Description</th>
            <th>Amount</th>
            <th>Monthly Deduction</th>
            <th>Date Assigned</th>
            <th class="no-print">Status</th>
            <th class="no-print">Action</th>
          </tr>
        </thead>
       <tbody>
  <?php if (!empty($assigned_loans)): ?>
    <?php foreach ($assigned_loans as $loan): ?>
      <tr>
        <td><?= "{$loan->last_name}, {$loan->first_name} " . ($loan->middle_name ?? '') . " " . ($loan->name_ext ?? '') ?></td>
        <td><?= htmlspecialchars($loan->position ?? '') ?></td>
        <td><?= htmlspecialchars($loan->loan_description ?? 'N/A') ?></td>
        <td>₱<?= number_format($loan->amount, 2) ?></td>
        <td>₱<?= number_format($loan->monthly_deduction, 2) ?></td>
        <td><?= $loan->date_assigned ? date('Y-m-d', strtotime($loan->date_assigned)) : 'N/A' ?></td>
        <td class="no-print">
          
          <?php if ($loan->status == 1): ?>
            <span class="badge badge-success">Active</span>
          <?php else: ?>
            <span class="badge badge-secondary">Deducted</span>
          <?php endif; ?>
        </td>
        <td class="no-print">
          <!-- EDIT Button -->
          <button class="btn btn-outline-info btn-sm me-1 edit-btn"
            data-loanid="<?= $loan->loan_id ?>"
            data-personnelid="<?= $loan->personnelID ?>"
            data-description="<?= $loan->loan_description ?>"
            data-amount="<?= $loan->amount ?>"
            data-monthly="<?= $loan->monthly_deduction ?>"
            data-toggle="tooltip"
            title="Edit Loan">
            <i class="fas fa-edit"></i>
          </button>

          <!-- DELETE Button -->
          <a href="<?= base_url('Loan/delete_personnel_loan/' . $loan->loan_id . '/' . $loan->personnelID) ?>"
            class="btn btn-outline-danger btn-sm"
            onclick="return confirm('Delete this loan?')"
            data-toggle="tooltip"
            title="Delete Loan">
            <i class="fas fa-trash-alt"></i>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="8" class="text-center text-muted">No data available in table</td>
    </tr>
  <?php endif; ?>
</tbody>

      </table>
    </div>
  </div>
</div>


        <!-- Assign Loan Modal -->
        <div class="modal fade" id="assignLoanModal" tabindex="-1" role="dialog">
          <div class="modal-dialog">
            <form method="post" action="<?= base_url('Loan/assign_personnel_loan') ?>">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title text-dark">Assign Loan</h5>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                  <div class="form-group">
                    <label>Personnel</label>
                    <select name="personnelID" class="form-control" required>
                      <option value="">Select Personnel</option>
                      <?php foreach($personnel as $p): ?>
                        <option value="<?= $p->personnelID ?>">
                          <?= $p->last_name . ', ' . $p->first_name ?>
                          <?= ($p->middle_name) ? ' ' . substr($p->middle_name, 0, 1) . '.' : '' ?>
                          <?= ($p->name_ext) ? ' ' . $p->name_ext : '' ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Loan Description</label>
                    <input type="text" name="loan_description" class="form-control" required>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label>Loan Amount</label>
                      <input type="number" name="loan_amount" class="form-control" required step="0.01">
                    </div>
                    <div class="form-group col-md-6">
                      <label>Monthly Deduction</label>
                      <input type="number" name="monthly_deduction" class="form-control" required step="0.01">
                    </div>
                  </div>
                </div>

                <div class="modal-footer">
                  <button type="submit" class="btn btn-info">Save</button>
                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Edit Loan Modal -->
        <div class="modal fade" id="editLoanModal" tabindex="-1">
          <div class="modal-dialog">
            <form method="post" action="<?= base_url('Loan/update_personnel_loan') ?>">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title text-dark">Edit Loan</h5>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                  <input type="hidden" name="loan_id" id="editLoanID">
                  <input type="hidden" name="personnelID" id="editPersonnelID">

                  <div class="form-group">
                    <label>Loan Description</label>
                    <input type="text" name="loan_description" id="editLoanDescription" class="form-control" required>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label>Loan Amount</label>
                      <input type="number" name="loan_amount" id="editLoanAmount" class="form-control" required step="0.01">
                    </div>
                    <div class="form-group col-md-6">
                      <label>Monthly Deduction</label>
                      <input type="number" name="monthly_deduction" id="editMonthlyDeduction" class="form-control" required step="0.01">
                    </div>
                  </div>
                </div>

                <div class="modal-footer">
                  <button class="btn btn-info" type="submit">Update</button>
                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
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

<!-- Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
$(document).on('click', '.edit-btn', function () {
    $('#editLoanID').val($(this).data('loanid'));
    $('#editPersonnelID').val($(this).data('personnelid'));
    $('#editLoanDescription').val($(this).data('description'));
    $('#editLoanAmount').val($(this).data('amount'));
    $('#editMonthlyDeduction').val($(this).data('monthly'));
    $('#editLoanModal').modal('show');
});
</script>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
</script>
<script>
$(document).ready(function () {
    $('#sessionToast').toast('show');
});
</script>
<script>
$(function () {
  // Prevent double init if global datatables.init.js also touches #datatable
  if ($.fn.DataTable.isDataTable('#datatable')) {
    $('#datatable').DataTable().destroy();
  }

  // Columns: 0 Name | 1 Position | 2 Loan Desc | 3 Amount | 4 Monthly | 5 Date Assigned | 6 Status | 7 Action
  var table = $('#datatable').DataTable({
    order: [[5, 'asc'], [0, 'asc']],           // Date Assigned ASC, then Name ASC
    columnDefs: [
      { targets: [5], type: 'date' },
      { targets: [7], orderable: false }       // Action not sortable
    ],
    pageLength: 10
  });

  // PRINT: wait for draw of ALL rows, hide Action col, then print and restore
  $('#printBtn').on('click', function () {
    $('#printTimestamp').text(new Date().toLocaleString());

    var previousLength = table.page.len();
    var actionCol = 7;
    var actionWasVisible = table.column(actionCol).visible();

    function restore() {
      table.page.len(previousLength).draw(false);
      table.column(actionCol).visible(actionWasVisible, false);
      window.removeEventListener('afterprint', restore);
      $(window).off('focus.printRestore');
    }
    window.addEventListener('afterprint', restore, { once: true });
    $(window).one('focus.printRestore', restore);

    // After redraw (showing ALL rows), hide Action and print
    table.one('draw', function () {
      table.column(actionCol).visible(false, false);
      window.print();
    });

    // Trigger redraw with ALL rows
    table.page.len(-1).draw();
  });

  // Keep your existing UI behaviors
  $('[data-toggle="tooltip"]').tooltip();
  $('#sessionToast').toast('show');
});
</script>

</body>
</html>
