<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
    private $table = 'projects';

    public function get_all()
    {
        $this->db->select('projects.*, users.username AS creator_username');
        $this->db->from($this->table);
        $this->db->join('users', 'users.id = projects.created_by', 'left');
        $this->db->order_by('projects.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function create($data)
    {
        $this->db->insert($this->table, [
            'name'       => $data['name'],
            'created_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update($this->table, [
            'name'       => $data['name'],
            'created_by' => $data['created_by'],
            'status'     => $data['status'],
            'completed_at' => $data['status'] == 1 ? date('Y-m-d') : null
        ]);
        return $this->db->affected_rows();
    }

    public function get_all_completed_projects()
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('status', 1);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }   
    

    public function delete($id)
    {   
        $this->db->delete($this->table, ['id' => $id]);
        return $this->db->affected_rows();
    }

    public function update_share_token($id, $token)
    {
        // Stores or updates the randomized share token for a project
        $this->db->where('id', $id)->update($this->table, [
            'share_token' => $token
        ]);
        return $this->db->affected_rows();
    }

    /**
     * Fetch a project row via its public share token
     *
     * @param string $token
     * @return object|null
     */
    public function get_by_share_token($token)
    {
        return $this->db->get_where($this->table, ['share_token' => $token])->row();
    }

    /**
     * Fetch all projects created by a specific user id.
     *
     * @param int $user_id
     * @return array
     */
    public function get_by_creator($user_id)
    {
        $this->db->select('projects.*, users.username AS creator_username');
        $this->db->from($this->table);
        $this->db->join('users', 'users.id = projects.created_by', 'left');
        $this->db->where('projects.created_by', $user_id);
        $this->db->order_by('projects.created_at', 'DESC');
        return $this->db->get()->result();
    }
} 