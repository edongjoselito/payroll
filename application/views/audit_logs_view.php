<!DOCTYPE html>
<html>
<head>
    <title>Audit Attendance Logs</title>
    <?php include('includes/head.php'); ?>
</head>
<body>
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <h4 class="page-title">Audit Trail – Attendance Logs</h4>
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($logs)): ?>
                            <p class="text-center">No audit logs found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                               <td><?= ($log->fName || $log->lName) ? $log->fName . ' ' . $log->lName : 'Unknown User'; ?></td>

                                                <td><?= ucfirst($log->action); ?></td>
                                                <td><?= $log->description; ?></td>
                                                <td><?= date('F d, Y h:i A', strtotime($log->date_time)); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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
