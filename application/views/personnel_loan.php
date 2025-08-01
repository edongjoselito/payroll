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
/* Smooth animation on all buttons */
.btn {
  transition: all 0.25s ease-in-out;
}

/* Scale and glow on hover */
.btn:hover {
  transform: scale(1.05);
  opacity: 0.95;
}

/* Specific glow for Bootstrap button colors */
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
          <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#assignLoanModal">
            <i class="mdi mdi-plus"></i> Add Loan
          </button>
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
            <th>Status</th>
            <th>Action</th>
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
                <td>
                  <?php if ($loan->status == 1): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Deducted</span>
                  <?php endif; ?>
                </td>
                <td>
              <!-- EDIT Button: outline-info -->
               
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

<!-- DELETE Button: outline-danger -->
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

</body>
</html>
