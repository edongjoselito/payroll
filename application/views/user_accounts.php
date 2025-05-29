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
            <div class="col-md-12">
              <?php if ($this->session->flashdata('success')) : ?>

                <?= '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>'
                  . $this->session->flashdata('success') .
                  '</div>';
                ?>
              <?php endif; ?>

              <?php if ($this->session->flashdata('danger')) : ?>
                <?= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>'
                  . $this->session->flashdata('danger') .
                  '</div>';
                ?>
              <?php endif; ?>
              <div class="page-title-box">
                <h4 class="page-title">
                  <button type="button" class="btn btn-info waves-effect waves-light" data-toggle="modal" data-target=".bs-example-modal-lg">+Add New</button>
                  <a href="<?= base_url(); ?>Page/copy_users_to_o_users"><button type="button" class="btn btn-success waves-effect waves-light">Import Local Accounts</button></a>
                  <!-- <a href="<?= base_url(); ?>Page/create_stude_accts"><button type="button" class="btn btn-success waves-effect waves-light">Create All Students' Accounts</button></a> -->
                  <a href="<?= base_url(); ?>Page/updateNames"><button type="button" class="btn btn-primary waves-effect waves-light">Update Students' Names</button></a>
                </h4>
                <div class="page-title-right">
                  <ol class="breadcrumb p-0 m-0">
                    <!-- <li class="breadcrumb-item"><a href="#">Currently login to <b>SY <?php echo $this->session->userdata('sy'); ?> <?php echo $this->session->userdata('semester'); ?></b></a></li> -->
                  </ol>
                </div>
                <div class="clearfix"></div>
                <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:20px 0;" />
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body table-responsive">
                  <h4 class="m-t-0 header-title mb-4">User Accounts <br /><span class="badge badge-purple mb-3"><b>SY <?php echo $this->session->userdata('sy'); ?> <?php echo $this->session->userdata('semester'); ?></b></span></h4>

                  <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                      <tr>
                        <th>Student Name</th>
                        <th>Username</th>
                        <th>Level</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . $row->fName . ', ' . $row->mName . ' ' . $row->lName . "</td>";
                      ?>
                        <td><?php echo $row->username; ?></td>
                        <td><?php echo $row->position; ?></td>
                        <td><?php echo $row->email; ?></td>
                        <td><?php echo $row->acctStat; ?></td>
                        <td>
                          <!-- <a href="<?= base_url(); ?>page/studentsprofile?u=<?php echo $row->username; ?>" class="text-info">
                            <i class="mdi mdi-file-document-box-check-outline"></i>Update
                          </a> -->
                          <a href="<?= base_url(); ?>page/resetPass?u=<?php echo $row->username; ?>" class="text-success" onclick="return confirm('Are you sure you want to reset the password of this account?')">
                            <i class="mdi mdi-file-document-box-check-outline"></i>Reset Password
                          </a>
                          <?php if ($row->acctStat == 'active'): ?>
                            <a href="<?= base_url(); ?>page/changeUserStat?u=<?php echo $row->username; ?>&t=Deactivate" class="text-danger" onclick="return confirm('Are you sure you want to deactivate this account?')">
                              <i class="mdi mdi-file-document-box-check-outline"></i>Deactivate
                            </a>
                          <?php else: ?>
                            <a href="<?= base_url(); ?>page/changeUserStat?u=<?php echo $row->username; ?>&t=Activate" class="text-success" onclick="return confirm('Are you sure you want to activate this account?')">
                              <i class="mdi mdi-file-document-box-check-outline"></i>Activate
                            </a>
                          <?php endif; ?>
                        </td>
                      <?php
                        echo "</tr>";
                      }
                      ?>
                    </tbody>
                  </table>

                </div>
              </div>
            </div>
          </div>



        </div>

        <!-- end container-fluid -->

      </div>
      <!-- end content -->



      <!-- Footer Start -->
      <?php include('includes/footer.php'); ?>
      <!-- end Footer -->

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

  </div>
  <!-- END wrapper -->


  <!-- Right Sidebar -->
  <?php include('includes/themecustomizer.php'); ?>
  <!-- /Right-bar -->


  <!--  Modal content for the above example -->
  <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myLargeModalLabel">Add New User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body">
          <form class="form-horizontal parsley-examples" method="POST">
            <div class="card-body">
              <div class="form-group row">
                <label for="inputEmail3" class="col-sm-4 col-form-label">First Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fName" placeholder="" required>
                </div>
              </div>
              <div class="form-group row">
                <label for="inputEmail3" class="col-sm-4 col-form-label">Middle Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="mName" placeholder="">
                </div>
              </div>
              <div class="form-group row">
                <label for="inputEmail3" class="col-sm-4 col-form-label">Last Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="lName" placeholder="" required>
                </div>
              </div>
              <div class="form-group row">
                <label for="inputEmail3" class="col-sm-4 col-form-label">Employee No./Student No.</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="IDNumber" placeholder="" required>
                </div>
              </div>
              <div class="form-group row">
                <label for="inputEmail3" class="col-sm-4 col-form-label">E-mail</label>
                <div class="col-sm-8">
                  <input type="email" class="form-control" name="email" placeholder="" required>
                </div>
              </div>
              <div class="form-group row">
                <label for="inputEmail3" class="col-sm-4 col-form-label">Account Level</label>
                <div class="col-sm-8">
                  <select class="form-control" name="acctLevel" required>
                    <option value=""></option>
                    <option value="Accounting">Accounting</option>

                    <option value="Human Resource">Human Resource</option>
                    <option value="Guidance">Guidance</option>
                    <option value="Librarian">Librarian</option>
                    <option value="Property Custodian">Property Custodian</option>
                    <option value="Instructor">Instructor</option>
                    <option value="Registrar">Registrar</option>
                    <option value="Registrar Clerk">Registrar Clerk</option>
                    <!-- <option value="School Admin">School Admin</option> -->
                    <option value="School Nurse">School Nurse</option>
                    <option value="Student">Student</option>
                    <option value="Admin">School Admin</option>
                    <option value="IT">School I.T.</option>

                  </select>
                </div>
              </div>


              <div class="form-group row">
                <label for="inputEmail3" class="col-sm-4 col-form-label">Username<br /><span style="color:red"><small>Student No. for Students/Employee No. for Instructors</small></span></label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="username" placeholder="" required>
                </div>
              </div>
              <div class="form-group row">
                <label for="inputPassword3" class="col-sm-4 col-form-label">Password</label>
                <div class="col-sm-8">
                  <input
                    type="password"
                    class="form-control"
                    name="password"
                    placeholder=""
                    required
                    minlength="8"

                    title="Password must be at least 8 characters long.">

                </div>
              </div>

            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <input type="submit" name="submit" class="btn btn-info float-right" value="Create Account">
            </div>
            <!-- /.card-footer -->
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

  <script>
    document.querySelector('input[name="password"]').addEventListener('input', function(e) {
      const password = e.target.value;
      const minLength = 8;
      // const hasUpperCase = /[A-Z]/.test(password);
      // const hasLowerCase = /[a-z]/.test(password);
      // const hasDigit = /\d/.test(password);
      // const hasSpecialChar = /[@$!%*?&]/.test(password);

      if (password.length >= minLength) {
        // if (password.length >= minLength && hasUpperCase && hasLowerCase && hasDigit && hasSpecialChar) {
        e.target.setCustomValidity('');
      } else {
        e.target.setCustomValidity('Password must be at least 8 characters long.');
        // e.target.setCustomValidity('Password must be at least 8 characters long and include a mix of uppercase letters, lowercase letters, digits, and special characters.');
      }
    });
  </script>



  <!-- Vendor js -->
  <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

  <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>

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
  <script src="<?= base_url(); ?>assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
  <!-- Responsive examples -->
  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>

  <!-- Datatables init -->
  <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

  <!-- Plugin js-->
  <script src="<?= base_url(); ?>assets/libs/parsleyjs/parsley.min.js"></script>

  <!-- Validation init js-->
  <script src="<?= base_url(); ?>assets/js/pages/form-validation.init.js"></script>


</body>

</html>