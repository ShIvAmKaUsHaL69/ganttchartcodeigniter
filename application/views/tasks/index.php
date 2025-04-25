<?php $this->load->view('layout/header'); ?>
<h3>Tasks for Project: <?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h3>
<div class="d-flex flex-wrap mb-3">
    <a href="<?= site_url('projects'); ?>" class="btn btn-secondary mr-2 mb-2">Back to Projects</a>
    <a href="<?= site_url('projects/add_task/'.$project->id); ?>" class="btn btn-primary mb-2">Add Task</a>
</div>

<?php if (count($tasks)): ?>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Task Name</th>
                <th>Assigned To</th>
                <th>Duration</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Progress</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tasks as $t): ?>
            <tr>
                <td><?= $t->id; ?></td>
                <td><?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($t->assigned_to, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= (strtotime($t->end_date) - strtotime($t->start_date)) / (60 * 60 * 24); ?> days</td>
                <td><?= $t->start_date; ?></td>
                <td><?= $t->end_date; ?></td>
                <td><?= $t->progress; ?>%</td>
                <td>
                    <div class="d-flex flex-column d-md-block">
                        <a href="<?= site_url('projects/edit_task/'.$project->id.'/'.$t->id); ?>" class="btn btn-sm btn-warning mb-1 mb-md-0 mr-md-1">Edit</a>
                        <a href="<?= site_url('projects/delete_task/'.$project->id.'/'.$t->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this task?');">Delete</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p>No tasks found.</p>
<?php endif; ?>

<a href="<?= site_url('projects/gantt/'.$project->id); ?>" class="btn btn-info mt-3">View Gantt Chart</a>

<!-- You can integrate a JS Gantt library here using the tasks data -->
<?php $this->load->view('layout/footer'); ?> 