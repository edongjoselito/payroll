<?php
function getDateRange($start, $end) {
    $dates = [];
    $current = strtotime($start);
    $end = strtotime($end);
    while ($current <= $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $dates;
}

$dates = getDateRange($start, $end);
?>

<form action="<?= base_url('Overtime/save_overtime') ?>" method="post">
    <input type="hidden" name="projectID" value="<?= $project->projectID ?>">
    <input type="hidden" name="start" value="<?= $start ?>">
    <input type="hidden" name="end" value="<?= $end ?>">

    <div class="mb-3">
        <h5>
            <i class="mdi mdi-calendar"></i>
            Date Range:
            <strong><?= date('F d, Y', strtotime($start)) ?> to <?= date('F d, Y', strtotime($end)) ?></strong>
        </h5>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Personnel</th>
                    <?php foreach ($dates as $d): ?>
                        <th><?= date('M d', strtotime($d)) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($personnel as $p): ?>
                    <tr>
                        <td><?= $p->last_name . ', ' . $p->first_name ?></td>
                        <?php foreach ($dates as $d): ?>
                            <td>
                                <input type="number"
                                       name="hours[<?= $p->personnelID ?>][<?= $d ?>]"
                                       class="form-control input-hours"
                                       min="0" step="0.25" placeholder="Hrs">
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-right mt-3">
        <button type="submit" class="btn btn-info shadow-sm">
            <i class="mdi mdi-content-save"></i> Save Overtime
        </button>
    </div>
</form>
