<!DOCTYPE html>
<html lang="en">
    <title>PMS - Cash Advance</title>

<?php include('includes/head.php'); ?>
<style>
/* Smooth button animation */
.btn {
    transition: all 0.25s ease-in-out;
}

/* Scale on hover */
.btn:hover {
    transform: scale(1.05);
    opacity: 0.95;
}

/* Glowing effect for primary/important buttons */
.btn-primary:hover,
.btn-success:hover,
.btn-info:hover,
.btn-danger:hover {
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
    border-color: rgba(0, 123, 255, 0.3);
}
</style>

<body>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
<div class="content">
<div class="container-fluid">

    <div class="page-title-box d-flex justify-content-between align-items-center">
        <h4 class="page-title">Cash Advance</h4>
        <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#addCashModal">+ Add Cash Advance</button>
    </div>

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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Personnel Name</th>
                            <th>C/A Amount</th>
                            <th>Date</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cash_advances as $row): ?>
                        <tr>
                            <td><?= $row->fullname ?></td>
                            <td>₱<?= number_format($row->amount, 2) ?></td>
                            <td><?= $row->date ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editCashModal<?= $row->id ?>">Edit</button>
                                <a href="<?= base_url('Borrow/delete_cash_advance/'.$row->id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
                            </td>
                        </tr>

                        <div class="modal fade" id="editCashModal<?= $row->id ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                   <form method="post" action="<?= base_url('Borrow/update_cash_advance/' . $row->id) ?>">


                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Cash Advance</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                            <div class="form-group">
                                                <label>Personnel</label>
                                                <input class="form-control" value="<?= $row->fullname ?>" readonly>
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
        <input type="date" name="deduct_from" class="form-control" value="<?= $row->deduct_from ?? '' ?>">
    </div>
    <div class="form-group col-md-6">
        <label>Deduct To</label>
        <input type="date" name="deduct_to" class="form-control" value="<?= $row->deduct_to ?? '' ?>">
    </div>
</div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Update</button>
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
<div class="modal fade" id="addCashModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Borrow/save_cash_advance') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Cash Advance</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Personnel</label>
                        <select name="personnelID" class="form-control" required>
                            <option value="">Select Personnel</option>
                            <?php foreach($personnel as $p): ?>
                                <option value="<?= $p->personnelID ?>">
                                 <?= $p->last_name . ', ' . $p->first_name 
    . (isset($p->middle_name) && $p->middle_name ? ' ' . substr($p->middle_name, 0, 1) . '.' : '') 
    . (isset($p->name_ext) && $p->name_ext ? ' ' . $p->name_ext : '') ?>


                                </option>
                            <?php endforeach; ?>
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

<!-- JS -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
