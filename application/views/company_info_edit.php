<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<?php
$logoMime = '';
$logoData = '';
if (!empty($info->schoolLogo)) {
    $logoImageInfo = @getimagesizefromstring($info->schoolLogo);
    $logoMime = $logoImageInfo && !empty($logoImageInfo['mime']) ? $logoImageInfo['mime'] : 'image/png';
    $logoData = base64_encode($info->schoolLogo);
}

$letterHeadMime = '';
$letterHeadData = '';
if (!empty($info->letterHead)) {
    $letterHeadImageInfo = @getimagesizefromstring($info->letterHead);
    $letterHeadMime = $letterHeadImageInfo && !empty($letterHeadImageInfo['mime']) ? $letterHeadImageInfo['mime'] : 'image/png';
    $letterHeadData = base64_encode($info->letterHead);
}
?>

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
              <input type="hidden" name="settingsID" value="<?= html_escape($info->settingsID); ?>">

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="SchoolName" class="form-control" value="<?= html_escape($info->SchoolName); ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Company Head</label>
                    <input type="text" name="SchoolHead" class="form-control" value="<?= html_escape($info->SchoolHead); ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Contact Number/s</label>
                    <input type="text" name="contactNos" class="form-control" value="<?= html_escape($info->contactNos ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label>Company Logo</label>
                    <?php if (!empty($logoData)): ?>
                      <div class="mb-2">
                        <img src="data:<?= $logoMime; ?>;base64,<?= $logoData; ?>" alt="Company Logo" class="img-thumbnail" style="max-height: 90px;">
                      </div>
                    <?php else: ?>
                      <p class="text-muted small">No logo uploaded yet.</p>
                    <?php endif; ?>
                    <input type="file" name="schoolLogo" class="form-control-file" accept="image/png, image/jpeg">
                    <small class="form-text text-muted">Allowed: PNG or JPG, up to 2MB. Leave blank to keep the current logo.</small>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Company Address</label>
                    <input type="text" name="SchoolAddress" class="form-control" value="<?= html_escape($info->SchoolAddress); ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Head Position</label>
                    <input type="text" name="sHeadPosition" class="form-control" value="<?= html_escape($info->sHeadPosition); ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Telephone Number</label>
                    <input type="text" name="telNo" class="form-control" value="<?= html_escape($info->telNo ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label>TIN</label>
                    <input type="text" name="tinNo" class="form-control" value="<?= html_escape($info->tinNo ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label>Letter Head</label>
                    <?php if (!empty($letterHeadData)): ?>
                      <div class="mb-2">
                        <img src="data:<?= $letterHeadMime; ?>;base64,<?= $letterHeadData; ?>" alt="Letterhead" class="img-thumbnail" style="max-height: 90px;">
                      </div>
                    <?php else: ?>
                      <p class="text-muted small">No letterhead uploaded yet.</p>
                    <?php endif; ?>
                    <input type="file" name="letterHead" class="form-control-file" accept="image/png, image/jpeg">
                    <small class="form-text text-muted">Allowed: PNG or JPG, up to 2MB. Leave blank to keep the current image.</small>
                  </div>
                </div>
              </div>

              <hr>
              <h5 class="mt-4"><strong>Signatories</strong></h5>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Prepared By (Name)</label>
                    <input type="text" name="prepared_by_name" class="form-control" value="<?= html_escape($info->prepared_by_name ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label>Prepared By (Position)</label>
                    <input type="text" name="prepared_by_position" class="form-control" value="<?= html_escape($info->prepared_by_position ?? ''); ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Checked By (Name)</label>
                    <input type="text" name="checked_by_name" class="form-control" value="<?= html_escape($info->checked_by_name ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label>Checked By (Position)</label>
                    <input type="text" name="checked_by_position" class="form-control" value="<?= html_escape($info->checked_by_position ?? ''); ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Additional Signatory (Name)</label>
                    <input type="text" name="additional_name" class="form-control" value="<?= html_escape($info->additional_name ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label>Additional Signatory (Position)</label>
                    <input type="text" name="additional_position" class="form-control" value="<?= html_escape($info->additional_position ?? ''); ?>">
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
