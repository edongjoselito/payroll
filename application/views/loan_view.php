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
                            <small><i>Administer Employee Loans</i></small>
                        </h4>
                        <button class="btn btn-success" data-toggle="modal" data-target="#addLoanModal">
                            <i class="fas fa-plus-circle"></i> Add Loan
                        </button>
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
                                            <th>Personnel</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th>Total Amount</th>
                                            <th>Salary Basis</th>
                                            <th>Balance</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($loans)) : ?>
                                            <tr><td colspan="7" class="text-center">No loans recorded.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($loans as $loan): ?>
                                                <tr>
                                                    <td><?= $loan->full_name ?></td>
                                                    <td><?= htmlspecialchars($loan->loan_description) ?></td>
                                                    <td><?= htmlspecialchars($loan->loan_type) ?></td>
                                                    <td class="text-right">₱<?= number_format($loan->loan_amount, 2) ?></td>
                                                    <td class="text-center"><?= ucfirst($loan->salary_basis) ?></td>
                                                    <td class="text-right">₱<?= number_format($loan->loan_amount, 2) ?></td>
                                                    <td class="text-center">
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editLoanModal<?= $loan->loan_id ?>">Edit</button>
                                                            <a href="<?= base_url('Loan/delete/'.$loan->loan_id); ?>" class="btn btn-danger btn-sm ml-1" onclick="return confirm('Delete this loan?')">
                                                            Remove
                                                            </a>

                                                    </td>
                                                </tr>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="editLoanModal<?= $loan->loan_id ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <form method="post" action="<?= base_url('Loan/update') ?>">
                                                            <input type="hidden" name="loan_id" value="<?= $loan->loan_id ?>">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-primary text-white">
                                                                    <h5 class="modal-title">Edit Loan</h5>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <label><strong>Personnel</strong></label>
                                                                        <select name="personnelID" id="personnelSelect" class="form-control" required>


                                                                            <option value="">Select Personnel</option>
                                                                            <?php foreach ($personnel as $p): ?>
                                                                                <option value="<?= $p->personnelID ?>" <?= $loan->personnelID == $p->personnelID ? 'selected' : '' ?>>
                                                                                    <?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="form-row">
                                                                        <div class="form-group col-md-6">
                                                                            <label><strong>Loan Description</strong></label>
                                                                            <select name="loan_description" id="descriptionSelect" class="form-control" required>

                                                                                <?php foreach (["Emergency Loan", "Personal Loan", "Medical Assistance Loan", "Educational Loan", "Salary Loan", "Multi-Purpose Loan"] as $desc): ?>
                                                                                    <option value="<?= $desc ?>" <?= $loan->loan_description == $desc ? 'selected' : '' ?>><?= $desc ?></option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group col-md-3">
                                                                            <label><strong>Loan Type</strong></label>
                                                                            <select class="form-control" name="loan_type" required>
                                                                                <option value="Short Term" <?= $loan->loan_type == 'Short Term' ? 'selected' : '' ?>>Short Term</option>
                                                                                <option value="Long Term" <?= $loan->loan_type == 'Long Term' ? 'selected' : '' ?>>Long Term</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group col-md-3">
                                                                            <label><strong>Loan Amount</strong></label>
                                                                            <select class="form-control" name="loan_amount" required>
                                                                                <?php foreach ([20000,30000,40000,50000,60000,70000,80000,90000,100000] as $amount): ?>
                                                                                    <option value="<?= $amount ?>" <?= $loan->loan_amount == $amount ? 'selected' : '' ?>>₱<?= number_format($amount) ?></option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label><strong>Salary Basis</strong></label>
                                                                        <select class="form-control" name="salary_basis" required>
                                                                            <?php foreach (["hour", "day", "week", "month"] as $basis): ?>
                                                                                <option value="<?= $basis ?>" <?= $loan->salary_basis == $basis ? 'selected' : '' ?>><?= ucfirst($basis) ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- Add Loan Modal -->
<div class="modal fade" id="addLoanModal" tabindex="-1" role="dialog" aria-labelledby="addLoanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" action="<?= base_url(); ?>Loan/add">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addLoanModalLabel"><i class="fas fa-plus-circle"></i> Add New Loan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label><strong>Personnel</strong></label>
                        <select name="personnelID" id="personnelSelect" class="form-control" required>

                            <option value="">Select Personnel</option>
                            <?php foreach ($personnel as $p): ?>
                                <option value="<?= $p->personnelID ?>"><?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label><strong>Loan Description</strong></label>
                            <select name="loan_description" id="descriptionSelect" class="form-control" required>

                                <option value="">Select Description</option>
                                <option value="Emergency Loan">Emergency Loan</option>
                                <option value="Personal Loan">Personal Loan</option>
                                <option value="Medical Assistance Loan">Medical Assistance Loan</option>
                                <option value="Educational Loan">Educational Loan</option>
                                <option value="Salary Loan">Salary Loan</option>
                                <option value="Multi-Purpose Loan">Multi-Purpose Loan</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label><strong>Loan Type</strong></label>
                            <select class="form-control" name="loan_type" required>
                                <option value="">Select Type</option>
                                <option value="Short Term">Short Term</option>
                                <option value="Long Term">Long Term</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label><strong>Loan Amount</strong></label>
                            <select class="form-control" name="loan_amount" required>
                                <option value="">Select Amount</option>
                                <?php foreach ([20000,30000,40000,50000,60000,70000,80000,90000,100000] as $amount): ?>
                                    <option value="<?= $amount ?>">₱<?= number_format($amount) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Salary Basis</strong></label>
                        <select class="form-control" name="salary_basis" required>
                            <option value="">Select Basis</option>
                            <option value="hour">Hourly</option>
                            <option value="day">Daily</option>
                            <option value="week">Weekly</option>
                            <option value="month">Monthly</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Save Loan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
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
        $('.select2').select2({
            width: 'resolve'
        });

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

</body>
</html>
