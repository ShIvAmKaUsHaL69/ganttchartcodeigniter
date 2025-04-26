<?php $this->load->view('layout/header'); ?>
<!-- Add required libraries -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #ganttChart, #ganttChart * {
        visibility: visible;
    }
    #ganttChart {
        position: absolute;
        left: 0;
        top: 0;
        transform-origin: top left;
        width: 100%;
        max-width: none !important;
        overflow: visible !important;
    }
    .gantt-container {
        overflow: visible !important;
        width: 100% !important;
        max-width: none !important;
    }
    .gantt-header, .gantt-row {
        width: 100% !important;
        max-width: none !important;
    }
    .gantt-timeline {
        width: 100% !important;
        min-width: 100% !important;
    }
    .no-print {
        display: none !important;
    }
    .print-title {
        display: block !important;
        text-align: start;
        margin-bottom: 20px;
    }
    @page {
        size: landscape;
        margin: 1cm;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3>Gantt Chart – <?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h3>
        <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary no-print">Back to Tasks</a>
    </div>
    <button id="exportPdf" class="btn btn-primary no-print"><i class="fas fa-file-pdf"></i> Export as PDF</button>
</div>

<?php
if (!count($tasks)) {
    echo '<p>No tasks found.</p>';
    $this->load->view('layout/footer');
    return;
}

// Calculate max task name length for dynamic column width
$maxTaskNameLength = 0;
foreach ($tasks as $t) {
    $taskNameLength = strlen($t->task_name);
    if ($taskNameLength > $maxTaskNameLength) {
        $maxTaskNameLength = $taskNameLength;
    }
}
// Set min width 150px, and add ~10px per character beyond 15 chars
$taskNameWidth = max(150, 150 + ($maxTaskNameLength - 15) * 8);

// Determine date range across all tasks
$minDateStr = null;
$maxDateStr = null;
foreach ($tasks as $t) {
    if ($minDateStr === null || $t->start_date < $minDateStr) $minDateStr = $t->start_date;
    if ($maxDateStr === null || $t->end_date > $maxDateStr) $maxDateStr = $t->end_date;
}

// Adjust range to encompass full weeks (Monday to Sunday)
$overallStart = new DateTime($minDateStr);
$overallEnd = new DateTime($maxDateStr);

// Find Monday of the starting week
if ($overallStart->format('N') != 1) { // N = 1 (for Monday) through 7 (for Sunday)
    $overallStart->modify('last monday');
}

// Find Sunday of the ending week
if ($overallEnd->format('N') != 7) {
    $overallEnd->modify('next sunday');
}

$interval = new DateInterval('P1W'); // Weekly interval
$periodEnd = clone $overallEnd;
$periodEnd->modify('+1 day'); // DatePeriod excludes end date
$period = new DatePeriod($overallStart, $interval, $periodEnd);

$weeks = [];
foreach ($period as $weekStartDate) {
    $weeks[] = $weekStartDate->format('Y-m-d');
}

$totalWeeks = count($weeks);
$weekWidthPercent = ($totalWeeks > 0) ? 100 / $totalWeeks : 0;

// Total days in the *displayed* range for accurate percentage calculation
$totalRangeDays = $overallEnd->diff($overallStart)->days + 1;

// Calculate task info width based on task name width
$taskInfoWidth = $taskNameWidth + 430; // 430px for other columns

?>

<style>
.gantt-container {
    position: relative;
    overflow-x: auto;
    padding: 10px;
}
.gantt-header {
    display: flex;
    padding-bottom: 5px;
    margin-bottom: 10px;
    font-size: 10px;
    font-weight: bold;
    min-width: <?= $totalWeeks * 70 ?>px; /* Estimate min width based on weeks */
}
.gantt-week-header {
    flex: 0 0 <?= $weekWidthPercent ?>%;
    white-space: nowrap;
    border-right: 1px dotted #eee;
    padding: 0 2px;
}
.gantt-row {
    display: flex;
    align-items: center;
    min-height: 50px;
    border-bottom: 1px dashed #eee;
    padding-bottom: 10px;
}
.gantt-task-info {
    display: flex;
    flex: 0 0 <?= $taskInfoWidth ?>px; /* Dynamic width based on task name */
    padding-right: 15px;
    font-size: 14px;
}
.task-column {
    padding: 0 10px;
    border-right: 1px solid #e0e0e0;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.task-name {
    width: <?= $taskNameWidth ?>px; /* Dynamic width */
    font-weight: bold;
}
.task-assignee {
    width: 100px;
}
.task-duration {
    width: 100px;
}
.task-dates {
    width: 150px;
}
.task-progress {
    width: 80px;
    border-right: none;
}
.gantt-timeline {
    flex-grow: 1;
    position: relative;
    height: 25px; /* Height of the timeline bar area */
    background: repeating-linear-gradient(
        to right,
        #fff,
        #fff calc(<?= $weekWidthPercent ?>% - 1px),
        #eee calc(<?= $weekWidthPercent ?>% - 1px),
        #eee <?= $weekWidthPercent ?>%
    );
    min-width: 100%; /* Estimate min width */
    border: 1px solid #0000004f;
    border-radius: 5px;
}
.gantt-bar {
    position: absolute;
    top: 0;
    height: 100%;
    background-color: #007bff; /* Blue */
    border-radius: 3px;
    opacity: 0.7;
}
.gantt-progress {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background-color: #28a745; /* Green */
    border-radius: 3px;
    opacity: 0.9;
}
</style>

<div id="ganttChart" class="gantt-container">
    <div class="print-title" style="display: none;">
        <h2><?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h2>
    </div>
    <!-- Header Row -->
    <div class="gantt-header">
        <div style="flex: 0 0 <?= $taskInfoWidth ?>px; padding-right: 15px; display: flex;" class='gantt-header-row'>
            <div class="task-column task-name">Task</div>
            <div class="task-column task-assignee">Assignee</div>
            <div class="task-column task-duration">Duration (Days)</div>
            <div class="task-column task-dates">Start Date</div>
            <div class="task-column task-dates">End Date</div>
            <div class="task-column task-progress">Expected <br>Progress</div>
        </div>
        <?php foreach ($weeks as $weekStartStr): ?>
            <div class="gantt-week-header">
                <?= (new DateTime($weekStartStr))->format('d M'); // Show week start date ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Task Rows -->
    <?php foreach ($tasks as $t):
        $taskStartDate = new DateTime($t->start_date);
        $taskEndDate   = new DateTime($t->end_date);

        // Ensure task dates are within the overall calculated range for safety
        if ($taskEndDate < $overallStart || $taskStartDate > $overallEnd) {
            continue;
        }
        // Clamp task dates to the overall range if they extend beyond
        $clampedTaskStart = max($taskStartDate, $overallStart);
        $clampedTaskEnd   = min($taskEndDate, $overallEnd);

        $taskOffsetDays = $clampedTaskStart->diff($overallStart)->days;
        $taskDurationDays = $clampedTaskEnd->diff($clampedTaskStart)->days + 1;

        // Calculate percentages based on the total *days* in the displayed range
        $barLeftPercent = ($totalRangeDays > 0) ? ($taskOffsetDays / $totalRangeDays) * 100 : 0;
        $barWidthPercent = ($totalRangeDays > 0) ? ($taskDurationDays / $totalRangeDays) * 100 : 0;

        // Original duration for display
        $originalDurationDays = $taskEndDate->diff($taskStartDate)->days + 1;
    ?>
        <div class="gantt-row">
            <div class="gantt-task-info">
                <div class="task-column task-name" title="<?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="task-column task-assignee"><?= htmlspecialchars($t->assigned_to ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="task-column task-duration"><?= $originalDurationDays ?></div>
                <div class="task-column task-dates"><?= $t->start_date ?></div>
                <div class="task-column task-dates"><?= $t->end_date ?></div>
                <div class="task-column task-progress"><?= $t->progress ?>%</div>
            </div>
            <div class="gantt-timeline">
                <div class="gantt-bar" 
                     style="left: <?= $barLeftPercent ?>%; width: <?= $barWidthPercent ?>%;"
                     title="Task: <?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?> &#10;Assignee: <?= htmlspecialchars($t->assigned_to ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?> &#10;Start Date: <?= $t->start_date ?> &#10;End Date: <?= $t->end_date ?> &#10;Progress: <?= $t->progress ?>% &#10;Duration: <?= $taskDurationDays ?> days">
                    <div class="gantt-progress" style="width: <?= $t->progress ?>%;"></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.getElementById('exportPdf').addEventListener('click', function() {
    // Prepare chart for printing
    var ganttChart = document.getElementById('ganttChart');
    var fullWidth = ganttChart.scrollWidth;
    
    // Store original styles to restore after printing
    var originalOverflow = ganttChart.style.overflow;
    var originalWidth = ganttChart.style.width;
    var originalTransform = ganttChart.style.transform;
    
    // Set explicit width for printing
    ganttChart.style.overflow = 'visible';
    ganttChart.style.width = fullWidth + 'px';
    
    // Calculate scale factor to fit the chart on the page
    // A typical landscape A4 page is around 1123px wide in most browsers' print preview
    var targetWidth = 1700; // Standard for most print layouts in landscape
    var scaleRatio = 1;
    
    if (fullWidth > targetWidth) {
        scaleRatio = targetWidth / fullWidth;
        ganttChart.style.transform = 'scale(' + scaleRatio + ')';
    }
    
    // Use timeout to allow browser to apply the style changes
    setTimeout(function() {
        window.print();
        
        // Restore original styles after printing
        setTimeout(function() {
            ganttChart.style.overflow = originalOverflow;
            ganttChart.style.width = originalWidth;
            ganttChart.style.transform = originalTransform;
        }, 500);
    }, 100);
});
</script>

<?php $this->load->view('layout/footer'); ?> 