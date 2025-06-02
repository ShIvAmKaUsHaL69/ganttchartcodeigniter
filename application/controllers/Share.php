<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public controller that serves read-only Gantt charts to anyone with a valid
 * share token.  Does NOT inherit from MY_Controller so that the password gate
 * is bypassed for shared links.
 *
 * @property Project_model $project
 * @property Task_model    $task
 */
class Share extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load models required for fetching data
        $this->load->model('Project_model', 'project');
        $this->load->model('Task_model', 'task');
    }

    /**
     * Render the Gantt chart for a given share token
     *
     * @param string|null $token
     */
    public function index($token = null)
    {
        $this->view($token);
    }

    public function view($token = null)
    {
        if (!$token) {
            show_404();
            return;
        }

        $project = $this->project->get_by_share_token($token);
        if (!$project) {
            show_404();
            return;
        }

        $data['project'] = $project;
        $data['tasks']   = $this->task->get_all($project->id);

        // Re-use the existing gantt view so that no duplication of UI occurs
        $this->load->view('tasks/gantt', $data);
    }
} 