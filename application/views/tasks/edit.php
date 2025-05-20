<?php $this->load->view('layout/header'); ?>
<h3 class="mb-4">Edit Task</h3>
<div class="card">
    <div class="card-body">
        <form method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="task_name">Task Name</label>
                        <input type="text" name="task_name" id="task_name" class="form-control" value="<?= set_value('task_name', $task->task_name); ?>">
                        <?= form_error('task_name', '<small class="text-danger">', '</small>'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="assigned_to">Assigned To</label>
                        <input type="text" name="assigned_to" id="assigned_to" class="form-control" value="<?= set_value('assigned_to', $task->assigned_to); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?= set_value('start_date', $task->start_date); ?>">
                        <?= form_error('start_date', '<small class="text-danger">', '</small>'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expected_end_date">Expected End Date</label>
                        <input type="date" name="expected_end_date" id="expected_end_date" class="form-control" value="<?= set_value('expected_end_date', $task->expected_end_date); ?>">
                        <?= form_error('expected_end_date', '<small class="text-danger">', '</small>'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="progress">Progress (%)</label>
                        <input type="number" name="progress" id="progress" class="form-control" value="<?= set_value('progress', $task->progress); ?>" min="0" max="100">
                        <?= form_error('progress', '<small class="text-danger">', '</small>'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control"">
                            <option value="0" <?= $task->status == 0 ? 'selected' : ''; ?>>In Progress</option>
                            <option value="2" <?= $task->status == 2 ? 'selected' : ''; ?>>Hold</option>
                            <option value="1" <?= $task->status == 1 ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary">Update</button>
                <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $this->load->view('layout/footer'); ?> 