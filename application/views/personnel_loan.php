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
                      <th>Deduction</th>
                      <th>Term</th>
                      <th>From</th>
                      <th>To</th>
                      <th>Date Assigned</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($assigned_loans as $loan): ?>
<pre>
<?php foreach ($assigned_loans as $loan) {
    echo $loan->loan_name . "\n";
} ?>
</pre>


                     <tr>
  <td><?= htmlspecialchars($loan->first_name . ' ' . $loan->last_name) ?></td>
  <td><?= htmlspecialchars($loan->position) ?></td>
<td><?= htmlspecialchars($loan->loan_name ?? 'N/A') ?></td>





  <td>₱<?= number_format($loan->amount, 2) ?></td>
  <td><?= ucfirst($loan->deduction_type) ?></td>
  <td><?= $loan->term_months ?> month(s)</td>
  <td><?= $loan->start_date ? date('Y-m-d', strtotime($loan->start_date)) : 'N/A' ?></td>
<td><?= $loan->end_date ? date('Y-m-d', strtotime($loan->end_date)) : 'N/A' ?></td>

  <td><?= date('Y-m-d', strtotime($loan->created_at)) ?></td>
  <td>
    <button class="btn btn-info btn-sm btn-edit" 
          data-personnelid="<?= $loan->personnelID ?>"
data-loanid="<?= $loan->loan_id ?>"

            data-description="<?= htmlspecialchars($loan->loan_name ?? 'N/A') ?>"

            data-amount="<?= $loan->amount ?>"
            data-deduct="<?= $loan->deduction_type ?>"
            data-term="<?= $loan->term_months ?>"
            data-start="<?= $loan->start_date ?>"
            data-end="<?= $loan->end_date ?>"
            data-date="<?= date('Y-m-d', strtotime($loan->created_at)) ?>">
      Edit
    </button>
    <a href="<?= base_url('Loan/delete_personnel_loan/' . $loan->loan_id) ?>" 
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
                  <h5 class="modal-title">Add Loan</h5>
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
                                data-ratetype="<?= strtolower($p->rateType) ?>"
                                data-name="<?= htmlspecialchars($p->first_name . ' ' . $p->last_name) ?>"
                                data-position="<?= htmlspecialchars($p->position) ?>">
                          <?= htmlspecialchars($p->first_name . ' ' . $p->last_name . " ({$p->rateType})") ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div id="personnelInfoBox" class="alert alert-secondary d-none">
                    <strong>Personnel Info:</strong><br>
                    Name: <span id="personnelName"></span><br>
                    Position: <span id="personnelPosition"></span><br>
                    Rate Type: <span id="personnelType"></span><br>
                    Salary: ₱<span id="personnelSalary"></span>
                  </div>

                  <div class="form-group">
                    <label>Loan Type</label>
                    <select name="loan_id" class="form-control" id="loanOptions" required>
                      <option value="">Select Loan</option>
                    </select>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-4">
                      <label>Amount</label>
                      <input type="number" name="loan_amount" id="loan_amount" class="form-control" readonly required>
                    </div>
                    <div class="form-group col-md-4">
  <label>Deduct Type</label>
  <select name="deduction_type" class="form-control" required>
    <option value="Daily">Per Day</option>
    <option value="Weekly">Per Week</option>
    <option value="Monthly">Per Month</option>
  </select>
</div>

                    <div class="form-group col-md-4">
                      <label>Term (months)</label>
                      <input type="number" name="term_months" class="form-control" min="1" required>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label>Start Date</label>
                      <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label>End Date</label>
                      <input type="date" name="end_date" class="form-control" required>
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

      <!-- Edit Loan Modal -->
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

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Amount</label>
              <input type="number" name="loan_amount" id="editLoanAmount" class="form-control" required>
            </div>

            <div class="form-group col-md-4">
              <label>Deduction Type</label>
              <select name="deduction_type" id="editDeductionType" class="form-control" required>
                <option value="Daily">Per Day</option>
                <option value="Weekly">Per Week</option>
                <option value="Monthly">Per Month</option>
              </select>
            </div>

            <div class="form-group col-md-4">
              <label>Term (months)</label>
              <input type="number" name="term_months" id="editTermMonths" class="form-control" min="1" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Start Date</label>
              <input type="date" name="start_date" id="editStartDate" class="form-control" required>
            </div>

            <div class="form-group col-md-6">
              <label>End Date</label>
              <input type="date" name="end_date" id="editEndDate" class="form-control" required>
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

<!-- JS Scripts (unchanged) -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
$(document).ready(function () {
  // When personnel is selected
  $('#selectPersonnel').change(function () {
    const sel = $(this).find('option:selected');
    const salary = parseFloat(sel.data('rateamount')) || 0;
    const type = sel.data('ratetype').charAt(0).toUpperCase() + sel.data('ratetype').slice(1);
    
    $('#personnelInfoBox').removeClass('d-none');
    $('#personnelName').text(sel.data('name'));
    $('#personnelPosition').text(sel.data('position'));
    $('#personnelType').text(type);
    $('#personnelSalary').text(salary.toLocaleString(undefined, { minimumFractionDigits: 2 }));
    $('#assignLoanModal').data('personnelSalary', salary);

    $('#loan_amount').val('');
    $('#loanOptions').html('<option value="">Select Loan</option>');
    $('#eligibilityMsg').addClass('d-none').text('');
 $('#assignLoanModal button[type="submit"]').prop('disabled', true);


    $.post('<?= base_url('Loan/get_loans_by_ratetype') ?>', { rateType: sel.data('ratetype') }, function(resp) {
      const json = JSON.parse(resp);
      if (json.status == 'success') {
        $('#loanOptions').append(json.loans.map(l =>
          `<option value="${l.loan_id}" data-amount="${l.loan_amount}">${l.loan_description} (₱${parseFloat(l.loan_amount).toLocaleString()})</option>`
        ));
      } else {
        $('#loanOptions').html('<option value="">No eligible loans</option>');
      }
    });
  });

  // When loan is selected
  $('#loanOptions').change(function () {
    const sel = $(this).find('option:selected');
    const loanAmt = parseFloat(sel.data('amount')) || 0;
    const salary = $('#assignLoanModal').data('personnelSalary') || 0;
    $('#loan_amount').val(loanAmt);

    const deductionType = $('select[name="deduction_type"]').val();

    if (loanAmt <= salary) {
      if (deductionType) {
        $('#eligibilityMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text('Eligible.');
        $('button[type="submit"]').prop('disabled', false);
      } else {
        $('#eligibilityMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text('Please select a deduction type.');
        $('#assignLoanModal button[type="submit"]').prop('disabled', true);

      }
    } else {
      $('#eligibilityMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text('Loan exceeds salary. Not allowed.');
     $('#assignLoanModal button[type="submit"]').prop('disabled', true);

    }
  });

  // When deduction type is changed manually (in case user selects it after loan)
  $('select[name="deduction_type"]').change(function () {
    const deductionType = $(this).val();
    const loanAmt = parseFloat($('#loan_amount').val()) || 0;
    const salary = $('#assignLoanModal').data('personnelSalary') || 0;

    if (deductionType && loanAmt && loanAmt <= salary) {
      $('#eligibilityMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text('Eligible.');
      $('button[type="submit"]').prop('disabled', false);
    } else {
      $('#eligibilityMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text('Incomplete or not eligible.');
    $('#assignLoanModal button[type="submit"]').prop('disabled', true);

    }
  });

  // Edit modal load
 $(document).on('click', '.btn-edit', function () {
  $('#editLoanID').val($(this).data('loanid'));
  $('#editPersonnelID').val($(this).data('personnelid'));
  $('#editLoanAmount').val($(this).data('amount'));
  $('#editDeductionType').val($(this).data('deduct'));
  $('#editTermMonths').val($(this).data('term'));
  $('#editStartDate').val($(this).data('start'));
  $('#editEndDate').val($(this).data('end'));
  $('#editLoanModal').modal('show');
});

});
</script>

</body>
</html>
