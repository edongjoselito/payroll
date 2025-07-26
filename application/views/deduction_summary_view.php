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

    <?php
    $has_ca = false;
    $has_gd = false;
    foreach ($summary as $row) {
        if (!empty($row->ca_amount) && $row->ca_amount > 0) $has_ca = true;
        if (!empty($row->gd_amount) && $row->gd_amount > 0) $has_gd = true;
    }
    ?>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span><strong>All Deductions</strong></span>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-toggle="collapse" data-target="#deductionTable" aria-expanded="true">
                Hide / View Table
            </button>
        </div>
        <div class="collapse show" id="deductionTable">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>Personnel Name</th>
                                <?php if ($has_ca): ?>
                                    <th>Cash Advance Desc</th>
                                    <th>CA Amount</th>
                                    <th>CA Date</th>
                                <?php endif; ?>
                                <?php if ($has_gd): ?>
                                    <th>Gov't Deduction Desc</th>
                                    <th>GD Amount</th>
                                    <th>GD Date</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_ca = 0;
                            $total_gd = 0;
                            foreach ($summary as $row): 
                                if (
                                    (empty($row->ca_amount) || $row->ca_amount == 0) &&
                                    (empty($row->gd_amount) || $row->gd_amount == 0)
                                ) continue;

                                $total_ca += $row->ca_amount ?? 0;
                                $total_gd += $row->gd_amount ?? 0;
                            ?>
                            <tr>
                                <td><?= $row->full_name ?></td>
                                <?php if ($has_ca): ?>
                                    <td><?= $row->ca_desc ?? '-' ?></td>
                                    <td><?= $row->ca_amount ? '₱' . number_format($row->ca_amount, 2) : '-' ?></td>
                                    <td><?= $row->ca_date ?? '-' ?></td>
                                <?php endif; ?>
                                <?php if ($has_gd): ?>
                                    <td><?= $row->gd_desc ?? '-' ?></td>
                                    <td><?= $row->gd_amount ? '₱' . number_format($row->gd_amount, 2) : '-' ?></td>
                                    <td><?= $row->gd_date ?? '-' ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-white border-top">
                                <td class="text-right"><?= $has_ca && $has_gd ? 'TOTAL CA:' : 'TOTAL:' ?></td>
                                <?php if ($has_ca): ?>
                                    <td></td>
                                    <td>₱<?= number_format($total_ca, 2) ?></td>
                                    <td></td>
                                <?php endif; ?>
                                <?php if ($has_gd): ?>
                                    <td class="text-right"><?= $has_ca ? 'TOTAL GD:' : '' ?></td>
                                    <td>₱<?= number_format($total_gd, 2) ?></td>
                                    <td></td>
                                <?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
