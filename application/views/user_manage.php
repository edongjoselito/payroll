        <!DOCTYPE html>
        <html lang="en">
            <title>PMS - Manage Users</title>

        <?php include('includes/head.php'); ?>
<style>
/* Button Enhancements */
.btn {
    
    border-radius: 6px;
    transition: all 0.25s ease-in-out;
}

.btn:hover {
    transform: scale(1.07);
    opacity: 0.95;
}

.btn-primary:hover {
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
}
.btn-info:hover {
    box-shadow: 0 0 8px rgba(23, 162, 184, 0.4);
}
.btn-success:hover {
    box-shadow: 0 0 8px rgba(40, 167, 69, 0.4);
}
.btn-danger:hover {
    box-shadow: 0 0 8px rgba(220, 53, 69, 0.4);
}
.btn-secondary:hover {
    box-shadow: 0 0 8px rgba(108, 117, 125, 0.4);
}
</style>

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
                            <h4 class="page-title">Manage Users</h4>
                            <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#addModal">Add User</button>
                        </div>

                        <?php if ($this->session->flashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show"><?= $this->session->flashdata('success') ?></div>
                        <?php elseif ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show"><?= $this->session->flashdata('error') ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Position</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=1; foreach ($users as $u): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= $u->username ?></td>
                                                    <td><?= $u->name ?></td>
                                                    <td><?= $u->email ?></td>
                                                    <td><?= $u->position ?></td>
                                                    <td><?= $u->acctStat ?></td>
                                                    <td>
                                                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editModal<?= $u->username ?>">Edit</button>
                                                        <a href="<?= base_url('User/delete/'.$u->username) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
                                                    </td>
                                                </tr>

                                               <!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $u->username ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= base_url('User/edit/'.$u->username) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Username</label>
                            <input type="text" class="form-control" value="<?= $u->username ?>" disabled>
                        </div>
                        <div class="form-group col-md-6">
                            <label>New Password <small>(Leave blank to keep current)</small></label>
                            <input type="password" name="password" class="form-control" placeholder="New Password">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>First Name</label>
                            <input type="text" name="fName" class="form-control" value="<?= $u->fName ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Middle Name</label>
                            <input type="text" name="mName" class="form-control" value="<?= $u->mName ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Last Name</label>
                            <input type="text" name="lName" class="form-control" value="<?= $u->lName ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= $u->email ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Position</label>
                            <select name="position" class="form-control" required>
                                <option value="Admin" <?= $u->position == 'Admin' ? 'selected' : '' ?>>Admin</option>
                               
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="acctStat" class="form-control">
                                <option value="Active" <?= $u->acctStat == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $u->acctStat == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>ID Number</label>
                            <input type="text" class="form-control" value="<?= $u->IDNumber ?>" disabled>
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
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= base_url('User/add') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" name="fName" class="form-control" placeholder="First Name" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Middle Name</label>
                            <input type="text" name="mName" class="form-control" placeholder="Middle Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="lName" class="form-control" placeholder="Last Name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>ID Number <span class="text-danger">*</span></label>
                            <input type="text" name="IDNumber" class="form-control" placeholder="ID Number" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Position <span class="text-danger">*</span></label>
                            <select name="position" class="form-control" required>
                                <option value="Admin">Admin</option>
                               
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="acctStat" class="form-control">
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
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
        <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

        </body>
        </html>
