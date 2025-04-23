<?php $this->load->view('layout/header'); ?>
<h3>Edit Task</h3>
<form method="post">
    <div class="form-group">
        <label for="task_name">Task Name</label>
        <input type="text" name="task_name" id="task_name" class="form-control" value="<?= set_value('task_name', $task->task_name); ?>">
        <?= form_error('task_name', '<small class="text-danger">', '</small>'); ?>
    </div>
    <div class="form-group">
        <label for="assigned_to">Assigned To</label>
        <input type="text" name="assigned_to" id="assigned_to" class="form-control" value="<?= set_value('assigned_to', $task->assigned_to); ?>">
    </div>
    <div class="form-group">
        <label for="start_date">Start Date</label>
        <input type="date" name="start_date" id="start_date" class="form-control" value="<?= set_value('start_date', $task->start_date); ?>">
        <?= form_error('start_date', '<small class="text-danger">', '</small>'); ?>
    </div>
    <div class="form-group">
        <label for="end_date">End Date</label>
        <input type="date" name="end_date" id="end_date" class="form-control" value="<?= set_value('end_date', $task->end_date); ?>">
        <?= form_error('end_date', '<small class="text-danger">', '</small>'); ?>
    </div>
    <div class="form-group">
        <label for="progress">Progress (%)</label>
        <input type="number" name="progress" id="progress" class="form-control" value="<?= set_value('progress', $task->progress); ?>" min="0" max="100">
        <?= form_error('progress', '<small class="text-danger">', '</small>'); ?>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary">Cancel</a>
</form>
<?php $this->load->view('layout/footer'); ?> 