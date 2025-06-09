<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Application base controller that protects all child controllers
 * with a single-password gate.
 *
 * @property CI_Session $session
 * @property CI_Router $router
 */
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Ensure session library loaded (also autoloaded globally).
        $this->load->library('session');

        // Controllers that are publicly accessible should be whitelisted.
        $publicControllers = ['auth', 'share'];
        if (in_array(strtolower($this->router->fetch_class()), $publicControllers, true)) {
            return;
        }

        // If user is not logged in, redirect to login page.
        if (!$this->session->userdata('user_id')) {
            $this->session->set_userdata('redirect_after_login', current_url());
            redirect('auth/login');
        }
    }
} 