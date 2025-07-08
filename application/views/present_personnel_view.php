<!DOCTYPE html>
<html lang="en">
<title>Present Personnel</title>
<?php include('includes/head.php'); ?>

<body>
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Present Personnel</h4>
                    <form method="get" class="form-inline">
                        <input type="date" name="date" value="<?= $date ?>" class="form-control mr-2">
                        <button class="btn btn-primary">Load</button>
                    </form>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <?php if (!empty($present_personnel)): ?>
                            <h5>Present Personnel for <?= date('F d, Y', strtotime($date)) ?></h5>
                            <table class="table table-bordered table-sm table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Work Duration (hrs)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($present_personnel as $p): ?>
                                        <tr>
                                            <td><?= "{$p->first_name} {$p->middle_name} {$p->last_name} {$p->name_ext}" ?></td>
                                            <td><?= $p->workDuration ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No present personnel recorded for this date.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <?php include('includes/footer.php'); ?>
    </div>
</div>
</body>
</html>
