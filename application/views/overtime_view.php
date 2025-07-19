<!DOCTYPE html>
<html lang="en">
<title>PMS - Overtime</title>

<?php include('includes/head.php'); ?>

<body>
<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>


    <div class="content-page">
        <div class="content">
          
            <div class="container-fluid">

                <h4 class="page-title">Overtime Management</h4>

                <button class="btn btn-primary mb-2 shadow-sm" data-toggle="modal" data-target="#generateModal">
                    <i class="mdi mdi-calendar-plus"></i> Add Overtime
                </button>

                <button class="btn btn-success mb-2 shadow-sm" data-toggle="modal" data-target="#viewModal">
                    <i class="mdi mdi-eye"></i> View Saved Overtime
                </button>
<input type="hidden" id="reload_projectID">
<input type="hidden" id="reload_start">
<input type="hidden" id="reload_end">

                <div id="generatedResult" class="mt-3"></div>
              <?php if ($this->session->flashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $this->session->flashdata('success'); ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif; ?>

<div id="savedResult" class="mt-3">
    <?php if ($this->session->flashdata('saved_overtime_block')): ?>
        <?= $this->session->flashdata('saved_overtime_block'); ?>
    <?php endif; ?>
</div>



                <!-- Generate Modal -->
                <div class="modal fade" id="generateModal">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <form id="generateForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Select Project & Date Range</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                   <?php if ($this->session->flashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $this->session->flashdata('success'); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

                            <div class="modal-body">
    <select name="projectID" class="form-control mb-2" required>
        <option value="">Select Project</option>
        <?php foreach ($projects as $p): ?>
            <option value="<?= $p->projectID ?>"><?= $p->projectTitle ?></option>
        <?php endforeach; ?>
    </select>

    <input type="date" name="start" class="form-control mb-2" required>
    <input type="date" name="end" class="form-control" required>
</div>


                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Generate</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

          <!-- View Modal -->
<div class="modal fade" id="viewModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="viewForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Saved Overtime</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <select name="projectID" class="form-control mb-2" required>
                        <option value="">Select Project</option>
                        <?php foreach ($projects as $p): ?>
                           <option value="<?= $p->projectID ?>"><?= $p->projectTitle ?></option>

                        <?php endforeach; ?>
                    </select>

                    <select name="date" id="viewDate" class="form-control" required>
                        <option value="">Select Date</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">View</button>
                </div>
            </div>
        </form>
    </div>
</div>


            </div> 
        </div> 
        <?php include('includes/footer.php'); ?>
    </div> 
</div> <!-- wrapper -->
<!-- JS Scripts -->
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

<?php if ($this->session->flashdata('open_modal') == 'viewModal'): ?>
<script>
  $(document).ready(function(){
    $('#viewModal').modal('show');
  });
</script>
<?php endif; ?>

<script>
$(document).ready(function() {
    // ✅ Handle generate overtime submission with duplicate check
    $('#generateForm').submit(function(e){
        e.preventDefault();
        $.post("<?= base_url('Overtime/generate_personnel') ?>", $(this).serialize(), function(res){
            try {
                const json = typeof res === 'string' ? JSON.parse(res) : res;

                if (json.status === 'duplicate') {
                    const html = `
                    <div class="modal fade show" id="duplicateModal" tabindex="-1" style="display:block; background: rgba(0,0,0,0.5);">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-warning">
                          <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">Duplicate Batch Found</h5>
                            <button type="button" class="close" onclick="location.reload()">&times;</button>
                          </div>
                          <div class="modal-body">
                            <p>${json.message}</p>
                           <p><strong>Project:</strong> ${json.projectTitle}</p>

                            <p><strong>Period:</strong> ${json.start} to ${json.end}</p>
                          </div>
                          <div class="modal-footer">
                            <form method="post" action="<?= base_url('Overtime/view_saved_overtime') ?>">
                              <input type="hidden" name="projectID" value="${json.projectID}">
                              <input type="hidden" name="start" value="${json.start}">
                              <input type="hidden" name="end" value="${json.end}">
                              <button type="submit" class="btn btn-primary">View Existing</button>
                              <button type="button" class="btn btn-secondary" onclick="location.reload()">Cancel</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>`;
                    $('#generatedResult').html(html);
                    $('#generateModal').modal('hide');
                } else {
                    // If not JSON, assume it's the HTML form
                    $('#generatedResult').html(res);
                    $('#generateModal').modal('hide');
                }
            } catch (err) {
                // If it's not JSON (no duplicate), treat as normal view
                $('#generatedResult').html(res);
                $('#generateModal').modal('hide');
            }
        }).fail(function(xhr){
            alert('❌ Failed to generate overtime. ' + xhr.responseText);
        });
    });

    // 🔄 Dynamically populate date dropdown based on selected project
    $('#viewModal select[name="projectID"]').change(function() {
        const projectID = $(this).val();

        if (projectID !== "") {
            $.post("<?= base_url('Overtime/get_dates_by_project') ?>", { projectID: projectID }, function(response) {
                $('#viewModal select[name="date"]').html(response);
            }).fail(function(xhr) {
                alert('Failed to fetch dates: ' + xhr.responseText);
            });
        } else {
            $('#viewModal select[name="date"]').html('<option value="">Select Project First</option>');
        }
    });

    // ✅ View Saved Overtime Handler
    $('#viewForm').submit(function(e){
        e.preventDefault();

        const projectID = $('#viewForm select[name="projectID"]').val();
        const dateValue = $('#viewForm select[name="date"]').val();

        if (!projectID || !dateValue) {
            alert("Please select both project and date.");
            return;
        }

        const [start, end] = dateValue.split('|');

        $('#reload_projectID').val(projectID);
        $('#reload_start').val(start);
        $('#reload_end').val(end);

        $.post("<?= base_url('Overtime/view_saved_overtime') ?>", {
            projectID: projectID,
            start: start,
            end: end
        }, function(res){
            $('#savedResult').html(res);
            $('#viewModal').modal('hide');
        }).fail(function(xhr){
            alert('❌ Failed to fetch saved overtime. ' + xhr.responseText);
        });
    });
});
</script>

<?php if ($this->session->flashdata('open_modal') == 'duplicateModal'): ?>
<script>
  $(document).ready(function() {
    $('#duplicateModal').modal('show');
  });
</script>
<?php endif; ?>

</body>
</html>
