<!DOCTYPE html>
<html lang="en">
<title>PMS - Other Deductions</title>

<?php include('includes/head.php'); ?>
<style>
/* Smooth transition for all buttons */
.btn {
  transition: all 0.25s ease-in-out;
}

/* Hover effects: scale and light glow */
.btn:hover {
  transform: scale(1.05);
  opacity: 0.95;
}

/* Soft glow for colored buttons */
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

  table { width: 100% !important; border-collapse: collapse !important; }
  th, td { white-space: nowrap; } 
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

  table { width: 100% !important; border-collapse: collapse !important; }
  thead { display: table-header-group; } 
  tfoot { display: table-footer-group; }
  tr, img { page-break-inside: avoid; }
  th, td { white-space: nowrap; }
  
  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  
}

@page { size: A4 portrait; margin: 15mm; }

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
  <div>
    <button class="btn btn-primary btn-md no-print" data-toggle="modal" data-target="#addMaterialModal">+ Add Other Deduction</button>
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
      <div class="sub">Other Deductions Report</div>
      <div class="lh-meta">
        Printed on: <span id="printTimestamp"></span>
      </div>
    </div>
  </div>
  <div class="lh-line"></div>
</div>
<div class="print-header-space"></div>




    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Personnel Name</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Deduct From</th>
                                <th>Deduct To</th>
                           <th class="no-print">Manage</th>

                        </tr>
                    </thead>
                   <tbody>
  <?php if (!empty($material_loans)): ?>
    <?php foreach ($material_loans as $row): ?>
    <tr>
      <td><?= $row->fullname ?></td>
      <td><?= $row->description ?></td>
      <td>₱<?= number_format($row->amount, 2) ?></td>
<td data-order="<?= date('Y-m-d', strtotime($row->date)) ?>">
  <?= date('Y-m-d', strtotime($row->date)) ?>
</td>

      <td><?= $row->deduct_from ? date('Y-m-d', strtotime($row->deduct_from)) : '' ?></td>
      <td><?= $row->deduct_to ? date('Y-m-d', strtotime($row->deduct_to)) : '' ?></td>
      <td class="no-print">
        <!-- EDIT Button -->
        <button class="btn btn-outline-info btn-sm me-1" data-toggle="modal" data-target="#editMaterialModal<?= $row->id ?>">
          <i class="fas fa-edit" data-toggle="tooltip" title="Edit Deduction"></i>
        </button>

        <!-- DELETE Button -->
        <a href="<?= base_url('Borrow/delete_material/' . $row->id) ?>" 
           class="btn btn-outline-danger btn-sm" 
           onclick="return confirm('Delete this record?')">
          <i class="fas fa-trash-alt" data-toggle="tooltip" title="Delete Deduction"></i>
        </a>
      </td>
    </tr>

  <!-- Edit Modal -->
<div class="modal fade" id="editMaterialModal<?= $row->id ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?= base_url('Borrow/update_material'); ?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit Other Deduction</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= $row->id ?>">
          <input type="hidden" name="personnelID" value="<?= $row->personnelID ?>">

          <div class="form-group">
            <label>Personnel</label>
            <input class="form-control" value="<?= $row->fullname ?>" readonly>
          </div>

          <div class="form-group">
            <label>Description</label>
            <input type="text" name="description" class="form-control" 
                   value="<?= $row->description ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Amount</label>
              <input type="number" name="amount" class="form-control" 
                     value="<?= $row->amount ?>" required>
            </div>
            <div class="form-group col-md-6">
              <label>Date</label>
              <!-- ensure correct Y-m-d format for HTML5 date -->
              <input type="date" name="date" class="form-control" 
                     value="<?= date('Y-m-d', strtotime($row->date)) ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Deduct From</label>
              <input type="date" name="deduct_from" class="form-control" 
                     value="<?= $row->deduct_from ? date('Y-m-d', strtotime($row->deduct_from)) : '' ?>">
            </div>
            <div class="form-group col-md-6">
              <label>Deduct To</label>
              <input type="date" name="deduct_to" class="form-control" 
                     value="<?= $row->deduct_to ? date('Y-m-d', strtotime($row->deduct_to)) : '' ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
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
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Borrow/save_material') ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Add Other Deduction</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
               <div class="modal-body">
    <div class="form-group">
        <label>Personnel</label>
        <select name="personnelID" class="form-control" required>
            <option value="">Select Personnel</option>
            <?php foreach($personnel_list as $p): ?>
                <option value="<?= $p->personnelID ?>">
                    <?= $p->last_name . ' ' . ($p->middle_name ? substr($p->middle_name, 0, 1) . '. ' : '') . $p->first_name . ($p->name_ext ? ' ' . $p->name_ext : '') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Description</label>
        <input type="text" name="description" class="form-control" required>
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
  // Avoid double init if datatables.init.js also runs
  if ($.fn.DataTable.isDataTable('#datatable')) {
    $('#datatable').DataTable().destroy();
  }

  var table = $('#datatable').DataTable({
    // 0 Name | 1 Desc | 2 Amount | 3 Date | 4 From | 5 To | 6 Manage
    order: [[3, 'asc'], [0, 'asc']],
    columnDefs: [
      { targets: [3,4,5], type: 'date' },
      { targets: [6], orderable: false }
    ],
    pageLength: 10
  });

  // PRINT handler
  $('#printBtn').on('click', function () {
    // 1) Fill timestamp on header
    var ts = new Date().toLocaleString();
    $('#printTimestamp').text(ts);

    // 2) Remember current page length, then show ALL rows
    var previousLength = table.page.len();
    table.page.len(-1).draw();

    // 3) Also hide Manage column in DataTables (extra safety)
    var manageCol = 6;
    var manageWasVisible = table.column(manageCol).visible();
    table.column(manageCol).visible(false, false);

    // 4) Restore after printing
    var restore = function () {
      table.page.len(previousLength).draw(false);
      table.column(manageCol).visible(manageWasVisible, false);
      window.removeEventListener('afterprint', restore);
      $(window).off('focus.printRestore');
    };
    window.addEventListener('afterprint', restore);
    $(window).on('focus.printRestore', restore);

    // 5) Trigger native print
    window.print();
  });

  // Other UI
  $('#sessionToast').toast('show');
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

</body>
</html>
