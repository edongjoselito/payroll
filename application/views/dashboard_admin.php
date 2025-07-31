<!DOCTYPE html>
<html lang="en">
<title>PMS - Dashboard</title>

<?php include('includes/head.php'); ?>
<style>
.card-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    background-color: #f7f9fc;
}
.card-hover .media i {
    transition: transform 0.3s ease;
}
.card-hover:hover .media i {
    transform: scale(1.2);
}
.company-header-box {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-left: 5px solid #007bff;
    border-radius: 6px;
    transition: box-shadow 0.3s ease;
}

.company-header-box:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.company-header-box h4 {
    font-size: 22px;
    letter-spacing: 0.5px;
}

.company-header-box p {
    font-size: 14px;
}


</style>

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
        <div class="company-header-box p-4 mb-3 rounded shadow-sm d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 text-uppercase text-primary font-weight-bold">
                    <i class="mdi mdi-office-building mr-1 text-secondary"></i>
                    <?= isset($company->SchoolName) ? strtoupper($company->SchoolName) : 'NO SCHOOL NAME'; ?>
                </h4>
                <p class="mb-0 text-muted">
                    <i class="mdi mdi-map-marker text-danger mr-1"></i>
                    <?= $company->SchoolAddress ?? 'No Address'; ?>
                </p>
            </div>
            <div class="page-title-right">
                <ol class="breadcrumb p-0 m-0">
                    <!-- <li class="breadcrumb-item"><span class="badge badge-purple">SY <?= $this->session->userdata('sy') ?> <?= $this->session->userdata('semester') ?></span></li> -->
                </ol>
            </div>
        </div>
        <hr style="border:0; height:2px; background:linear-gradient(to right, #4285F4 60%, #FBBC05 80%, #34A853 100%); border-radius:1px; margin:10px 0 30px;" />
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
    <div class="card card-hover" onclick="window.location.href='<?= base_url(); ?>Project/project_view'">
        <div class="card-body widget-style-2">
            <div class="media">
                <div class="media-body align-self-center">
                    <h2 class="my-0">
                        <span data-plugin="counterup"><?= $project_count ?></span>
                    </h2>
                    <p class="mb-0 text-dark">Projects</p>
                </div>
                <i class="mdi mdi-domain text-primary bg-light"></i>
            </div>
        </div>
    </div>
</div>


<div class="col-xl-6 col-sm-6">
    <div class="card card-hover" onclick="window.location.href='<?= base_url(); ?>Personnel/manage'">
        <div class="card-body widget-style-2">
            <div class="media">
                <div class="media-body align-self-center">
                    <h2 class="my-0">
                        <span data-plugin="counterup"><?= $personnel_count ?></span>
                    </h2>
                    <p class="mb-0 text-dark">Personnel</p>
                </div>
                <i class="mdi mdi-account-group text-primary bg-light"></i>
            </div>
        </div>
    </div>
</div>


    <div class="col-xl-6 col-sm-6">
    <div class="card card-hover" onclick="window.location.href='<?= base_url(); ?>User'">
        <div class="card-body widget-style-2">
            <div class="media">
                <div class="media-body align-self-center">
                    <h2 class="my-0">
                        <span data-plugin="counterup"><?= $user_count ?></span>
                    </h2>
                    <p class="mb-0 text-dark">Manage Users</p>
                </div>
                <i class="mdi mdi-account text-primary bg-light"></i>

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