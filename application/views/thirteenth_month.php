<!DOCTYPE html>
<html lang="en">
<title>PMS - 13th Month Pay</title>

<?php include('includes/head.php'); ?>
<style>
thead th {
    background-color: #f8f9fa;
    font-weight: bold;
    vertical-align: middle;
}
.table td, .table th {
    vertical-align: middle;
}
.btn + .btn {
    margin-left: 8px;
}
.glow-hover {
    transition: all 0.3s ease-in-out;
}
.glow-hover:hover {
    transform: scale(1.1);
    box-shadow: 0 0 12px rgba(0, 123, 255, 0.6);
    z-index: 10;
}
tfoot th, tfoot td { font-weight: 700; background: #f8f9fa; }

/* Optional visual helper when printing via iframe payload */
.page-break { page-break-after: always; }

@media print {
    .d-print-none { display: none !important; }
    tfoot { display: table-footer-group; }
    .dataTables_filter,
    .dataTables_length,
    .dataTables_info,
    .dataTables_paginate {
        display: none !important;
    }
    @page { size: A4 landscape; margin: 20mm; }
    .table th, .table td {
        border: 1px solid #000 !important;
        padding: 4px !important;
    }
    body { font-size: 11pt; color: #000; }
}
</style>
<body>
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.css">

<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
<div class="content">
<div class="container-fluid">

    <div class="page-title-box d-flex justify-content-between align-items-center">
        <h4 class="page-title">13th Month Pay Report</h4>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php elseif ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3 d-print-none">
    <!-- UPDATED: give the button an id and let JS handle printing ALL rows -->
    <button id="btn-print-all" class="btn btn-outline-secondary btn-sm glow-hover me-2" type="button">
        <i class="fas fa-print"></i> Print Report
    </button>

    <button class="btn btn-outline-secondary btn-sm glow-hover me-2" data-toggle="modal" data-target="#filterModal">
        <i class="fas fa-search"></i> Select Period
    </button>
    <button class="btn btn-outline-secondary btn-sm glow-hover" type="button" data-toggle="collapse" data-target="#reportTable" aria-expanded="true">
        Hide / View Table
    </button>
</div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span><strong>13th Month Summary</strong></span>
            <?php if (!empty($selected_period)): ?>
            <div class="alert alert-info mb-0 py-1 px-2">
                Showing 13th month data for:
                <strong>
                    <?= $selected_period == 'jan-jun' ? 'January – June' : ($selected_period == 'jul-dec' ? 'July – December' : 'Full Year') ?>
                </strong>
                &nbsp; | &nbsp; Year: <strong><?= htmlspecialchars($year) ?></strong>
                &nbsp; | &nbsp; Employment:
                <strong>
                    <?php
                        $emp = $employment ?? 'active';
                        echo $emp === 'inactive' ? 'Inactive' : ($emp === 'all' ? 'All' : 'Active');
                    ?>
                </strong>
            </div>
            <?php endif; ?>
        </div>

        <div class="collapse show" id="reportTable">
            <div class="card-body">
                <div class="table-responsive">
                  <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                      <thead class="thead-light text-center">
                        <tr>
                          <th>No.</th>
                          <th>Name of Employees</th>
                          <th>Designation</th>
                          <th>Total Basic Salary for Year <?= htmlspecialchars($year) ?></th>
                          <th>13th Month Pay<br><small>(Divided by 12)</small></th>
                          <th>Net Pay</th>
                          <th>Received By</th>
                        </tr>
                      </thead>

                      <tbody>
                        <?php
                          $i = 1;
                          $total_basic = 0.0;
                          $total_13th  = 0.0;
                          $total_net   = 0.0;

                          foreach ($payroll_data as $emp):
                            $basic      = (float)($emp['basic_total'] ?? 0.0);
                            $thirteenth = $basic / 12;
                            $netpay     = $thirteenth;

                            $total_basic += $basic;
                            $total_13th  += $thirteenth;
                            $total_net   += $netpay;
                        ?>
                        <tr class="text-center">
                          <td><?= $i++ ?></td>
                          <td class="text-left"><?= htmlspecialchars(($emp['last_name'] ?? '').', '.($emp['first_name'] ?? '')) ?></td>
                          <td><?= htmlspecialchars($emp['position'] ?? '') ?></td>
                          <td>₱<?= number_format($basic, 2) ?></td>
                          <td><strong>₱<?= number_format($thirteenth, 2) ?></strong></td>
                          <td>₱<?= number_format($netpay, 2) ?></td>
                          <td>____________________</td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>

                      <tfoot>
                        <tr class="text-center">
                          <td colspan="3" class="text-right"><strong>Totals:</strong></td>
                          <td><strong>₱<?= number_format($total_basic, 2) ?></strong></td>
                          <td><strong>₱<?= number_format($total_13th, 2) ?></strong></td>
                          <td><strong>₱<?= number_format($total_net, 2) ?></strong></td>
                          <td></td>
                        </tr>
                      </tfoot>
                  </table>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<?php include('includes/footer.php'); ?>
</div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="<?= base_url('Thirteenth') ?>" method="get">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter 13th Month Pay</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="period">Select Period</label>
                    <select class="form-control" name="period" required>
                        <option value="">Choose Period</option>
                        <option value="jan-jun" <?= ($selected_period ?? '')==='jan-jun'?'selected':''; ?>>January – June</option>
                        <option value="jul-dec" <?= ($selected_period ?? '')==='jul-dec'?'selected':''; ?>>July – December</option>
                        <option value="full"    <?= ($selected_period ?? '')==='full'?'selected':''; ?>>Full Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="number"
                           class="form-control"
                           name="year"
                           value="<?= htmlspecialchars($year ?? date('Y')) ?>"
                           min="2000"
                           max="2100" />
                </div>
                <div class="form-group">
                    <label for="employment">Employment Status</label>
                    <select class="form-control" name="employment">
                        <?php $emp = $employment ?? 'active'; ?>
                        <option value="active"   <?= $emp==='active'   ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $emp==='inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="all"      <?= $emp==='all'      ? 'selected' : '' ?>>All</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-sm">Apply Filter</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- JS -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
(function(){
  var dt;
  try {
    dt = $('#datatable').DataTable();
  } catch(e) {
    dt = $('#datatable').DataTable({
      responsive: true,
      ordering: true,
      autoWidth: false
    });
  }

  (function addDynamicNumbering(){
    var noIdx = -1;
    $('#datatable thead th').each(function (i) {
      var txt = $(this).text();
      if (/^(No\.?|#)$/i.test(txt)) noIdx = i;
    });

    if (noIdx >= 0) {
      dt.on('order.dt search.dt draw.dt', function () {
        var i = 1;
        dt.column(noIdx, { search: 'applied', order: 'applied' }).nodes().each(function (cell) {
          cell.textContent = i++;
        });
      });
      dt.draw();
    }
  })();

  function buildPrintableFromDT(dataTable, rowsPerPage) {
    var headers = dataTable.columns().header().toArray().map(function(h){ return $(h).text(); });

    var headHtml = '<tr>';
    headers.forEach(function(h){ headHtml += '<th>' + h + '</th>'; });
    headHtml += '</tr>';

    var rowsApi = dataTable.rows({ search: 'applied', order: 'applied' });

    var strip = function(html){ return $('<div>').html(html).text(); };

    var bodyParts = [];
    var chunk = [];
    var countInPage = 0;
    var noIdx = headers.findIndex(function(h){ return /^(No\.?|#)$/i.test(h); });
    var lnCounter = 1;

    var addChunk = function(){
      if (!chunk.length) return;
      bodyParts.push(chunk.join(''));
      chunk = [];
      bodyParts.push('<tr class="page-break"><td colspan="'+ headers.length +'"></td></tr>');
    };

    rowsApi.every(function(){
      var rowData = this.data(); 

      var tds = rowData.map(function(cell, idx){
        if (idx === noIdx) return '<td>' + (lnCounter++) + '</td>';
        return '<td>' + cell + '</td>';
      }).join('');

      chunk.push('<tr>' + tds + '</tr>');
      countInPage++;

      if (rowsPerPage && countInPage >= rowsPerPage) {
        addChunk();
        countInPage = 0;
      }
    });

    if (chunk.length) {
      bodyParts.push(chunk.join(''));
    } else if (!bodyParts.length) {
      return { headHtml: headHtml, bodyHtml: '' };
    }

    if (bodyParts.length && /class="page-break"/i.test(bodyParts[bodyParts.length - 1])) {
      bodyParts.pop();
    }

    return { headHtml: headHtml, bodyHtml: bodyParts.join('') };
  }

  function printWithIframe(title, headHtml, bodyHtml, footHtml) {
    var css = '\
      @media print { @page { size: A4 landscape; margin: 20mm; } }\
      body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; }\
      h3 { margin: 0 0 8px 0; }\
      .meta { margin: 4px 0 10px 0; font-size: 12px; }\
      table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; page-break-inside: auto; }\
      tr { page-break-inside: avoid; page-break-after: auto; }\
      th, td { border: 1px solid #000; padding: 4px; text-align: left; }\
      thead { display: table-header-group; }\
      tfoot { display: table-footer-group; }\
      thead th { background: #f8f9fa; }';

    if (!footHtml) {
      var tfootNode = document.querySelector('#datatable tfoot');
      footHtml = tfootNode ? tfootNode.innerHTML : '';
      if (footHtml) footHtml = '<tr>' + $(tfootNode).find('tr').first().html() + '</tr>';
    }

    var infoBar = document.querySelector('.card-header .alert-info');
    var infoText = infoBar ? infoBar.innerText : '';

    var html = '\
      <!doctype html>\n\
      <html><head><meta charset="utf-8"><title>'+title+'</title><style>'+css+'</style></head>\n\
      <body>\n\
        <h3>'+title+'</h3>\n\
        '+ (infoText ? '<div class="meta">'+ infoText +'</div>' : '') +'\n\
        <small>Generated: '+ new Date().toLocaleString() +'</small>\n\
        <table>\n\
          <thead>'+ headHtml +'</thead>\n\
          <tbody>'+ bodyHtml +'</tbody>\n\
          '+ (footHtml ? '<tfoot>'+ footHtml +'</tfoot>' : '') +'\n\
        </table>\n\
      </body></html>';

    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = 0;
    iframe.style.bottom = 0;
    iframe.style.width = 0;
    iframe.style.height = 0;
    iframe.style.border = 0;
    document.body.appendChild(iframe);

    var doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open(); doc.write(html); doc.close();

    iframe.onload = function () {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
      setTimeout(function(){ document.body.removeChild(iframe); }, 500);
    };
  }

  $('#btn-print-all').on('click', function () {
    if (!dt) { window.print(); return; } 

    var payload = buildPrintableFromDT(dt, 35);
    if (!payload.bodyHtml || payload.bodyHtml.length === 0) {
      alert('No rows to print.');
      return;
    }

    var tfootHtml = $('#datatable tfoot').length ? $('#datatable tfoot').html() : '';
    printWithIframe('13th Month Pay Report', payload.headHtml, payload.bodyHtml, tfootHtml);
  });
})();
</script>

</body>
</html>
