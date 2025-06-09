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
            <style>
                .select2-container .select2-selection--single {
                    height: 36px !important;
                }
                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    line-height: 34px !important;
                }
            </style>    
            <div class="col-md-6">
                <div class="form-group">
                    <label for="assigned_to">Assigned To</label>
                    <select name="assigned_to" id="assigned_to" class="form-control select2">
                        <option value="">Select User</option>
                        <option value="Abhishek">Abhishek</option>
                        <option value="Chirag">Chirag</option>
                        <option value="Gautam">Gautam</option>
                        <option value="Khushboo">Khushboo</option>
                        <option value="Lovely">Lovely</option>
                        <option value="Neeraj">Neeraj</option>
                        <option value="Paltan">Paltan</option>
                        <option value="Sahil">Sahil</option>
                        <option value="Sudipto">Sudipto</option>
                        <option value="Ajay">Ajay</option>
                        <option value="Akshat">Akshat</option>
                        <option value="Ashutosh">Ashutosh</option>
                        <option value="Deepti">Deepti</option>
                        <option value="Dhruv">Dhruv</option>
                        <option value="Divish">Divish</option>
                        <option value="Ganesh">Ganesh</option>
                        <option value="Harsh">Harsh</option>
                        <option value="Homan">Homan</option>
                        <option value="Karan">Karan</option>
                        <option value="Madhav">Madhav</option>
                        <option value="Manu">Manu</option>
                        <option value="Mehak">Mehak</option>
                        <option value="Neeru">Neeru</option>
                        <option value="Priyanshu">Priyanshu</option>
                        <option value="Ravi">Ravi</option>
                        <option value="Reshav">Reshav</option>
                        <option value="Rupali">Rupali</option>
                        <option value="Sameer">Sameer</option>
                        <option value="Rupali">Rupali</option>
                        <option value="Sanchit">Sanchit</option>
                        <option value="Shivam">Shivam</option>
                        <option value="Sandeep">Sandeep</option>
                        <option value="Sarthak">Sarthak</option>
                    </select>
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

<!-- Initialize Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select User",
            allowClear: true
        });
    });
</script> 