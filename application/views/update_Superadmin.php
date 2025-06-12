<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
    <!-- Begin page -->
    <div id="wrapper">

        <!-- Topbar Start -->
        <?php include('includes/top-nav-bar.php'); ?>
        <!-- end Topbar -->

        <!-- Left Sidebar Start -->
        <?php include('includes/sidebar.php'); ?>
        <!-- Left Sidebar End -->

        <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="page-title-box">
                                    <h4 class="page-title">UPDATE SCHOOL INFO</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb p-0 m-0">
                                            <li class="breadcrumb-item"><a href="#"><span class="badge badge-purple mb-3">Currently login to <b>SY <?php echo $this->session->userdata('sy');?> <?php echo $this->session->userdata('semester');?></span></b></a></li>
                                        </ol>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
					 
                        <!-- end page title -->
						<div class="row">
                            <div class="col-md-12">
							<?php echo $this->session->flashdata('msg'); ?>
                                <div class="card">
                                    <div class="card-body table-responsive">
                                    <?php foreach ($data as $row) { ?>
										<form role="form" method="post" enctype="multipart/form-data">
    <div class="card-body">
        <div class="row">
            <!-- Column 1 -->
            <div class="col-md-6">
                <label for="SchoolName">School Name</label>
                <input type="text" class="form-control" name="SchoolName" value="<?php echo $row->SchoolName; ?>">
            </div>
            <div class="col-md-6">
                <label for="SchoolAddress">School Address</label>
                <input type="text" class="form-control" name="SchoolAddress" value="<?php echo $row->SchoolAddress; ?>">
            </div>
            <div class="col-md-6">
                <label for="SchoolHead">School Head</label>
                <input type="text" class="form-control" name="SchoolHead" value="<?php echo $row->SchoolHead; ?>">
            </div>
            <div class="col-md-6">
                <label for="sHeadPosition">Head Position</label>
                <input type="text" class="form-control" name="sHeadPosition" value="<?php echo $row->sHeadPosition; ?>">
            </div>
        </div>


        <!-- <div class="row mt-3">
            <div class="col-md-6">
                <label for="administrative">Administrative</label>
                <input type="text" class="form-control" name="administrative" value="<?php echo $row->administrative; ?>">
            </div>
            <div class="col-md-6">
                <label for="administrativePosition">Administrative Position</label>
                <input type="text" class="form-control" name="administrativePosition" value="<?php echo $row->administrativePosition; ?>">
            </div>
        </div> -->

        <!-- Additional fields as needed -->
        <!-- <div class="row mt-3">
            <div class="col-md-4">
                <label for="admissionOfficer">Admission Officer</label>
                <input type="text" class="form-control" name="admissionOfficer" value="<?php echo $row->admissionOfficer; ?>">
            </div>
            <div class="col-md-4">
                <label for="studentNoCode">Student No. Code</label>
                <input type="text" class="form-control" name="studentNoCode" value="<?php echo $row->studentNoCode; ?>">
            </div>
            <div class="col-md-4">
                <label for="scholarshipCoordinator">Scholarship Coordinator</label>
                <input type="text" class="form-control" name="scholarshipCoordinator"  value="<?php echo $row->scholarshipCoordinator; ?>">
            </div>
        </div> -->

        <div class="row mt-3">
            <div class="col-md-6">
                <label for="schoolLogo">School Logo</label>
                <input type="file" class="form-control" name="schoolLogo" accept="image/*">
            </div>
            <div class="col-md-6">
                <label for="letterHead">Letter Head</label>
                <input type="file" class="form-control" name="letterHead" accept="image/*">
            </div>
        </div>

        <!-- Add more fields as needed -->
        <div class="modal-footer mt-4">
            <input type="submit" name="update" value="Save Data" class="btn btn-primary waves-effect waves-light">
        </div>
    </div>
</form>

                    <?php } ?>  
						</div>
						</div>
						</div>
						</div>	
                    </div>

                    <!-- end container-fluid -->

                </div>
                <!-- end content -->
    </div>
    <!-- END wrapper -->

    <!-- Right Sidebar -->
    <!-- <?php include('includes/themecustomizer.php'); ?> -->
    <!-- /Right-bar -->

    <!-- Vendor js -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

    <!-- Datatables js -->
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <!-- Datatables init -->
    <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

    <!-- App js -->
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>

</html>