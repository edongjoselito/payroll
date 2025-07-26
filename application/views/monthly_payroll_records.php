<!DOCTYPE html>
<html lang="en">
<?php
function formatHoursAndMinutes($decimal) {
    $hours = floor($decimal);
    $minutes = round(($decimal - $hours) * 60);
    return "{$hours} hr" . ($hours != 1 ? "s" : "") . " and {$minutes} mins";
}
?>

<title>PMS - Monthly Payroll Records</title>
<?php include('includes/head.php'); ?>
<style>
thead th, thead td {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 2;
    vertical-align: middle !important;
    text-align: center;
    border-bottom: 2px solid #dee2e6;
    padding: 8px 6px;
    font-size: 14px;
    min-width: 75px;
    white-space: nowrap;
}
td, th {
    vertical-align: middle !important;
    text-align: center;
    padding: 7px 6px;
    font-size: 14px;
}
td {
    min-height: 36px;
    height: 36px;
}
th:first-child, td:first-child {
    position: sticky;
    left: 0;
    background: #f8f9fa;
    z-index: 3;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.03);
    font-weight: bold;
    min-width: 120px;
    text-align: left !important;
}
tbody tr td {
    border-bottom: 1px solid #f0f0f0;
}

.cell-label { font-size: 13px; font-weight: 500; }
.text-blue    { color: #007bff; }      /* Present */
.text-red     { color: #e74c3c; }      /* Absent */
.text-dayoff  { color: #55aee6; }      /* Day Off */
.text-gray    { color: #7b7d7d; }      /* Holidays */
.cell-hours   { font-size: 12px; color: #2d9c5a; font-weight: 400; }
.cell-ot      { color: #c0392b; font-size:12px;}
.totals-cell      { font-size: 15px; font-weight: bold; color: #1abc9c; white-space: nowrap; }
.totals-cell-ot   { color: #e67e22; }
.edit-pen {
    color: #aaa; font-size: 14px; margin-left: 4px; vertical-align: middle; cursor: pointer;
}
.edit-pen:hover { color: #007bff; }

@media print {
    .btn, .modal, .modal-backdrop, .navbar, .sidebar, .page-title, .alert, .modal-open body {
        display: none !important;
    }
    table { font-size: 11px; border-collapse: collapse !important; }
    th, td { border: 1px solid black !important; padding: 3px !important; }
    body { margin: 10px; }
}
</style>

<body>
<div id="wrapper">
<?php include('includes/top-nav-bar.php'); ?>
<?php include('includes/sidebar.php'); ?>

<div class="content-page">
<div class="content">
<div class="container-fluid">

<div class="card">
<div class="card-body">
    
    <h4 class="text-dark font-weight-bold mb-4">
        <i class="mdi mdi-calendar-month mr-1"></i>
        Monthly Payroll Records for <?= date('F Y', strtotime($month . '-01')) ?>
        <div>
             <a href="<?= base_url('WeeklyAttendance/records') ?>" class="btn btn-secondary mt-3">Back</a>
        <button class="btn btn-outline-primary mt-3" onclick="window.print()">
        <i class="mdi mdi-printer"></i> Print
    </button></div>
    
    </h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            
            <thead class="thead-light">
                
                <tr>
                    <th>Personnel</th>
                    <?php foreach ($records['dates'] as $d): ?>
                        <th><?= date('M d', strtotime($d)) ?></th>
                    <?php endforeach; ?>
                    <th>Total Reg</th>
                    <th>Total OT</th>
                </tr>
            </thead>
          <tbody>
<?php foreach ($records['personnel'] as $person): ?>
    <tr>
        <td class="text-left font-weight-bold">
            <?= $person->last_name . ', ' . $person->first_name ?>
            <?= $person->name_ext ? ' ' . $person->name_ext : '' ?>
        </td>
        <?php
            $total_reg = 0;
            $total_ot = 0;
            foreach ($records['dates'] as $d):
                $day = date('d', strtotime($d));
                $rec = isset($records['attendance'][$person->personnelID][$day])
                    ? $records['attendance'][$person->personnelID][$day]
                    : null;
                $status = $rec ? $rec['status'] : 'Absent';
                $reg = $rec ? (float)$rec['reg'] : 0;
                $ot  = $rec ? (float)$rec['ot'] : 0;
                $total_reg += $reg;
                $total_ot  += $ot;

                // Color logic for label
                $labelClass = 'cell-label ';
                if ($status === 'Present') $labelClass .= 'text-blue';
                elseif ($status === 'Absent') $labelClass .= 'text-red';
                elseif ($status === 'Day Off') $labelClass .= 'text-dayoff';
                elseif ($status === 'Regular Holiday' || $status === 'Special Non-Working Holiday') $labelClass .= 'text-gray';

                $label = $status;
                if ($label === 'Regular Holiday') $label = 'R. Holiday';
                elseif ($label === 'Special Non-Working Holiday') $label = 'S. Holiday';
        ?>
        <td>
            <span class="<?= $labelClass ?>"><?= $label ?></span>
            <?php if ($reg > 0): ?>
                <br><span class="cell-hours"><?= number_format($reg,2) ?> hrs</span>
            <?php endif; ?>
            <?php if ($ot > 0): ?>
                <br><span class="cell-ot"><?= number_format($ot,2) ?> OT</span>
            <?php endif; ?>
            <!-- Edit icon/button -->
            <a href="#" 
                class="edit-pen" 
                title="Edit"
                data-toggle="modal"
                data-target="#editAttendanceModal"
                data-personnel="<?= $person->personnelID ?>"
                data-day="<?= $day ?>"
                data-status="<?= $status ?>"
                data-reg="<?= $reg ?>"
                data-ot="<?= $ot ?>"
            ><i class="mdi mdi-pencil"></i></a>
        </td>
        <?php endforeach; ?>
        <td class="totals-cell">
            <?= $total_reg > 0 ? formatHoursAndMinutes($total_reg) : '-' ?>
        </td>
        <td class="totals-cell-ot">
            <?= $total_ot > 0 ? formatHoursAndMinutes($total_ot) : '-' ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
        </table>
    </div>
    
   
</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="post" action="<?= base_url('MonthlyPayroll/update_attendance') ?>">
      <!-- HIDDEN FIELDS: these must always be present for correct context! -->
      <input type="hidden" name="payroll_month" id="editPayrollMonth" value="<?= $month ?>">
      <input type="hidden" name="personnelID" id="editPersonnelID">
      <input type="hidden" name="day" id="editDay">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="editStatus" class="form-control" required>
              <option value="Present">Present</option>
              <option value="Absent">Absent</option>
              <option value="Day Off">Day Off</option>
              <option value="Regular Holiday">Regular Holiday</option>
              <option value="Special Non-Working Holiday">Special Non-Working Holiday</option>
            </select>
          </div>
          <div class="form-group">
            <label>Regular Hours</label>
            <input type="number" step="0.01" max="8" min="0" name="reg" id="editReg" class="form-control">
          </div>
          <div class="form-group">
            <label>Overtime Hours</label>
            <input type="number" step="0.01" max="8" min="0" name="ot" id="editOT" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save changes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- End Edit Modal -->

</div>
</div>
<?php include('includes/footer.php'); ?>
</div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
$(document).ready(function(){
    $('.edit-pen').click(function(){
        $('#editPersonnelID').val($(this).data('personnel'));
        $('#editDay').val($(this).data('day'));
        $('#editStatus').val($(this).data('status'));
        $('#editReg').val($(this).data('reg'));
        $('#editOT').val($(this).data('ot'));
    });
});
</script>
</body>
</html>
