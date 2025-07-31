<!DOCTYPE html>
<html lang="en">
<?php include(APPPATH . 'views/includes/head.php'); ?>
<style>
/* Base transition for all buttons */
.btn {
    transition: all 0.25s ease-in-out;
}

/* Glow + scale effect on hover */
.btn:hover {
    transform: scale(1.05);
    opacity: 0.95;
}

/* Specific glow per button type */
.btn-primary:hover {
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
    border-color: rgba(0, 123, 255, 0.4);
}

.btn-secondary:hover {
    box-shadow: 0 0 8px rgba(108, 117, 125, 0.5);
    border-color: rgba(108, 117, 125, 0.4);
}

.btn-info:hover {
    box-shadow: 0 0 8px rgba(23, 162, 184, 0.5);
    border-color: rgba(23, 162, 184, 0.4);
}

.btn-danger:hover {
    box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
    border-color: rgba(220, 53, 69, 0.4);
}
</style>

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

                <!-- Header with Page Title and Back Button -->
                <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                  <a href="<?= base_url('Personnel/manage') ?>" class="btn btn-info text-white">

                        <i class="mdi mdi-arrow-left" ></i> Back to List
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

                <!-- Edit Form -->
             <div class="card mt-3 border-0 shadow-none">

    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Edit Personnel Information</h5>
    </div>
    <div class="card-body">

                        <form method="post" action="<?= base_url('personnel/update') ?>">
                            <input type="hidden" name="personnelID" value="<?= $personnel->personnelID ?>">

                            <?php
                                // Load shared form fields
                                $this->load->view('personnel/form_fields', compact('personnel'));
                            ?>

                                                    <div class="form-group text-right mt-4">
    <button type="submit" class="btn btn-info">
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
