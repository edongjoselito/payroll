<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="page-title-box d-flex justify-content-between align-items-center">
                        <h4 class="page-title">Rate Management</h4>
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addRateModal">
                            <i class="mdi mdi-plus"></i> Add New Rate
                        </button>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show"><?= $this->session->flashdata('success') ?></div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show"><?= $this->session->flashdata('error') ?></div>
                    <?php endif; ?>

                    <div class="card mt-3">
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Rate Type</th>
                                        <th>Rate Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rates as $rate): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rate->rateType) ?></td>
                                            <td>₱ <?= number_format($rate->rateAmount, 2) ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editRateModal<?= $rate->rateID ?>">Edit</button>
                                                <a href="<?= base_url('rate/delete/' . $rate->rateID) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this rate?')">Delete</a>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editRateModal<?= $rate->rateID ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="post" action="<?= base_url('rate/update') ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Rate</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="rateID" value="<?= $rate->rateID ?>">
                                                            <div class="form-group">
                                                                <label>Rate Type</label>
<select name="rateType" class="form-control" required>
    <option value="Hour" <?= $rate->rateType == 'Hour' ? 'selected' : '' ?>>Hour</option>
    <option value="Day" <?= $rate->rateType == 'Day' ? 'selected' : '' ?>>Day</option>
</select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Rate Amount</label>
                                                                <input type="number" step="0.01" name="rateAmount" value="<?= $rate->rateAmount ?>" class="form-control" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Add Rate Modal -->
    <div class="modal fade" id="addRateModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?= base_url('rate/store') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Rate</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Rate Type</label>
<select name="rateType" class="form-control" required>
    <option value="">-- Select Rate Type --</option>
    <option value="Hour">Hour</option>
    <option value="Day">Day</option>
</select>
                        </div>
                        <div class="form-group">
                            <label>Rate Amount</label>
                            <input type="number" step="0.01" name="rateAmount" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
