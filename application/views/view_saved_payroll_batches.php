<!-- view_saved_payroll_batches.php -->
<!DOCTYPE html>
<html>
<head>
  <title>View Saved Payroll Batches</title>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .container {
      margin: 50px auto;
      max-width: 600px;
    }
  </style>
</head>
<body>
<div class="container">
  <h3>Select a Saved Payroll Batch</h3>
  
 <form action="<?= base_url('view_payroll_batch') ?>" method="get">

    <label for="batch_id">Payroll Batch:</label>
    <select id="batch_id" name="batch_id" class="form-control select2" required>
      <option value="" disabled selected>Select a batch</option>
      <?php foreach ($batches as $batch): ?>
   <option value="<?= $batch->projectID . '|' . $batch->start_date . '|' . $batch->end_date ?>">
  <?= $batch->projectTitle ?> (<?= date('M d, Y', strtotime($batch->start_date)) ?> - <?= date('M d, Y', strtotime($batch->end_date)) ?>)
</option>

          <?= $batch->projectTitle ?> (<?= date('M d, Y', strtotime($batch->start_date)) ?> - <?= date('M d, Y', strtotime($batch->end_date)) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <br><br>
    <button type="submit" class="btn btn-primary">View Payroll</button>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2();
  });
</script>
</body>
</html>
