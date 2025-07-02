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
          <h4 class="page-title"><i class="mdi mdi-office-building mr-1"></i> Company Information</h4>
         <button class="btn btn-info" data-toggle="modal" data-target="#editCompanyModal">
  <i class="mdi mdi-pencil"></i> Edit Information
</button>
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
            <div class="row">

              <div class="col-md-6">
                <h5 class="text-info mb-3">General Information</h5>
                <p><strong>Company Name:</strong><br><?= $info->SchoolName ?></p>
                <p><strong>Company Address:</strong><br><?= $info->SchoolAddress ?></p>
                <p><strong>Company Head:</strong><br><?= $info->SchoolHead ?></p>
                <p><strong>Head Position:</strong><br><?= $info->sHeadPosition ?></p>
              </div>

              <div class="col-md-6">
                <h5 class="text-info mb-3">Visual Assets</h5>
                <div class="mb-3">
                  <strong>Company Logo:</strong><br>
                  <?php if (!empty($info->schoolLogo)): ?>
                    <img src="data:image/png;base64,<?= base64_encode($info->schoolLogo) ?>" alt="Company Logo" class="img-thumbnail" style="max-height: 100px;">
                  <?php else: ?>
                    <p class="text-muted">No logo uploaded.</p>
                  <?php endif; ?>
                </div>

                <div class="mb-3">
                  <strong>Letter Head:</strong><br>
                  <?php if (!empty($info->letterHead)): ?>
                    <img src="data:image/png;base64,<?= base64_encode($info->letterHead) ?>" alt="Letter Head" class="img-thumbnail" style="max-height: 100px;">
                  <?php else: ?>
                    <p class="text-muted">No letterhead uploaded.</p>
                  <?php endif; ?>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
    <?php include('includes/footer.php'); ?>
  </div>
</div>
<!-- Edit Company Info Modal -->
<div class="modal fade" id="editCompanyModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post" action="<?= base_url('Company/update') ?>" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title">Edit Company Information</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
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
                  <img src="data:image/png;base64,<?= base64_encode($info->schoolLogo) ?>" class="img-thumbnail mb-2" style="max-height: 80px;">
                <?php endif; ?>
                <input type="file" name="schoolLogo" class="form-control">
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
                  <img src="data:image/png;base64,<?= base64_encode($info->letterHead) ?>" class="img-thumbnail mb-2" style="max-height: 80px;">
                <?php endif; ?>
                <input type="file" name="letterHead" class="form-control">
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Changes</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
