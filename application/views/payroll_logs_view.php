<!DOCTYPE html>
<html lang="en">
<head>
  <title>PMS - Payroll Logs</title>
  <?php include('includes/head.php'); ?>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">

  <style>
    .page-title-box {
      margin-bottom: 20px;
      padding: 15px 20px;
      background-color: #f8f9fa;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-title-box h4 {
      margin: 0;
      font-weight: bold;
    }

    .btn-sm i {
      margin-right: 4px;
    }

    .alert {
      margin-top: 10px;
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

        <!-- Page Title -->
        <div class="page-title-box d-flex justify-content-between align-items-center">
          <h4 class="page-title mb-0">
            <i class="fa fa-calendar-check-o mr-2"></i> Payroll Logs
          </h4>
        </div>

        <!-- Flash Messages -->
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

        <!-- Logs Table -->
        <div class="card shadow">
          <div class="card-body">
            <div class="table-responsive">
              <table id="datatable" class="table table-bordered table-hover table-striped dt-responsive nowrap" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th>Project Title</th>
                    <th>Location</th>
                    <th>Period</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Payroll Date</th>
                    <th>Total Gross</th>
                    <th>Date Saved</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($logs as $log): ?>
                    <tr>
                      <td><?= $log->project_title ?></td>
                      <td><?= $log->location ?></td>
                      <td><?= $log->period ?></td>
                      <td><?= $log->date_from ?></td>
                      <td><?= $log->date_to ?></td>
                      <td><?= $log->payroll_date ?></td>
                      <td>₱<?= number_format($log->total_gross, 2) ?></td>
                      <td><?= $log->date_saved ?></td>
                      <td class="text-center">
                        <a href="<?= base_url('project/payroll_summary/' . $this->session->userdata('settingsID') . '/' . $log->projectID) ?>?start=<?= $log->date_from ?>&end=<?= $log->date_to ?>" 
                           class="btn btn-primary btn-sm" target="_blank">
                          <i class="fa fa-eye"></i> View
                        </a>
                        <a href="<?= base_url('report/delete_log/' . $log->id) ?>" 
                           onclick="return confirm('Are you sure you want to delete this log?')"
                           class="btn btn-danger btn-sm">
                          <i class="fa fa-trash"></i> Delete
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
    <?php include('includes/footer.php'); ?>
  </div>
</div>

<!-- JS Dependencies -->
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

<script>
  $(document).ready(function () {
    $('#datatable').DataTable();
  });
</script>

</body>
</html>
