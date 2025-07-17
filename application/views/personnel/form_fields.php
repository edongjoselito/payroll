<div class="card mb-3">
    <div class="card-header bg-light"><strong>Basic Information</strong></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>First Name</label>
                <input class="form-control" name="first_name" value="<?= $personnel->first_name ?? '' ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label>Middle Name</label>
                <input class="form-control" name="middle_name" value="<?= $personnel->middle_name ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Last Name</label>
                <input class="form-control" name="last_name" value="<?= $personnel->last_name ?? '' ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Name Extension</label>
                <input class="form-control" name="name_ext" value="<?= $personnel->name_ext ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Contact Number</label>
                <input class="form-control" name="contact_number" value="<?= $personnel->contact_number ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Email</label>
                <input type="email" class="form-control" name="email" value="<?= $personnel->email ?? '' ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Birthdate</label>
                <input type="date" class="form-control" name="birthdate" value="<?= $personnel->birthdate ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Gender</label>
                <select class="form-control" name="gender">
                    <option value="">Select</option>
                    <option <?= ($personnel->gender ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option <?= ($personnel->gender ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option <?= ($personnel->gender ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>Civil Status</label>
                <select class="form-control" name="civil_status">
                    <option value="">Select</option>
                    <option <?= ($personnel->civil_status ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                    <option <?= ($personnel->civil_status ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
                    <option <?= ($personnel->civil_status ?? '') === 'Widow' ? 'selected' : '' ?>>Widow</option>
                </select>
            </div>
        </div>

 <div class="form-row">
    <div class="form-group col-md-12">
        <label>Complete Address</label>
        <textarea class="form-control" name="address" rows="2"><?= $personnel->address ?? '' ?></textarea>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Date Employed</label>
        <input type="date" name="date_employed" class="form-control"
               value="<?= $personnel->date_employed ?? '' ?>">
    </div>

    <div class="form-group col-md-6">
        <label>Date Terminated</label>
        <input type="date" name="date_terminated" class="form-control"
               value="<?= $personnel->date_terminated ?? '' ?>">
        <small class="text-muted">Leave blank while employee is still active.</small>
    </div>
</div>


</div>

    </div>
</div>


<div class="card mb-3">
    <div class="card-header bg-light"><strong>Employment & Salary</strong></div>
    <div class="card-body">
        
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Position</label>
                <input type="text" class="form-control" name="position" value="<?= $personnel->position ?? '' ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label>Salary Type</label>
                <select name="rateType" class="form-control" required>
                    <option value="">Select</option>
                    <option value="Hour" <?= ($personnel->rateType ?? '') === 'Hour' ? 'selected' : '' ?>>Per Hour</option>
                    <option value="Day" <?= ($personnel->rateType ?? '') === 'Day' ? 'selected' : '' ?>>Per Day</option>
                    <option value="Month" <?= ($personnel->rateType ?? '') === 'Month' ? 'selected' : '' ?>>Per Month</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>Salary Amount</label>
                <input type="text" class="form-control" name="rateAmount" value="<?= $personnel->rateAmount ?? '' ?>" required>
            </div>
  

        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-light"><strong>Government IDs</strong></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>PhilHealth Number</label>
                <input class="form-control" name="philhealth_number" value="<?= $personnel->philhealth_number ?? '' ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Pag-IBIG Number</label>
                <input class="form-control" name="pagibig_number" value="<?= $personnel->pagibig_number ?? '' ?>">
            </div>
            <div class="form-group col-md-3">
                <label>SSS Number</label>
                <input class="form-control" name="sss_number" value="<?= $personnel->sss_number ?? '' ?>">
            </div>
            <div class="form-group col-md-3">
                <label>TIN Number</label>
                <input class="form-control" name="tin_number" value="<?= $personnel->tin_number ?? '' ?>">
            </div>
        </div>
    </div>
</div>

<!-- <div class="card mb-3">
    <div class="card-header bg-light"><strong>Deductions</strong></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>SSS Deduction</label>
                <input type="text" class="form-control" name="sss_deduct" value="<?= $personnel->sss_deduct ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Pag-IBIG Deduction</label>
                <input type="text" class="form-control" name="pagibig_deduct" value="<?= $personnel->pagibig_deduct ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>PhilHealth Deduction</label>
                <input type="text" class="form-control" name="philhealth_deduct" value="<?= $personnel->philhealth_deduct ?? '' ?>">
            </div>
        </div>
    </div>
</div> -->
