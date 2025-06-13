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

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex justify-content-between align-items-center">
                                <h4 class="page-title">Project Management</h4>
                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addModal">
                                    <i class="mdi mdi-plus"></i> Add New Project
                                </button>
                            </div>
                            <hr>
                        </div>
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

                    <!-- Project Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Project Title</th>
                                                <!-- <th>Date Created</th> -->
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($projects)) : ?>
                                                <tr><td colspan="3" class="text-center">No projects found.</td></tr>
                                            <?php else : ?>
                                                <?php foreach ($projects as $proj) : ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($proj->projectTitle) ?></td>
                                                        <!-- <td><?= date('M d, Y', strtotime($proj->created_at ?? 'now')) ?></td> -->
                                                    <td>
                                                        <a href="<?= base_url('project/attendance/' . $proj->settingsID) ?>" class="btn btn-info btn-sm">Attendance</a>

                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $proj->projectID ?>">
                                                            Edit
                                                        </button>

                                                        <a href="<?= base_url('Project/delete/' . $proj->projectID) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this project?')">
                                                            Delete
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

                </div> <!-- container-fluid -->
            </div> <!-- content -->
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog" style="margin-top: 60px;">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Project/store') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Project</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="projectTitle" class="form-control" placeholder="Project Title" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    <?php foreach ($projects as $proj) : ?>
        <div class="modal fade" id="editModal<?= $proj->projectID ?>" tabindex="-1">
            <div class="modal-dialog" style="margin-top: 60px;">
                <div class="modal-content">
                    <form method="post" action="<?= base_url('Project/update') ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Project</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="projectID" value="<?= $proj->projectID ?>">
                            <input type="text" name="projectTitle" class="form-control" value="<?= htmlspecialchars($proj->projectTitle) ?>" required>
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

    <!-- Scripts -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
