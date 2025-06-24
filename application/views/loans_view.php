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

                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Manage Loans</h4>
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addLoanModal">
                        <i class="mdi mdi-plus"></i> Add Loan
                    </button>
                </div>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
                <?php elseif ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Loan Type</th>
                                        <th>Rate Type</th>
                                        <th>Service Charge</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($loans as $loan): ?>
                                        <tr>
                                            <td><?= $loan->loan_description ?></td>
                                            <td>₱<?= number_format($loan->loan_amount, 2) ?></td>
                                            <td><?= ucfirst($loan->loan_type) ?></td>
                                            <td><?= ucfirst($loan->rateType) ?></td>
                                            <td><?= number_format($loan->service_charge, 2) ?>%</td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $loan->loan_id ?>">Edit</button>
                                                <a href="<?= base_url('Loan/delete_loan_entry/'.$loan->loan_id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this loan?')">Delete</a>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?= $loan->loan_id ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="post" action="<?= base_url('Loan/update_loan_entry') ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Loan</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="loan_id" value="<?= $loan->loan_id ?>">
                                                            <div class="form-group">
                                                                <label>Loan Description</label>
                                                                <input type="text" name="loan_description" class="form-control" value="<?= $loan->loan_description ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Loan Amount</label>
                                                                <input type="number" name="loan_amount" class="form-control" value="<?= $loan->loan_amount ?>" step="0.01" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Loan Type</label>
                                                                <select name="loan_type" class="form-control" required>
                                                                    <option <?= $loan->loan_type == 'short' ? 'selected' : '' ?>>Short Term</option>
                                                                    <option <?= $loan->loan_type == 'long' ? 'selected' : '' ?>>Long Term</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Rate Type</label>
                                                                <select name="rateType" class="form-control" required>
                                                                    <option <?= $loan->rateType == 'day' ? 'selected' : '' ?>>Per Day</option>
                                                                    <option <?= $loan->rateType == 'week' ? 'selected' : '' ?>>Per Week</option>
                                                                    <option <?= $loan->rateType == 'month' ? 'selected' : '' ?>>Per Month</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Service Charge (%)</label>
                                                                <input type="number" name="service_charge" class="form-control" step="0.01" value="<?= $loan->service_charge ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Loan Modal -->
                <div class="modal fade" id="addLoanModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" action="<?= base_url('Loan/add_loan_entry') ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add New Loan</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Loan Description</label>
                                        <input type="text" name="loan_description" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Loan Amount</label>
                                        <input type="number" name="loan_amount" class="form-control" step="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Loan Type</label>
                                        <select name="loan_type" class="form-control" required>
                                            <option value="short">Short Term</option>
                                            <option value="long">Long Term</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Rate Type</label>
                                        <select name="rateType" class="form-control" required>
                                            <option value="day">Per Day</option>
                                            <option value="week">Per Week</option>
                                            <option value="month">Per Month</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Service Charge (%)</label>
                                        <input type="number" name="service_charge" class="form-control" step="0.01" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Add Loan</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
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
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
