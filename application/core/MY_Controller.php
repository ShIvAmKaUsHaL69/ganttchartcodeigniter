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
    /**
     * SHA-256 hash of the valid password (defined in constants.php).
     * @var string
     */
    private $password_hash;

    public function __construct()
    {
        parent::__construct();

        // Ensure session library loaded (also autoloaded globally).
        $this->load->library('session');

        // Determine password hash
        $this->password_hash = defined('GANTT_PASSWORD_HASH') ? GANTT_PASSWORD_HASH : hash('sha256', 'ekarigar@gantt');

        // Let the Auth controller itself bypass the gate (prevents loop)
        if (strtolower($this->router->fetch_class()) === 'auth') {
            return;
        }

        // If session does not contain the correct hash, redirect to login
        if ($this->session->userdata('gantt_auth') !== $this->password_hash) {
            $this->session->set_userdata('redirect_after_login', current_url());
            redirect('auth/login');
        }
    }
} 