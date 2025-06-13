<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<style>
    /* Styling for the tables */
    .separated-table {
        margin-bottom: 30px;
        /* Adds space between tables */
        border-collapse: separate;
        /* Ensures borders are spaced out */
        border-spacing: 0 15px;
        /* Adds space between rows */
    }

    .separated-table th,
    .separated-table td {
        padding: 10px;
        border: 1px solid #ddd;
        /* Adds border around each cell */
    }

    .separated-table th {
        background-color: #f2f2f2;
        /* Adds background color for headers */
    }

    .table-section {
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 1px solid #ccc;
    }

    h4 {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
        /* Adds a separator under headings */
    }
</style>

<body>

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Topbar Start -->
        <?php include('includes/top-nav-bar.php'); ?>
        <!-- end Topbar -->

        <!-- Lef Side bar -->
        <?php include('includes/sidebar.php'); ?>
        <!-- Left Sidebar End -->

        <!-- Start Page Content here -->
        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb p-0 m-0">
                                    </ol>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Collection Report Section -->
                 

                    <!-- Yearly Collection Summary Section -->
                    <div class="row table-section">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body table-responsive">
                                     <h4 class="page-title">📝 Project Attendance</h4>
                <div>
                     <div class="mb-3 fw-semibold">
                        📅 Attendance Date: <strong><?= date('F d, Y') ?></strong>
                    </div>
                    <form method="post" action="<?= base_url('project/save_attendance') ?>" class="d-inline-block" id="attendanceFormTop">
                        <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
                        <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">
                        <button type="submit" class="btn btn-primary btn-sm me-1">
                            <i class="bi bi-save"></i> Save Attendance
                        </button>
                    </form>
                    <a href="<?= base_url('project/project_view') ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
                                    <table class="table mb-0">
                                        <thead>
                                           <tr>
                    <th>👤 Personnel</th>
                    <th>📌 Status</th>
                    <th>🕒 Work Hours (Hrs:Mins)</th>
                </tr>
                                        </thead>
                                        <tbody>
                                         <?php foreach ($personnels as $p): ?>
                <tr>
                    <td class="text-start fw-semibold"><?= $p->first_name . ' ' . $p->last_name ?></td>
                    <td>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-success btn-sm">
                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="Present" required> Present
                            </label>
                            <label class="btn btn-outline-danger btn-sm">
                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="Absent"> Absent
                            </label>
                            <label class="btn btn-outline-warning btn-sm">
                                <input type="radio" name="attendance_status[<?= $p->personnelID ?>]" value="On Leave"> On Leave
                            </label>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="workDuration[<?= $p->personnelID ?>]" class="form-control form-control-sm text-center" placeholder="e.g. 8:30" required>
                    </td>
                </tr>
            <?php endforeach; ?>
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

    </div>
    <!-- END wrapper -->


    <!-- Right Sidebar -->
    <!-- /Right-bar -->


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

</body>

</html>