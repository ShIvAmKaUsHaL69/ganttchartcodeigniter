<?php $this->load->view('layout/header'); ?>
<h3>Add Task to Project: <?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h3>
<form method="post">
    <div class="form-group">
        <label for="task_name">Task Name</label>
        <input type="text" name="task_name" id="task_name" class="form-control" value="<?= set_value('task_name'); ?>">
        <?= form_error('task_name', '<small class="text-danger">', '</small>'); ?>
    </div>
    <div class="form-group">
        <label for="assigned_to">Assigned To</label>
        <input type="text" name="assigned_to" id="assigned_to" class="form-control" value="<?= set_value('assigned_to'); ?>">
    </div>
    <div class="form-group">
        <label for="start_date">Start Date</label>
        <input type="date" name="start_date" id="start_date" class="form-control" value="<?= set_value('start_date'); ?>">
        <?= form_error('start_date', '<small class="text-danger">', '</small>'); ?>
    </div>
    <div class="form-group">
        <label for="end_date">End Date</label>
        <input type="date" name="end_date" id="end_date" class="form-control" value="<?= set_value('end_date'); ?>">
        <?= form_error('end_date', '<small class="text-danger">', '</small>'); ?>
    </div>
    <div class="form-group">
        <label for="progress">Progress (%)</label>
        <input type="number" name="progress" id="progress" class="form-control" value="<?= set_value('progress', 0); ?>" min="0" max="100">
        <?= form_error('progress', '<small class="text-danger">', '</small>'); ?>
    </div>
    <button class="btn btn-primary">Save</button>
    <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary">Cancel</a>
</form>
<?php $this->load->view('layout/footer'); ?> 