<?php $this->load->view('layout/header'); ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
    <h3 class="mb-3 mb-md-0">All Completed Projects</h3>
    <a href="<?= site_url('projects'); ?>" class="btn btn-primary">Back to Main List</a>
</div>

<?php if (isset($completed_projects) && count($completed_projects)): ?>
<div class="table-responsive">
    <table class="table table-bordered" id="completedProjectsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Completed At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $srno = 1;
        foreach ($completed_projects as $proj): ?>
            <tr>
                <td><?= $srno++; ?></td>
                <td><a href="<?= site_url('projects/tasks/'.$proj->id); ?>"><?= htmlspecialchars($proj->name, ENT_QUOTES, 'UTF-8'); ?></a></td>
                <td><?= $proj->created_by; ?></td>
                <td>Completed</td>
                <td><?= $proj->created_at; ?></td>
                <td><?= !empty($proj->completed_at) ? $proj->completed_at : 'N/A'; ?></td>
                <td>
                    <div class="btn-group-vertical btn-group-sm" style="gap: 10px; flex-direction: row;">
                        <a href="<?= site_url('projects/edit/'.$proj->id); ?>" class="btn btn-warning mb-1" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="<?= site_url('projects/tasks/'.$proj->id); ?>" class="btn btn-success mb-1" title="View Tasks"><i class="fas fa-tasks"></i></a>
                        <a href="<?= site_url('projects/gantt/'.$proj->id); ?>" class="btn btn-info" title="View Gantt Chart"><i class="fas fa-chart-bar"></i></a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p>No completed projects found.</p>
<?php endif; ?>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        if ($.fn.DataTable) {
            $('#completedProjectsTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 6 } // Assuming 'Actions' is the 7th column (index 6)
                ],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        } else {
            console.error('DataTables library not loaded properly for completedProjectsTable');
        }
    });
</script>

<?php $this->load->view('layout/footer'); ?> 