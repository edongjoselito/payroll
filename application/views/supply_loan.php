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
     <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php elseif ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Supply Loans<br><small><i>Assign loans directly to personnel</i></small></h4>
                </div>
                <hr>
<div class="mb-3">
    <button class="btn btn-primary" data-toggle="modal" data-target="#addPersonnelModal">
        <i class="fas fa-user-plus"></i> Add Personnel
    </button>
</div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th>Personnel</th>
                                        <th>Rate</th>
                                        <th>Position</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($personnel)): ?>
                                        <tr><td colspan="4" class="text-center">No personnel found.</td></tr>
                                    <?php else: foreach ($personnel as $p): ?>
                                        <tr>
                                            <td><?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?></td>
                                            <td><?= $p->rateType ?> - ₱<?= number_format((float)$p->rateAmount, 2) ?></td>
                                            <td><?= $p->position ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#assignLoanModal<?= $p->personnelID ?>">Assign</button>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editPersonnelModal<?= $p->personnelID ?>">Edit</button>
                                                <a href="<?= base_url('Loan/delete_personnel/'.$p->personnelID) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this personnel?')">Delete</a>
                                            </td>
                                        </tr>

<!-- Assign Modal -->
<div class="modal fade" id="assignLoanModal<?= $p->personnelID ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('Loan/save_personnel_loan') ?>" onsubmit="return checkEligibility<?= $p->personnelID ?>();" class="loan-form">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Assign Loan to <?= $p->first_name ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="error<?= $p->personnelID ?>"></div>
                    <div class="alert alert-success d-none" id="success<?= $p->personnelID ?>"></div>

                    <input type="hidden" name="personnelID" value="<?= $p->personnelID ?>">
                    <input type="hidden" id="rate<?= $p->personnelID ?>" value="<?= (float)$p->rateAmount ?>">

                    <div class="form-group">
                        <label>Loan Description</label>
                        
<select name="item_description" class="form-control" required>
    <option disabled selected>Select description</option>
    <optgroup label="DepEd Provident Fund">
        <option>Multi-Purpose Loan (MPL)</option>
        <option>Additional Loan (Extreme Cases)</option>
        <option>Calamity Loan (PF)</option>
    </optgroup>
    <optgroup label="GSIS Loans">
        <option>Multi-Purpose Loan (MPL) Plus</option>
        <option>Salary Loan</option>
        <option>Restructured Salary Loan</option>
        <option>Enhanced Salary Loan</option>
        <option>Emergency Loan Assistance</option>
        <option>Summer One-Month Salary Loan</option>
        <option>eCard Cash Advance</option>
        <option>Home Emergency Loan Program (HELP)</option>
        <option>Educational Assistance Loan I (EAL I)</option>
        <option>Educational Assistance Loan II (EAL II)</option>
        <option>Fly PAL, Pay Later (FPPL)</option>
        <option>Study Now, Pay Later (SNPL)</option>
        <option>Stock Purchase Loan (SPL)</option>
        <option>Policy Loan</option>
        <option>GSIS Emergency Loan</option>
        <option>GSIS Financial Assistance Loan (GFAL)</option>
        <option>GSIS Housing Loan</option>
        <option>GFAL for Housing</option>
    </optgroup>
    <optgroup label="Pag-IBIG Fund">
        <option>Pag-IBIG Multi-Purpose Loan</option>
        <option>Pag-IBIG Calamity Loan</option>
        <option>Pag-IBIG Housing Loan</option>
        <option>Pag-IBIG HELPs</option>
    </optgroup>
    <optgroup label="Banks / Private Lenders">
        <option>Teacher’s Loan (APDS)</option>
        <option>Personal Loan (non-APDS)</option>
        <option>Auto Loan</option>
        <option>Home Loan (non-GSIS/Pag-IBIG)</option>
    </optgroup>
</select>

                    </div>

                    <div class="form-group">
                        <label>Loan Type</label>
                        <select name="loan_type" class="form-control" required>
                            <option value="">Select</option>
                            <option>Short Term</option>
                            <option>Long Term</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Deduction Schedule</label>
                        <select name="deduction_type" class="form-control" required>
                            <option value="">Select</option>
                            <option value="15">15th of the month</option>
                            <option value="30">30th of the month</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" id="amount<?= $p->personnelID ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Date Issued</label>
                        <input type="date" name="date_purchased" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Assign</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editPersonnelModal<?= $p->personnelID ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('Loan/update_personnel/'.$p->personnelID) ?>">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Edit Personnel</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>First Name</label><input name="first_name" class="form-control" value="<?= $p->first_name ?>" required></div>
                    <div class="form-group"><label>Middle Name</label><input name="middle_name" class="form-control" value="<?= $p->middle_name ?>"></div>
                    <div class="form-group"><label>Last Name</label><input name="last_name" class="form-control" value="<?= $p->last_name ?>" required></div>
                    <div class="form-group"><label>Extension</label><input name="name_ext" class="form-control" value="<?= $p->name_ext ?>"></div>
                    <div class="form-group"><label>Rate Type</label><input name="rateType" class="form-control" value="<?= $p->rateType ?>" required></div>
                    <div class="form-group"><label>Rate Amount</label><input name="rateAmount" type="number" step="0.01" class="form-control" value="<?= $p->rateAmount ?>" required></div>
                    <div class="form-group"><label>Position</label><input name="position" class="form-control" value="<?= $p->position ?>" required></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-info">Update</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function checkEligibility<?= $p->personnelID ?>() {
    const rate = parseFloat(document.getElementById('rate<?= $p->personnelID ?>').value);
    const amount = parseFloat(document.getElementById('amount<?= $p->personnelID ?>').value);
    const errBox = document.getElementById('error<?= $p->personnelID ?>');
    const successBox = document.getElementById('success<?= $p->personnelID ?>');

    if (isNaN(amount) || amount <= 0) {
        errBox.classList.remove('d-none');
        errBox.textContent = 'Please enter a valid amount.';
        return false;
    }

    if (amount > rate * 2) {
        errBox.classList.remove('d-none');
        errBox.textContent = 'Amount exceeds allowed loan limit (twice the rate).';
        return false;
    }

    errBox.classList.add('d-none');
    successBox.classList.remove('d-none');
    successBox.textContent = 'Submitting...';
    return true;
}
</script>

                                    <?php endforeach; endif; ?>
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
</body>
</html>