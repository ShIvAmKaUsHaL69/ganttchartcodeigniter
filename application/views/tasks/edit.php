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
                            <option value="3" <?= $task->status == 3 ? 'selected' : ''; ?>>Discarded</option>
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