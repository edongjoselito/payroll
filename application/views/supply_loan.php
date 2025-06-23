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
                    <h4 class="page-title">Supply Loan<br><small><i>Borrowed supplies deducted from salary</i></small></h4>
                    <button class="btn btn-success" data-toggle="modal" data-target="#addSupplyLoanModal">
                        <i class="fas fa-plus-circle"></i> Add Supply Loan
                    </button>
                </div>
                <hr>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-white text-dark text-center">
                                    <tr>
                                        <th>Personnel</th>
                                        <th>Item</th>
                                        <th>Amount</th>
                                        <th>Date Purchased</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($supply_loans)): ?>
                                        <tr><td colspan="6" class="text-center">No records found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($supply_loans as $sl): ?>
                                            <tr>
                                                <td><?= $sl->full_name ?></td>
                                                <td><?= htmlspecialchars($sl->item_description) ?></td>
                                                <td class="text-right">₱<?= number_format($sl->amount, 2) ?></td>
                                                <td><?= date('M d, Y', strtotime($sl->date_purchased)) ?></td>
                                                <td class="text-center"><?= ucfirst($sl->status) ?></td>
                                                <td class="text-center">
    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editSupplyLoanModal<?= $sl->id ?>">Edit</button>

    <?php if ($sl->status == 'pending'): ?>
        <a href="<?= base_url('Loan/mark_supply_loan_deducted/'.$sl->id) ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark this supply loan as deducted?')">Deduct</a>
    <?php else: ?>
        <a href="<?= base_url('Loan/mark_supply_loan_deducted/'.$sl->id) ?>?undo=1" class="btn btn-sm btn-warning" onclick="return confirm('Undo deduction status?')">Undo</a>
    <?php endif; ?>

    <a href="<?= base_url('Loan/delete_supply_loan/'.$sl->id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this supply loan?')">Delete</a>
</td>

                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editSupplyLoanModal<?= $sl->id ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form method="post" action="<?= base_url('Loan/update_supply_loan') ?>">
                                                        <input type="hidden" name="supply_id" value="<?= $sl->id ?>">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">Edit Supply Loan</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Personnel</label>
                                                                    <select name="personnelID" class="form-control" required>
                                                                        <?php foreach ($personnel as $p): ?>
                                                                            <option value="<?= $p->personnelID ?>" <?= $p->personnelID == $sl->personnelID ? 'selected' : '' ?>>
                                                                                <?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Item Description</label>
                                                                    <input type="text" name="item_description" class="form-control" value="<?= htmlspecialchars($sl->item_description) ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Amount</label>
                                                                    <input type="number" name="amount" class="form-control" step="0.01" value="<?= $sl->amount ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Date Purchased</label>
                                                                    <input type="date" name="date_purchased" class="form-control" value="<?= $sl->date_purchased ?>" required>
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

                <!-- Modal: Add Supply Loan -->
                <div class="modal fade" id="addSupplyLoanModal" tabindex="-1" role="dialog" aria-labelledby="addSupplyLoanModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="<?= base_url('Loan/save_supply_loan') ?>">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Add Supply Loan</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label><strong>Personnel</strong></label>
                                        <select name="personnelID" class="form-control" required>
                                            <option value="">Select Personnel</option>
                                            <?php foreach ($personnel as $p): ?>
                                                <option value="<?= $p->personnelID ?>">
                                                    <?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Item Description</strong></label>
                                        <input type="text" name="item_description" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Amount</strong></label>
                                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Date Purchased</strong></label>
                                        <input type="date" name="date_purchased" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
