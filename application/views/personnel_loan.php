<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<body>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

   <div class="page-title-box mb-2">
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#assignLoanModal">
        <i class="mdi mdi-plus"></i> Assign Loan
    </button>
</div>



                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php elseif ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Personnel's Loan</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($assigned_loans)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm dt-responsive nowrap" style="width:100%">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Personnel Name</th>
                                            <th>Position</th>
                                            <th>Loan Description</th>
                                            <th>Amount</th>
                                            <th>Date Assigned</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assigned_loans as $loan): ?>
                                            <tr>
                                                <td><?= $loan->first_name . ' ' . $loan->last_name ?></td>
                                                <td><?= $loan->position ?></td>
                                                <td><?= $loan->loan_description ?></td>
                                                <td>₱<?= number_format($loan->amount, 2) ?></td>
                                                <td><?= date('F d, Y', strtotime($loan->created_at)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">No personnel have been assigned loans yet.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Assign Loan Modal -->
                <div class="modal fade" id="assignLoanModal" tabindex="-1">
                    <div class="modal-dialog modal-md">
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
                                            <option value="">-- Choose Personnel --</option>
                                            <?php foreach ($personnel as $p): ?>
                                                <option value="<?= $p->personnelID ?>"
                                                    data-ratetype="<?= strtolower($p->rateType) ?>"
                                                    data-rateamount="<?= $p->rateAmount ?>"
                                                    data-name="<?= $p->first_name . ' ' . $p->last_name ?>"
                                                    data-position="<?= $p->position ?>"
                                                    data-type="<?= $p->rateType ?>">
                                                    <?= "$p->first_name $p->last_name (" . ucfirst($p->rateType) . ")" ?>
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
                                            <option value="">-- Select Loan --</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Loan Amount</label>
                                        <input type="number" name="loan_amount" id="loan_amount" class="form-control" required readonly>
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

            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
$(document).ready(function () {
    $('#selectPersonnel').change(function () {
        const selected = $(this).find('option:selected');
        const rateType = selected.data('ratetype');
        const salary = parseFloat(selected.data('rateamount')) || 0;
        const name = selected.data('name');
        const position = selected.data('position');
        const type = selected.data('type');

        $('#personnelInfoBox').removeClass('d-none');
        $('#personnelName').text(name);
        $('#personnelPosition').text(position);
        $('#personnelType').text(type);
        $('#personnelSalary').text(salary.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#assignLoanModal').data('personnelSalary', salary);
        $('#loan_amount').val('');
        $('#loanOptions').html('<option value="">-- Select Loan --</option>');
        $('#eligibilityMsg').addClass('d-none');
        $('button[type="submit"]').prop('disabled', true);

        $.post('<?= base_url('Loan/get_loans_by_ratetype') ?>', { rateType: rateType }, function (response) {
            const res = JSON.parse(response);
            if (res.status === 'success') {
                const options = res.loans.map(loan =>
                    `<option value="${loan.loan_id}" data-amount="${loan.loan_amount}">${loan.loan_description} (₱${parseFloat(loan.loan_amount).toLocaleString()})</option>`
                );
                $('#loanOptions').html('<option value="">-- Select Loan --</option>' + options.join(''));
            } else {
                $('#loanOptions').html('<option value="">No eligible loans</option>');
            }
        });
    });

    $('#loanOptions').change(function () {
        const selected = $(this).find('option:selected');
        const loanAmount = parseFloat(selected.data('amount')) || 0;
        const personnelSalary = $('#assignLoanModal').data('personnelSalary') || 0;
        $('#loan_amount').val(loanAmount);

        if (loanAmount <= personnelSalary) {
            $('#eligibilityMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text('Eligible.');
            $('button[type="submit"]').prop('disabled', false);
        } else {
            $('#eligibilityMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text('Not eligible: Loan exceeds salary.');
            $('button[type="submit"]').prop('disabled', true);
        }
    });
});
</script>

</body>
</html>
