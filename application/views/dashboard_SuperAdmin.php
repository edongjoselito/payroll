<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Topbar Start -->
        <?php include('includes/top-nav-bar.php'); ?>
        <!-- end Topbar --> <!-- ========== Left Sidebar Start ========== -->

        <!-- Lef Side bar -->
        <?php include('includes/sidebar.php'); ?>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Super Admin Dashboard<br />
                                    <!-- <small class="text-muted">Company Address</small> -->
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb p-0 m-0">
                                      <!-- Add New Button -->
<!-- Add New Button -->
<div class="mb-3">
    <button class="btn btn-success" data-toggle="modal" data-target="#addSuperAdminModal">
        <i class="mdi mdi-plus"></i> Add Company
    </button>
</div>


                                    </ol>
                                </div>
                                <div class="clearfix"></div>
                                <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:20px 0;" />
                            </div>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('msg')) : ?>
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<?= $this->session->flashdata('msg'); ?>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
<?php endif; ?>

                      <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <!-- <div class="panel-heading">
                                                    <h4>Invoice</h4>
                                                </div> -->
                                    <div class="card-body">
                                        <div class="clearfix">
                    <!-- Table Section -->
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>Settings ID</th>
                                <th>School Name</th>
                                <th>School Address</th>
                                <th>School Head</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row) { ?>
                                <tr>
                                    <td><?= $row->settingsID; ?></td>
                                    <td><?= $row->SchoolName; ?></td>
                                    <td><?= $row->SchoolAddress; ?></td>
                                    <td><?= $row->SchoolHead; ?></td>
                                    <td style="text-align: center;">
                                        <a href="<?= base_url('Page/updateSuperAdmin?settingsID=' . $row->settingsID); ?>" 
                                        class="btn btn-primary waves-effect waves-light btn-sm">
                                        <i class="mdi mdi-pencil"></i> Edit
                                        </a>

                                        <!-- Add Admin Button -->
                                        <!-- Add Admin Button -->
                                    <button 
                                        class="btn btn-success btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#addAdminModal"
                                        data-settingsid="<?= $row->settingsID; ?>">
                                        <i class="mdi mdi-account-plus"></i> Add Admin
                                    </button>

                                    </td>

                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                </div>
                </div>
                </div>
                </div>
                </div>
                <!-- End container-fluid -->
<!-- Add Super Admin Modal -->
<div class="modal fade" id="addSuperAdminModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form action="<?= base_url('Page/addNewSuperAdmin'); ?>" method="post" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Super Admin</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>School Name</label>
            <input type="text" name="SchoolName" class="form-control" required>
          </div>
          <div class="form-group">
            <label>School Address</label>
            <input type="text" name="SchoolAddress" class="form-control" required>
          </div>
          <div class="form-group">
            <label>School Head</label>
            <input type="text" name="SchoolHead" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Head Position</label>
            <input type="text" name="sHeadPosition" class="form-control" required>
          </div>
          <div class="form-group">
            <label>School Logo</label>
            <input type="file" name="schoolLogo" class="form-control" accept="image/*">
          </div>
          <div class="form-group">
            <label>Letter Head</label>
            <input type="file" name="letterHead" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-labelledby="addAdminLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="<?= base_url('Page/saveAdminFromSuperAdmin'); ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Admin</h5>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <!-- Hidden field for settingsID -->
          <input type="hidden" name="settingsID" id="modalSettingsID">
          
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">First Name</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="fName" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Middle Name</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="mName">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Last Name</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="lName" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Employee No.</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="IDNumber" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Email</label>
            <div class="col-sm-8">
              <input type="email" class="form-control" name="email" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Account Level</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="acctLevel" value="Admin" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Username</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="username" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Password</label>
            <div class="col-sm-8">
              <input type="password" class="form-control" name="password" required minlength="8">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-info">Create Admin</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>



            </div>
            <!-- End content -->

            <!-- Footer Start -->
            <?php include('includes/footer.php'); ?>
            <!-- End Footer -->

        </div>
        <!-- End content-page -->

    </div>

    <!-- Vendor js -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

    <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>

    <!-- Calendar init -->
    <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>

    <!-- Chat app -->
    <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>

    <!-- Todo app -->
    <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>

    <!--Morris Chart-->
    <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>

    <!-- Sparkline charts -->
    <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>

    <!-- Dashboard init JS -->
    <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>

    <!-- App js -->
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>
    <!-- Required datatable js -->
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>

    <!-- Datatables init -->
    <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>


<script>
  $('#addAdminModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var settingsID = button.data('settingsid');
    $('#modalSettingsID').val(settingsID);
  });
</script>


</body>




</html>