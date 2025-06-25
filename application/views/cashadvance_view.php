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
                    <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#addCashAdvanceModal">
                        <i class="mdi mdi-plus"></i> Add Cash Advance
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
                    <div class="card-body">
                        <h5 class="page-title">Cash Advance Records</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Amount</th>
                                        <th>Date Given</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($cash_advances)): ?>
                                        <tr><td colspan="6" class="text-center">No cash advance records found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($cash_advances as $row): ?>
                                            <tr>
                                                <td><?= $row->first_name . ' ' . $row->middle_name . ' ' . $row->last_name ?></td>
                                                <td><?= $row->position ?></td>
                                                <td>₱<?= number_format($row->amount, 2) ?></td>
                                                <td><?= !empty($row->date_requested) ? date('F d, Y', strtotime($row->date_requested)) : '' ?></td>
                                                <td><?= $row->remarks ?></td>
                                                <td>
                                                    <a href="<?= base_url('Cashadvance/delete/' . $row->id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">
                                                        <i class="mdi mdi-delete"></i>
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

            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- Add Cash Advance Modal -->
<div class="modal fade" id="addCashAdvanceModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Cashadvance/save') ?>" onsubmit="return validateCashAdvance()">
                <div class="modal-header">
                    <h5 class="modal-title">Add Cash Advance</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Personnel</label>
                        <select class="form-control" name="personnelID" required>
                            <option value="">-- Choose Personnel --</option>
                            <?php foreach ($personnel as $p): ?>
                                <option value="<?= $p->personnelID ?>">
                                    <?= $p->first_name . ' ' . $p->last_name ?> - <?= $p->rateType ?> - ₱<?= number_format($p->rateAmount, 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Date Requested</label>
                        <input type="date" name="date_requested" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Deduct On</label>
                        <select name="deduct_on" class="form-control" required>
                            <option value="">-- Select Deduction Cutoff --</option>
                            <option value="15">15th of the Month</option>
                            <option value="30">30th of the Month</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
function validateCashAdvance() {
    const personnel = document.querySelector('[name="personnelID"]').value;
    const amount = document.querySelector('[name="amount"]').value;
    const date = document.querySelector('[name="date_requested"]').value;
    const deduct = document.querySelector('[name="deduct_on"]').value;

    if (!personnel || !amount || !date || !deduct) {
        alert("Please fill in all required fields.");
        return false;
    }

    if (parseFloat(amount) <= 0) {
        alert("Amount must be greater than 0.");
        return false;
    }

    return true;
}
</script>

</body>
</html>
