<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Project_model $project
 * @property Task_model $task
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property CI_Upload $upload
 * @property CI_Session $session
 * @property CI_Output $output
 */
class Projects extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Project_model', 'project');
        $this->load->model('Task_model', 'task');
        $this->load->library('upload');
        $this->load->library('session');
    }

    public function index()
    {
        $user_role = (int)$this->session->userdata('user_role');
        $user_id   = (int)$this->session->userdata('user_id');

        if ($user_role === 1) {
            // Admin - see every project
            $data['projects'] = $this->project->get_all();
        } else {
            // Regular user - only own projects
            $data['projects'] = $this->project->get_by_creator($user_id);
        }
        $this->load->view('projects/index', $data);
    }

    public function create()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Project Name', 'required');
            if ($this->form_validation->run()) {
                $payload = [
                    'name' => $this->input->post('name'),
                    'created_by' => (int)$this->session->userdata('user_id')
                ];
                $this->project->create($payload);
                redirect('projects');
            }
        }
        $this->load->view('projects/create');
    }

    public function edit($id)
    {
        $data['project'] = $this->project->get($id);
        if (!$data['project']) show_404();

        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Project Name', 'required');
            $this->form_validation->set_rules('status', 'Status', 'required');
            if ($this->form_validation->run()) {
                $payload = [
                    'name' => $this->input->post('name'),
                    'status' => $this->input->post('status'),
                    'created_by' => $data['project']->created_by
                ];
                $this->project->update($id, $payload);
                redirect('projects');
            }
        }
        $this->load->view('projects/edit', $data);
    }

    public function delete($id)
    {
        $this->project->delete($id);
        redirect('projects');
    }

    /* TASKS */
    public function tasks($project_id)
    {
        $data['project'] = $this->project->get($project_id);
        if (!$data['project']) show_404();
        $data['tasks'] = $this->task->get_all($project_id);
        $this->load->view('tasks/index', $data);
    }

    public function add_task($project_id)
    {
        $data['project'] = $this->project->get($project_id);
        if (!$data['project']) show_404();

        if ($this->input->post()) {
            $this->form_validation->set_rules('task_name', 'Task Name', 'required');
            $this->form_validation->set_rules('start_date', 'Start Date', 'required');
            $this->form_validation->set_rules('expected_end_date', 'Expected End Date', 'required');
            $this->form_validation->set_rules('progress', 'Progress', 'required|integer');
            $this->form_validation->set_rules('status', 'Status', 'required');
            if ($this->form_validation->run()) {
                $payload = [
                    'project_id' => $project_id,
                    'task_name'  => $this->input->post('task_name'),
                    'assigned_to'=> $this->input->post('assigned_to'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date'   => null,
                    'expected_end_date'   => $this->input->post('expected_end_date') ? $this->input->post('expected_end_date') : null,
                    'progress'   => $this->input->post('progress'),
                    'status'     => $this->input->post('status'),
                    'modified_at' => date('Y-m-d')
                ];
                $this->task->create($payload);
                redirect('projects/tasks/'.$project_id);
            }
        }
        $this->load->view('tasks/create', $data);
    }

    public function edit_task($project_id, $task_id)
    {
        $data['project'] = $this->project->get($project_id);
        if (!$data['project']) show_404();
        $data['task'] = $this->task->get($task_id);
        if(!$data['task']) show_404();

        if ($this->input->post()) {
            $this->form_validation->set_rules('task_name', 'Task Name', 'required');
            $this->form_validation->set_rules('start_date', 'Start Date', 'required');
            $this->form_validation->set_rules('expected_end_date', 'Expected End Date', 'required');
            $this->form_validation->set_rules('progress', 'Progress', 'required|integer');
            $this->form_validation->set_rules('status', 'Status', 'required');
            if ($this->form_validation->run()) {
                $payload = [
                    'task_name'  => $this->input->post('task_name'),
                    'assigned_to'=> $this->input->post('assigned_to'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date'   => $this->input->post('status') == 1 ? date('Y-m-d') : null,
                    'expected_end_date'   => $this->input->post('expected_end_date') ? $this->input->post('expected_end_date') : null,
                    'progress'   => $this->input->post('progress'),
                    'status'     => $this->input->post('status'),
                    'modified_at' => date('Y-m-d')
                ];
                $this->task->update($task_id, $payload);
                redirect('projects/tasks/'.$project_id);
            }
        }
        $this->load->view('tasks/edit', $data);
    }

    public function all_completed() {
        $data['completed_projects'] = $this->project->get_all_completed_projects();
        $this->load->view('projects/all_completed_view', $data);
    }

    public function delete_task($project_id, $task_id)
    {
        $this->task->delete($task_id);
        redirect('projects/tasks/'.$project_id);
    }

    public function gantt($project_id)
    {
        $data['project'] = $this->project->get($project_id);
        if(!$data['project']) show_404();
        $data['tasks'] = $this->task->get_all($project_id);
        $this->load->view('tasks/gantt', $data);
    }

    /**
     * Download a template Excel file for bulk task uploads
     */
    public function download_task_template()
    {
        // Load PhpSpreadsheet library
        require_once FCPATH . 'vendor/autoload.php';
        
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Add headings to the first row
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Task Name');
        $sheet->setCellValue('B1', 'Assigned To');
        $sheet->setCellValue('C1', 'Start Date (DD-MM-YYYY)');
        $sheet->setCellValue('D1', 'Expected End Date (DD-MM-YYYY)');
        $sheet->setCellValue('E1', 'Progress (%)');
        $sheet->setCellValue('F1', 'Status (0 = In Progress, 1 = Completed, 2 = Hold)');
        
        // Make the heading row bold
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        
        // Auto-size columns
        foreach(range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create a sample data row
        $sheet->setCellValue('A2', 'Sample Task');
        $sheet->setCellValue('B2', 'John Doe');
        $sheet->setCellValue('C2', date('d-m-Y'));
        $sheet->setCellValue('D2', date('d-m-Y', strtotime('+1 week')));
        $sheet->setCellValue('E2', '0');
        $sheet->setCellValue('F2', '0');

        // Set content-type and filename
        $filename = 'tasks_template.xlsx';
        
        // Redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Handle bulk upload of tasks from Excel file
     */
    public function bulk_upload_tasks($project_id)
    {
        $data['project'] = $this->project->get($project_id);
        if (!$data['project']) show_404();
        
        // Check if a file has been uploaded
        if (empty($_FILES['excel_file']['name'])) {
            $data['excel_error'] = 'Please select a file to upload';
            $this->load->view('tasks/create', $data);
            return;
        }
        
        // Set upload configuration
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xlsx|xls|csv';
        $config['max_size'] = 2048; // 2MB max
        
        // Create directory if it doesn't exist
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        
        $this->upload->initialize($config);
        
        // Attempt to upload the file
        if (!$this->upload->do_upload('excel_file')) {
            $data['excel_error'] = $this->upload->display_errors('', '');
            $this->load->view('tasks/create', $data);
            return;
        }
        
        // Get the uploaded file info
        $file_data = $this->upload->data();
        $file_path = './uploads/' . $file_data['file_name'];
        
        // Load PhpSpreadsheet library
        require_once FCPATH . 'vendor/autoload.php';
        
        try {
            // Load the spreadsheet
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($file_path);
            
            // Get the first worksheet
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            // Skip the header row
            $tasks_added = 0;
            $tasks_skipped = 0;
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $task_name = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $assigned_to = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $start_date = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $expected_end_date = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $progress = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $status = $worksheet->getCellByColumnAndRow(6, $row)->getValue();

                // Skip empty rows
                if (empty($task_name)) {
                    continue;
                }
                
                // Format dates if they are Excel date values
                if (is_numeric($start_date)) {
                    try {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($start_date);
                        // Explicitly format as YYYY-MM-DD to avoid any ambiguity
                        $start_date = $dateObj->format('Y') . '-' . 
                                    str_pad($dateObj->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                    str_pad($dateObj->format('d'), 2, '0', STR_PAD_LEFT);
                    } catch (Exception $e) {
                        $tasks_skipped++;
                        continue;
                    }
                } else {
                    // Try multiple date formats
                    $possibleFormats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];
                    $validDate = false;
                    foreach ($possibleFormats as $format) {
                        $dt = DateTime::createFromFormat($format, $start_date);
                        if ($dt && $dt->format($format) === $start_date) {
                            // Explicitly format as YYYY-MM-DD
                            $start_date = $dt->format('Y') . '-' . 
                                        str_pad($dt->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                        str_pad($dt->format('d'), 2, '0', STR_PAD_LEFT);
                            $validDate = true;
                            break;
                        }
                    }
                    if (!$validDate) {
                        $tasks_skipped++;
                        continue;
                    }
                }
                
                if (is_numeric($expected_end_date)) {
                    try {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expected_end_date);
                        // Explicitly format as YYYY-MM-DD to avoid any ambiguity
                        $expected_end_date = $dateObj->format('Y') . '-' . 
                                           str_pad($dateObj->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                           str_pad($dateObj->format('d'), 2, '0', STR_PAD_LEFT);
                    } catch (Exception $e) {
                        $tasks_skipped++;
                        continue;
                    }
                } else {
                    // Try multiple date formats
                    $possibleFormats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];
                    $validDate = false;
                    foreach ($possibleFormats as $format) {
                        $dtExp = DateTime::createFromFormat($format, $expected_end_date);
                        if ($dtExp && $dtExp->format($format) === $expected_end_date) {
                            // Explicitly format as YYYY-MM-DD
                            $expected_end_date = $dtExp->format('Y') . '-' . 
                                               str_pad($dtExp->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                               str_pad($dtExp->format('d'), 2, '0', STR_PAD_LEFT);
                            $validDate = true;
                            break;
                        }
                    }
                    if (!$validDate) {
                        $tasks_skipped++;
                        continue;
                    }
                }
                
                // Validate data
                if (empty($task_name) || empty($start_date) || empty($expected_end_date)) {
                    $tasks_skipped++;
                    continue;
                }
                
                // Sanitize numeric columns
                $progress = is_numeric($progress) ? min(max((int)$progress, 0), 100) : 0;
                $status   = in_array((int)$status, [0,1,2], true) ? (int)$status : 0;

                // Completed tasks get an end_date of today if none provided
                $end_date = $status === 1 ? date('Y-m-d') : null;

                // Prepare task data
                $payload = [
                    'project_id' => $project_id,
                    'task_name'  => $task_name,
                    'assigned_to'=> $assigned_to,
                    'start_date' => $start_date,
                    'expected_end_date' => $expected_end_date,
                    'end_date'   => $end_date,
                    'progress'   => $progress,
                    'status'     => $status,
                    'modified_at' => date('Y-m-d')
                ];
                
                // Insert the task
                $this->task->create($payload);
                $tasks_added++;
            }
            
            // Delete the uploaded file
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Set success message
            $this->session->set_flashdata('success', "Imported $tasks_added tasks successfully. Skipped $tasks_skipped tasks.");
            
            // Redirect to tasks list
            redirect('projects/tasks/' . $project_id);
            
        } catch (Exception $e) {
            // Delete the uploaded file if it exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            $data['excel_error'] = 'Error processing Excel file: ' . $e->getMessage();
            $this->load->view('tasks/create', $data);
            return;
        }
    }

    /* NOTES */
    public function add_note($project_id)
    {
        $data['project'] = $this->project->get($project_id);
        if (!$data['project']) show_404();

        if ($this->input->post()) {
            $this->form_validation->set_rules('task_name', 'Note Name', 'required');
            $this->form_validation->set_rules('start_date', 'Start Date', 'required');
            $this->form_validation->set_rules('expected_end_date', 'Expected End Date', 'required');
            $this->form_validation->set_rules('progress', 'Progress', 'required|integer');
            $this->form_validation->set_rules('status', 'Status', 'required');

            if ($this->form_validation->run()) {
                // Get the start and end dates for the note
                $note_start_date = new DateTime($this->input->post('start_date'));
                $note_end_date = new DateTime($this->input->post('expected_end_date'));
                
                // Calculate the duration in days
                $duration = $note_start_date->diff($note_end_date)->days;

                // First create the note task
                $payload = [
                    'project_id'          => $project_id,
                    'task_name'           => $this->input->post('task_name'),
                    'assigned_to'         => $this->input->post('assigned_to'),
                    'start_date'          => $this->input->post('start_date'),
                    'end_date'            => null,
                    'expected_end_date'   => $this->input->post('expected_end_date') ? $this->input->post('expected_end_date') : null,
                    'progress'            => $this->input->post('progress'),
                    'status'              => $this->input->post('status'),
                    'is_note_task'        => 1,
                    'modified_at'         => date('Y-m-d')
                ];

                $this->task->create($payload);

                // Get all tasks for this project that start after the note's start date
                $affected_tasks = $this->task->get_tasks_after_date($project_id, $this->input->post('start_date'));

                // Update each affected task's dates
                foreach ($affected_tasks as $task) {
                    // Skip if it's the note task we just created
                    if ($task->is_note_task == 1 && $task->start_date == $this->input->post('start_date')) {
                        continue;
                    }

                    // Create DateTime objects for the task dates
                    $task_start = new DateTime($task->start_date);
                    $task_end = $task->expected_end_date ? new DateTime($task->expected_end_date) : null;
                    $task_actual_end = $task->end_date ? new DateTime($task->end_date) : null;

                    // Add the duration days to each date
                    $task_start->add(new DateInterval("P{$duration}D"));
                    if ($task_end) {
                        $task_end->add(new DateInterval("P{$duration}D"));
                    }
                    if ($task_actual_end) {
                        $task_actual_end->add(new DateInterval("P{$duration}D"));
                    }

                    // Update the task with new dates
                    $update_payload = [
                        'start_date' => $task_start->format('Y-m-d'),
                        'expected_end_date' => $task_end ? $task_end->format('Y-m-d') : null,
                        'end_date' => $task_actual_end ? $task_actual_end->format('Y-m-d') : null,
                        'modified_at' => date('Y-m-d')
                    ];

                    $this->task->update($task->id, $update_payload);
                }

                redirect('projects/tasks/'.$project_id);
            }
        }

        $this->load->view('tasks/create_note', $data);
    }

    /**
     * Handle bulk upload of notes from Excel file. Works the same as tasks but sets is_note_task = 1.
     */
    public function bulk_upload_notes($project_id)
    {
        $data['project'] = $this->project->get($project_id);
        if (!$data['project']) show_404();

        // Check if a file has been uploaded
        if (empty($_FILES['excel_file']['name'])) {
            $data['excel_error'] = 'Please select a file to upload';
            $this->load->view('tasks/create_note', $data);
            return;
        }

        // Set upload configuration
        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'xlsx|xls|csv';
        $config['max_size']      = 2048; // 2MB max

        // Create directory if it doesn't exist
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);

        // Attempt to upload the file
        if (!$this->upload->do_upload('excel_file')) {
            $data['excel_error'] = $this->upload->display_errors('', '');
            $this->load->view('tasks/create_note', $data);
            return;
        }

        // Get the uploaded file info
        $file_data = $this->upload->data();
        $file_path = './uploads/' . $file_data['file_name'];

        // Load PhpSpreadsheet library
        require_once FCPATH . 'vendor/autoload.php';

        try {
            // Load the spreadsheet
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
            $reader        = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet   = $reader->load($file_path);

            // Get the first worksheet
            $worksheet   = $spreadsheet->getActiveSheet();
            $highestRow  = $worksheet->getHighestRow();

            // Skip the header row
            $tasks_added   = 0;
            $tasks_skipped = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $task_name          = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $assigned_to        = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $start_date         = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $expected_end_date  = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $progress           = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $status             = $worksheet->getCellByColumnAndRow(6, $row)->getValue();

                // Skip empty rows
                if (empty($task_name)) {
                    continue;
                }

                // Format dates if they are Excel date values
                if (is_numeric($start_date)) {
                    try {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($start_date);
                        // Explicitly format as YYYY-MM-DD to avoid any ambiguity
                        $start_date = $dateObj->format('Y') . '-' . 
                                    str_pad($dateObj->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                    str_pad($dateObj->format('d'), 2, '0', STR_PAD_LEFT);
                    } catch (Exception $e) {
                        $tasks_skipped++;
                        continue;
                    }
                } else {
                    // Try multiple date formats
                    $possibleFormats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];
                    $validDate = false;
                    foreach ($possibleFormats as $format) {
                        $dt = DateTime::createFromFormat($format, $start_date);
                        if ($dt && $dt->format($format) === $start_date) {
                            // Explicitly format as YYYY-MM-DD
                            $start_date = $dt->format('Y') . '-' . 
                                        str_pad($dt->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                        str_pad($dt->format('d'), 2, '0', STR_PAD_LEFT);
                            $validDate = true;
                            break;
                        }
                    }
                    if (!$validDate) {
                        $tasks_skipped++;
                        continue;
                    }
                }

                if (is_numeric($expected_end_date)) {
                    try {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expected_end_date);
                        // Explicitly format as YYYY-MM-DD to avoid any ambiguity
                        $expected_end_date = $dateObj->format('Y') . '-' . 
                                           str_pad($dateObj->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                           str_pad($dateObj->format('d'), 2, '0', STR_PAD_LEFT);
                    } catch (Exception $e) {
                        $tasks_skipped++;
                        continue;
                    }
                } else {
                    // Try multiple date formats
                    $possibleFormats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];
                    $validDate = false;
                    foreach ($possibleFormats as $format) {
                        $dtExp = DateTime::createFromFormat($format, $expected_end_date);
                        if ($dtExp && $dtExp->format($format) === $expected_end_date) {
                            // Explicitly format as YYYY-MM-DD
                            $expected_end_date = $dtExp->format('Y') . '-' . 
                                               str_pad($dtExp->format('m'), 2, '0', STR_PAD_LEFT) . '-' . 
                                               str_pad($dtExp->format('d'), 2, '0', STR_PAD_LEFT);
                            $validDate = true;
                            break;
                        }
                    }
                    if (!$validDate) {
                        $tasks_skipped++;
                        continue;
                    }
                }

                // Validate data
                if (empty($task_name) || empty($start_date) || empty($expected_end_date)) {
                    $tasks_skipped++;
                    continue;
                }

                // Sanitize numeric columns
                $progress = is_numeric($progress) ? min(max((int)$progress, 0), 100) : 0;
                $status   = in_array((int)$status, [0,1,2], true) ? (int)$status : 0;

                // Completed tasks get an end_date of today if none provided
                $end_date = $status === 1 ? date('Y-m-d') : null;

                // Prepare task data
                $payload = [
                    'project_id'        => $project_id,
                    'task_name'         => $task_name,
                    'assigned_to'       => $assigned_to,
                    'start_date'        => $start_date,
                    'expected_end_date' => $expected_end_date,
                    'end_date'          => $end_date,
                    'progress'          => $progress,
                    'status'            => $status,
                    'is_note_task'      => 1,
                    'modified_at'       => date('Y-m-d')
                ];

                // Insert the task/note
                $this->task->create($payload);
                $tasks_added++;
            }

            // Delete the uploaded file
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Set success message
            $this->session->set_flashdata('success', "Imported $tasks_added notes successfully. Skipped $tasks_skipped rows.");

            // Redirect to tasks list
            redirect('projects/tasks/' . $project_id);

        } catch (Exception $e) {
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            $data['excel_error'] = 'Error processing Excel file: ' . $e->getMessage();
            $this->load->view('tasks/create_note', $data);
            return;
        }
    }

    public function generate_share_link($project_id)
    {
        // Ensure the request is made via AJAX to avoid unintended direct hits
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // Validate project exists
        $project = $this->project->get($project_id);
        if (!$project) {
            $this->output->set_status_header(404);
            echo json_encode(['error' => 'Project not found']);
            return;
        }

        // Create a cryptographically-secure random token
        try {
            $token = bin2hex(random_bytes(16)); // 32-char token
        } catch (Exception $e) {
            $this->output->set_status_header(500);
            echo json_encode(['error' => 'Could not generate token']);
            return;
        }

        // Persist token against the project (overwrites any previous token)
        $this->project->update_share_token($project_id, $token);

        // Build absolute shareable URL
        $shareUrl = site_url('share/' . $token);

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(['url' => $shareUrl]));
    }

    public function get_current_share_link($project_id)
    {
        // Ensure the request is made via AJAX to avoid unintended direct hits
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $project = $this->project->get($project_id);
        if (!$project) {
            $this->output->set_status_header(404);
            echo json_encode(['error' => 'Project not found']);
            return;
        }

        $shareUrl = null;
        if (!empty($project->share_token)) {
            $shareUrl = site_url('share/' . $project->share_token);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['url' => $shareUrl]));
    }
} 