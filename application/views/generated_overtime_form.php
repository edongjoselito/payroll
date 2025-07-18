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

<div class="mb-3 d-flex justify-content-between align-items-center">
    <h4 class="page-title">Overtime Entry</h4>
    <button class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#overtimeNotesModal">
        <i class="mdi mdi-information-outline"></i> Notes
    </button>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="overtimeNotesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-sm">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Overtime Entry Notes</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-dark" style="font-size: 14px;">
                🕒 Overtime must be entered in <strong>decimal format</strong><br>
                📌 <em>0.25 = 15 min, 0.50 = 30 min, 0.75 = 45 min</em><br>
                ⚠️ Blank fields will be ignored during save.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h5>
            <i class="mdi mdi-briefcase-outline text-primary"></i>
            <strong><?= $project->projectTitle ?></strong>
        </h5>
        <p class="mb-2 text-muted">
            📍 <?= $project->projectLocation ?> <br>
            📅 <?= date('F d, Y', strtotime($start)) ?> to <?= date('F d, Y', strtotime($end)) ?>
        </p>

        <form action="<?= base_url('Overtime/save_overtime') ?>" method="post">
            <input type="hidden" name="projectID" value="<?= $project->projectID ?>">
            <input type="hidden" name="start" value="<?= $start ?>">
            <input type="hidden" name="end" value="<?= $end ?>">

            <div class="table-responsive mt-4">
                <table class="table table-bordered table-hover table-striped nowrap" id="overtimeTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Personnel</th>
                            <?php
                                $period = new DatePeriod(
                                    new DateTime($start),
                                    new DateInterval('P1D'),
                                    (new DateTime($end))->modify('+1 day')
                                );
                                foreach ($period as $date) {
                                    echo '<th>' . $date->format('M d') . '</th>';
                                }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
usort($personnel, function($a, $b) {
    $cmp = strcmp($a->last_name, $b->last_name);
    return $cmp === 0 ? strcmp($a->first_name, $b->first_name) : $cmp;
});
?>

                        <?php foreach ($personnel as $p): ?>
                            <tr>
                                <td><?= $p->last_name . ', ' . $p->first_name ?></td>
                                <?php foreach ($period as $date): ?>
                                    <td>
                                        <input type="number"
                                            name="hours[<?= $p->personnelID ?>][<?= $date->format('Y-m-d') ?>]"
                                            class="form-control input-hours"
                                            step="0.25"
                                            min="0"
                                            max="24"
                                            placeholder="Leave as empty if no OT">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-info shadow-sm px-4">
                    <i class="mdi mdi-content-save"></i> Save Overtime
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DataTables init -->
<script>
    $(document).ready(function () {
        $('#overtimeTable').DataTable({
            paging: false,
            ordering: false,
            info: false,
            searching: false,
            scrollX: true
        });
    });
</script>
