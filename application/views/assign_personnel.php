<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/select2/select2.min.css">

<style>
.btn {
  padding: 3px 10px !important;
  font-size: 13px;
  border-radius: 4px;
  margin-right: 6px;
  transition: all 0.25s ease-in-out;
  line-height: 1.4;
  box-shadow: none;
}
td .btn:last-child, form .btn:last-child { margin-right: 0; }
.btn:hover { transform: scale(1.05); opacity: 0.95; }
.btn-success:hover { box-shadow: 0 0 5px rgba(40,167,69,.4); }
.btn-primary:hover { box-shadow: 0 0 5px rgba(0,123,255,.4); }
.btn-danger:hover { box-shadow: 0 0 5px rgba(220,53,69,.4); }
.btn-secondary:hover { box-shadow: 0 0 5px rgba(108,117,125,.4); }

.toast-header-success { background:#28a745 !important;color:#fff;border-radius:4px 4px 0 0; }
.toast-body-success { background:#eaf9ef;color:#155724; }
.toast-header-danger { background:#dc3545 !important;color:#fff;border-radius:4px 4px 0 0; }
.toast-body-danger { background:#f8d7da;color:#721c24; }
.toast-header i { font-size:1.1rem;margin-right:.6rem; }
.toast-header strong { font-weight:600;font-size:.95rem;margin-right:auto; }
.toast .close,.toast .btn-close { color:white;font-size:1rem;opacity:.85;margin-left:.5rem; }
.toast .close:hover,.toast .btn-close:hover { opacity:1; }
.btn i:hover { transform: scale(1.15); }

.gap-2 { gap: .5rem; }
.gap-3 { gap: .75rem; }

.position-toolbar {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}
.position-toolbar .select2-container {
  min-width: 260px;
  width: 260px !important; 
}
.position-toolbar .btn {
  margin-right: 0; 
}

@media print {
  .no-print,
  .dataTables_filter,
  .dataTables_length,
  .dataTables_info,
  .dataTables_paginate,
  .select2,
  .select2-container { display:none !important; }

  @page { size: A4; margin: 12mm; }
  body { color:#000; }
  table { border-collapse: collapse !important; width:100% !important; font-size: 12px !important; }
  thead th { background:#f2f2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  th, td { border:1px solid #333 !important; padding:6px 8px !important; }
}

@media (max-width: 576px) {
  .position-toolbar .select2-container { min-width: 180px; width: 100% !important; }
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
          <h4 class="page-title"><?= $project->projectTitle; ?></h4>
        </div>
        <hr>

<?php
$success = $this->session->flashdata('success');
$error   = $this->session->flashdata('error');
if ($success || $error):
  $message = $success ?: $error;
  $isDelete = stripos($message, 'deleted') !== false;
  $type  = $error || $isDelete ? 'danger' : 'success';
  $icon  = $type === 'success' ? 'check-circle' : 'trash-alt';
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
      <input type="hidden" name="projectID"  value="<?= $projectID ?>">

      <div class="col-md-10">
        <label for="personnelID" class="form-label fw-bold">Select Personnel</label>
        <select id="personnelID" name="personnelID" class="form-control select2" required>
          <option value="">Select Personnel</option>
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

      <div class="col-md-2 text-end mt-md-0 mt-2">
        <button type="submit" class="btn btn-primary glow-hover" data-toggle="tooltip" title="Assign Personnel">
          <i class="fas fa-plus-circle fa-lg"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<?php
$uniquePositions = [];
foreach ($assignments as $a) {
  if (isset($a->rateType) && ($a->rateType === 'Month' || $a->rateType === 'Bi-Month')) continue;
  $pos = trim($a->position ?? '');
  if ($pos !== '' && !in_array($pos, $uniquePositions, true)) $uniquePositions[] = $pos;
}
natcasesort($uniquePositions);
?>

<h5 class="mt-4">Assigned Personnel List</h5>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 no-print gap-2">
  <div>
    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-print-all">
      <i class="fas fa-print"></i> Print All
    </button>
  </div>

  <div class="position-toolbar gap-2">
    <select id="positionFilter" class="select2" data-placeholder="Select position to print">
      <option value=""></option>
      <?php foreach ($uniquePositions as $pos): ?>
        <option value="<?= htmlspecialchars($pos) ?>"><?= htmlspecialchars($pos) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="button" class="btn btn-light border btn-sm" id="btn-clear-position" title="Clear selection">
      <i class="fas fa-times"></i>
    </button>
<button type="button" class="btn btn-outline-primary btn-sm" id="btn-print-position">
      <i class="fas fa-print"></i>
      <span class="d-none d-sm-inline"> Print by Position</span>
    </button>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th style="width:50px">L/N</th>
          <th>Personnel Name</th>
          <th style="width:220px">Position</th>
          <th class="text-end" style="width:120px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach ($assignments as $a): ?>
          <?php if (isset($a->rateType) && ($a->rateType === 'Month' || $a->rateType === 'Bi-Month')) continue; ?>
          <tr data-position="<?= htmlspecialchars($a->position ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <td class="text-center"><?= $i++ ?></td>
            <td>
              <?= $a->last_name . ', ' . $a->first_name .
                   ($a->middle_name ? ' ' . substr($a->middle_name, 0, 1) . '.' : '') .
                   ($a->name_ext ? ' ' . $a->name_ext : '') ?>
            </td>
            <td class="position-cell"><?= htmlspecialchars($a->position ?? '') ?></td>
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

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
<link href="<?= base_url(); ?>assets/libs/select2/select2.min.css" rel="stylesheet" />
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

<script>
/* Make project meta available to JS (safe-escaped) */
const PROJECT_TITLE    = <?= json_encode($project->projectTitle ?? '') ?>;
const PROJECT_LOCATION = <?= json_encode($project->projectLocation ?? '') ?>;

$(function () {
  $('#datatable').DataTable({
    responsive: true,
    ordering: false,
    autoWidth: false
  });

  $('.select2').select2({ width: '100%', placeholder: 'Select personnel' });
  $('.toast').toast({ delay: 4000 }).toast('show');

  // --- Helpers --------------------------------------------------------------

  // Build printable <thead>/<tbody> HTML without the "Actions" column
  function buildPrintableTable(filterPosition) {
    const tbl   = document.getElementById('datatable');

    // find index of the "Actions" header (don’t assume it’s the last)
    const ths = Array.from(tbl.querySelectorAll('thead th'));
    const actionsIdx = ths.findIndex(th =>
      th.textContent.trim().toLowerCase() === 'actions'
    );

    // header HTML
    let headHtml = '<tr>';
    ths.forEach((th, idx) => {
      if (idx !== actionsIdx) headHtml += '<th>' + th.textContent.trim() + '</th>';
    });
    headHtml += '</tr>';

    // rows (derive position from data-attribute or the visible cell)
    const rows = Array.from(tbl.querySelectorAll('tbody tr'));
    const bodyHtml = rows
      .filter(tr => {
        let pos = (tr.getAttribute('data-position') || '').trim();
        if (!pos) {
          const cell = tr.querySelector('.position-cell');
          pos = cell ? cell.textContent.trim() : '';
          tr.setAttribute('data-position', pos); // cache
        }
        return !filterPosition || pos.toLowerCase() === filterPosition.toLowerCase();
      })
      .map(tr => {
        // rebuild row, skip Actions column
        const tds = Array.from(tr.children)
          .map((td, idx) => (idx === actionsIdx ? '' : '<td>' + td.innerHTML + '</td>'))
          .join('');
        return '<tr>' + tds + '</tr>';
      })
      .join('');

    return { headHtml, bodyHtml };
  }

  // Print via hidden iframe, including Project Title & Location at the top
  function printWithIframe(title, headHtml, bodyHtml) {
    const css = `
      @media print { @page { size: A4; margin: 12mm; } }
      body { font-family: Arial, Helvetica, sans-serif; }
      h3 { margin: 0 0 6px 0; }
      .meta { margin: 4px 0 8px 0; font-size: 12px; }
      .meta strong { display:inline-block; min-width:72px; }
      small { color: #444; display:block; margin-top: 4px; }
      table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
      th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
      thead th { background: #f2f2f2; }
    `;

    const projectTitle    = PROJECT_TITLE || '';
    const projectLocation = PROJECT_LOCATION || '';

    const html = `
      <!doctype html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>${title}</title>
          <style>${css}</style>
        </head>
        <body>
          <h3>${title}</h3>
          <div class="meta">
            <div><strong>Project:</strong> ${projectTitle}</div>
            <div><strong>Location:</strong> ${projectLocation}</div>
          </div>
          <small>Generated: ${new Date().toLocaleString()}</small>
          <table>
            <thead>${headHtml}</thead>
            <tbody>${bodyHtml}</tbody>
          </table>
        </body>
      </html>`;

    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = 0;
    iframe.style.bottom = 0;
    iframe.style.width = 0;
    iframe.style.height = 0;
    iframe.style.border = 0;
    document.body.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();

    iframe.onload = function () {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
      setTimeout(() => document.body.removeChild(iframe), 500);
    };
  }

  // --- Buttons --------------------------------------------------------------

  $('#btn-print-all').on('click', function () {
    const { headHtml, bodyHtml } = buildPrintableTable(null);
    printWithIframe('Assigned Personnel List', headHtml, bodyHtml);
  });

  $('#btn-print-position').on('click', function () {
    const pos = ($('#positionFilter').val() || '').trim();
    if (!pos) { alert('Please select a position to print.'); return; }
    const { headHtml, bodyHtml } = buildPrintableTable(pos);
    if (!bodyHtml.trim()) { alert('No rows found for: ' + pos); return; }
    printWithIframe('Assigned Personnel List — ' + pos, headHtml, bodyHtml);
  });
});
</script>

</body>
</html>
