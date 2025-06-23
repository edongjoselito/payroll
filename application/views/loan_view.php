<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="page-title-box d-flex justify-content-between align-items-center">
                        <h4 class="page-title">Loan Management <br>
                            <small><i>Manage Employee Loans</i></small>
                        </h4>
                    </div>
                    <hr>

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
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-white text-dark text-center">
                                        <tr>
                                            <th>Borrower</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th>Total Amount</th>
                                            <th>Salary Basis</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($loans)) : ?>
                                            <tr><td colspan="7" class="text-center">No loans recorded.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($loans as $loan): ?>
                                                <tr id="loan-row-<?= $loan->loan_id ?>">
    <td><?= $loan->full_name ?></td>
    <td><?= htmlspecialchars($loan->loan_description ?? '') ?></td>
    <td><?= htmlspecialchars($loan->loan_type ?? '') ?></td>
    <td class="text-right">₱<?= number_format($loan->loan_amount, 2) ?></td>
    <td class="text-center"><?= ucfirst($loan->salary_basis) ?></td>
    <td class="text-center status-cell"><?= ucfirst($loan->status ?? 'pending') ?></td>
    <td class="text-center">
        <button class="btn btn-success btn-sm approve-btn" data-id="<?= $loan->loan_id ?>">Approve</button>
        <button class="btn btn-warning btn-sm disapprove-btn" data-id="<?= $loan->loan_id ?>">Disapprove</button>
        <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $loan->loan_id ?>" onclick="return confirm('Delete this loan?')">Delete</button>
    </td>
</tr>

                                            <?php endforeach; ?>
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
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
    <link href="<?= base_url(); ?>assets/libs/select2/select2.min.css" rel="stylesheet" />

    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: 'resolve' });
            const personnelCount = <?= count($personnel) ?>;
            $('.select-personnel').select2({
                width: 'resolve',
                minimumResultsForSearch: personnelCount >= 10 ? 0 : Infinity
            });

            $('.modal').on('shown.bs.modal', function () {
                $(this).find('.select2').each(function () {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal'),
                        width: 'resolve'
                    });
                });

                $(this).find('.select-personnel').select2({
                    dropdownParent: $(this).closest('.modal'),
                    width: 'resolve',
                    minimumResultsForSearch: personnelCount >= 10 ? 0 : Infinity
                });
            });
        });
    </script>
    <script>
$(document).ready(function () {
    function updateStatus(loanID, status) {
        $('#loan-row-' + loanID + ' .status-cell').text(status.charAt(0).toUpperCase() + status.slice(1));
    }

    $('.approve-btn').click(function () {
        var id = $(this).data('id');
        $.post("<?= base_url('Loan/ajax_approve') ?>", { loan_id: id }, function (response) {
            if (response.success) {
                updateStatus(id, 'approved');
            }
        }, 'json');
    });

    $('.disapprove-btn').click(function () {
        var id = $(this).data('id');
        $.post("<?= base_url('Loan/ajax_disapprove') ?>", { loan_id: id }, function (response) {
            if (response.success) {
                updateStatus(id, 'disapproved');
            }
        }, 'json');
    });

    $('.delete-btn').click(function () {
        var id = $(this).data('id');
        $.post("<?= base_url('Loan/ajax_delete') ?>", { loan_id: id }, function (response) {
            if (response.success) {
                $('#loan-row-' + id).fadeOut(300, function () { $(this).remove(); });
            }
        }, 'json');
    });
});
</script>


</body>
</html>
