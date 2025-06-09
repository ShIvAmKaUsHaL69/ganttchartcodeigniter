<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $table = 'users';

    /**
     * Fetch a single user row by username or e-mail.
     *
     * @param string $identity Username **or** e-mail address
     * @return object|null
     */
    public function get_by_identity($identity)
    {
        $this->db->where('username', $identity)->or_where('email', $identity);
        return $this->db->get($this->table)->row();
    }

    /**
     * Insert a new user record.  Expects the password to already be hashed using password_hash().
     *
     * @param array $data
     * @return int Insert id
     */
    public function create($data)
    {
        $this->db->insert($this->table, [
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $data['password'], // hashed
            'role'     => $data['role'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }
} 