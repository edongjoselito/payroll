<!DOCTYPE html>
<html lang="en">
<title>PMS - Loan Summary</title>

<?php include('includes/head.php'); ?>
<style>
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.emoji-bounce {
  width: 80px;
  height: 80px;
  animation: bounce 2s infinite;
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
        <h4 class="page-title">Loan Summary</h4>
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
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span><strong>All Record of Loans</strong></span>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-toggle="collapse" data-target="#loanTable" aria-expanded="true">
                Hide / View Table
            </button>
        </div>
        <div class="collapse show" id="loanTable">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>Personnel Name</th>
                                <th>Loan Description</th>
                                <th>Monthly Deduction</th>
                                <th>Loan Amount</th>
                                
                            </tr>
                        </thead>
                   <tbody>
<?php 
$total_amount = 0;
$total_monthly = 0;
$hasData = false;

foreach ($summary as $row): 
    if (empty($row->amount) && empty($row->monthly_deduction)) continue;

    $hasData = true;
    $total_amount += $row->amount ?? 0;
    $total_monthly += $row->monthly_deduction ?? 0;
?>
    <tr>
        <td><?= $row->full_name ?></td>
        <td><?= $row->loan_description ?? '-' ?></td>
        <td><?= $row->monthly_deduction ? '₱' . number_format($row->monthly_deduction, 2) : '-' ?></td>
        <td><?= $row->amount ? '₱' . number_format($row->amount, 2) : '-' ?></td>
    </tr>
<?php endforeach; ?>

<?php if (!$hasData): ?>
    <tr>
        <td colspan="4" class="text-center">
            <img src="https://em-content.zobj.net/source/apple/391/thinking-face_1f914.png" alt="Thinking Emoji" class="emoji-bounce"><br>
            <span class="text-muted" style="font-size: 1.2rem;">
                That’s weird, they dont have any Loans ?
            </span>
        </td>
    </tr>
<?php endif; ?>
</tbody>


<?php if ($hasData): ?>
<tfoot>
    <tr class="font-weight-bold bg-white border-top">
        <td colspan="3" class="text-right">Total Amount:</td>
        <td>₱<?= number_format($total_amount, 2) ?></td>
        <td></td>
    </tr>
</tfoot>
<?php endif; ?>

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
