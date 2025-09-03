<!DOCTYPE html>
<html lang="en">
    <title>PMS - Personnel List</title>

<?php include('includes/head.php'); ?>
<?php
$CI =& get_instance();
$showAdmins = ($CI->input->get('type') === 'admin');
?>

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
@media print {
  .no-print,
  #statusFilter,
  .page-title-box,
  .navbar-custom,
  .left-side-menu,
  .footer,
  .dataTables_length,
  .dataTables_filter,
  .dataTables_info,
  .dataTables_paginate,
  .dt-buttons { display: none !important; }

  .content-page, .content, .container-fluid, .card, .card-body, .table-responsive {
    padding: 0 !important; margin: 0 !important; box-shadow: none !important; border: 0 !important;
  }

  table { width: 100% !important; border-collapse: collapse !important; font-size: 11px; }
  tr, td, th { page-break-inside: avoid !important; }
  thead { display: table-header-group; } 

  td.no-details, th:last-child { display: none !important; }

  .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
@media print {
  .no-print,
  #statusFilter,
  .page-title-box,
  .navbar-custom,
  .left-side-menu,
  .footer,
  .dataTables_length,
  .dataTables_filter,
  .dataTables_info,
  .dataTables_paginate,
  .dt-buttons { display: none !important; }

  .content-page, .content, .container-fluid, .card, .card-body, .table-responsive {
    padding: 0 !important; margin: 0 !important; box-shadow: none !important; border: 0 !important;
  }

  table { width: 100% !important; border-collapse: collapse !important; font-size: 11px; }
  thead { display: table-header-group; }
  tr, td, th { page-break-inside: avoid !important; }

  #datatable th.print-hide,
  #datatable td.print-hide { display: none !important; }

  #datatable td.dtr-control,
  #datatable th.dtr-control { display: none !important; }
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
 <?php
  $role = $this->session->userdata('position') ?: $this->session->userdata('level');
  $isAdmin = ($role === 'Admin');
  $showAdmins = ($this->input->get('type') === 'admin');
?>
<div class="mb-3">
  <a href="<?= base_url('personnel/create') ?>" class="btn btn-primary btn-md mr-2" title="Add Now" data-bs-toggle="tooltip">
    <i class="fas fa-user-plus me-1"></i> Add New
  </a>

  <a href="<?= base_url('personnel/service_years') ?>" class="btn btn-secondary btn-md" title="View Year of Service" data-bs-toggle="tooltip">
    <i class="fas fa-calendar-alt me-1"></i> Years of Service
  </a>

  <?php if ($isAdmin): ?>
    <a href="<?= base_url('personnel/manage?type=admin') ?>" class="btn btn-warning btn-md mr-2 no-print" title="Show Monthly / Bi-Monthly">
      <i class="fas fa-user-tie me-1"></i> Monthly / Bi-Monthly
    </a>
    <?php if ($showAdmins): ?>
      <a href="<?= base_url('personnel/manage') ?>" class="btn btn-outline-secondary btn-md mr-2 no-print" title="Show Workers Only">
        <i class="fas fa-people-carry me-1"></i> Workers Only
      </a>
    <?php endif; ?>
  <?php endif; ?>

  <button type="button" class="btn btn-info btn-md no-print" onclick="printAllPersonnel()">
    <i class="fas fa-print me-1"></i> Print
  </button>
</div>




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
<h5 class="page-title">
  Personnel List
  <span class="badge badge-info ml-2">
    <?= $showAdmins ? 'Monthly / Bi-Monthly (Admins)' : 'Workers (Non-Monthly)' ?>
  </span>
</h5>
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
$showAdmins = $this->input->get('type') === 'admin';

?>
<div class="d-flex align-items-center mb-3">
  <label for="statusFilter" class="mb-0 mr-2"><strong>Status:</strong></label>
  <select id="statusFilter" class="form-control form-control-sm" style="max-width:180px;">
    <option value="">All</option>
    <option value="Active">Active</option>
    <option value="Inactive">Inactive</option>
  </select>
</div>

  <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
   <thead>
  <tr>
    <th>Name</th>
    <th>Position</th>
    <th>Address</th>
    <th>Contact</th>
    <?php if ($hasSSS): ?><th>SSS</th><?php endif; ?>
    <?php if ($hasPhilHealth): ?><th>PhilHealth</th><?php endif; ?>
    <?php if ($hasPagibig): ?><th>Pag-IBIG</th><?php endif; ?>
    <?php if ($hasTIN): ?><th>TIN</th><?php endif; ?>
    <th>Date Employed</th>
    <?php if ($hasTerminated): ?><th>Date Terminated</th><?php endif; ?>
    <th>Status</th>
    <th>Duration</th>
    <th>Actions</th>
  </tr>
</thead>


                             <tbody>
<?php if (empty($personnel)): ?>
 <?php
$colCount = 10;

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

$rateTypeRaw  = strtolower((string)($p->rateType ?? ''));
$rateTypeNorm = preg_replace('/[^a-z]/', '', $rateTypeRaw); 
$isAdminRate  = in_array($rateTypeNorm, ['month','permonth','bimonth','bimonthly'], true);

if (!$showAdmins && $isAdminRate)  { continue; }
if ($showAdmins  && !$isAdminRate) { continue; }
?>
  <tr data-status="<?= $status ?>" data-ratetype="<?= htmlspecialchars($rateTypeNorm, ENT_QUOTES, 'UTF-8') ?>">


  <td><?= "{$p->last_name}, {$p->first_name} {$p->middle_name} {$p->name_ext}" ?></td>
  <td><?= htmlspecialchars($p->position ?? '—') ?></td>
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

  <!-- Make actions cell NEVER act as responsive toggle -->
  <td class="no-details" style="white-space:nowrap">
    <a href="<?= base_url('personnel/edit/'.$p->personnelID) ?>"
       class="btn btn-outline-info btn-sm me-1" title="Edit" data-bs-toggle="tooltip">
       <i class="fas fa-edit"></i>
    </a>
    <a href="<?= base_url('personnel/delete/'.$p->personnelID) ?>"
       class="btn btn-outline-danger btn-sm" title="Delete" data-bs-toggle="tooltip"
       onclick="return confirm('Delete this record?')">
       <i class="fas fa-trash-alt"></i>
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

<script>
var table;

function ensureDataTable() {
  if ($.fn.dataTable.isDataTable('#datatable')) {
    table = $('#datatable').DataTable();
  } else {
    table = $('#datatable').DataTable({
      responsive: { details: { type: 'inline', target: 'td:not(.no-details)' } },
      columnDefs: [{ orderable: false, searchable: false, targets: 'no-details' }]
    });
  }
  return table;
}

function attachStatusFilterOnce() {
  if (window._statusFilterAttached) return;
  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    var selected = $('#statusFilter').val();
    if (!selected) return true;
    var rowNode = table.row(dataIndex).node();
    var rowStatus = rowNode ? rowNode.getAttribute('data-status') : '';
    return rowStatus === selected;
  });
  $('#statusFilter').on('change', function() { table.draw(); });
  window._statusFilterAttached = true;
}

function initTooltips() {
  [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    .map(function (el) { return new bootstrap.Tooltip(el); });
}

document.addEventListener("DOMContentLoaded", function () {
  initTooltips();
  ensureDataTable();
  attachStatusFilterOnce();
});

function printAllPersonnel() {
  ensureDataTable();
  if (!table) return;
  var info = table.page.info();
  var oldStart = info.start;
  var oldLength = table.page.len();
  table.rows().every(function(){ if (this.child && this.child.isShown()) this.child.hide(); });
  table.page.len(-1).draw(false);
  var restore = function() {
    table.page.len(oldLength).draw(false);
    if (oldLength > 0 && oldLength !== -1) {
      var oldPage = Math.floor(oldStart / oldLength);
      table.page(oldPage).draw(false);
    }
    window.removeEventListener('afterprint', restore);
  };
  if ('onafterprint' in window) {
    window.addEventListener('afterprint', restore, { once: true });
  } else if (window.matchMedia) {
    var mql = window.matchMedia('print');
    var listener = function(q){ if (!q.matches) { restore(); mql.removeListener(listener); } };
    mql.addListener(listener);
  }
  setTimeout(function(){ window.print(); }, 150);
}
</script>
<script>
(function(){
  var keep = new Set(['Name','Position','Address','Contact','Date Employed','SSS','PhilHealth','Pag-IBIG']);

  function tagPrintColumns() {
    var $table = $('#datatable');
    var $ths = $table.find('thead th');
    $ths.each(function(i){
      var label = $(this).text().trim();
      var keepThis = keep.has(label);
      if (!keepThis) {
        $(this).addClass('print-hide');
        $table.find('tbody tr').each(function(){
          var $tds = $(this).children('td');
          if ($tds.eq(i).length) $tds.eq(i).addClass('print-hide');
        });
      }
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    var readyCheck = setInterval(function(){
      if ($.fn.dataTable.isDataTable('#datatable')) {
        clearInterval(readyCheck);
        tagPrintColumns();
        var dt = $('#datatable').DataTable();
        dt.on('draw responsive-display responsive-resize', function(){
          $('#datatable thead th, #datatable tbody td').removeClass('print-hide');
          tagPrintColumns();
        });
      }
    }, 50);
  });
})();
</script>


</body>
</html>
