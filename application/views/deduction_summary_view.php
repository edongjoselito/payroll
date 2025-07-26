<!DOCTYPE html>
<html lang="en">
<title>PMS - Deduction Summary</title>

<?php include('includes/head.php'); ?>

<body>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
<div class="content">
<div class="container-fluid">

    <div class="page-title-box d-flex justify-content-between align-items-center">
        <h4 class="page-title">Deduction Summary</h4>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php elseif ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                    <thead class="thead-light">
                        <tr>
                            <th>Personnel Name</th>
                            <th>Cash Advance Desc</th>
                            <th>CA Amount</th>
                            <th>CA Date</th>
                            <th>Gov't Deduction Desc</th>
                            <th>GD Amount</th>
                            <th>GD Date</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php foreach ($summary as $row): ?>
                        <tr>
                            <td><?= $row->full_name ?></td>
                            <td><?= $row->ca_desc ?? '-' ?></td>
                            <td><?= $row->ca_amount ? '₱' . number_format($row->ca_amount, 2) : '-' ?></td>
                            <td><?= $row->ca_date ?? '-' ?></td>
                            <td><?= $row->gd_desc ?? '-' ?></td>
                            <td><?= $row->gd_amount ? '₱' . number_format($row->gd_amount, 2) : '-' ?></td>
                            <td><?= $row->gd_date ?? '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
<?php include('includes/footer.php'); ?>
</div>
</div>

<!-- JS -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
