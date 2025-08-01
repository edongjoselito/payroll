<!DOCTYPE html>
<html lang="en">
<title>PMS - 13th Month Pay</title>

<?php include('includes/head.php'); ?>
<style>
thead th {
    background-color: #f8f9fa;
    font-weight: bold;
    vertical-align: middle;
}
.table td, .table th {
    vertical-align: middle;
}
.btn + .btn {
    margin-left: 8px;
}
/* Glow and Enlarge on Hover */
.glow-hover {
    transition: all 0.3s ease-in-out;
}

.glow-hover:hover {
    transform: scale(1.1);
    box-shadow: 0 0 12px rgba(0, 123, 255, 0.6);
    z-index: 10;
}

@media print {
    .d-print-none {
        display: none !important;
    }

    .dataTables_filter,
    .dataTables_length,
    .dataTables_info,
    .dataTables_paginate {
        display: none !important;
    }

    @page {
        size: A4 landscape;
        margin: 20mm;
    }

    .table th, .table td {
        border: 1px solid #000 !important;
        padding: 4px !important;
    }

    body {
        font-size: 11pt;
        color: #000;
    }
}

</style>
<body>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
<div class="content">
<div class="container-fluid">

    <div class="page-title-box d-flex justify-content-between align-items-center">
        <h4 class="page-title">13th Month Pay Report</h4>
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
<div class="d-flex flex-wrap align-items-center gap-2 mb-3 d-print-none">
   <button class="btn btn-outline-secondary btn-sm glow-hover me-2" onclick="window.print();">

        <i class="fas fa-print"></i> Print Report
    </button>
<button class="btn btn-outline-secondary btn-sm glow-hover me-2" data-toggle="modal" data-target="#filterModal">
        <i class="fas fa-search"></i> Select Period
    </button>
    <button class="btn btn-outline-secondary btn-sm glow-hover" type="button" data-toggle="collapse" data-target="#reportTable" aria-expanded="true">
        Hide / View Table
    </button>
</div>



    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span><strong>13th Month Summary</strong></span>
            <?php if (!empty($selected_period)): ?>
    <div class="alert alert-info">
        Showing 13th month data for: 
        <strong>
            <?= $selected_period == 'jan-jun' ? 'January – June' : ($selected_period == 'jul-dec' ? 'July – December' : 'Full Year') ?>
        </strong>
    </div>
<?php endif; ?>

           
        </div>
        <div class="collapse show" id="reportTable">
            <div class="card-body">
                <div class="table-responsive">
                  <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
    <thead class="thead-light text-center">
        <tr>
            <th>No.</th>
            <th>Name of Worker</th>
            <th>Designation</th>
            <th>Total Basic Salary for Year 2024</th>
            <th>13th Month Pay<br><small>(Divided by 12)</small></th>
            <th>Net Pay</th>
            <th>Received By</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; foreach ($payroll_data as $emp): 
            $rate = $emp['rateAmount'];
            $rateType = $emp['rateType'];
            $regHours = $emp['total_regular_hours'];
            switch (strtolower($rateType)) {
                case 'hour': $hourly = $rate; break;
                case 'day': $hourly = $rate / 8; break;
                case 'month': $hourly = ($rate / 30) / 8; break;
                case 'bi-month': $hourly = ($rate / 15) / 8; break;
                default: $hourly = 0; break;
            }
            $basic = $hourly * $regHours;
            $thirteenth = $basic / 12;
            $netpay = $thirteenth; // You may change this logic if needed
        ?>
        <tr class="text-center">
            <td><?= $i++ ?></td>
            <td class="text-left"><?= $emp['name'] ?></td>
            <td><?= $emp['position'] ?></td>
            <td>₱<?= number_format($basic, 2) ?></td>
            <td><strong>₱<?= number_format($thirteenth, 2) ?></strong></td>
            <td>₱<?= number_format($netpay, 2) ?></td>
            <td>____________________</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

                </div>
            </div>
        </div>
    </div>

</div>
</div>
<?php include('includes/footer.php'); ?>
</div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="<?= base_url('Thirteenth') ?>" method="get">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter 13th Month Pay</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="period">Select Period</label>
                    <select class="form-control" name="period" required>
                        <option value="">Choose Period</option>
                        <option value="jan-jun">January – June</option>
                        <option value="jul-dec">July – December</option>
                        <option value="full">Full Year</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-sm">Apply Filter</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- JS -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
