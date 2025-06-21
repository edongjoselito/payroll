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
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex justify-content-between align-items-center">
                            <!-- <h4 class="page-title">Project Management</h4> -->
                            <button class="btn btn-success btn-md" data-toggle="modal" data-target="#addModal">
                                <i class="mdi mdi-plus"></i> Add New Project
                            </button>
                        </div>
                        <hr>
                    </div>
                </div>



                <!-- Project Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            
                            <div class="card-body">
                                <h4 class="page-title">Project Management</h4>
                                <table id="datatable" class="table table-bordered dt-responsive nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Project Title</th>
                                            <!-- <th>Location</th> -->
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($projects)) : ?>
                                            <tr><td colspan="2" class="text-center">No projects found.</td></tr>
                                        <?php else : ?>
                                            <?php foreach ($projects as $proj) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($proj->projectTitle) ?></td>
                                                    <!-- <td><?= htmlspecialchars($proj->projectLocation) ?></td> -->
                                                 <td>

    <a href="<?= base_url('project/assign_personnel/' . $proj->settingsID . '/' . $proj->projectID) ?>"
       class="btn btn-success btn-sm"
       data-toggle="tooltip"
       title="Assign Personnel">
        <i class="fas fa-user-plus"></i>
    </a>

    <a href="<?= base_url('project/attendance/' . $proj->settingsID . '?pid=' . $proj->projectID) ?>"
       class="btn btn-info btn-sm"
       data-toggle="tooltip"
       title="View Attendance">
        <i class="fas fa-calendar-check"></i>
    </a>

    <a href="<?= base_url('project/attendance_list/' . $proj->settingsID . '?pid=' . $proj->projectID) ?>"
       class="btn btn-primary btn-sm"
       data-toggle="tooltip"
       title="Attendance List">
        <i class="fas fa-list"></i>
    </a>


    <button class="btn btn-dark btn-sm"
            data-toggle="modal"
            data-target="#payrollModal<?= $proj->projectID ?>"
            data-toggle="tooltip"
            title="Payroll">
        <i class="fas fa-money-check-alt"></i>
    </button>

    <button class="btn btn-warning btn-sm"
            data-toggle="modal"
            data-target="#editModal<?= $proj->projectID ?>"
            data-toggle="tooltip"
            title="Edit Project">
        <i class="fas fa-edit"></i>
    </button>

    <a href="<?= base_url('Project/delete/' . $proj->projectID) ?>"
       class="btn btn-danger btn-sm"
       onclick="return confirm('Delete this project?')"
       data-toggle="tooltip"
       title="Delete Project">
        <i class="fas fa-trash-alt"></i>
    </a>
</td>

                                                </tr>

                                                <!-- Payroll Modal -->
                                                <div class="modal fade" id="payrollModal<?= $proj->projectID ?>" tabindex="-1" role="dialog" aria-labelledby="payrollModalLabel<?= $proj->projectID ?>" aria-hidden="true">
                                                  <div class="modal-dialog mt-5" role="document">

                                        <form method="get" action="<?= base_url('project/payroll_report/' . $proj->settingsID) ?>" target="_blank">
                                                      <input type="hidden" name="pid" value="<?= $proj->projectID ?>">
                                                      <div class="modal-content">
                                                        <div class="modal-header">
                                                          <h5 class="modal-title" id="payrollModalLabel<?= $proj->projectID ?>">Generate Payroll Report</h5>
                                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span>&times;</span>
                                                          </button>
                                                        </div>
                                                        <div class="modal-body">
                                                          <div class="form-group">
                                                            <label>Start Date</label>
                                                            <input type="date" name="start" class="form-control" required>
                                                          </div>
                                                          <div class="form-group">
                                                            <label>End Date</label>
                                                            <input type="date" name="end" class="form-control" required>
                                                          </div>
                                                        </div>
                                                        <div class="form-group">
                                                        <label>Salary Type</label>
                                                        <select name="rateType" class="form-control">
                                                            <option value="" disabled selected>Select salary type</option>
                                                            <option value="Hour">Per Hour</option>
                                                            <option value="Day">Per Day</option>
                                                            <option value="Month">Per Month</option>
                                                        </select>
                                                        </div>

                                                        <div class="modal-footer">
                                                          <button type="submit" class="btn btn-primary">Generate</button>
                                                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        </div>
                                                      </div>
                                                    </form>
                                                  </div>
                                                </div>

                                                <!-- Edit Modal -->
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
                                                                 <div class="modal-body">
                                                                    <input type="text" name="projectLocation" class="form-control" value="<?= htmlspecialchars($proj->projectLocation) ?>" required>
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
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
                     <div class="modal-body">
                        <input type="text" name="projectLocation" class="form-control" placeholder="Project Location" required>
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

    <script>
        $(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

    </script>
</body>
</html>
