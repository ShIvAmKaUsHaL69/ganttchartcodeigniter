<?php $this->load->view('layout/header'); ?>
<h3>Add Note to Project: <?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h3>

<ul class="nav nav-tabs mb-3" id="noteTabs" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="single-note-tab" data-toggle="tab" href="#singleNote" role="tab">Single Note</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="bulk-note-tab" data-toggle="tab" href="#bulkNoteUpload" role="tab">Bulk Upload</a>
  </li>
</ul>

<div class="tab-content" id="noteTabsContent">
  <div class="tab-pane fade show active" id="singleNote" role="tabpanel">
    <form method="post">
        <!-- Hidden flag so we know this is a note -->
        <input type="hidden" name="is_note_task" value="1">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="task_name">Note Title</label>
                    <input type="text" name="task_name" id="task_name" class="form-control" value="<?= set_value('task_name'); ?>">
                    <?= form_error('task_name', '<small class="text-danger">', '</small>'); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="assigned_to">Assigned To</label>
                    <input type="text" name="assigned_to" id="assigned_to" class="form-control" value="<?= set_value('assigned_to'); ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= set_value('start_date'); ?>">
                    <?= form_error('start_date', '<small class="text-danger">', '</small>'); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="expected_end_date">Expected End Date</label>
                    <input type="date" name="expected_end_date" id="expected_end_date" class="form-control" value="<?= set_value('expected_end_date'); ?>">
                    <?= form_error('expected_end_date', '<small class="text-danger">', '</small>'); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="progress">Progress (%)</label>
                    <input type="number" name="progress" id="progress" class="form-control" value="<?= set_value('progress', 0); ?>" min="0" max="100">
                    <?= form_error('progress', '<small class="text-danger">', '</small>'); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" onchange="toggleEndDate(this.value)">
                        <option value="0" <?= set_select('status', '0'); ?>>In Progress</option>
                        <option value="2" <?= set_select('status', '2'); ?>>Hold</option>
                        <option value="1" <?= set_select('status', '1'); ?>>Completed</option>
                    </select>
                </div>
                <div class="form-group" id="endDateGroup" style="display: none;">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?= set_value('end_date'); ?>">
                </div>
            </div>
            <script>
            function toggleEndDate(status) {
                var endDateGroup = document.getElementById('endDateGroup');
                if (status === '1') { // Completed
                    endDateGroup.style.display = 'block';
                } else {
                    endDateGroup.style.display = 'none';
                }
            }
            </script>
        </div>
        <div class="form-group">
            <button class="btn btn-danger">Save Note</button>
            <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
  </div>

  <div class="tab-pane fade" id="bulkNoteUpload" role="tabpanel">
    <form method="post" enctype="multipart/form-data" action="<?= site_url('projects/bulk_upload_notes/'.$project->id); ?>">
        <div class="form-group">
            <a href="<?= site_url('projects/download_task_template'); ?>" class="btn btn-info">
                <i class="fa fa-download"></i> Download Sample Excel
            </a>
        </div>
        <div class="form-group">
            <label for="excel_file">Upload Excel File</label>
            <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx, .xls, .csv">
            <small class="form-text text-muted">Excel file should have these columns: Note Title, Assigned To, Start Date, Expected End Date, Progress, Status</small>
            <?php if(isset($excel_error)): ?>
                <small class="text-danger"><?= $excel_error; ?></small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <button class="btn btn-danger">Upload and Save Notes</button>
            <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
  </div>
</div>
<?php $this->load->view('layout/footer'); ?> 