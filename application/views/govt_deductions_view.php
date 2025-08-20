<!DOCTYPE html>
<html lang="en">
<head>
    <title>PMS - Government Deductions</title>
    <?php include('includes/head.php'); ?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">
</head>
<style>
/* Smooth button animation */
.btn {
    transition: all 0.25s ease-in-out;
}

/* Scale on hover */
.btn:hover {
    transform: scale(1.05);
    opacity: 0.95;
}

/* Glow on hover for main button types */
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
  <h4 class="page-title">Government Deductions</h4>
  <div>
    <button class="btn btn-primary no-print" data-toggle="modal" data-target="#addGovDeductionModal">+ Add Deduction</button>
    <button id="printBtn" class="btn btn-outline-secondary no-print" type="button">
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
      <div class="sub">Government Deductions Report</div>
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
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Deduct From</th>
                                        <th>Deduct To</th>
                                        <th class="no-print">Action</th>
                                    </tr>
                                </thead>
                              <tbody>
  <?php if (!empty($deductions)): ?>
    <?php foreach ($deductions as $row): ?>
      <tr>
        <td><?= $row->fullname ?></td>
        <td><?= $row->description ?></td>
        <td>₱<?= number_format($row->amount, 2) ?></td>
        <td><?= $row->date ?></td>
        <td><?= $row->deduct_from ?? '—' ?></td>
        <td><?= $row->deduct_to ?? '—' ?></td>
        <td>
          <!-- EDIT Button -->
          <button class="btn btn-outline-info btn-sm me-1" 
                  data-toggle="modal" 
                  data-target="#editModal<?= $row->id ?>">
            <i class="fas fa-edit" data-toggle="tooltip" title="Edit Deduction"></i>
          </button>

          <!-- DELETE Button -->
          <a href="<?= base_url('Borrow/delete_govt_deduction/' . $row->id) ?>" 
             class="btn btn-outline-danger btn-sm" 
             onclick="return confirm('Delete this deduction?')">
            <i class="fas fa-trash-alt" data-toggle="tooltip" title="Delete Deduction"></i>
          </a>
        </td>
      </tr>

      <!-- Edit Modal -->
      <div class="modal fade" id="editModal<?= $row->id ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" action="<?= base_url('Borrow/update_govt_deduction/' . $row->id) ?>">
              <div class="modal-header">
                <h5 class="modal-title">Edit Deduction</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
<div class="form-group">
  <label>Description</label>
  <input type="text"
         name="description"
         class="form-control"
         value="<?= htmlspecialchars($row->description ?? '', ENT_QUOTES, 'UTF-8') ?>"
         required
         maxlength="150"
         list="govt-desc-suggestions-<?= $row->id ?>">
  <datalist id="govt-desc-suggestions-<?= $row->id ?>">
    <option value="SSS"></option>
    <option value="PhilHealth"></option>
    <option value="Pag-IBIG"></option>
  </datalist>
  <small class="form-text text-muted">This exact text will appear in payroll.</small>
</div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Amount</label>
                    <input type="number" name="amount" class="form-control" value="<?= $row->amount ?>" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="<?= $row->date ?>" required>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Deduct From</label>
                    <input type="date" name="deduct_from" class="form-control" value="<?= $row->deduct_from ?>">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Deduct To</label>
                    <input type="date" name="deduct_to" class="form-control" value="<?= $row->deduct_to ?>">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="7" class="text-center text-muted">No data available in table</td>
    </tr>
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

<!-- Add Modal -->
<div class="modal fade" id="addGovDeductionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Borrow/save_govt_deduction') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Government Deduction</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Personnel Name</label>
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
  <label>Description</label>
  <input type="text"
         name="description"
         class="form-control"
         placeholder=""
         required
         maxlength="150"
         list="govt-desc-suggestions">
  <datalist id="govt-desc-suggestions">
    <option value="SSS"></option>
    <option value="PhilHealth"></option>
    <option value="Pag-IBIG"></option>
  </datalist>
  <small class="form-text text-muted"><strong>NOTE:</strong> always include Philhealth , SSS , Pag-IBIG before your comment to reflect at payroll.</small>
    <small class="form-text text-muted"><strong>EXAMPLE:</strong> PhilHealth - Month of June , SSS - Month of July , Pag-IBIG - Month of August ,</small>

</div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Deduct From</label>
                            <input type="date" name="deduct_from" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Deduct To</label>
                            <input type="date" name="deduct_to" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
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
$(document).ready(function () {
    $('#sessionToast').toast('show');
});
</script>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
</script>
<script>
$(function () {
  if ($.fn.DataTable.isDataTable('#datatable')) {
    $('#datatable').DataTable().destroy();
  }

  // Columns: 0 Name | 1 Desc | 2 Amount | 3 Date | 4 From | 5 To | 6 Action
  var table = $('#datatable').DataTable({
    order: [[3, 'asc'], [0, 'asc']],          // Date ASC, then Name ASC
    columnDefs: [
      { targets: [3], type: 'date' },
      { targets: [6], orderable: false }  
    ],
    pageLength: 10
  });

  $('#printBtn').on('click', function () {
    $('#printTimestamp').text(new Date().toLocaleString());

    var previousLength = table.page.len();
    var actionCol = 6;
    var actionWasVisible = table.column(actionCol).visible();

    function restore() {
      table.page.len(previousLength).draw(false);
      table.column(actionCol).visible(actionWasVisible, false);
      window.removeEventListener('afterprint', restore);
      $(window).off('focus.printRestore');
    }
    window.addEventListener('afterprint', restore, { once: true });
    $(window).one('focus.printRestore', restore);

    table.one('draw', function () {
      table.column(actionCol).visible(false, false);
      window.print();
    });

    table.page.len(-1).draw();
  });

  $('#sessionToast').toast('show');
  $('[data-toggle="tooltip"]').tooltip();
});
</script>


</body>
</html>
