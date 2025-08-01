<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<style>
/* --- Button Styles --- */
.btn {
  padding: 3px 10px !important;
  font-size: 13px;
  border-radius: 4px;
  margin-right: 6px;
  transition: all 0.25s ease-in-out;
  line-height: 1.4;
  box-shadow: none;
}

td .btn:last-child,
form .btn:last-child {
  margin-right: 0;
}

.btn:hover {
  transform: scale(1.05);
  opacity: 0.95;
}

/* Button Glow Effects */
.btn-success:hover {
  box-shadow: 0 0 5px rgba(40, 167, 69, 0.4);
}
.btn-primary:hover {
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.4);
}
.btn-danger:hover {
  box-shadow: 0 0 5px rgba(220, 53, 69, 0.4);
}
.btn-secondary:hover {
  box-shadow: 0 0 5px rgba(108, 117, 125, 0.4);
}

 /* Toast Appearance */
        .toast-header-success {
            background-color: #28a745 !important;
            color: #fff;
            border-radius: 4px 4px 0 0;
        }

        .toast-body-success {
            background-color: #eaf9ef;
            color: #155724;
        }

        .toast-header-danger {
            background-color: #dc3545 !important;
            color: #fff;
            border-radius: 4px 4px 0 0;
        }

        .toast-body-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .toast-header i {
            font-size: 1.1rem;
            margin-right: 0.6rem;
        }

        .toast-header strong {
            font-weight: 600;
            font-size: 0.95rem;
            margin-right: auto;
        }

        .toast .close,
        .toast .btn-close {
            color: white;
            font-size: 1rem;
            opacity: 0.85;
            margin-left: 0.5rem;
        }

        .toast .close:hover,
        .toast .btn-close:hover {
            opacity: 1;
        }
.btn i:hover {
    transform: scale(1.15);
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
                    <h4 class="page-title"> <?php echo $project->projectTitle; ?> <br>
                    </div>
                    <hr>

<?php
$success = $this->session->flashdata('success');
$error = $this->session->flashdata('error');

if ($success || $error):
    $message = $success ?: $error;
    $isDelete = stripos($message, 'deleted') !== false;
    $type = $error || $isDelete ? 'danger' : 'success';
    $icon = $type === 'success' ? 'check-circle' : 'trash-alt';
    $title = $type === 'success' ? 'Success' : ($isDelete ? 'Deleted' : 'Error');
?>
<div aria-live="polite" aria-atomic="true" style="position: fixed; top: 75px; left: 50%; transform: translateX(-50%); z-index: 1055;">
    <div class="toast show shadow" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 320px;">
        <div class="toast-header toast-header-<?= $type ?>">
            <i class="fas fa-<?= $icon ?> me-2"></i>
            <strong class="me-auto"><?= $title ?></strong>
            <button type="button" class="close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body toast-body-<?= $type ?>">
            <?= $message ?>
        </div>
    </div>
</div>
<?php endif; ?>



             <div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-user-plus text-primary me-2"></i> Assign Personnel to Project
        </h6>
    </div>
    <div class="card-body">
        <form method="post" action="<?= base_url('project/save_assignment') ?>" class="row align-items-end gx-2">
            <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
            <input type="hidden" name="projectID" value="<?= $projectID ?>">

            <!-- Dropdown takes up most of the row -->
            <div class="col-md-10">
                <label for="personnelID" class="form-label fw-bold">Select Personnel</label>
                <select id="personnelID" name="personnelID" class="form-control select2" required>
                    <option value="">-- Select Personnel --</option>
                   <?php foreach ($personnel as $p): ?>
    <?php if ($p->rateType === 'Month' || $p->rateType === 'Bi-Month') continue; ?>
    <option value="<?= $p->personnelID ?>">
        <?= $p->last_name . ', ' . $p->first_name .
            ($p->middle_name ? ' ' . substr($p->middle_name, 0, 1) . '.' : '') .
            ($p->name_ext ? ' ' . $p->name_ext : '') ?>
    </option>
<?php endforeach; ?>

                </select>
            </div>

            <!-- Button floats right on same row -->
            <div class="col-md-2 text-end mt-md-0 mt-2">
              <button type="submit" 
        class="btn btn-primary glow-hover"
        data-toggle="tooltip"
        title="Assign Personnel">
    <i class="fas fa-plus-circle fa-lg"></i>
</button>

            </div>
        </form>
    </div>
</div>



                    <h5 class="mt-4">Assigned Personnel List</h5>
                    <div class="card">
                        <div class="card-body">
                        <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
    <thead class="thead-light">
        <tr>
            <th>Personnel Name</th>
            <th class="text-end" style="width: 120px;">Actions</th>
        </tr>
    </thead>
    <tbody>
       <?php foreach ($assignments as $a): ?>
<?php if (isset($a->rateType) && ($a->rateType === 'Month' || $a->rateType === 'Bi-Month')) continue; ?>

<tr>
    <td><?= $a->last_name . ', ' . $a->first_name .
        ($a->middle_name ? ' ' . substr($a->middle_name, 0, 1) . '.' : '') .
        ($a->name_ext ? ' ' . $a->name_ext : '') ?>
    </td>
    <td class="text-end">
      <a href="<?= base_url('project/delete_assignment/' . $a->ppID . '/' . $settingsID . '/' . $projectID) ?>"
   class="btn btn-danger btn-sm"
   title="Remove Personnel"
   data-toggle="tooltip"
   onclick="return confirm('Remove this assignment?')">
   <i class="fas fa-trash-alt"></i>
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
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
    <link href="<?= base_url(); ?>assets/libs/select2/select2.min.css" rel="stylesheet" />
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

<script>
    $(document).ready(function () {
        $('#datatable').DataTable({
            responsive: true,
            ordering: false,
            autoWidth: false
        });

        $('.select2').select2({
            width: '100%',
            placeholder: 'Select personnel'
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select personnel'
        });
    });
       $(document).ready(function () {
        $('.toast').toast({ delay: 4000 });
        $('.toast').toast('show');
    });
</script>

</body>
</html>
