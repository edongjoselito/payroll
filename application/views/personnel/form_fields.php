<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light"><strong>Basic Information</strong></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>First Name</label>
                <input class="form-control" name="first_name" pattern="^[A-Za-z\s\-]{2,50}$" title="Only letters, spaces, and hyphens allowed"
                       value="<?= $personnel->first_name ?? '' ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label>Middle Name</label>
                <input class="form-control" name="middle_name" placeholder=""
                       pattern="^[A-Za-z\s\-]{0,50}$"
                       value="<?= $personnel->middle_name ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Last Name</label>
                <input class="form-control" name="last_name" placeholder=""
                       pattern="^[A-Za-z\s\-]{2,50}$" title="Only letters, spaces, and hyphens allowed"
                       value="<?= $personnel->last_name ?? '' ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Name Extension</label>
                <input class="form-control" name="name_ext" placeholder="" value="<?= $personnel->name_ext ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Contact Number</label>
                <input class="form-control" name="contact_number" placeholder=""
                       pattern="^09\d{9}$" title="Enter a valid Philippine mobile number"
                       value="<?= $personnel->contact_number ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Email</label>
                <input type="email" class="form-control" name="email"
                       placeholder="" value="<?= $personnel->email ?? '' ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Birthdate</label>
                <input type="date" class="form-control" name="birthdate" value="<?= $personnel->birthdate ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Gender</label>
                <select class="form-control" name="gender" required>
                    <option value="">Select</option>
                    <option <?= ($personnel->gender ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option <?= ($personnel->gender ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option <?= ($personnel->gender ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>Civil Status</label>
                <select class="form-control" name="civil_status" required>
                    <option value="">Select</option>
                    <option <?= ($personnel->civil_status ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                    <option <?= ($personnel->civil_status ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
                    <option <?= ($personnel->civil_status ?? '') === 'Widow' ? 'selected' : '' ?>>Widow</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Complete Address</label>
            <textarea class="form-control" name="address" rows="2" placeholder="Street, Barangay, City/Province"><?= $personnel->address ?? '' ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Date Employed</label>
                <input type="date" name="date_employed" class="form-control"
                       value="<?= $personnel->date_employed ?? '' ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label>Date Terminated</label>
                <input type="date" name="date_terminated" class="form-control"
                       value="<?= $personnel->date_terminated ?? '' ?>">
                <small class="text-muted">Leave blank if currently active.</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light"><strong>Employment & Salary</strong></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Position</label>
                <input type="text" class="form-control" name="position" placeholder=""
                       value="<?= $personnel->position ?? '' ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label>Salary Type</label>
                <select name="rateType" class="form-control" required>
                    <option value="">Select Salary Type</option>
                    <option value="Hour" <?= ($personnel->rateType ?? '') === 'Hour' ? 'selected' : '' ?>>Per Hour</option>
                    <option value="Day" <?= ($personnel->rateType ?? '') === 'Day' ? 'selected' : '' ?>>Per Day</option>
                    <option value="Month" <?= ($personnel->rateType ?? '') === 'Month' ? 'selected' : '' ?>>Per Month</option>
                    <option value="Bi-Month" <?= ($personnel->rateType ?? '') === 'Bi-Month' ? 'selected' : '' ?>>Bi-Month</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>Salary Amount</label>
                <input type="text" class="form-control" name="rateAmount"
                       pattern="^\d+(\.\d{1,2})?$"
                       title="Enter a valid amount"
                       placeholder="" value="<?= $personnel->rateAmount ?? '' ?>" required>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-light"><strong>Government IDs</strong></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>PhilHealth Number</label>
                <input class="form-control" name="philhealth_number" placeholder="PH No." value="<?= $personnel->philhealth_number ?? '' ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Pag-IBIG Number</label>
                <input class="form-control" name="pagibig_number" placeholder="Pag-IBIG No." value="<?= $personnel->pagibig_number ?? '' ?>">
            </div>
            <div class="form-group col-md-3">
                <label>SSS Number</label>
                <input class="form-control" name="sss_number" placeholder="SSS No." value="<?= $personnel->sss_number ?? '' ?>">
            </div>
            <div class="form-group col-md-3">
                <label>TIN Number</label>
                <input class="form-control" name="tin_number" placeholder="TIN No." value="<?= $personnel->tin_number ?? '' ?>">
            </div>
        </div>
    </div>
</div>
