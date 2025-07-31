<!DOCTYPE html>
<html lang="en">
<title>PMS - Monthly Birthday Celebrants</title>

<?php include('includes/head.php'); ?>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">
<style>
.noti-icon-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 10px;
    padding: 4px 6px;
    border-radius: 50%;
    background-color: #dc3545;
    color: white;
    animation: bounce 1.5s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50%      { transform: translateY(-5px); }
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
        <h4 class="page-title"><?= $title; ?></h4>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span><strong>Personnel with birthdays this month</strong></span>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-toggle="collapse" data-target="#bdayMonthTable" aria-expanded="true">
                Hide / View Table
            </button>
        </div>
        <div class="collapse show" id="bdayMonthTable">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Birthdate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($birthdays)): ?>
                                <?php foreach ($birthdays as $person): ?>
                                    <tr>
                                        <td><?= $person->first_name . ' ' . $person->last_name; ?></td>
                                        <td><?= $person->position; ?></td>
                                        <td><?= date('F d', strtotime($person->birthdate)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <img src="https://em-content.zobj.net/source/apple/391/thinking-face_1f914.png" class="emoji-bounce" width="80" height="80">
                                        <p class="text-muted mt-2" style="font-size: 1.1rem;">No birthday celebrants this month?</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
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
