<!DOCTYPE html>
<html lang="en">
<title>PMS - Project List</title>
<?php include('includes/head.php'); ?>
<style>
/* General button styling */
.btn {
  padding: 6px 12px !important;
  font-size: 15px;
  border-radius: 6px;
  margin-right: 4px; /* Add spacing between buttons */
  transition: all 0.25s ease-in-out;
}

/* Final button margin fix for last item */
td .btn:last-child {
  margin-right: 0;
}

/* Hover effects */
.btn:hover {
  transform: scale(1.07);
  opacity: 0.95;
}

/* Colored button glow */
.btn-success:hover {
  box-shadow: 0 0 8px rgba(40, 167, 69, 0.4);
}
.btn-info:hover {
  box-shadow: 0 0 8px rgba(23, 162, 184, 0.4);
}
.btn-primary:hover {
  box-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
}
.btn-warning:hover {
  box-shadow: 0 0 8px rgba(255, 193, 7, 0.4);
}
.btn-danger:hover {
  box-shadow: 0 0 8px rgba(220, 53, 69, 0.4);
}
.btn-secondary:hover {
  box-shadow: 0 0 8px rgba(108, 117, 125, 0.4);
}
.icon-expand-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 44px;
  height: 44px;
  padding: 0 12px;
  overflow: hidden;
  white-space: nowrap;
  border-radius: 6px;
  transition: width 0.6s ease, padding 0.6s ease;
  position: relative;
}

.icon-expand-btn i {
  font-size: 18px;
  transition: transform 0.4s ease;
}

.icon-expand-btn span {
  opacity: 0;
  width: 0;
  margin-left: 0;
  overflow: hidden;
  transition: all 0.6s ease;
  display: inline-block;
}

.icon-expand-btn:hover {
  width: 170px; /* Enough space for icon + "Add New Project" */
  justify-content: flex-start;
  padding: 0 16px;
}

.icon-expand-btn:hover span {
  opacity: 1;
  width: auto;
  margin-left: 8px;
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
        <button type="button"
    class="btn btn-success icon-expand-btn"
    data-toggle="modal"
    data-target="#addModal">
    <i class="mdi mdi-plus"></i>
    <span>Add New Project</span>
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
<!-- 
<a href="<?= base_url('project/attendance/' . $proj->settingsID . '?pid=' . $proj->projectID) ?>"
   class="btn btn-info btn-sm"
   data-toggle="tooltip"
   title="View Attendance">
    <i class="fas fa-calendar-check"></i>
</a> -->

<a href="<?= base_url('project/attendance_list/' . $proj->settingsID . '?pid=' . $proj->projectID) ?>"
   class="btn btn-primary btn-sm"
   data-toggle="tooltip"
   title="Attendance List">
    <i class="fas fa-list"></i>
</a>

<!-- <button class="btn btn-dark btn-sm"
        data-toggle="modal"
        data-target="#payrollModal<?= $proj->projectID ?>"
        data-toggle="tooltip"
        title="Payroll">
    <i class="fas fa-money-check-alt"></i>
</button> -->

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

</a>


</td>

                                                </tr>

                                            
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
                                                        <div class="form-group">
                                                        <label>Salary Type</label>
                                                        <select name="rateType" class="form-control">
                                                            <option value="" disabled selected>Select salary type</option>
                                                            <option value="Hour">Per Hour</option>
                                                            <option value="Day">Per Day</option>
                                                            <option value="Month">Per Month</option>
                                                        </select>
                                                        </div>
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
    $('[title]').tooltip(); // initialize all elements with title as tooltip
});


    </script>
</body>
</html>
