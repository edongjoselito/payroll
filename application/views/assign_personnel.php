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
                            <small><i>Assign Loan to Personnel</i></small></h4>
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
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Personnel Name</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Basis</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($loans)) : ?>
                                        <tr><td colspan="6" class="text-center">No loans recorded.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($loans as $loan): ?>
                                            <tr>
                                                <td><?= $loan->full_name ?></td>
                                                <td><?= $loan->loan_description ?></td>
                                                <td><?= $loan->loan_type ?></td>
                                                <td>₱<?= number_format($loan->loan_amount, 2) ?></td>
                                                <td><?= ucfirst($loan->salary_basis) ?></td>
                                                <td>
                                                    <a href="#" data-toggle="modal" data-target="#editLoanModal<?= $loan->loan_id ?>" class="btn btn-primary btn-sm">Edit</a>
                                                    <a href="<?= base_url('Loan/delete/' . $loan->loan_id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this loan?')">Remove</a>
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
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Add Loan Modal -->
    <div class="modal fade" id="addLoanModal" tabindex="-1" role="dialog" aria-labelledby="addLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="post" action="<?= base_url('Loan/add') ?>">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="addLoanModalLabel"><i class="fas fa-plus-circle"></i> Add Loan</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Personnel</label>
                            <select name="personnelID" class="form-control select2" required>
                                <option value="">-- Select Personnel --</option>
                                <?php foreach ($personnel as $p): ?>
                                    <option value="<?= $p->personnelID ?>">
                                        <?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Description</label>
                                <select name="loan_description" class="form-control" required>
                                    <option value="">Select Description</option>
                                    <option>Emergency Loan</option>
                                    <option>Personal Loan</option>
                                    <option>Medical Assistance Loan</option>
                                    <option>Educational Loan</option>
                                    <option>Salary Loan</option>
                                    <option>Multi-Purpose Loan</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Type</label>
                                <select name="loan_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option>Short Term</option>
                                    <option>Long Term</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Amount</label>
                                <select name="loan_amount" class="form-control" required>
                                    <option value="">Select Amount</option>
                                    <?php foreach ([20000,30000,40000,50000,60000,70000,80000,90000,100000] as $amt): ?>
                                        <option value="<?= $amt ?>">₱<?= number_format($amt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Salary Basis</label>
                            <select name="salary_basis" class="form-control" required>
                                <option value="">Select Basis</option>
                                <option>hour</option>
                                <option>day</option>
                                <option>week</option>
                                <option>month</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Loan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
    <link href="<?= base_url(); ?>assets/libs/select2/select2.min.css" rel="stylesheet" />

    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });
        });
    </script>
</body>
</html>
