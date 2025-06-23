
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
                    <h4 class="page-title">Cash Advance <br><small><i>Manage cash advances & 15/30 deductions</i></small></h4>
                    <button class="btn btn-success" data-toggle="modal" data-target="#addCashAdvanceModal">
                        <i class="fas fa-plus-circle"></i> Add Cash Advance
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
                                        <th>Amount</th>
                                        <th>Date Requested</th>
                                        <th>Deduct On</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($cash_advances)): ?>
                                        <tr><td colspan="6" class="text-center">No records found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($cash_advances as $ca): ?>
                                            <tr>
                                                <td><?= $ca->full_name ?></td>
                                                <td class="text-right">₱<?= number_format($ca->amount, 2) ?></td>
                                                <td><?= date('M d, Y', strtotime($ca->date_requested)) ?></td>
                                                <td class="text-center"><?= $ca->deduct_on ?>th</td>
                                                <td class="text-center"><?= ucfirst($ca->status) ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editCashAdvanceModal<?= $ca->id ?>">Edit</button>

                                                    <?php if ($ca->status == 'pending'): ?>
                                                        <a href="<?= base_url('Loan/mark_cash_advance_deducted/'.$ca->id) ?>" 
                                                           class="btn btn-sm btn-success" 
                                                           onclick="return confirm('Mark this cash advance as deducted?')">Deduct</a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('Loan/mark_cash_advance_deducted/'.$ca->id) ?>?undo=1" 
                                                           class="btn btn-sm btn-warning" 
                                                           onclick="return confirm('Undo deduction status?')">Undo</a>
                                                    <?php endif; ?>

                                                    <a href="<?= base_url('Loan/delete_cash_advance/'.$ca->id) ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this cash advance?')">Delete</a>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editCashAdvanceModal<?= $ca->id ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form method="post" action="<?= base_url('Loan/update_cash_advance') ?>">
                                                        <input type="hidden" name="cash_id" value="<?= $ca->id ?>">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">Edit Cash Advance</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Personnel</label>
                                                                    <select name="personnelID" class="form-control" required>
                                                                        <?php foreach ($personnel as $p): ?>
                                                                            <option value="<?= $p->personnelID ?>" <?= $p->personnelID == $ca->personnelID ? 'selected' : '' ?>>
                                                                                <?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Amount</label>
                                                                    <input type="number" name="amount" class="form-control" step="0.01" value="<?= $ca->amount ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Date Requested</label>
                                                                    <input type="date" name="date_requested" class="form-control" value="<?= $ca->date_requested ?>" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Deduct On</label>
                                                                    <select name="deduct_on" class="form-control" required>
                                                                        <option value="15" <?= $ca->deduct_on == '15' ? 'selected' : '' ?>>15th of the Month</option>
                                                                        <option value="30" <?= $ca->deduct_on == '30' ? 'selected' : '' ?>>30th of the Month</option>
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

                <!-- Modal: Add Cash Advance -->
                <div class="modal fade" id="addCashAdvanceModal" tabindex="-1" role="dialog" aria-labelledby="addCashAdvanceModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="<?= base_url('Loan/save_cash_advance') ?>">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="addCashAdvanceModalLabel">Add Cash Advance</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label><strong>Personnel</strong></label>
                                        <select name="personnelID" class="form-control" required>
                                            <option value="">Select Personnel</option>
                                            <?php foreach ($personnel as $p): ?>
                                                <option value="<?= $p->personnelID ?>"><?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Amount</strong></label>
                                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Date Requested</strong></label>
                                        <input type="date" name="date_requested" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Deduct On</strong></label>
                                        <select name="deduct_on" class="form-control" required>
                                            <option value="">Select Cutoff</option>
                                            <option value="15">15th of the Month</option>
                                            <option value="30">30th of the Month</option>
                                        </select>
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
