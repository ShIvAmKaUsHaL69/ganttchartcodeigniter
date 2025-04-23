<?php $this->load->view('layout/header'); ?>
<h3>Edit Project</h3>
<form method="post">
    <div class="form-group">
        <label for="name">Project Name</label>
        <input type="text" name="name" id="name" class="form-control" value="<?= set_value('name', $project->name); ?>">
        <?= form_error('name', '<small class="text-danger">', '</small>'); ?>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="<?= site_url('projects'); ?>" class="btn btn-secondary">Cancel</a>
</form>
<?php $this->load->view('layout/footer'); ?> 