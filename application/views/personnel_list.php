<!DOCTYPE html>
<html lang="en">
    <title>PMS - Personnel List</title>

<?php include('includes/head.php'); ?>
<style>
.btn {
    transition: all 0.25s ease-in-out;
}

.btn:hover {
    transform: scale(1.05);
    opacity: 0.95;
}

.btn-primary:hover {
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
    border-color: rgba(0, 123, 255, 0.4);
}

.btn-secondary:hover {
    box-shadow: 0 0 8px rgba(108, 117, 125, 0.5);
    border-color: rgba(108, 117, 125, 0.4);
}

.btn-info:hover {
    box-shadow: 0 0 8px rgba(23, 162, 184, 0.5);
    border-color: rgba(23, 162, 184, 0.4);
}

.btn-danger:hover {
    box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
    border-color: rgba(220, 53, 69, 0.4);
}
</style>

<body>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">   
            <div class="container-fluid">

                <div class="page-title-box d-flex justify-content-between align-items-center">
                  <a href="<?= base_url('personnel/create') ?>" class="btn btn-primary btn-md">Add New</a>
<a href="<?= base_url('personnel/service_years') ?>" class="btn btn-secondary btn-md mb-3">Years of Service</a>

                </div>
                
<!-- to be modified by aria live -->
                <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>

<!-- to be modified into aria live -->




<?php elseif ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- to be modified into aria live -->
                <div class="card">
                    <div class="card-body">
                      <h5 class="page-title">Personnel List</h5>
<div class="table-responsive">
    <?php
$hasTerminated = false;
foreach ($personnel as $p) {
    if (!empty($p->date_terminated)) {
        $hasTerminated = true;
        break;
    }
}
$hasSSS = $hasPhilHealth = $hasPagibig = $hasTIN = false;

foreach ($personnel as $p) {
    if (!empty($p->sss_number)) $hasSSS = true;
    if (!empty($p->philhealth_number)) $hasPhilHealth = true;
    if (!empty($p->pagibig_number)) $hasPagibig = true;
    if (!empty($p->tin_number)) $hasTIN = true;
}

?>

  <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
    <thead>
    <tr>
       <th>Name</th>
<th>Address</th>
<th>Contact</th>
<?php if ($hasSSS): ?><th>SSS</th><?php endif; ?>
<?php if ($hasPhilHealth): ?><th>PhilHealth</th><?php endif; ?>
<?php if ($hasPagibig): ?><th>Pag-IBIG</th><?php endif; ?>
<?php if ($hasTIN): ?><th>TIN</th><?php endif; ?>

<th>Date Employed</th>
<?php if ($hasTerminated): ?>
<th>Date Terminated</th>
<?php endif; ?>
<th>Status</th>
<th>Duration</th>

        <th>Actions</th>
    </tr>
</thead>

                             <tbody>
<?php if (empty($personnel)): ?>
 <?php
$colCount = 9; // base columns: name, address, contact, date employed, status, duration, actions

if ($hasSSS) $colCount++;
if ($hasPhilHealth) $colCount++;
if ($hasPagibig) $colCount++;
if ($hasTIN) $colCount++;
if ($hasTerminated) $colCount++;
?>

<tr><td colspan="<?= $colCount ?>" class="text-center">No personnel records found.</td></tr>


<?php else: ?>
  <?php foreach ($personnel as $p): ?>
    <?php
        $hasStart = !empty($p->date_employed) && $p->date_employed !== '0000-00-00';
        $hasEnd = !empty($p->date_terminated) && $p->date_terminated !== '0000-00-00';

        if ($hasStart) {
            $start = new DateTime($p->date_employed);
            $end = $hasEnd ? new DateTime($p->date_terminated) : new DateTime();
            $interval = $start->diff($end);
            $duration = $interval->y . ' yr, ' . $interval->m . ' month/s';
        } else {
            $duration = '—';
        }

        $status = $hasEnd ? 'Inactive' : 'Active';
    ?>
    <tr>
        <td><?= "{$p->last_name}, {$p->first_name} {$p->middle_name} {$p->name_ext}" ?></td>
        <td><?= $p->address ?></td>
        <td><?= $p->contact_number ?></td>
     <?php if ($hasSSS): ?><td><?= $p->sss_number ?></td><?php endif; ?>
<?php if ($hasPhilHealth): ?><td><?= $p->philhealth_number ?></td><?php endif; ?>
<?php if ($hasPagibig): ?><td><?= $p->pagibig_number ?></td><?php endif; ?>
<?php if ($hasTIN): ?><td><?= $p->tin_number ?></td><?php endif; ?>

<td><?= $hasStart ? date('M d, Y', strtotime($p->date_employed)) : '—'; ?></td>
<?php if ($hasTerminated): ?>
<td><?= $hasEnd ? date('M d, Y', strtotime($p->date_terminated)) : '—'; ?></td>
<?php endif; ?>
<td>
    <span class="badge badge-<?= $status === 'Active' ? 'success' : 'danger' ?>">
        <?= $status ?>
    </span>
</td>
<td><?= $duration ?></td>

        <td>
            <a href="<?= base_url('personnel/edit/'.$p->personnelID) ?>" class="btn btn-info btn-sm">Edit</a>
            <a href="<?= base_url('personnel/delete/'.$p->personnelID) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
        </td>
    </tr>
<?php endforeach; ?>

<?php endif; ?>
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
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>
