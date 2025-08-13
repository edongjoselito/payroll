<!DOCTYPE html>
<html lang="en">
<head>
    <title>PMS - Years of Service</title>
    <?php include(APPPATH . 'views/includes/head.php'); ?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

    <style>
        th, td { vertical-align: middle !important; font-size: 14px; white-space: nowrap; }
        td { text-align: center; }
        td.text-start { text-align: left !important; }

        .btn { transition: all 0.25s ease-in-out; }
        .btn:hover { transform: scale(1.05); opacity: 0.95; }
        .btn-primary:hover { box-shadow: 0 0 8px rgba(0, 123, 255, 0.4); border-color: rgba(0, 123, 255, 0.4); }

        @media print {
          .no-print,
          .page-title-box,
          .navbar-custom,
          .left-side-menu,
          .footer,
          .dataTables_length,
          .dataTables_filter,
          .dataTables_info,
          .dataTables_paginate { display: none !important; }

          .content-page, .content, .container-fluid, .card, .card-body, .table-responsive {
            padding: 0 !important; margin: 0 !important; box-shadow: none !important; border: 0 !important;
          }

          table { width: 100% !important; border-collapse: collapse !important; font-size: 12px; }
          thead { display: table-header-group; }
          tr, td, th { page-break-inside: avoid !important; }
        }
    </style>
</head>

<body>
<div id="wrapper">
    <?php include(APPPATH . 'views/includes/top-nav-bar.php'); ?>
    <?php include(APPPATH . 'views/includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Years of Service - All Personnel</h4>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('personnel/manage') ?>" class="btn btn-secondary btn-md no-print">Back to List</a>
                        <button type="button" class="btn btn-info btn-md no-print" onclick="printAllPersonnel()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>

                <div class="card mt-2">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3 no-print">
                            <label for="statusFilter" class="mb-0 mr-2"><strong>Status:</strong></label>
                            <select id="statusFilter" class="form-control form-control-sm" style="max-width:180px;">
                                <option value="">All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-hover dt-responsive nowrap" style="width:100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">#</th>

                                        <th class="text-start">Name</th>
                                        <th class="text-center">Designation/Position</th>
                                        <th class="text-center">Date Employed</th>
                                        <th class="text-center">Length of Service</th>
                                        <th style="display:none;">Months</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($personnel as $p): ?>
                                        <?php
                                            $months = -1;
                                            $formatted = '<span style="color: #888;">—</span>';
                                            if (!empty($p->date_employed) && $p->date_employed != '0000-00-00') {
                                                $start = new DateTime($p->date_employed);
                                                $hasEnd = (!empty($p->date_terminated) && $p->date_terminated != '0000-00-00');
                                                $end = $hasEnd ? new DateTime($p->date_terminated) : new DateTime();
                                                $interval = $start->diff($end);
                                                $months = $interval->y * 12 + $interval->m;
                                                $formatted = "{$interval->y} year(s), {$interval->m} month(s)";
                                            }
                                            $status = (!empty($p->date_terminated) && $p->date_terminated != '0000-00-00') ? 'Inactive' : 'Active';
                                        ?>
                                        <tr data-status="<?= $status ?>">
                                            <td></td>
                                            <td class="text-start"><?= "{$p->last_name}, {$p->first_name}" ?></td>
                                            <td><?= htmlspecialchars($p->position ?? '—') ?></td>
                                            <td>
                                                <?php
                                                    if (!empty($p->date_employed) && $p->date_employed != '0000-00-00') {
                                                        echo date('M d, Y', strtotime($p->date_employed));
                                                    } else {
                                                        echo '<span style="color: #888;">—</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td><?= $formatted ?></td>
                                            <td style="display:none;"><?= $months ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div> 
                    </div> 
                </div> 

            </div> 
        </div> 
        <?php include(APPPATH . 'views/includes/footer.php'); ?>
    </div> 
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<script>
var yosTable;

function ensureDataTable() {
  if ($.fn.dataTable.isDataTable('#datatable')) {
    yosTable = $('#datatable').DataTable();
  } else {
    yosTable = $('#datatable').DataTable({
      order: [[1, 'asc']], // 1 = Name column (0 is # column)
      columnDefs: [
        { targets: 0, orderable: false, searchable: false }, // # column
        { targets: [5], visible: false, searchable: false } // hidden Months col
      ],
      responsive: true
    });

    // Auto-generate row numbers
    yosTable.on('order.dt search.dt draw.dt', function () {
      yosTable.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
        cell.innerHTML = i + 1;
      });
    }).draw();
  }
  return yosTable;
}


function attachStatusFilterOnce() {
  if (window._yosStatusFilterAttached) return;
  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (settings.nTable !== document.getElementById('datatable')) return true;
    var selected = $('#statusFilter').val();
    if (!selected) return true;
    var rowNode = yosTable.row(dataIndex).node();
    var rowStatus = rowNode ? rowNode.getAttribute('data-status') : '';
    return rowStatus === selected;
  });
  $('#statusFilter').on('change', function(){ yosTable.draw(); });
  window._yosStatusFilterAttached = true;
}

document.addEventListener("DOMContentLoaded", function () {
  ensureDataTable();
  attachStatusFilterOnce();
});

function printAllPersonnel() {
  ensureDataTable();
  if (!yosTable) return;
  var info = yosTable.page.info();
  var oldStart = info.start;
  var oldLength = yosTable.page.len();
  yosTable.rows().every(function(){ if (this.child && this.child.isShown()) this.child.hide(); });
  yosTable.page.len(-1).draw(false);

  var restore = function() {
    yosTable.page.len(oldLength).draw(false);
    if (oldLength > 0 && oldLength !== -1) {
      var oldPage = Math.floor(oldStart / oldLength);
      yosTable.page(oldPage).draw(false);
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
</body>
</html>
