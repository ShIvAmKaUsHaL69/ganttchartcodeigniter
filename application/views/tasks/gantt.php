<?php $this->load->view('layout/header'); ?>
<!-- Add required libraries -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #ganttChart, #ganttChart * {
        visibility: visible;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
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
        margin: 1cm;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3>Gantt Chart – <?= htmlspecialchars($project->name, ENT_QUOTES, 'UTF-8'); ?></h3>
        <a href="<?= site_url('projects/tasks/'.$project->id); ?>" class="btn btn-secondary no-print">Back to Tasks</a>
    </div>
    <div class="d-flex flex-column flex-md-row">
        <button id="exportPdf" class="btn btn-primary no-print"><i class="fas fa-file-pdf"></i> Export as PDF</button>
    </div>
</div>
<div class="input-group mb-3 mr-md-2 no-print">
    <input type="text" id="ganttSearch" class="form-control" placeholder="Search tasks, assignees, status...">
</div>

<div class="color-legend-box no-print mb-3">
    <h6 class="mb-2">Color Legend:</h6>
    <div class="d-flex flex-wrap">
        <div class="legend-item">
            <span class="color-box" style="background-color: #007bff;"></span>
            <span class="legend-text">Task Duration (Start to Expected End)</span>
        </div>
        <div class="legend-item">
            <span class="color-box" style="background-color: #fff; border: 1px solid #ddd;"></span>
            <span class="legend-text">Weekend (Saturday/Sunday)</span>
        </div>
        <div class="legend-item">
            <span class="color-box" style="background-color: darkred;"></span>
            <span class="legend-text">Late Completion</span>
        </div>
        <div class="legend-item">
            <span class="color-box" style="background-color: #00ff3a;"></span>
            <span class="legend-text">Early Completion</span>
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
$taskInfoWidth = $taskNameWidth + 430; // 430px for other columns
?>

<style>
.gantt-container {
    position: relative;
    overflow-x: auto;
    padding: 10px;
    scrollbar-width: thin;
}
.gantt-header {
    display: flex;
    padding-bottom: 5px;
    margin-bottom: 10px;
    font-size: 10px;
    font-weight: bold;
    min-width: <?= $timelineWidth ?>px;
}
.gantt-week-header {
    display: flex;
    align-items: center;
    justify-content: start;
    border-right: 1px dotted #000;
    padding: 0 2px;
    height: 30px;
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
    height: 25px;
    min-width: <?= $timelineWidth ?>px;
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
    cursor: pointer;
    z-index: 1;
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
}

.tooltiptext {
  visibility: hidden;
  width: 220px;
  background-color: #000;
  color: #fff;
  text-align: left;
  border-radius: 6px;
  padding: 10px;
  line-height: 1.4;
  font-size: 12px;

  /* Position the tooltip */
  position: absolute;
  z-index: 100;
  bottom: 120%;
  transform: translateX(-50%);
  white-space: nowrap;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
}

/* Weekend highlight style */
.weekend-highlight {
  position: absolute;
  top: 0;
  height: 100%;
  background-color: #fff; /* Light yellow */
  opacity: 0.5;
  z-index: 2;
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
            <div class="task-column task-duration">Work Days <br> (Total Days)</div>
            <div class="task-column task-dates">Start Date</div>
            <div class="task-column task-dates">Expected End Date</div>
            <div class="task-column task-dates">End Date</div>
            <div class="task-column task-dates">Status</div>
        </div>
        <div style="position: relative; min-width: <?= $timelineWidth ?>px;">
            <?php foreach ($weeks as $index => $weekStartStr): 
                $weekStart = new DateTime($weekStartStr);
                $weekWidth = 0;
                $currentDay = clone $weekStart;
                $dayCount = 0;
                
                // Calculate days in this week (or until the overall end date)
                while ($dayCount < 7 && $currentDay <= $overallEnd) {
                    $weekWidth += $dayWidthPx;
                    $currentDay->modify('+1 day');
                    $dayCount++;
                }
                
                // Calculate position based on days from start
                $daysFromStart = $weekStart->diff($overallStart)->days;
                $leftPosition = $daysFromStart * $dayWidthPx;
            ?>
                <div class="gantt-week-header" style="position: absolute; left: <?= $leftPosition ?>px; width: <?= $weekWidth ?>px;">
                    <?= $weekStart->format('d M Y'); ?>
                </div>
            <?php endforeach; ?>

            <!-- Day-of-week boxes (M T W T F S S) -->
            <?php foreach ($days as $index => $dayStr):
                $dayDateObj = new DateTime($dayStr);
                $dayLetter = strtoupper(substr($dayDateObj->format('D'), 0, 1));
                $leftPos = $index * $dayWidthPx;
            ?>
                <div class="day-of-week-box" style="left: <?= $leftPos ?>px;">
                    <?= $dayLetter ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Task Rows -->
    <?php foreach ($tasks as $t):
        $taskStartDate = new DateTime($t->start_date);
        $taskEndDate   = new DateTime($t->expected_end_date);

        // Ensure task dates are within the overall calculated range for safety
        if ($taskEndDate < $overallStart || $taskStartDate > $overallEnd) {
            continue;
        }
        // Clamp task dates to the overall range if they extend beyond
        $clampedTaskStart = max($taskStartDate, $overallStart);
        $clampedTaskEnd   = min($taskEndDate, $overallEnd);

        // Days from start for positioning
        $taskOffsetDays = $clampedTaskStart->diff($overallStart)->days;
        $taskDurationDays = $clampedTaskEnd->diff($clampedTaskStart)->days + 1;

        // Calculate pixel positions for exact alignment
        $barLeftPx = $taskOffsetDays * $dayWidthPx;
        $barWidthPx = $taskDurationDays * $dayWidthPx;

        // Calculate business days (excluding weekends) for display
        $businessDays = 0;
        $currentDate = clone $taskStartDate;
        while ($currentDate <= $taskEndDate) {
            $dayOfWeek = (int)$currentDate->format('N'); // 1 (Monday) to 7 (Sunday)
            if ($dayOfWeek < 6) { // Only count weekdays (Monday-Friday)
                $businessDays++;
            }
            $currentDate->modify('+1 day');
        }
        
        // Original duration for display (total calendar days)
        $originalDurationDays = $taskEndDate->diff($taskStartDate)->days + 1;
    ?>
        <div class="gantt-row">
            <div class="gantt-task-info">
                <div class="task-column task-name" title="<?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="task-column task-assignee"><?= htmlspecialchars($t->assigned_to ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="task-column task-duration"><?= $businessDays ?> (<?= $originalDurationDays ?>)</div>
                <div class="task-column task-dates"><?= $t->start_date ?></div>
                <div class="task-column task-dates"><?= $t->expected_end_date ?></div>
                <div class="task-column task-dates"><?= $t->end_date ? $t->end_date : 'Not Completed' ?></div>
                <div class="task-column task-dates"><?= $t->status == 0 ? 'In Progress' : ($t->status == 1 ? 'Completed' : 'Hold') ?></div>
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
                        $actualEnd = new DateTime($t->end_date);
                        $expectedEnd = clone $taskEndDate; // already set to expected_end_date
                        if ($actualEnd < $expectedEnd) {
                            // Finished early – green boxes between actualEnd+1 and expectedEnd
                            $colorStart = clone $actualEnd;
                            $colorStart->modify('+1 day');
                            $colorEnd = clone $expectedEnd;
                            $perfColor = '#00ff3a'; // green
                        } elseif ($actualEnd > $expectedEnd) {
                            // Finished late – red boxes between expectedEnd+1 and actualEnd
                            $colorStart = clone $expectedEnd;
                            $colorStart->modify('+1 day');
                            $colorEnd = clone $actualEnd;
                            $perfColor = 'darkred'; // red
                        }

                        if (isset($perfColor)) {
                            // Ensure dates lie within overall range
                            if ($colorStart < $overallStart) $colorStart = clone $overallStart;
                            if ($colorEnd > $overallEnd) $colorEnd = clone $overallEnd;

                            $perfOffsetDays = $colorStart->diff($overallStart)->days;
                            $perfDurationDays = $colorEnd->diff($colorStart)->days + 1;
                            $perfLeftPx = $perfOffsetDays * $dayWidthPx;
                            $perfWidthPx = $perfDurationDays * $dayWidthPx;
                ?>
                            <div class="gantt-performance" style="left: <?= $perfLeftPx ?>px; width: <?= $perfWidthPx ?>px; background-color: <?= $perfColor ?>; opacity: 0.7 !important; border-radius: 3px;"></div>
                <?php       }
                    }
                ?>
                
                <div class="gantt-bar tooltip" style="left: <?= $barLeftPx ?>px; width: <?= $barWidthPx ?>px;">
                    <div class="tooltiptext">
                        <strong>Task:</strong> <?= htmlspecialchars($t->task_name, ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>Assignee:</strong> <?= htmlspecialchars($t->assigned_to ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>Start Date:</strong> <?= $t->start_date ?><br>
                        <strong>Expected End Date:</strong> <?= $t->expected_end_date ?><br>
                        <strong>End Date:</strong> <?= $t->end_date ? $t->end_date : 'Not Completed' ?><br>
                        <strong>Duration:</strong> <?= $businessDays ?> work days (<?= $originalDurationDays ?> days)<br>
                        <strong>Status:</strong> <?= $t->status == 0 ? 'In Progress' : ($t->status == 1 ? 'Completed' : 'Hold') ?>
                    </div>
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

// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ganttSearch');
    const ganttRows = document.querySelectorAll('.gantt-row');
    
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
});
</script>

<?php $this->load->view('layout/footer'); ?> 