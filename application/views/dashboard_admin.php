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
                                <h4 class="page-title">Company Name Here<br />
                                    <small class="text-muted">Company Address</small>
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb p-0 m-0">
                                        <!-- <li class="breadcrumb-item"><a href="#"><span class="badge badge-purple mb-3">Currently login to <b>SY <?php echo $this->session->userdata('sy'); ?> <?php echo $this->session->userdata('semester'); ?></span></b></a></li> -->
                                    </ol>
                                </div>
                                <div class="clearfix"></div>
                                <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:20px 0;" />
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <!-- <div class="col-xl-3 col-sm-6">
                            <div class="card">
                                <div class="card-body widget-style-2">
                                    <div class="media">
                                        <div class="media-body align-self-center">
                                            <h2 class="my-0"><span data-plugin="counterup">0</span></h2>
                                            <p class="mb-0"><a href="<?= base_url(); ?>Page/proof_payment_view">For Payment Verification</a></p>
                                            </a>
                                        </div>
                                        <i class="mdi mdi-cash-marker text-pink bg-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="col-xl-3 col-sm-6">
                            <div class="card">
                                <div class="card-body widget-style-2">
                                    <div class="media">
                                        <div class="media-body align-self-center">
                                            <h2 class="my-0"><span data-plugin="counterup">0</span></h2>
                                            <p class="mb-0"><a href="<?= base_url(); ?>Page/forValidation">For Admission</a></p>
                                        </div>
                                        <i class=" mdi mdi-briefcase-plus-outline text-purple bg-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="col-xl-6 col-sm-6">
                            <div class="card">
                                <div class="card-body widget-style-2">
                                    <div class="media">
                                        <div class="media-body align-self-center">
                                            <h2 class="my-0"><span data-plugin="counterup">0</span></h2>
                                            <p class="mb-0"><a href="<?= base_url(); ?>Page/profileList">Projects</a></p>
                                        </div>
                                        <i class="mdi mdi-layers-plus text-info bg-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-sm-6">
                            <div class="card">
                                <div class="card-body widget-style-2">
                                    <div class="media">
                                        <div class="media-body align-self-center">
                                            <h2 class="my-0"><span data-plugin="counterup">0</span></h2>
                                            <p class="mb-0"><a href="<?= base_url(); ?>Page/employeeList">Personnel</a></p>
                                        </div>
                                        <i class="mdi mdi-teach text-primary bg-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                 

            </div>

       
        </div>


    <!-- Footer Start -->
    <?php include('includes/footer.php'); ?>
    <!-- end Footer -->

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

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

</body>




</html>