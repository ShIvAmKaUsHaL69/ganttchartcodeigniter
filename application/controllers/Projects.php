<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Project_model $project
 * @property Task_model $task
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property CI_Upload $upload
 * @property CI_Session $session
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
        $data['projects'] = $this->project->get_all();
        $this->load->view('projects/index', $data);
    }

    public function create()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Project Name', 'required');
            $this->form_validation->set_rules('created_by', 'Created By', 'required');
            if ($this->form_validation->run()) {
                $this->project->create($this->input->post());
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
            $this->form_validation->set_rules('created_by', 'Created By', 'required');
            $this->form_validation->set_rules('status', 'Status', 'required');
            if ($this->form_validation->run()) {
                $this->project->update($id, $this->input->post());
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
                    $start_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($start_date)->format('Y-m-d');
                } else {
                    $dt = DateTime::createFromFormat('d-m-Y', $start_date);
                    if ($dt) { $start_date = $dt->format('Y-m-d'); }
                }
                
                if (is_numeric($expected_end_date)) {
                    $expected_end_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expected_end_date)->format('Y-m-d');
                } else {
                    $dtExp = DateTime::createFromFormat('d-m-Y', $expected_end_date);
                    if ($dtExp) { $expected_end_date = $dtExp->format('Y-m-d'); }
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
} 