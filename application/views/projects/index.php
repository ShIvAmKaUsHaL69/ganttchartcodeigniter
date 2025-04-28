<?php $this->load->view('layout/header'); ?>
<div class="d-flex flex-column flex-md-row justify-content-between mb-3">
    <h3 class="mb-3 mb-md-0"> </h3>
    <h3 class="mb-3 mb-md-0">Gantt Chart Projects</h3>
    <a href="<?= site_url('projects/create'); ?>" class="btn btn-primary">Add Project</a>
</div>
<?php if (count($projects)): ?>
<div class="table-responsive">
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
        <?php
        $srno = 1;
        foreach ($projects as $proj): ?>
            <tr>
                <td><?= $srno++; ?></td>
                <td><a href="<?= site_url('projects/tasks/'.$proj->id); ?>"><?= htmlspecialchars($proj->name, ENT_QUOTES, 'UTF-8'); ?></a></td>
                <td><?= $proj->created_at; ?></td>
                <td>
                    <div class="btn-group-vertical btn-group-sm" style="gap: 10px; flex-direction: row;">
                        <a href="<?= site_url('projects/edit/'.$proj->id); ?>" class="btn btn-warning mb-1" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="<?= site_url('projects/tasks/'.$proj->id); ?>" class="btn btn-success mb-1" title="View Tasks"><i class="fas fa-tasks"></i></a>
                        <a href="<?= site_url('projects/add_task/'.$proj->id); ?>" class="btn btn-secondary mb-1" title="Add Task"><i class="fas fa-plus"></i></a>
                        <a href="<?= site_url('projects/gantt/'.$proj->id); ?>" class="btn btn-info" title="View Gantt Chart"><i class="fas fa-chart-bar"></i></a>
                    </div>
                    <div class="btn-group btn-group-sm d-none " style="gap: 10px;">
                        <a href="<?= site_url('projects/edit/'.$proj->id); ?>" class="btn btn-warning">Edit</a>
                        <a href="<?= site_url('projects/tasks/'.$proj->id); ?>" class="btn btn-success">View Task</a>
                        <a href="<?= site_url('projects/add_task/'.$proj->id); ?>" class="btn btn-secondary">Add Task</a>
                        <a href="<?= site_url('projects/gantt/'.$proj->id); ?>" class="btn btn-info">View Gantt Chart</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p>No projects found.</p>
<?php endif; ?>
<?php $this->load->view('layout/footer'); ?> 