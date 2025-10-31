<!DOCTYPE html>
<html lang="en">
    <title>PMS - Company Info</title>

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
<style>
    .btn-info, .btn-success, .btn-light {
        transition: all 0.3s ease;
        transform-origin: center;
        border-radius: 8px;
        font-weight: 500;
        padding: 8px 16px;
    }

    .btn-info:hover,
    .btn-success:hover,
    .btn-light:hover {
        transform: scale(1.06);
        box-shadow: 0 0 12px rgba(0, 170, 255, 0.4);
    }

    /* Optional slight glow always */
    .btn-info:focus,
    .btn-success:focus,
    .btn-light:focus {
        box-shadow: 0 0 8px rgba(0, 170, 255, 0.4);
    }

    /* Optional better spacing between title and button */
    .page-title-box {
        margin-bottom: 20px;
    }
</style>

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
                <p><strong>Company Name:</strong><br><?= html_escape($info->SchoolName); ?></p>
                <p><strong>Company Address:</strong><br><?= html_escape($info->SchoolAddress); ?></p>
                <p><strong>Contact Number/s:</strong><br><?= !empty($info->contactNos) ? html_escape($info->contactNos) : '<span class="text-muted">N/A</span>'; ?></p>
                <p><strong>Telephone Number:</strong><br><?= !empty($info->telNo) ? html_escape($info->telNo) : '<span class="text-muted">N/A</span>'; ?></p>
                <p><strong>TIN:</strong><br><?= !empty($info->tinNo) ? html_escape($info->tinNo) : '<span class="text-muted">N/A</span>'; ?></p>
                <p><strong>Company Head:</strong><br><?= html_escape($info->SchoolHead); ?></p>
                <p><strong>Head Position:</strong><br><?= html_escape($info->sHeadPosition); ?></p>
              </div>

              <div class="col-md-6">
                <h5 class="text-info mb-3">Visual Assets</h5>
                <div class="mb-3">
                  <strong>Company Logo:</strong><br>
                  <?php if (!empty($logoData)): ?>
                    <img src="data:<?= $logoMime; ?>;base64,<?= $logoData; ?>" alt="Company Logo" class="img-thumbnail" style="max-height: 120px;">
                  <?php else: ?>
                    <p class="text-muted">No logo uploaded.</p>
                  <?php endif; ?>
                </div>

                <div class="mb-3">
                  <strong>Letter Head:</strong><br>
                  <?php if (!empty($letterHeadData)): ?>
                    <img src="data:<?= $letterHeadMime; ?>;base64,<?= $letterHeadData; ?>" alt="Letter Head" class="img-thumbnail" style="max-height: 120px;">
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

<!-- Signatories Card -->
<div class="container-fluid mt-3">
  <div class="card">
    <div class="card-body">
      <h5 class="text-info mb-3">Signatories</h5>
      <div class="row">
        <div class="col-md-4">
          <p><strong>Prepared By:</strong><br>
            <?= !empty($info->prepared_by_name) ? html_escape($info->prepared_by_name) : 'N/A'; ?><br>
            <small><?= !empty($info->prepared_by_position) ? html_escape($info->prepared_by_position) : ''; ?></small>
          </p>
        </div>
        <div class="col-md-4">
          <p><strong>Checked By:</strong><br>
            <?= !empty($info->checked_by_name) ? html_escape($info->checked_by_name) : 'N/A'; ?><br>
            <small><?= !empty($info->checked_by_position) ? html_escape($info->checked_by_position) : ''; ?></small>
          </p>
        </div>
        <div class="col-md-4">
          <p><strong>Additional Signatory:</strong><br>
            <?= !empty($info->additional_name) ? html_escape($info->additional_name) : 'N/A'; ?><br>
            <small><?= !empty($info->additional_position) ? html_escape($info->additional_position) : ''; ?></small>
          </p>
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
                    <img src="data:<?= $logoMime; ?>;base64,<?= $logoData; ?>" class="img-thumbnail" style="max-height: 90px;" alt="Current company logo">
                  </div>
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
                    <img src="data:<?= $letterHeadMime; ?>;base64,<?= $letterHeadData; ?>" class="img-thumbnail" style="max-height: 90px;" alt="Current letter head">
                  </div>
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
