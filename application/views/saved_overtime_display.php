<?php if (!empty($records)): ?>
<?php
$grouped = [];
$dates = [];

foreach ($records as $r) {
    $pid = $r->personnelID;
    $date = $r->date;
    $name = $r->last_name . ' ' . $r->first_name;

    $grouped[$pid]['name'] = $name;
    $grouped[$pid]['last_name'] = $r->last_name;
    $grouped[$pid]['first_name'] = $r->first_name;
    $grouped[$pid]['dates'][$date] = $r->hours;
    $grouped[$pid]['ids'][] = $r->id;

    if (!in_array($date, $dates)) $dates[] = $date;
}

sort($dates);

// 🔠 Sort personnel by last_name then first_name
uasort($grouped, function ($a, $b) {
    $cmp = strcmp($a['last_name'], $b['last_name']);
    if ($cmp === 0) {
        return strcmp($a['first_name'], $b['first_name']);
    }
    return $cmp;
});
?>
<?php
$start = isset($start) ? $start : '';
$end = isset($end) ? $end : '';
?>

<form id="viewForm">
<input type="hidden" id="reload_projectID" name="projectID" value="<?= $this->input->post('projectID') ?>">
<input type="hidden" id="reload_start" name="start" value="<?= $start ?>">
<input type="hidden" id="reload_end" name="end" value="<?= $end ?>">




    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <strong><i class="mdi mdi-calendar-clock-outline"></i> Saved Overtime Records</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle m-0 table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-start">Personnel</th>
                            <?php foreach ($dates as $d): ?>
                                <th><?= date('M d', strtotime($d)) ?></th>
                            <?php endforeach; ?>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped as $personnelID => $person): ?>
                            <tr>
                                <td class="text-start"><?= $person['name'] ?></td>
                                <?php
                                    $total = 0;
                                    foreach ($dates as $d):
                                        $val = $person['dates'][$d] ?? null;
                                        $total += floatval($val);
                                ?>
                                    <td><?= $val !== null ? number_format($val, 2) : '—' ?></td>
                                <?php endforeach; ?>
                                <td><strong><?= number_format($total, 2) ?></strong></td>
                               <td>
    <button type="button"
            class="btn btn-danger btn-sm delete-row"
            data-ids="<?= implode(',', $person['ids']) ?>"
            onclick="console.log('Deleting IDs: <?= implode(',', $person['ids']) ?>')">
        <i class="mdi mdi-delete"></i> Delete
    </button>
</td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<?php else: ?>
    <div class="alert alert-warning">No overtime records found for the selected batch.</div>
<?php endif; ?>

<!-- ✅ Toast Success Message -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 9999; top: 20px;">
    <div id="deleteToast" class="toast bg-success text-white shadow" role="alert" data-delay="3000" style="min-width: 280px;">
        <div class="toast-body text-center">
            ✅ Overtime entries deleted.
        </div>
    </div>
</div>
<!-- ✅ Required for DataTables -->
<link rel="stylesheet" href="<?= base_url('assets/datatables/dataTables.bootstrap4.min.css') ?>">
<script src="<?= base_url('assets/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/datatables/dataTables.bootstrap4.min.js') ?>"></script>

<script>
$(document).on('click', '.delete-row', function () {
    const ids = $(this).data('ids');
    if (!confirm("Are you sure you want to delete all overtime entries for this personnel?")) return;

    $('#savedResult').html('<div class="text-center p-4 text-muted">⏳ Deleting...</div>');

    $.post("<?= base_url('Overtime/delete_overtime') ?>", { id: ids }, function (res) {
        try {
            const result = JSON.parse(res);
            if (result.status === 'success') {
                $('#deleteToast').toast('show');

                // Load new table
         setTimeout(() => {
 const projectID = $('#reload_projectID').val();
const start = $('#reload_start').val();
const end = $('#reload_end').val();


    console.log("🔁 Reloading with:", { projectID, start, end });

    if (!projectID || !start || !end) {
        console.error("❌ Missing reload data!");
        $('#savedResult').html('<div class="text-danger text-center p-3">Reload failed: Missing data.</div>');
        return;
    }

  $('#savedResult').load("<?= base_url('Overtime/loadSavedOvertimeView') ?>", {
    projectID: projectID,
    start: start,
    end: end
}, function() {
    // ✅ After content is loaded, reinitialize DataTable
    $('.table').DataTable(); // or $('#yourTableID').DataTable();
});

}, 1000);


            } else {
                alert('❌ Failed to delete.');
            }
        } catch {
            alert('❌ Unexpected response.');
        }
    }).fail(() => alert('❌ Server error.'));
});
</script>

