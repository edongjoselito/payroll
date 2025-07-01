<div class="container mt-4">
    <h4>Edit Company Information</h4>
    <hr>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('Company/update') ?>">
        <div class="form-group">
            <label>Company Name</label>
            <input type="text" name="company_name" class="form-control" value="<?= $company->company_name ?>">
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= $company->address ?></textarea>
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact" class="form-control" value="<?= $company->contact ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= $company->email ?>">
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Update</button>
    </form>
</div>
