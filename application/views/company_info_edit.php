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
          <h4 class="page-title"><i class="mdi mdi-pencil-box-outline mr-1"></i> Edit Company Information</h4>
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
            <form method="post" action="<?= base_url('Company/update') ?>" enctype="multipart/form-data">
              <input type="hidden" name="settingsID" value="<?= $info->settingsID ?>">

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="SchoolName" class="form-control" value="<?= $info->SchoolName ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Company Head</label>
                    <input type="text" name="SchoolHead" class="form-control" value="<?= $info->SchoolHead ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Company Logo</label>
                    <?php if (!empty($info->schoolLogo)): ?>
                      <div class="mb-2">
                        <img src="data:image/png;base64,<?= base64_encode($info->schoolLogo) ?>" alt="Company Logo" class="img-thumbnail" style="max-height: 80px;">
                      </div>
                    <?php else: ?>
                      <p class="text-muted small">No logo uploaded yet.</p>
                    <?php endif; ?>
                    <input type="file" name="schoolLogo" class="form-control">
                    <small class="form-text text-muted">Allowed: JPG, PNG. Max size: 2MB.</small>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Company Address</label>
                    <input type="text" name="SchoolAddress" class="form-control" value="<?= $info->SchoolAddress ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Head Position</label>
                    <input type="text" name="sHeadPosition" class="form-control" value="<?= $info->sHeadPosition ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Letter Head</label>
                    <?php if (!empty($info->letterHead)): ?>
                      <div class="mb-2">
                        <img src="data:image/png;base64,<?= base64_encode($info->letterHead) ?>" alt="Letterhead" class="img-thumbnail" style="max-height: 80px;">
                      </div>
                    <?php else: ?>
                      <p class="text-muted small">No letterhead uploaded yet.</p>
                    <?php endif; ?>
                    <input type="file" name="letterHead" class="form-control">
                    <small class="form-text text-muted">Allowed: JPG, PNG. Max size: 2MB.</small>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-success">Save Data</button>
              <a href="<?= base_url('Company') ?>" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>

      </div>
    </div>
    <?php include('includes/footer.php'); ?>
  </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
