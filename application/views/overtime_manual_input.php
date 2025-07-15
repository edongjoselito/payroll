<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manual Overtime Input</title>
  <?php include('includes/head.php'); ?>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/select2/select2.min.css">
  <style>
    th, td {
      text-align: center;
      vertical-align: middle !important;
      font-size: 14px;
    }
    th.sticky {
      position: sticky;
      top: 0;
      background: #f8f9fa;
      z-index: 2;
    }
    td:first-child,
    th:first-child {
      text-align: left;
      background: #f8f9fa;
      position: sticky;
      left: 0;
      z-index: 1;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    }
    .input-hours {
      width: 90px;
      min-width: 80px;
      height: 28px;
      font-size: 13px;
      text-align: center;
      padding: 2px 4px;
    }
    .overtime-row:hover {
      background-color: #f9f9f9;
    }
  </style>
</head>

<body>
  <div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">
          <div class="mb-3">
            <h4 class="page-title">Manual Overtime Input</h4>
            <p class="text-muted mb-2">
              <strong>Project ID:</strong> <?= $projectID ?> |
              <strong>From:</strong> <?= date('F j, Y', strtotime($from)) ?> |
              <strong>To:</strong> <?= date('F j, Y', strtotime($to)) ?>
            </p>
          </div>

          <form method="post" action="<?= base_url('Overtime/save_overtime') ?>">
            <input type="hidden" name="projectID" value="<?= $projectID ?>">
            <input type="hidden" name="from" value="<?= $from ?>">
            <input type="hidden" name="to" value="<?= $to ?>">

            <div class="card">
              <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                  <thead class="thead-light">
                    <tr>
                      <th class="sticky">Personnel</th>
                      <?php foreach ($dates as $date): ?>
                        <th class="sticky"><?= date('M d', strtotime($date)) ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($personnel as $person): ?>
                      <tr class="overtime-row">
                        <td><?= $person->last_name . ', ' . $person->first_name ?></td>
                        <?php foreach ($dates as $date): ?>
                          <td>
                            <input type="number" step="0.25" min="0" max="24"
                                   class="form-control input-hours"
                                   name="overtime[<?= $person->personnelID ?>][<?= $date ?>]"
                                   placeholder="hrs">
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-success shadow-sm">
                <i class="mdi mdi-content-save"></i> Save Overtime
              </button>
              <a href="<?= base_url('WeeklyAttendance') ?>" class="btn btn-secondary ml-2">Cancel</a>
            </div>
          </form>

        </div>
      </div>
      <?php include('includes/footer.php'); ?>
    </div>
  </div>

  <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
  <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('.select2').select2({ width: 'resolve' });
    });
  </script>
</body>
</html>
