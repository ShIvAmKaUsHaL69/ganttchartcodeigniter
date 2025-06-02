<?php $this->load->view('layout/header'); ?>
<h3>Tasks for Project: <?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h3>
<div class="d-flex flex-wrap mb-3 ">
    <a href="<?= site_url('projects'); ?>" class="btn btn-secondary mr-2 mb-2">Back to Projects</a>
    <a href="<?= site_url('projects/add_task/'.$project->id); ?>" class="btn btn-primary mb-2 mr-2">Add Task</a>
    <a href="<?= site_url('projects/add_note/'.$project->id); ?>" class="btn btn-danger mb-2">Add Change-Note</a>
    <a href="<?= site_url('projects/gantt/'.$project->id); ?>" class="btn btn-info mb-2 ml-2">View Gantt Chart</a>
</div>
<div class="d-flex flex-wrap mb-3" style="gap: 20px;">
<p class="font-weight-bold">Color Legend:</p>
<div class="d-flex flex-wrap mb-3">
    <div class="legend-item" style="background-color: #f8d7da; width: 20px; height: 20px; border: 1px solid #ddd;"></div>
    <span style="width: 10px;"></span>
    <span class="legend-text">Change-Note Task</span>
</div>
<div class="d-flex flex-wrap mb-3">
    <div class="legend-item" style="background-color: #eed19a; width: 20px; height: 20px; border: 1px solid #ddd;"></div>
    <span style="width: 10px;"></span>
    <span class="legend-text">Discarded Task</span>
</div>
</div>

<?php if (count($tasks)): ?>
<div class="table-responsive">
    <table class="table table-bordered" id="tasksTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Task Name</th>
                <th>Assigned To</th>
                <th>Duration</th>
                <th>Start Date</th>
                <th>Expected End Date</th>
                <th>End Date</th>
                <th>Progress</th>
                <th>Last Updated</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $srno = 1;
        foreach ($tasks as $t): ?>
            <tr <?= isset($t->is_note_task) && $t->is_note_task == 1 ? 'style="background-color: #f8d7da;"' : ($t->status == 3 ? 'style="background-color: #eed19a"' : ''); ?>>
                <td><?= $srno++; ?></td>
                <td><?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($t->assigned_to, ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <?php 
                    $duration = (strtotime($t->expected_end_date) - strtotime($t->start_date)) / (60 * 60 * 24);
                    $actual_duration = $t->end_date ? (strtotime($t->end_date) - strtotime($t->start_date)) / (60 * 60 * 24) : null;
                    $diff = $actual_duration ? $actual_duration - $duration : 0;
                    echo $duration . ' days';
                    if ($t->status == 1 && $actual_duration) { // Only show if task is completed
                        echo ' (' . ($diff > 0 ? '+' : '') . $diff . ')';
                    }
                    ?>
                </td>
                <td><?= $t->start_date; ?></td>
                <td><?= $t->expected_end_date; ?></td>
                <td><?= $t->end_date ? $t->end_date : 'Not Completed'; ?></td>
                <td><?= $t->progress; ?>%</td>
                <td><?= $t->modified_at; ?></td>
                <td><?= $t->status == 0 ? 'In Progress' : ($t->status == 1 ? 'Completed' : ($t->status == 2 ? 'Hold' : 'Discarded')); ?></td>
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

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        if ($.fn.DataTable) {
            $('#tasksTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 10 }, // Disable sorting on the Actions column
                    { "type": "date", "targets": [4, 5, 6, 8] } // Set date columns for proper sorting
                ],
                "order": [[0, 'asc']],
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

<!-- You can integrate a JS Gantt library here using the tasks data -->
<?php $this->load->view('layout/footer'); ?> 