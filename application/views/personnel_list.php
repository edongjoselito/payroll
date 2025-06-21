<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

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
                    <!-- <h4 class="page-title">Personnel List</h4> -->
                    <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#addModal">Add New</button>
                </div>
                <!-- <hr style="border:0; height:2px; background:linear-gradient(to right, #34A853, #FBBC05, #4285F4); border-radius:1px; margin:20px 0;"/> -->

                <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php elseif ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>


                <div class="card">
                    <div class="card-body">
                      <h5 class="page-title">Personnel List</h5>
<div class="table-responsive">
  <table id="datatable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
    
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>SSS</th>
                                        <th>PhilHealth</th>
                                        <th>Pag-IBIG</th>
                                        <th>TIN</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($personnel)): ?>
                                        <tr><td colspan="8" class="text-center">No personnel records found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($personnel as $p): ?>
                                            <tr>
                                                <td><?= "$p->first_name $p->middle_name $p->last_name $p->name_ext" ?></td>
                                                <td><?= $p->address ?></td>
                                                <td><?= $p->contact_number ?></td>
                                                <td><?= $p->sss_number ?></td>
                                                <td><?= $p->philhealth_number ?></td>
                                                <td><?= $p->pagibig_number ?></td>
                                                <td><?= $p->tin_number ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $p->personnelID ?>">Edit</button>
                                                    <a href="<?= base_url('personnel/delete/'.$p->personnelID) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
                                                </td>
                                            </tr>


<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $p->personnelID ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= base_url('personnel/update') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Personnel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="personnelID" value="<?= $p->personnelID ?>">

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>First Name</label>
                            <input class="form-control" name="first_name" value="<?= $p->first_name ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Middle Name</label>
                            <input class="form-control" name="middle_name" value="<?= $p->middle_name ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Last Name</label>
                            <input class="form-control" name="last_name" value="<?= $p->last_name ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Name Ext</label>
                            <input class="form-control" name="name_ext" value="<?= $p->name_ext ?>">
                        </div>
                        <div class="form-group col-md-5">
                            <label>Contact Number</label>
                            <input class="form-control" name="contact_number" value="<?= $p->contact_number ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?= $p->email ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Birthdate</label>
                            <input type="date" class="form-control" name="birthdate" value="<?= $p->birthdate ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Gender</label>
                            <select class="form-control" name="gender">
                                <option value="">Select</option>
                                <option <?= ($p->gender == 'Male') ? 'selected' : '' ?>>Male</option>
                                <option <?= ($p->gender == 'Female') ? 'selected' : '' ?>>Female</option>
                                <option <?= ($p->gender == 'Other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Civil Status</label>
                            <select class="form-control" name="civil_status">
                                <option value="">Select</option>
                                <option <?= ($p->civil_status == 'Single') ? 'selected' : '' ?>>Single</option>
                                <option <?= ($p->civil_status == 'Married') ? 'selected' : '' ?>>Married</option>
                                <option <?= ($p->civil_status == 'Widow') ? 'selected' : '' ?>>Widow</option>
                            </select>
                        </div>
                       
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" rows="2"><?= $p->address ?></textarea>
                    </div>

                     <div class="form-row">

               
                         <div class="form-group col-md-4">
                               <label>Position</label>
                                <input type="text" class="form-control" name="position" value="<?= $p->position ?>" required>
                        </div>
                           <div class="form-group col-md-4">
                               <label>Salary Type</label>
                            <select name="rateType" class="form-control" required>
                                <option value="Hour" <?= $p->rateType == 'Hour' ? 'selected' : '' ?>>Per Hour</option>
                                <option value="Day" <?= $p->rateType == 'Day' ? 'selected' : '' ?>>Per Day</option>
                                <option value="Month" <?= $p->rateType == 'Month' ? 'selected' : '' ?>>Per Month</option>
                            </select>
                        </div>
                            <div class="form-group col-md-4">
                               <label>Salary</label>
                                <input type="text" class="form-control" name="rateAmount" value="<?= $p->rateAmount ?>" required>
                        </div>
                        </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>PhilHealth Number</label>
                            <input class="form-control" name="philhealth_number" value="<?= $p->philhealth_number ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pag-IBIG Number</label>
                            <input class="form-control" name="pagibig_number" value="<?= $p->pagibig_number ?>">
                        </div>
                        
                            <div class="form-group col-md-3">
                            <label>SSS Number</label>
                            <input class="form-control" name="sss_number" value="<?= $p->sss_number ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label>TIN Number</label>
                            <input class="form-control" name="tin_number" value="<?= $p->tin_number ?>">
                        </div>
                        
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>




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
<!-- Add Modal -->
<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= base_url('personnel/store') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Personnel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>First Name</label>
                            <input class="form-control" name="first_name" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Middle Name</label>
                            <input class="form-control" name="middle_name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Last Name</label>
                            <input class="form-control" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Name Ext</label>
                            <input class="form-control" name="name_ext">
                        </div>
                        <div class="form-group col-md-5">
                            <label>Contact Number</label>
                            <input class="form-control" name="contact_number">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Birthdate</label>
                            <input type="date" class="form-control" name="birthdate">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Gender</label>
                            <select class="form-control" name="gender">
                                <option value="">Select</option>
                                <option>Male</option>
                                <option>Female</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Civil Status</label>
                            <select class="form-control" name="civil_status">
                                <option value="">Select</option>
                                <option>Single</option>
                                <option>Married</option>
                                <option>Widow</option>
                            </select>
                        </div>
                        
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>

                    
                      <div class="form-row">

               
                         <div class="form-group col-md-4">
                               <label>Position</label>
                                <input type="text" class="form-control" name="position" required>
                        </div>
                           <div class="form-group col-md-4">
                               <label>Salary Type</label>
                            <select name="rateType" class="form-control" required>
                                <option value="Hour">Per Hour</option>
                                <option value="Day">Per Day</option>
                                <option value="Month">Per Month</option>

                            </select>
                        </div>
                            <div class="form-group col-md-4">
                               <label>Salary</label>
                                <input type="text" class="form-control" name="rateAmount" required>
                        </div>
                        </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>PhilHealth Number</label>
                            <input class="form-control" name="philhealth_number">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pag-IBIG Number</label>
                            <input class="form-control" name="pagibig_number">
                        </div>
                        <div class="form-group col-md-3">
                            <label>SSS Number</label>
                            <input class="form-control" name="sss_number">
                        </div>
                        <div class="form-group col-md-3">
                            <label>TIN Number</label>
                            <input class="form-control" name="tin_number">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>
</html>
