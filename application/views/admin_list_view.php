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
                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="page-title">Admin Accounts</h4><br>
                                    <small class="text-muted">Company ID: <?= $settingsID; ?></small>
                                </div>
                                <div>
                                    <a href="<?= base_url('Page/superAdmin'); ?>" class="btn btn-secondary btn-sm">
                                        <i class="mdi mdi-arrow-left"></i> Back to Super Admin
                                    </a>
                                </div>
                            </div>
                            <hr style="border:0; height:2px; background:linear-gradient(to right, #34A853, #FBBC05, #4285F4); border-radius:1px; margin:20px 0;" />
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('msg')) : ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('msg'); ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <!-- Admin Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Full Name</th>
                                                    <th>Email</th>
                                                    <th>School</th>
                                                    <th>Employee No.</th>
                                                    <th>Status</th>
                                                    <th>Date Created</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($admins)): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No admin accounts found for this company.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($admins as $admin): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($admin->username); ?></td>
                                                            <td><?= htmlspecialchars($admin->fName . ' ' . $admin->mName . ' ' . $admin->lName); ?></td>
                                                            <td><?= htmlspecialchars($admin->email); ?></td>
                                                            <td>
                                                                <?= $admin->SchoolName ?? '—'; ?>
                                                                <br><small><?= $admin->SchoolAddress; ?></small>
                                                            </td>
                                                            <td><?= htmlspecialchars($admin->IDNumber); ?></td>
                                                            <td>
                                                                <span class="badge badge-<?= $admin->acctStat === 'active' ? 'success' : 'secondary'; ?>">
                                                                    <?= ucfirst($admin->acctStat); ?>
                                                                </span>
                                                            </td>
                                                            <td><?= date('M d, Y', strtotime($admin->dateCreated)); ?></td>
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

                </div> <!-- container-fluid -->
            </div> <!-- content -->

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>

</html>
