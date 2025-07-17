<!DOCTYPE html>
<html lang="en">
<head>
    <title>PMS - Years of Service</title>
    <?php include(APPPATH . 'views/includes/head.php'); ?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

    <style>
        th, td {
            vertical-align: middle !important;
            font-size: 14px;
        }
        th, td {
    text-align: center !important;
    vertical-align: middle !important;
    font-size: 14px;
    white-space: nowrap;
}

        td {
            text-align: center;
            white-space: nowrap;
        }
    </style>
</head>

<body>
<div id="wrapper">
    <?php include(APPPATH . 'views/includes/top-nav-bar.php'); ?>
    <?php include(APPPATH . 'views/includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Years of Service - All Personnel</h4>
                    <a href="<?= base_url('personnel/manage') ?>" class="btn btn-primary btn-md">Back to List</a>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Date Employed</th>
                                        <th>Years of Service</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($personnel as $p): ?>
                                        <tr>
                                            <td><?= "{$p->last_name}, {$p->first_name}" ?></td>
                                            <td>
                                                <?php
                                                    if (!empty($p->date_employed) && $p->date_employed != '0000-00-00') {
                                                        echo date('M d, Y', strtotime($p->date_employed));
                                                    } else {
                                                        echo '<span style="color: #888;">—</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($p->date_employed) && $p->date_employed != '0000-00-00') {
                                                        $start = new DateTime($p->date_employed);
                                                        $end = (!empty($p->date_terminated) && $p->date_terminated != '0000-00-00') ? new DateTime($p->date_terminated) : new DateTime();
                                                        $interval = $start->diff($end);
                                                        echo "{$interval->y} year(s), {$interval->m} month(s)";
                                                    } else {
                                                        echo '<span style="color: #888;">—</span>';
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div> <!-- /.table-responsive -->
                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->

            </div> <!-- /.container-fluid -->
        </div> <!-- /.content -->
        <?php include(APPPATH . 'views/includes/footer.php'); ?>
    </div> <!-- /.content-page -->
</div> <!-- /#wrapper -->

<!-- JS scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>
