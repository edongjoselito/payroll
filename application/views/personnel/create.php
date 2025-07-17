<!DOCTYPE html>
<html lang="en">
<?php include(APPPATH . 'views/includes/head.php'); ?>

<body>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
    <?php include(APPPATH . 'views/includes/top-nav-bar.php'); ?>
    <?php include(APPPATH . 'views/includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                    <a href="<?= base_url('Personnel/manage') ?>" class="btn btn-info text-white">
                        <i class="mdi mdi-arrow-left"></i> Back to List
                    </a>
                </div>

                <!-- Flash Messages -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php elseif ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="card mt-3 border">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Add New Personnel</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('personnel/store') ?>">
                            <?php
                                if (!isset($personnel)) $personnel = (object)[];
                                $this->load->view('personnel/form_fields', compact('personnel'));
                            ?>

                            <div class="form-group d-flex justify-content-end mt-4 pr-2">
                                <button type="submit" class="btn btn-info mr-2">
                                    <i class="fas fa-save mr-1"></i> Save
                                </button>
                                <a href="<?= base_url('Personnel/manage') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>



            </div>
        </div>
        <?php include(APPPATH . 'views/includes/footer.php'); ?>
    </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
