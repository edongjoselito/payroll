<!DOCTYPE html>
<html lang="en">
<head>
    <title>PMS - Government Deductions</title>
    <?php include('includes/head.php'); ?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
</head>
<body>
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Government Deductions</h4>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addGovDeductionModal">+ Add Deduction</button>
                </div>

                <?php if($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
                <?php endif; ?>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Personnel Name</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Deduct From</th>
                                        <th>Deduct To</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deductions as $row): ?>
                                        <tr>
                                            <td><?= $row->fullname ?></td>
                                            <td><?= $row->description ?></td>
                                            <td>₱<?= number_format($row->amount, 2) ?></td>
                                            <td><?= $row->date ?></td>
                                            <td><?= $row->deduct_from ?? '—' ?></td>
                                            <td><?= $row->deduct_to ?? '—' ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal<?= $row->id ?>">Edit</button>
                                                <a href="<?= base_url('Borrow/delete_govt_deduction/'.$row->id) ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">Delete</a>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?= $row->id ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="post" action="<?= base_url('Borrow/update_govt_deduction/' . $row->id) ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Deduction</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <select name="description" class="form-control" required>
                                                                    <option value="SSS" <?= $row->description == 'SSS' ? 'selected' : '' ?>>SSS</option>
                                                                    <option value="PhilHealth" <?= $row->description == 'PhilHealth' ? 'selected' : '' ?>>PhilHealth</option>
                                                                    <option value="Pag-IBIG" <?= $row->description == 'Pag-IBIG' ? 'selected' : '' ?>>Pag-IBIG</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6">
                                                                    <label>Amount</label>
                                                                    <input type="number" name="amount" class="form-control" value="<?= $row->amount ?>" required>
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label>Date</label>
                                                                    <input type="date" name="date" class="form-control" value="<?= $row->date ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6">
                                                                    <label>Deduct From</label>
                                                                    <input type="date" name="deduct_from" class="form-control" value="<?= $row->deduct_from ?>">
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label>Deduct To</label>
                                                                    <input type="date" name="deduct_to" class="form-control" value="<?= $row->deduct_to ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">Update</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addGovDeductionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Borrow/save_govt_deduction') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Government Deduction</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Personnel Name</label>
                        <select name="personnelID" class="form-control" required>
                            <option value="">Select Personnel</option>
                            <?php foreach($personnel as $p): ?>
                                <option value="<?= $p->personnelID ?>">
                                    <?= $p->last_name . ', ' . $p->first_name ?>
                                    <?= ($p->middle_name) ? ' ' . substr($p->middle_name, 0, 1) . '.' : '' ?>
                                    <?= ($p->name_ext) ? ' ' . $p->name_ext : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <select name="description" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="SSS">SSS</option>
                            <option value="PhilHealth">PhilHealth</option>
                            <option value="Pag-IBIG">Pag-IBIG</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Deduct From</label>
                            <input type="date" name="deduct_from" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Deduct To</label>
                            <input type="date" name="deduct_to" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
