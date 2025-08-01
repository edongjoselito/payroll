<!DOCTYPE html>
<html lang="en">
<title>PMS - Dashboard</title>

<?php include('includes/head.php'); ?>
<style>
    .card-hover {
        position: relative;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
        border-radius: 8px;
        padding: 0.6rem 0.8rem;
        overflow: hidden;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        background-color: #f8f9fa;
    }

    .hover-text {
        position: absolute;
        bottom: 6px;
        left: 50%;
        transform: translateX(-50%);
        color: #343a40;
        font-size: 0.7rem;
        padding: 3px 10px;
        border-radius: 20px;
        opacity: 0;
        transition: opacity 0.3s ease, bottom 0.3s ease;
        pointer-events: none;
        z-index: 2;
    }

    .card-hover:hover .hover-text {
        opacity: 1;
        bottom: 10px;
    }

    .widget-style-2 {
        padding: 1rem !important;
    }

    .widget-style-2 h2 {
        font-size: 1.4rem;
        margin-bottom: 0.25rem;
    }

    .widget-style-2 p {
        font-size: 0.9rem;
        margin: 0;
        color: #333;
    }

    .icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e9f0f9;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        margin-left: 15px;
    }

    .icon-box i {
        font-size: 22px;
        color: #007bff;
        line-height: 1;
    }

    .card-hover:hover .icon-box i {
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }

    @media (max-width: 576px) {
        .icon-box {
            width: 40px;
            height: 40px;
        }

        .icon-box i {
            font-size: 20px;
        }

        .widget-style-2 h2 {
            font-size: 1.2rem;
        }

        .widget-style-2 p {
            font-size: 0.85rem;
        }
    }
   .fb-style-popup {
    padding: 1.4rem 1.8rem !important;
    font-family: 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    border-radius: 12px;
    background-color: #fff;
    transition: all 0.2s ease-in-out;
}

.swal2-popup.swal2-modal {
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
}

.fb-style-title {
    font-size: 18px !important;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #050505;
}

.fb-style-text {
    font-size: 14px !important;
    color: #4b4f56;
    margin-bottom: 1.5rem;
}

.fb-style-confirm {
    background-color: #1877f2 !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 6px 16px !important;
    font-size: 14px;
    font-weight: 500;
    margin-left: 0.5rem;
    transition: background-color 0.2s ease;
}

.fb-style-confirm:hover,
.fb-style-confirm:focus {
    background-color: #1666d8 !important;
    box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.2);
}

.fb-style-cancel {
    background-color: transparent !important;
    color: #1877f2 !important;
    font-weight: 500;
    border: none !important;
    padding: 6px 14px !important;
    font-size: 14px;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.fb-style-cancel:hover,
.fb-style-cancel:focus {
    background-color: rgba(24, 119, 242, 0.05) !important;
    color: #1666d8 !important;
    border-radius: 6px;
}

.swal2-close {
    color: #606770 !important;
    font-size: 1.2rem !important;
    top: 12px;
    right: 14px;
}

.swal2-close:hover {
    color: #050505 !important;
}

</style>

<body class="light-mode">


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
<div class="row">
   <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
    <div class="card card-hover" onclick="confirmNavigation('<?= base_url(); ?>Project/project_view', event)">



            <div class="card-body widget-style-2">
                <div class="media">
                    <div class="media-body align-self-center">
                        <h2 class="my-0">
                            <span data-plugin="counterup"><?= $project_count ?></span>
                        </h2>
                        <p class="mb-0 text-dark">Projects</p>
                    </div>
                    <i class="mdi mdi-hard-hat text-primary bg-light ml-3"></i>
                </div>
            </div>
            <span class="hover-text">Click to view</span>
        </div>
    </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
     <div class="card card-hover" onclick="confirmNavigation('<?= base_url(); ?>Personnel/manage', event)">

            <div class="card-body widget-style-2">
                <div class="media">
                    <div class="media-body align-self-center">
                        <h2 class="my-0">
                            <span data-plugin="counterup"><?= $personnel_count ?></span>
                        </h2>
                        <p class="mb-0 text-dark">Personnel</p>
                    </div>
                    <i class="mdi mdi-account text-primary bg-light ml-3"></i>
                </div>
            </div>
            <span class="hover-text">Click to view</span>
        </div>
    </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
    <div class="card card-hover" onclick="confirmNavigation('<?= base_url(); ?>User', event)">

            <div class="card-body widget-style-2">
                <div class="media">
                    <div class="media-body align-self-center">
                        <h2 class="my-0">
                            <span data-plugin="counterup"><?= $user_count ?></span>
                        </h2>
                        <p class="mb-0 text-dark">Manage Users</p>
                    </div>
                    <i class="mdi mdi-account-group text-primary bg-light ml-3"></i>
                </div>
            </div>
            <span class="hover-text">Click to view</span>
        </div>
    </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
     <div class="card card-hover" onclick="confirmNavigation('<?= base_url(); ?>Company', event)">

            <div class="card-body widget-style-2">
                <div class="media">
                    <div class="media-body align-self-center">
                        <h2 class="my-0">
                            <span data-plugin="counterup">1</span>
                        </h2>
                        <p class="mb-0 text-dark">Company Info</p>
                    </div>
                    <i class="mdi mdi-office-building text-primary bg-light ml-3"></i>
                </div>
            </div>
            <span class="hover-text">Click to view</span>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
  function confirmNavigation(url, event) {
    if (event) event.stopPropagation();

    Swal.fire({
        title: '<strong>Are you sure?</strong>',
        html: "Do you want to visit this section?",
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: 'Proceed',
        cancelButtonText: 'Cancel',
        showCloseButton: true,
        width: '400px',
        customClass: {
            popup: 'fb-style-popup',
            title: 'fb-style-title',
            htmlContainer: 'fb-style-text',
            confirmButton: 'fb-style-confirm',
            cancelButton: 'fb-style-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            setTimeout(() => {
                window.location.href = url;
            }, 100);
        }
    });
}

</script>


</body>




</html>