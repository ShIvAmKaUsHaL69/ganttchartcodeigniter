<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input   $input
 */
class Auth extends CI_Controller
{
    /**
     * @var User_model
     */
    public $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('url', 'form'));
        $this->load->model('User_model', 'user');
    }

    public function login()
    {
        // Already logged in? Redirect to home.
        if ($this->session->userdata('user_id')) {
            $redirect = $this->session->userdata('redirect_after_login') ?: base_url();
            $this->session->unset_userdata('redirect_after_login');
            redirect($redirect);
        }

        $data = [];
        if ($this->input->post()) {
            $identity = $this->input->post('identity', true); // username or email
            $password = $this->input->post('password', true);

            $user = $this->user->get_by_identity($identity);
            if ($user && $password == $user->password) {
                // Successful login
                $this->session->set_userdata([
                    'user_id'   => $user->id,
                    'user_role' => (int)$user->role, // 0 = user, 1 = admin
                ]);

                $redirect = $this->session->userdata('redirect_after_login') ?: base_url();
                $this->session->unset_userdata('redirect_after_login');
                redirect($redirect);
            } else {
                $data['error'] = 'Invalid username/email or password.';
            }
        }

        $this->load->view('auth/login', $data);
    }

    public function logout()
    {
        $this->session->unset_userdata(['user_id', 'user_role']);
        redirect('auth/login');
    }
} 