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
                        <h4 class="page-title"> <?php echo $project[0]->projectTitle; ?> <br>
                        <small><i>Assign Personnel to Project</i></small></h4>
                        <h5></h5>
                  
                    </div>
                    <hr>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="<?= base_url('project/save_assignment') ?>">
                                <input type="hidden" name="settingsID" value="<?= $settingsID ?>">
                                <input type="hidden" name="projectID" value="<?= $projectID ?>">

                                <div class="form-group">
                                    <label>Select Personnel</label>
                                    <select name="personnelID" class="form-control select2" required>
                                        <option value="">-- Select Personnel --</option>
                                        <?php foreach ($personnel as $p): ?>
                                            <option value="<?= $p->personnelID ?>">
                                                <?= $p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Assign Personnel</button>
                            </form>
                        </div>
                    </div>

                    <h5 class="mt-4">Assigned Personnel List</h5>
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Personnel Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($assignments)) : ?>
                                        <tr><td colspan="2" class="text-center">No assignments yet.</td></tr>
                                    <?php else: ?>
                                       <?php foreach ($assignments as $a): ?>
                                            <tr>
                                                <td><?= $a->first_name . ' ' . $a->middle_name . ' ' . $a->last_name ?></td>
                                                <td>
                                                    <a href="<?= base_url('project/delete_assignment/' . $a->ppID . '/' . $settingsID . '/' . $projectID) ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Remove this assignment?')">
                                                    Remove
                                                    </a>
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
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
    <link href="<?= base_url(); ?>assets/libs/select2/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select personnel'
        });
    });
</script>

</body>
</html>
