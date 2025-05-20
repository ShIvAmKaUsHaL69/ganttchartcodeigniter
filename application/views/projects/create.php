<?php $this->load->view('layout/header'); ?>
<h3 class="mb-4">Add Project</h3>
<div class="card">
    <div class="card-body">
        <form method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Project Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= set_value('name'); ?>">
                        <?= form_error('name', '<small class="text-danger">', '</small>'); ?>
                    </div>
                    <div class="form-group">
                        <label for="created_by">Created By</label>
                        <input type="text" name="created_by" id="created_by" class="form-control" value="<?= set_value('created_by'); ?>">
                        <?= form_error('created_by', '<small class="text-danger">', '</small>'); ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary">Save</button>
                <a href="<?= site_url('projects'); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $this->load->view('layout/footer'); ?> 