<?php $this->load->view('layout/header'); ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <h3 class="mb-3 mb-md-0">Gantt Chart Projects</h3>
    <div class="btn-group">
        <a href="<?= site_url('projects/create'); ?>" class="btn btn-primary">Add Project</a>
        <a href="<?= site_url('projects/all_completed'); ?>" class="btn btn-info ml-md-2 mt-2 mt-md-0">View All Completed</a>
    </div>
</div>
<?php
$projects_to_display = [];
if (isset($projects) && is_array($projects)) {
    $one_week_ago = new DateTime('-7 days');
    $now = new DateTime(); // To ensure completed_at is not in the future

    foreach ($projects as $proj) {
        $display = false;
        if ($proj->status != 1) { // Not completed (In Progress, Hold, etc.)
            $display = true;
        } else { // Status is 1 (Completed)
            if (!empty($proj->completed_at)) {
                try {
                    $completed_date = new DateTime($proj->completed_at);
                    // Ensure completed_date is not in the future and is within the last 7 days
                    if ($completed_date <= $now && $completed_date >= $one_week_ago) {
                        $display = true;
                    }
                } catch (Exception $e) {
                    // Optional: Log error for invalid date format
                    // error_log("Invalid date format for project ID " . $proj->id . ": " . $proj->completed_at);
                }
            }
        }
        if ($display) {
            $projects_to_display[] = $proj;
        }
    }
}
?>
<?php if (count($projects_to_display)): ?>
<div class="table-responsive">
    <table class="table table-bordered" id="projectsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $srno = 1;
        foreach ($projects_to_display as $proj): ?>
            <tr>
                <td><?= $srno++; ?></td>
                <td><a href="<?= site_url('projects/tasks/'.$proj->id); ?>"><?= htmlspecialchars($proj->name, ENT_QUOTES, 'UTF-8'); ?></a></td>
                <td><?= htmlspecialchars($proj->creator_username ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= $proj->status == 0 ? 'In Progress' : ($proj->status == 1 ? 'Completed' : 'Hold'); ?></td>
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
<p>No projects found matching the criteria (active or recently completed).</p>
<?php endif; ?>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        if ($.fn.DataTable) {
            $('#projectsTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        } else {
            console.error('DataTables library not loaded properly');
        }
    });
</script>

<?php $this->load->view('layout/footer'); ?> 