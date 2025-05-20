<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input   $input
 */
class Auth extends CI_Controller
{
    private $password_hash;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('url', 'form'));
        $this->password_hash = GANTT_PASSWORD_HASH;
    }

    public function login()
    {
        // If already authenticated, redirect to default controller
        if ($this->session->userdata('gantt_auth') === $this->password_hash) {
            $redirect = $this->session->userdata('redirect_after_login') ?: base_url();
            $this->session->unset_userdata('redirect_after_login');
            redirect($redirect);
        }

        $data = [];
        if ($this->input->post()) {
            $password = $this->input->post('password', true);
            if (hash('sha256', $password) === $this->password_hash) {
                $this->session->set_userdata('gantt_auth', $this->password_hash);
                $redirect = $this->session->userdata('redirect_after_login') ?: base_url();
                $this->session->unset_userdata('redirect_after_login');
                redirect($redirect);
            } else {
                $data['error'] = 'Incorrect password. Please try again.';
            }
        }
        $this->load->view('auth/login', $data);
    }

    public function logout()
    {
        $this->session->unset_userdata('gantt_auth');
        redirect('auth/login');
    }
} 