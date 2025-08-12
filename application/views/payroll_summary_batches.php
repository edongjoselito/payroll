<!DOCTYPE html>
<html lang="en">
<head>
  <title>Payroll Summary</title>
  <?php include('includes/head.php'); ?>
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.css">
    <style>
    .dataTables_length { display: none; } 
    .btn { transition: all 0.3s ease; }
    .btn:hover { transform: scale(1.05); opacity: 0.95; }
    .btn-outline-danger:hover {
      background-color: #dc3545; color: white; box-shadow: 0 0 8px rgba(220, 53, 69, 0.5); border-color: #dc3545;
    }
    .btn-primary:hover { box-shadow: 0 0 8px rgba(0, 123, 255, 0.4); border-color: rgba(0, 123, 255, 0.3); }
    .gap-2 { gap: .5rem; }

    #payrollTable td, #payrollTable th {
      vertical-align: middle;
    }

    @media print {
      @page {
        size: A4 landscape;
        margin: 12mm;
      }

      .no-print,
      .btn,
      .page-title-box,
      .left-side-menu,
      .left-side-menu *,
      .topnav,
      .topnav * ,
      .footer,
      .footer * ,
      .dataTables_filter, 
      .dataTables_info, 
      .dataTables_paginate,
      .dataTables_length,
      .dataTables_wrapper .dt-buttons
      { display: none !important; }

      .content-page, .content, .container-fluid, .card, .card-body, .table-responsive {
        box-shadow: none !important;
        border: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
      }

      /* Table print look */
      table {
        border-collapse: collapse !important;
        width: 100% !important;
        font-size: 11px !important;
      }
      th, td {
        border: 1px solid #000 !important;
        padding: 6px 8px !important;
      }
      thead th {
        background: #f2f2f2 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      tfoot, tfoot * {
        page-break-inside: avoid !important;
      }
    }
  </style>
</head>

<body>
<div id="wrapper">
  <?php include('includes/top-nav-bar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <div class="page-title-box d-flex justify-content-between align-items-center mb-3">
          <h4 class="page-title mb-0">Payroll Summary</h4>
          <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('Generatepayroll/form') ?>" class="btn btn-primary">
              <i class="mdi mdi-arrow-left"></i> Back to Payroll
            </a>
            <!-- New action buttons -->
            <button type="button" class="btn btn-outline-secondary" id="btn-print">
              <i class="mdi mdi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-outline-dark" id="btn-download-pdf">
              <i class="mdi mdi-file-pdf"></i> Download PDF
            </button>
          </div>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
          <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
          <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
          <div class="card-body">

            <?php if (empty($batch_summaries)): ?>
              <div class="alert alert-warning">No payroll summaries found.</div>
            <?php else: ?>
            <div class="table-responsive">
              <table id="payrollTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                <thead class="thead-light">
                  <tr>
                    <th>Project Title</th>
                    <th>Location</th>
                    <th>Payroll Period</th>
                    <th class="text-right">Payroll Gross</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($batch_summaries as $batch): ?>
                    <tr class="batch-row"
                        data-project-id="<?= $batch['projectID'] ?>"
                        data-start="<?= $batch['start_date'] ?>"
                        data-end="<?= $batch['end_date'] ?>">
                      <td><?= $batch['projectTitle'] ?></td>
                      <td><?= $batch['projectLocation'] ?></td>
                      <td><?= date('M d, Y', strtotime($batch['start_date'])) ?> – <?= date('M d, Y', strtotime($batch['end_date'])) ?></td>
                      <td class="text-right text-success">
                        ₱ <span class="gross-cell"><?= number_format($batch['gross_total'], 2) ?></span>
                      </td>
                      <td class="text-center">
                        <form method="post" action="<?= base_url('Project/delete_summary_batch') ?>"
                              onsubmit="return confirm('Are you sure you want to delete this summary?');">
                          <input type="hidden" name="projectID" value="<?= $batch['projectID'] ?>">
                          <input type="hidden" name="start_date" value="<?= $batch['start_date'] ?>">
                          <input type="hidden" name="end_date" value="<?= $batch['end_date'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Summary">
                            <i class="mdi mdi-delete"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3" class="text-right font-weight-bold">Grand Totals</td>
                    <td class="text-right font-weight-bold text-success">₱ <span id="grand-total"><?= number_format($grand_total_gross, 2) ?></span></td>
                    <td></td>
                  </tr>
                </tfoot>
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

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>

<script>
  function fmt(n) {
    const x = (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);
    return x.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function recomputeGrandTotal() {
    let sum = 0;
    document.querySelectorAll('.gross-cell').forEach(span => {
      const raw = (span.textContent || '').replace(/,/g,'').trim();
      sum += parseFloat(raw) || 0;
    });
    const gt = document.getElementById('grand-total');
    if (gt) gt.textContent = fmt(sum);
  }

  async function refreshRowGross(tr) {
    const projectID = tr.dataset.projectId;
    const start     = tr.dataset.start;
    const end       = tr.dataset.end;

    const url = '<?= base_url('Project/api_get_batch_total_live'); ?>'
          + '?projectID=' + encodeURIComponent(projectID)
          + '&start_date=' + encodeURIComponent(start)
          + '&end_date=' + encodeURIComponent(end);

    const res = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    if (data && typeof data.gross_total !== 'undefined') {
      const cell = tr.querySelector('.gross-cell');
      if (cell) cell.textContent = fmt(data.gross_total);
    }
  }

  async function refreshAllGross() {
    const rows = Array.from(document.querySelectorAll('tr.batch-row'));
    await Promise.all(rows.map(refreshRowGross));
    recomputeGrandTotal();
  }

  $(document).ready(function() {
    $('#payrollTable').DataTable({
      paging: false,
      searching: true,
      ordering: true,
      responsive: true
    });

    refreshAllGross();


    $(document).on('click', '#btn-print', function(e) {
      e.preventDefault();
      window.print();
    });

    $(document).on('click', '#btn-download-pdf', async function(e) {
      e.preventDefault();

      await refreshAllGross();

      if (!window.jspdf || !window.jspdf.jsPDF) {
        alert('PDF library failed to load. Please check your internet connection or CDN.');
        return;
      }

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });

      doc.setFontSize(14);
      doc.text('Payroll Summary', 40, 30);

      doc.autoTable({
        html: '#payrollTable',
        startY: 50,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 4, overflow: 'linebreak' },
        headStyles: { fillColor: [240, 240, 240] },
        didDrawPage: function () {
          const pageNo = doc.internal.getNumberOfPages();
          doc.setFontSize(8);
          doc.text('Page ' + pageNo,
            doc.internal.pageSize.getWidth() - 60,
            doc.internal.pageSize.getHeight() - 20);
        }
      });

      const fileName = 'Payroll_Summary_' + new Date().toISOString().slice(0,10) + '.pdf';
      doc.save(fileName);
    });
  });
</script>

</body>
</html>
