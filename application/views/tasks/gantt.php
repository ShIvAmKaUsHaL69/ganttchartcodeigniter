<?php $this->load->view('layout/header'); ?>

<!-- Add required libraries -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #ganttChart, #ganttChart *, .color-legend-box, .color-legend-box *, .print-title, .print-title * {
        visibility: visible;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    #ganttChart {
        position: relative;
        transform-origin: top left;
        width: 100%;
        max-width: none !important;
        overflow: visible !important;
    }
    .gantt-container {
        overflow: visible !important;
        width: auto !important;
        max-width: none !important;
    }
    .gantt-header, .gantt-row {
        width: auto !important;
        max-width: none !important;
    }
    .gantt-timeline {
        min-width: <?= $timelineWidth ?>px !important;
        width: auto !important;
    }
    .no-print {
        display: none !important;
    }
    .print-title {
        display: block !important;
        text-align: start;
        margin-bottom: 20px;
    }
    .tooltiptext {
        display: none !important;
        visibility: hidden !important;
    }
    @page {
        size: landscape;
        margin: 0 0.5cm;
    }

    /* Added style for the legend in print */
    .color-legend-box {
        display: block !important;
        position: relative; /* Keep it in flow */
        margin-bottom: 20px; /* Space before the chart */
    }
    .hide-on-print {
        display: none !important;
    }
}
</style>

<?php 
// Determine if we're viewing in share mode
$is_shared_view = $this->router->fetch_class() === 'share';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3>Gantt Chart – <?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h3>
        <?php if (!$is_shared_view): ?>
            <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary no-print">Back to Tasks</a>
        <?php endif; ?>
    </div>
    <?php if (!$is_shared_view): ?>
    <div class="d-flex flex-column flex-md-row">
        <button id="exportPdf" class="btn btn-primary no-print"><i class="fas fa-file-pdf"></i> Export as PDF</button>
        <button id="generateShareLink" class="btn btn-info ml-md-2 no-print"><i class="fas fa-share-alt"></i> Share</button>
    </div>
    <?php else: ?>
    <div class="d-flex flex-column flex-md-row">
        <button id="exportPdf" class="btn btn-primary no-print"><i class="fas fa-file-pdf"></i> Export as PDF</button>
    </div>
    <?php endif; ?>
</div>


<div class="input-group mb-3 mr-md-2 no-print">
    <input type="text" id="ganttSearch" class="form-control" placeholder="Search tasks, assignees, status...">
</div>


<div class="print-title" style="display: none; margin-top: -50px;">
    <h2><?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h2>
</div>

<div class="color-legend-box mb-3">
    <h6 class="mb-2">Color Legend:</h6>
    <div class="d-flex flex-wrap">
        <div class="legend-item">
            <span class="color-box" style="background-color: #71b6ff; opacity: 0.7;"></span>
            <span class="legend-text">Task Duration (Start to Expected End)</span>
        </div>
        <div class="legend-item">
            <span class="color-box" style="background-color: #f5dcf8; border: 1px solid #ddd"></span>
            <span class="legend-text">Weekend (Saturday/Sunday)</span>
        </div>
        <div class="legend-item">
            <span class="color-box" style="background-color: #eed19a;"></span>
            <span class="legend-text">Change-Note Task</span>
        </div>
        <?php if (!$is_shared_view): ?>
        <div class="legend-item hide-on-print">
            <span class="color-box" style="background-color: #f56767; opacity: 0.7;"></span>
            <span class="legend-text">Late Completion</span>
        </div>
        <?php endif; ?>
        <div class="legend-item">
            <span class="color-box" style="background-color: #4eff77; opacity: 0.9;"></span>
            <span class="legend-text">Completed Task</span>
        </div>
        <div class="legend-item">
            <span class="color-box" style="background-color: #b1ff6e6e;"></span>
            <span class="legend-text">Discarded Task</span>
        </div>
    </div>
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
    if (!empty($t->start_date) && ($minDateStr === null || $t->start_date < $minDateStr)) {
        $minDateStr = $t->start_date;
    }
    
    // Check both end_date and expected_end_date to determine the latest date
    if (!empty($t->end_date) && ($maxDateStr === null || $t->end_date > $maxDateStr)) {
        $maxDateStr = $t->end_date;
    }
    
    if (!empty($t->expected_end_date) && ($maxDateStr === null || $t->expected_end_date > $maxDateStr)) {
        $maxDateStr = $t->expected_end_date;
    }
}

// Provide fallback dates if not found
if ($minDateStr === null) {
    $minDateStr = date('Y-m-d'); // Use today as fallback
}
if ($maxDateStr === null) {
    // If we have a start date but no end date, default to 2 weeks from start
    if ($minDateStr !== null) {
        $tempDate = new DateTime($minDateStr);
        $tempDate->modify('+2 weeks');
        $maxDateStr = $tempDate->format('Y-m-d');
    } else {
        // Both dates null, use today + 2 weeks
        $maxDateStr = date('Y-m-d', strtotime('+2 weeks'));
    }
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

// Create array of days for consistent positioning
$dayInterval = new DateInterval('P1D'); // Daily interval
$dayPeriodEnd = clone $overallEnd;
$dayPeriodEnd->modify('+1 day'); // DatePeriod excludes end date
$dayPeriod = new DatePeriod($overallStart, $dayInterval, $dayPeriodEnd);

$days = [];
foreach ($dayPeriod as $day) {
    $days[] = $day->format('Y-m-d');
}

$totalDays = count($days);
$dayWidthPx = 20; // Width per day in pixels
$timelineWidth = $totalDays * $dayWidthPx;

// Create weeks for header
$interval = new DateInterval('P1W'); // Weekly interval
$periodEnd = clone $overallEnd;
$periodEnd->modify('+1 day'); // DatePeriod excludes end date
$period = new DatePeriod($overallStart, $interval, $periodEnd);

$weeks = [];
foreach ($period as $weekStartDate) {
    $weeks[] = $weekStartDate->format('Y-m-d');
}

$totalWeeks = count($weeks);

// Calculate task info width based on task name width
$taskInfoWidth = $taskNameWidth + 910; // 60 (SrNo) + 150 (Assignee) + 100 (Duration) + 150 (Start) + 150 (Expected) + 150 (End) + 150 (Status) = 910px for other columns
?>

<style>
.gantt-container {
    position: relative;
    height: calc(100vh - 300px);
    overflow: hidden;
    padding: 10px;
    display: flex;
    flex-direction: column;
}

.gantt-wrapper {
    position: relative;
    flex: 1;
    overflow: auto;
    min-height: 0;
}

.gantt-header {
    display: flex;
    font-size: 10px;
    font-weight: bold;
    position: sticky;
    top: 0;
    background: white;
    z-index: 101;
    width: <?= $timelineWidth + 500 ?>px;
}

.gantt-header::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    pointer-events: none;
}

.gantt-task-info {
    display: flex;
    flex-shrink: 0;
    position: sticky;
    left: 0;
    background: white;
    z-index: 100;
    padding: 5px 0px;
}

.gantt-task-info-fixed {
    display: flex;
    position: sticky;
    left: 0;
    background: white;
    z-index: 100;
    padding: 4px 0px;
}

.gantt-task-info-scrollable {
    display: flex;
    background: white;
}

.gantt-row {
    display: flex;
    align-items: center;
    min-height: 50px;
    border-bottom: 1px dashed #eee;
    padding: 10px 0;
    width: <?= $timelineWidth + 500 ?>px;
    font-size: 14px;
}

.gantt-timeline {
    flex-grow: 1;
    position: relative;
    height: 25px;
    min-width: <?= $timelineWidth ?>px;
    border: 1px solid #0000004f;
    border-radius: 5px;
}

.task-column {
    padding: 0 10px;
    border-right: 1px solid #e0e0e0;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    background: white;
}

.gantt-timeline-header {
    position: relative;
    min-width: <?= $timelineWidth ?>px;
    height: 30px;
    background: white;
}

.gantt-week-header {
    display: flex;
    align-items: center;
    justify-content: start;
    border-right: 1px dotted #000;
    padding: 0 2px;
    height: 30px;
    background: white;
}

.task-sr-no {
    width: 60px;
}

.task-name {
    width: <?= $taskNameWidth ?>px;
    font-weight: bold;
}

.task-assignee {
    width: 150px;
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

.gantt-bar {
    position: absolute;
    top: 0;
    height: 100%;
    background-color: #71b6ff; /* Blue */
    border-radius: 3px;
    opacity: 0.7;
    cursor: pointer;
    z-index: 1;
}

.gantt-progress {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background-color: #4eff77; /* Green */
    border-radius: 3px;
    opacity: 0.9;
}

.day-grid-line {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 1px;
    background-color: #ddd;
}

.week-grid-line {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 1px;
    background-color: #943030;
    z-index: 2;
}

/* Custom Tooltip Styles */
.tooltip {
  position: relative;
  cursor: pointer;
}

.tooltiptext {
  display: none; /* Changed from visibility:hidden */
  width: 220px;
  background-color: #000;
  color: #fff;
  text-align: left;
  border-radius: 6px;
  padding: 10px;
  line-height: 1.4;
  font-size: 12px;
  
  /* Position the tooltip */
  position: fixed; /* Changed from absolute */
  z-index: 9999;
  pointer-events: none;
}

/* Weekend highlight style */
.weekend-highlight {
  position: absolute;
  top: 0;
  height: 100%;
  background-color: #f5dcf8; /* Light yellow */
  z-index: 4;
  pointer-events: none; /* Allow mouse events to pass through */
}

/* Performance overlay styles */
.gantt-performance {
    position: absolute;
    top: 0;
    height: 100%;
    opacity: 0.4;
    z-index: 3;
}

/* Hide previously added header-level day boxes */
.day-of-week-box {
    display: none !important;
}

/* Day-of-week cell inside each task row */
.day-of-week-cell {
    position: absolute;
    top: 0;
    height: 100%;
    font-size: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #555;
    pointer-events: none;
    width: <?= $dayWidthPx ?>px;
    z-index: 10; /* Behind bars */
}

.color-legend-box {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px 15px;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-right: 20px;
    margin-bottom: 5px;
}

.color-box {
    display: inline-block;
    width: 20px;
    height: 15px;
    margin-right: 5px;
    border-radius: 3px;
}

.legend-text {
    font-size: 13px;
}

/* Ensure content scrolls under fixed header */
.gantt-container > div:not(.gantt-header) {
    position: relative;
    z-index: 1;
}

/* Ensure the timeline header stays in place */
.gantt-timeline-header {
    position: relative;
    min-width: <?= $timelineWidth ?>px;
    height: 30px;
    background: white;
}

/* Ensure task info stays fixed with header */
.gantt-task-info {
    display: flex;
    width: <?= $taskInfoWidth ?>px;
    padding: 5px 0px;
    flex-shrink: 0;
    position: sticky;
    left: 0;
    background: white;
    z-index: 100;
}
</style>

<div id="ganttChart" 
     class="gantt-container" 
     data-overall-start="<?= $overallStart->format('Y-m-d') ?>" 
     data-day-width="<?= $dayWidthPx ?>">
    
    <div class="gantt-wrapper">
        <!-- Header -->
        <div class="gantt-header">
            <div class="gantt-task-info-fixed">
                <div class="task-column task-sr-no">Sr No.</div>
                <div class="task-column task-name">Task</div>
                <div class="task-column task-assignee">Assignee</div>
            </div>
            <div class="gantt-task-info-scrollable">
                <div class="task-column task-duration">Work Days <br> (Total Days)</div>
                <div class="task-column task-dates">Start Date</div>
                <div class="task-column task-dates">Expected End Date</div>
                <div class="task-column task-dates">End Date</div>
                <div class="task-column task-dates">Status</div>
            </div>
            <div class="gantt-timeline-header">
                <?php foreach ($weeks as $index => $weekStartStr): 
                    $weekStart = new DateTime($weekStartStr);
                    $weekWidth = 0;
                    $currentDay = clone $weekStart;
                    $dayCount = 0;
                    
                    while ($dayCount < 7 && $currentDay <= $overallEnd) {
                        $weekWidth += $dayWidthPx;
                        $currentDay->modify('+1 day');
                        $dayCount++;
                    }
                    
                    $daysFromStart = $weekStart->diff($overallStart)->days;
                    $leftPosition = $daysFromStart * $dayWidthPx;
                ?>
                    <div class="gantt-week-header" style="position: absolute; left: <?= $leftPosition ?>px; width: <?= $weekWidth ?>px;">
                        <?= $weekStart->format('d M Y'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Task Rows -->
        <?php 
        $srNo = 1;
        foreach ($tasks as $t):
            $taskStartDate = new DateTime($t->start_date);
            $taskEndDate   = new DateTime($t->expected_end_date);

            if ($taskEndDate < $overallStart || $taskStartDate > $overallEnd) {
                continue;
            }
            
            $clampedTaskStart = max($taskStartDate, $overallStart);
            $clampedTaskEnd   = min($taskEndDate, $overallEnd);
            
            $taskOffsetDays = $clampedTaskStart->diff($overallStart)->days;
            $taskDurationDays = $clampedTaskEnd->diff($clampedTaskStart)->days + 1;
            
            $barLeftPx = $taskOffsetDays * $dayWidthPx;
            $barWidthPx = $taskDurationDays * $dayWidthPx;

            // Calculate business days
            $businessDays = 0;
            $currentDate = clone $taskStartDate;
            while ($currentDate <= $taskEndDate) {
                $dayOfWeek = (int)$currentDate->format('N');
                if ($dayOfWeek < 6) {
                    $businessDays++;
                }
                $currentDate->modify('+1 day');
            }
            
            $originalDurationDays = $taskEndDate->diff($taskStartDate)->days + 1;
        ?>
            <div class="gantt-row" <?php if (isset($t->is_note_task) && $t->is_note_task == 1): ?>style="background-color: #eed19a;" <?php elseif ($t->status == 3): ?>style="background-color: #b1ff6e6e" <?php endif; ?>>
                <div class="gantt-task-info-fixed">
                    <div class="task-column task-sr-no"><?= $srNo++ ?></div>
                    <div class="task-column task-name" title="<?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="task-column task-assignee"><?= htmlspecialchars($t->assigned_to ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="gantt-task-info-scrollable">
                    <div class="task-column task-duration"><?= $businessDays ?> (<?= $originalDurationDays ?>)</div>
                    <div class="task-column task-dates"><?= $t->start_date ?></div>
                    <div class="task-column task-dates"><?= $t->expected_end_date ?></div>
                    <div class="task-column task-dates"><?= $t->end_date ? $t->end_date : 'Not Completed' ?></div>
                    <div class="task-column task-dates"><?= $t->status == 0 ? 'In Progress' : ($t->status == 1 ? 'Completed' : ($t->status == 2 ? 'Hold' : 'Discarded')) ?></div>
                </div>
                <div class="gantt-timeline">
                    <!-- Week grid lines -->
                    <?php foreach ($weeks as $weekStartStr): 
                        $weekStart = new DateTime($weekStartStr);
                        $daysFromStart = $weekStart->diff($overallStart)->days;
                        $leftPosition = $daysFromStart * $dayWidthPx;
                    ?>
                        <div class="week-grid-line" style="left: <?= $leftPosition ?>px;"></div>
                    <?php endforeach; ?>
                    
                    <!-- Day grid lines (lighter) -->
                    <?php for ($i = 0; $i < $totalDays; $i++): ?>
                        <div class="day-grid-line" style="left: <?= $i * $dayWidthPx ?>px;"></div>
                    <?php endfor; ?>
                    
                    <!-- Day letters for this row (M T W T F S S) -->
                    <?php for ($di = 0; $di < $totalDays; $di++):
                            $currentDay = clone $overallStart;
                            $currentDay->modify('+' . $di . ' days');
                            $dayLetter = strtoupper(substr($currentDay->format('D'), 0, 1));
                    ?>
                        <div class="day-of-week-cell" style="left: <?= $di * $dayWidthPx ?>px;">
                            <?= $dayLetter ?>
                        </div>
                    <?php endfor; ?>
                    
                    <!-- Weekend highlights -->
                    <?php 
                    for ($i = 0; $i < $totalDays; $i++): 
                        $currentDay = clone $overallStart;
                        $currentDay->modify('+' . $i . ' days');
                        $dayOfWeek = (int)$currentDay->format('N'); // 1 (Monday) to 7 (Sunday)
                        
                        // Check if it's Saturday (6) or Sunday (7)
                        if ($dayOfWeek == 6 || $dayOfWeek == 7): 
                    ?>
                        <div class="weekend-highlight" style="left: <?= $i * $dayWidthPx ?>px; width: <?= $dayWidthPx ?>px;"></div>
                    <?php 
                        endif;
                    endfor; 
                    ?>
                    
                    <!-- Performance overlay for early / late completion -->
                    <?php  
                        if (!empty($t->end_date)) {
                            unset($perfColor); // Reset perfColor
                            $actualEnd = new DateTime($t->end_date);
                            $expectedEnd = clone $taskEndDate; // already set to expected_end_date
                            
                            // Finished late – red boxes between expectedEnd+1 and actualEnd
                            if ($actualEnd > $expectedEnd) { 
                                $colorStart = clone $expectedEnd;
                                $colorStart->modify('+1 day');
                                $colorEnd = clone $actualEnd;
                                $perfColor = $is_shared_view == true ? '#fff00' : '#f56767'; // red
                            }

                            if (isset($perfColor)) {
                                // Ensure dates lie within overall range
                                if ($colorStart < $overallStart) $colorStart = clone $overallStart;
                                if ($colorEnd > $overallEnd) $colorEnd = clone $overallEnd;

                                if ($colorStart <= $colorEnd) { // Check for valid duration
                                    $perfOffsetDays = $colorStart->diff($overallStart)->days;
                                    $perfDurationDays = $colorEnd->diff($colorStart)->days + 1;
                                    $perfLeftPx = $perfOffsetDays * $dayWidthPx;
                                    $perfWidthPx = $perfDurationDays * $dayWidthPx;
                                    
                                    $performanceDivClass = "gantt-performance";
                                    if ($perfColor == '#f56767') { // Add class to hide late completion bar on print
                                        $performanceDivClass .= " hide-on-print";
                                    }
                    ?>
                                    <div class="<?= $performanceDivClass ?>" style="left: <?= $perfLeftPx ?>px; width: <?= $perfWidthPx ?>px; background-color: <?= $perfColor ?>; opacity: 0.7 !important; border-radius: 3px;"></div>
                    <?php       
                                } // end if $colorStart <= $colorEnd
                            } // end if isset($perfColor)
                        }
                        ?>
                    
                    <?php
                    $ganttBarStyle = "left: {$barLeftPx}px; width: {$barWidthPx}px;";
                    if (isset($t->status) && $t->status == 1) { // Completed task
                        $ganttBarStyle .= " background-color: #4eff77; opacity: 0.9;"; // Green
                    } else { // Default/In-progress task
                        $ganttBarStyle .= " background-color: #71b6ff; opacity: 0.7;"; // Blue (original style from .gantt-bar)
                    }
                    ?>
                    <div class="gantt-bar tooltip" style="<?= $ganttBarStyle ?>">
                        <div class="tooltiptext">
                            <strong>Task:</strong> <?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?><br>
                            <strong>Assignee:</strong> <?= htmlspecialchars($t->assigned_to ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?><br>
                            <strong>Start Date:</strong> <?= $t->start_date ?><br>
                            <strong>Expected End Date:</strong> <?= $t->expected_end_date ?><br>
                            <strong>End Date:</strong> <?= $t->end_date ? $t->end_date : 'Not Completed' ?><br>
                            <strong>Duration:</strong> <?= $businessDays ?> work days (<?= $originalDurationDays ?> days)<br>
                            <strong>Status:</strong> <?= $t->status == 0 ? 'In Progress' : ($t->status == 1 ? 'Completed' : 'Hold') ?><br>
                            <span class="hovered-date-line" style="display: none;"><strong>Date:</strong> <span class="hovered-date-value font-weight-bold" style="color: yellow;"></span><br></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
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
        // ganttChart.style.transform = 'scale(' + scaleRatio + ')';
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

// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ganttSearch');
    const ganttRows = document.querySelectorAll('.gantt-row');
    
    // Improved scroll to current week functionality
    function scrollToCurrentWeek() {
        const ganttWrapper = document.querySelector('.gantt-wrapper');
        const taskInfoFixed = document.querySelector('.gantt-task-info-fixed');
        if (ganttWrapper && taskInfoFixed) {
            const today = new Date();
            const overallStartDate = new Date(document.getElementById('ganttChart').dataset.overallStart);
            const dayWidth = parseFloat(document.getElementById('ganttChart').dataset.dayWidth);
            
            // Calculate days between overall start and today
            const diffTime = today.getTime() - overallStartDate.getTime();
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            
            // Calculate scroll position (center the current week)
            // Account for fixed columns width and add offset
            const fixedColumnsWidth = taskInfoFixed.offsetWidth;
            const twoWeeksWidth = 70 * dayWidth; // Add two weeks worth of pixels
            const scrollPosition = (diffDays * dayWidth) - ((ganttWrapper.offsetWidth - fixedColumnsWidth) / 2) + twoWeeksWidth;
            
            // Scroll to position with a slight delay to ensure rendering is complete
            setTimeout(() => {
                ganttWrapper.scrollLeft = Math.max(0, scrollPosition);
            }, 100);
        }
    }
    
    // Call the function after a short delay to ensure all elements are rendered
    setTimeout(scrollToCurrentWeek, 200);
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        ganttRows.forEach(function(row) {
            // Extract searchable text from the row
            const taskName = row.querySelector('.task-name').textContent.toLowerCase();
            const assignee = row.querySelector('.task-assignee').textContent.toLowerCase();
            const status = row.querySelector('.task-column:nth-last-child(1)').textContent.toLowerCase();
            
            // Check if any of the fields match the search term
            if (taskName.includes(searchTerm) || 
                assignee.includes(searchTerm) || 
                status.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Clear search on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            ganttRows.forEach(function(row) {
                row.style.display = '';
            });
        }
    });
    
    // Title change
    
    const title = document.querySelector("head > title")
    title.text = '<?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?>'
    
    /* Share link generation */
    const shareBtn = document.getElementById('generateShareLink');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            // Disable button to prevent multiple clicks
            shareBtn.disabled = true;

            fetch('<?= site_url('projects/generate_share_link/'.$project->id); ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    shareBtn.disabled = false;
                    if (data.url) {
                        // Try to copy to clipboard
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(data.url)
                                .then(() => alert('Share link copied to clipboard!\n' + data.url))
                                .catch(() => window.prompt('Copy the share link:', data.url));
                        } else {
                            window.prompt('Copy the share link:', data.url);
                        }
                    } else if (data.error) {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(() => {
                    shareBtn.disabled = false;
                    alert('An error occurred while generating the share link.');
                });
        });
    }

    // New tooltip functionality
    const tooltips = document.querySelectorAll('.tooltip');
    const tooltipTexts = document.querySelectorAll('.tooltiptext');
    
    const ganttChartContainer = document.getElementById('ganttChart');
    const overallStartDateStr = ganttChartContainer.dataset.overallStart;
    const overallStartDate = new Date(overallStartDateStr + 'T00:00:00'); // Use T00:00:00 to ensure correct date parsing
    const dayWidth = parseFloat(ganttChartContainer.dataset.dayWidth);

    // Create a container for tooltips outside the scrollable area
    const tooltipContainer = document.createElement('div');
    tooltipContainer.id = 'tooltip-container';
    document.body.appendChild(tooltipContainer);
    
    // Move all tooltips to the new container
    tooltipTexts.forEach(tooltip => {
        tooltipContainer.appendChild(tooltip);
    });
    
    tooltips.forEach(function(tooltip, index) {
        const tooltipText = tooltipTexts[index];
        const hoveredDateLine = tooltipText.querySelector('.hovered-date-line');
        const hoveredDateValue = tooltipText.querySelector('.hovered-date-value');

        tooltip.addEventListener('mousemove', function(e) {
            // Show tooltip and get dimensions
            tooltipText.style.display = 'block';
            if (hoveredDateLine) {
                hoveredDateLine.style.display = 'block';
            }
            
            const tooltipHeight = tooltipText.offsetHeight;
            const tooltipWidth = tooltipText.offsetWidth;
            
            // Position tooltip above cursor
            let leftPosition = e.clientX - (tooltipWidth / 2);
            let topPosition = e.clientY - tooltipHeight - 10; // 10px above cursor
            
            // Ensure tooltip stays within viewport bounds
            leftPosition = Math.max(10, Math.min(leftPosition, window.innerWidth - tooltipWidth - 10));
            topPosition = Math.max(10, Math.min(topPosition, window.innerHeight - tooltipHeight - 10));
            
            tooltipText.style.left = leftPosition + 'px';
            tooltipText.style.top = topPosition + 'px';

            // Update hovered date if within timeline
            const timelineElement = tooltip.closest('.gantt-timeline');
            if (timelineElement) {
                const timelineRect = timelineElement.getBoundingClientRect();
                const mouseXRelativeToTimeline = e.clientX - timelineRect.left;
                
                let dayIndex = Math.floor(mouseXRelativeToTimeline / dayWidth);
                dayIndex = Math.max(0, dayIndex);

                const hoveredDate = new Date(overallStartDate);
                hoveredDate.setDate(overallStartDate.getDate() + dayIndex);
                
                const hoveredDateString = hoveredDate.toLocaleDateString(undefined, { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });

                if (hoveredDateValue) {
                    hoveredDateValue.textContent = hoveredDateString;
                }
            }
        });
        
        tooltip.addEventListener('mouseleave', function() {
            tooltipText.style.display = 'none';
            if (hoveredDateLine) {
                hoveredDateLine.style.display = 'none';
            }
            if (hoveredDateValue) {
                hoveredDateValue.textContent = '';
            }
        });
    });
});
</script>

<?php $this->load->view('layout/footer'); ?> 