<?php $this->load->view('layout/header'); ?>
<div class="d-flex justify-content-between mb-3">
    <h3>Projects</h3>
    <a href="<?= site_url('projects/create'); ?>" class="btn btn-primary">Add Project</a>
</div>
<?php if (count($projects)): ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($projects as $proj): ?>
        <tr>
            <td><?= $proj->id; ?></td>
            <td><a href="<?= site_url('projects/tasks/'.$proj->id); ?>"><?= htmlspecialchars($proj->name, ENT_QUOTES, 'UTF-8'); ?></a></td>
            <td><?= $proj->created_at; ?></td>
            <td>
                <a href="<?= site_url('projects/edit/'.$proj->id); ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="<?= site_url('projects/delete/'.$proj->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this project?')">Delete</a>
                <a href="<?= site_url('projects/tasks/'.$proj->id); ?>" class="btn btn-sm btn-success">View Task</a>
                <a href="<?= site_url('projects/add_task/'.$proj->id); ?>" class="btn btn-sm btn-secondary">Add Task</a>
                <a href="<?= site_url('projects/gantt/'.$proj->id); ?>" class="btn btn-sm btn-info">View Gantt Chart</a>
                
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No projects found.</p>
<?php endif; ?>
<?php $this->load->view('layout/footer'); ?> 