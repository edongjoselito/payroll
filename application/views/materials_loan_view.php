<!DOCTYPE html>
<html lang="en">
<title>PMS - Other Deductions</title>

<?php include('includes/head.php'); ?>

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
        <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#addMaterialModal">+ Add Other Deduction</button>
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
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Deduct From</th>
                                <th>Deduct To</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($material_loans as $row): ?>
                        <tr>
                            <td><?= $row->fullname ?></td>
                            <td><?= $row->description ?></td>
                            <td>₱<?= number_format($row->amount, 2) ?></td>
                            <td><?= date('Y-m-d', strtotime($row->date)) ?></td>
                             <td><?= $row->deduct_from ? date('Y-m-d', strtotime($row->deduct_from)) : '' ?></td>
    <td><?= $row->deduct_to ? date('Y-m-d', strtotime($row->deduct_to)) : '' ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editMaterialModal<?= $row->id ?>">Edit</button>
                               <a href="<?= base_url('Borrow/delete_material/'.$row->id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>

                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editMaterialModal<?= $row->id ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" action="<?= base_url('Borrow/save_material'); ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Other Deduction</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                            <div class="form-group">
                                                <label>Personnel</label>
                                                <input class="form-control" value="<?= $row->fullname ?>" readonly>
                                                <input type="hidden" name="personnelID" value="<?= $row->personnelID ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Description</label>
                                                <input type="text" name="description" class="form-control" value="<?= $row->description ?>" required>
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
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Borrow/save_material') ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Add Other Deduction</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
               <div class="modal-body">
    <div class="form-group">
        <label>Personnel</label>
        <select name="personnelID" class="form-control" required>
            <option value="">Select Personnel</option>
            <?php foreach($personnel_list as $p): ?>
                <option value="<?= $p->personnelID ?>">
                    <?= $p->last_name . ' ' . ($p->middle_name ? substr($p->middle_name, 0, 1) . '. ' : '') . $p->first_name . ($p->name_ext ? ' ' . $p->name_ext : '') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Description</label>
        <input type="text" name="description" class="form-control" required>
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
