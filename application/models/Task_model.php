<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Task_model extends CI_Model
{
    private $table = 'tasks';

    public function get_all($project_id = null)
    {
        if ($project_id !== null) {
            $this->db->where('project_id', $project_id);
        }
        return $this->db->order_by('start_date', 'ASC')->get($this->table)->result();
    }

    public function get($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->delete($this->table, ['id' => $id]);
        return $this->db->affected_rows();
    }

    /**
     * Get all tasks for a project that start after a specific date
     * 
     * @param int $project_id The project ID
     * @param string $date The date in Y-m-d format
     * @return array Array of task objects
     */
    public function get_tasks_after_date($project_id, $date)
    {
        return $this->db
            ->where('project_id', $project_id)
            ->where('start_date >', $date)
            ->order_by('start_date', 'ASC')
            ->get($this->table)
            ->result();
    }
} 