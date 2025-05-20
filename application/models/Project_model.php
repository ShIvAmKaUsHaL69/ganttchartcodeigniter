<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
    private $table = 'projects';

    public function get_all()
    {
        return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
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
} 