<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
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
          <h4 class="page-title">Personnel's Loan</h4>
          <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#assignLoanModal">
            <i class="mdi mdi-plus"></i> Add Loan
          </button>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
          <div class="alert alert-success alert-dismissible fade show"><?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php elseif ($this->session->flashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show"><?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-body">
            <?php if (!empty($assigned_loans)): ?>
              <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                 <thead class="thead-light">
  <tr>
    <th>Personnel Name</th>
    <th>Position</th>
    <th>Loan Description</th>
    <th>Amount</th>
    <th>Monthly Deduction</th>
    <th>Term</th>
    <th>Date Assigned</th>
    <th>Action</th>
  </tr>
</thead>
<tbody>
  <?php foreach ($assigned_loans as $loan): ?>
    <tr>
      <td><?= htmlspecialchars($loan->first_name . ' ' . $loan->last_name) ?></td>
      <td><?= htmlspecialchars($loan->position ?? '') ?></td>
      <td><?= htmlspecialchars($loan->loan_name ?? 'N/A') ?></td>
      <td>₱<?= number_format($loan->amount, 2) ?></td>
      <td>₱<?= number_format($loan->monthly_deduction, 2) ?></td>
      <td><?= $loan->term_months ?> month(s)</td>
      <td><?= date('Y-m-d', strtotime($loan->created_at)) ?></td>
      <td>
        <button class="btn btn-info btn-sm btn-edit" 
          data-personnelid="<?= $loan->personnelID ?>"
          data-loanid="<?= $loan->loan_id ?>"
          data-amount="<?= $loan->amount ?>"
          data-monthly="<?= $loan->monthly_deduction ?>"
          data-term="<?= $loan->term_months ?>">
          Edit
        </button>
        <a href="<?= base_url('Loan/delete_personnel_loan/' . $loan->loan_id . '/' . $loan->personnelID) ?>"

           class="btn btn-danger btn-sm" 
           onclick="return confirm('Delete this loan?')">
           Delete
        </a>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>

                </table>
              </div>
            <?php else: ?>
              <div class="alert alert-info mb-0">No personnel loans assigned yet.</div>
            <?php endif; ?>
          </div>
        </div>

<!-- Assign Loan Modal -->
<div class="modal fade" id="assignLoanModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= base_url('Loan/save_personnel_loan') ?>">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Assign Loan</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Select Personnel</label>
            <select class="form-control" name="personnelID" id="selectPersonnel" required>
  <option value="">Choose Personnel</option>
  <?php foreach ($personnel as $p): ?>
    <option value="<?= $p->personnelID ?>"
      data-rateamount="<?= $p->rateAmount ?>"
      data-ratetype="<?= strtolower(str_replace('Per ', '', $p->rateType)) ?>"
      data-name="<?= htmlspecialchars($p->first_name . ' ' . $p->last_name) ?>"
      data-position="<?= htmlspecialchars($p->position) ?>">
      <?= htmlspecialchars($p->first_name . ' ' . $p->last_name . " ({$p->rateType})") ?>
    </option>
  <?php endforeach; ?>
</select>

          </div>

          <div class="form-group">
            <label>Select Loan</label>
            <select name="loan_id" class="form-control" id="loanOptions" required>
              <option value="">Select Loan</option>
            </select>
          </div>

          <div class="form-group">
            <label>Loan Description</label>
            <input type="text" name="loan_description" id="loan_description" class="form-control" readonly>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Loan Amount</label>
              <input type="text" name="loan_amount" id="loan_amount" class="form-control" readonly>
            </div>
            <div class="form-group col-md-6">
              <label>Monthly Deduction</label>
              <input type="text" name="monthly_deduction" id="monthly_deduction" class="form-control" readonly>
            </div>
          </div>

          <div id="eligibilityMsg" class="alert d-none"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit" disabled>Assign</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editLoanModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="<?= base_url('Loan/update_personnel_loan') ?>">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title">Edit Loan</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="loan_id" id="editLoanID">
          <input type="hidden" name="personnelID" id="editPersonnelID">

          <div class="form-group">
            <label>Loan Amount</label>
            <input type="number" name="loan_amount" id="editLoanAmount" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Monthly Deduction</label>
            <input type="number" name="monthly_deduction" id="editMonthlyDeduction" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
         <button class="btn btn-info" type="submit" id="updateLoanBtn">Update</button>

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

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
$(document).ready(function () {
  $('#selectPersonnel').change(function () {
    const sel = $(this).find('option:selected');
    const salary = parseFloat(sel.data('rateamount')) || 0;
    const rateType = sel.data('ratetype') || '';
    const name = sel.data('name');
    const position = sel.data('position');
    const personnelID = sel.val();

    $('#assignLoanModal').data('personnelSalary', salary);
    $('#loanOptions').html('<option value="">Select Loan</option>');
    $('#loan_description').val('');
    $('#loan_amount').val('');
    $('#monthly_deduction').val('');
    $('#eligibilityMsg').addClass('d-none').text('');
    $('button[type="submit"]').prop('disabled', true);

    $.post('<?= base_url('Loan/get_loans_all') ?>', function (resp) {
      const json = JSON.parse(resp);
      if (json.status === 'success' && json.loans.length > 0) {
        $('#loanOptions').html('<option value="">Select Loan</option>');
        json.loans.forEach(function (loan) {
          $('#loanOptions').append(`
            <option value="${loan.loan_id}"
                    data-description="${loan.loan_description}"
                    data-amount="${loan.loan_amount}"
                    data-monthly="${loan.monthly_deduction}">
              ${loan.loan_description} (₱${parseFloat(loan.loan_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })})
            </option>
          `);
        });
      } else {
        $('#loanOptions').html('<option value="">No loans available</option>');
      }
    });
  });

  $('#loanOptions').change(function () {
    const sel = $(this).find('option:selected');
    const amount = parseFloat(sel.data('amount')) || 0;
    const monthly = parseFloat(sel.data('monthly')) || 0;
    const salary = $('#assignLoanModal').data('personnelSalary') || 0;

    $('#loan_description').val(sel.data('description'));
    $('#loan_amount').val(amount.toFixed(2));
    $('#monthly_deduction').val(monthly.toFixed(2));

    if (amount <= salary) {
      $('#eligibilityMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text('Eligible');
      $('button[type="submit"]').prop('disabled', false);
    } else {
      $('#eligibilityMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text('Loan exceeds salary');
      $('button[type="submit"]').prop('disabled', true);
    }
  });
$(document).on('click', '.btn-edit', function () {

  $('#editLoanID').val($(this).data('loanid'));
  $('#editPersonnelID').val($(this).data('personnelid'));
  $('#editLoanAmount').val($(this).data('amount'));
  $('#editMonthlyDeduction').val($(this).data('monthly'));
  $('#editLoanModal').modal('show');
});
});
</script>

</body>
</html>
