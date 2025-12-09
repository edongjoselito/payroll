<!DOCTYPE html>
<html lang="en">
<?php include(APPPATH . 'views/includes/head.php'); ?>

<body>

    <div id="wrapper">
        <?php include(APPPATH . 'views/includes/top-nav-bar.php'); ?>
        <?php include(APPPATH . 'views/includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Payroll Statement of Account</h4>

                                    <?php if ($this->session->flashdata('error')): ?>
                                        <?= $this->session->flashdata('error'); ?>
                                    <?php endif; ?>

                                    <form method="post" action="<?= base_url('Payroll/generate'); ?>">
                                        <div class="form-group">
                                            <label for="cutoff">Cutoff Code</label>
                                            <input type="text"
                                                class="form-control"
                                                id="cutoff"
                                                name="cutoff"
                                                placeholder="e.g. 2025-12A"
                                                required>
                                        </div>

                                        <div class="form-group">
                                            <label for="date_from">Date From</label>
                                            <input type="date"
                                                class="form-control"
                                                id="date_from"
                                                name="date_from"
                                                required>
                                        </div>

                                        <div class="form-group">
                                            <label for="date_to">Date To</label>
                                            <input type="date"
                                                class="form-control"
                                                id="date_to"
                                                name="date_to"
                                                required>
                                        </div>

                                        <button type="submit" class="btn btn-primary mt-2">
                                            Generate SOA
                                        </button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div><!-- end row -->

                </div><!-- container -->
            </div><!-- content -->
        </div><!-- content-page -->
    </div><!-- wrapper -->

    <?php include(APPPATH . 'views/includes/footer.php'); ?>
</body>

</html>