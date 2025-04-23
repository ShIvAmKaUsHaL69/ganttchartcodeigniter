<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Project_model $project
 * @property Task_model $task
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 */
class Projects extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Project_model', 'project');
        $this->load->model('Task_model', 'task');
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
            $this->form_validation->set_rules('end_date', 'End Date', 'required');
            $this->form_validation->set_rules('progress', 'Progress', 'required|integer');
            if ($this->form_validation->run()) {
                $payload = [
                    'project_id' => $project_id,
                    'task_name'  => $this->input->post('task_name'),
                    'assigned_to'=> $this->input->post('assigned_to'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date'   => $this->input->post('end_date'),
                    'progress'   => $this->input->post('progress')
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
            $this->form_validation->set_rules('end_date', 'End Date', 'required');
            $this->form_validation->set_rules('progress', 'Progress', 'required|integer');
            if ($this->form_validation->run()) {
                $payload = [
                    'task_name'  => $this->input->post('task_name'),
                    'assigned_to'=> $this->input->post('assigned_to'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date'   => $this->input->post('end_date'),
                    'progress'   => $this->input->post('progress')
                ];
                $this->task->update($task_id, $payload);
                redirect('projects/tasks/'.$project_id);
            }
        }
        $this->load->view('tasks/edit', $data);
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
} 