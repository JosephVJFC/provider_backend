<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
public $db;
public  function __construct()
    {
        $db = \Config\Database::connect();
        $this->db = $db;
    }


	public function insert_data($data = array())
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->insertID();
    }



	public function get_all_details($table,$array)
    {

		$builder = $this->db->table($table); 
		$builder->select('*');
		$builder->where($array);
		$query = $builder->get();
		$result=$query->getResultArray();
		return $result;
      
 
    }


	public function get_selected_fields($table,$array,$fields)
    {

		$builder = $this->db->table($table);
		$builder->select($fields);
		$builder->where($array);
		$query = $builder->get();
		$result=$query->getResultArray();
		return $result;
      
 
    }





	
	public function get_all_counts($table,$array)
    {

		$builder = $this->db->table($table);
		$builder->select('*');
		$builder->where($array);
		$query = $builder->get();
		$result=$builder->countAllResults();
		return !empty($result)?$result:0;
      
 
    }


	public function update_data($table,$data = null,$whereCondition = null): bool
	{
		
		$builder = $this->db->table($table)->update($data, $whereCondition);
		
		return $builder;
	}
	


	
	// // // public function insert($table,$value)
	// // // {
	// // // 	$this->db->insert($table,$value);
	// // // 	return $this->db->insert_id();

	// // // }

    // // public function insert($data = null, bool $returnID = true)
    // // {
    // //     // Your custom insert logic here

    // //     // Call the parent insert method
    // //     return parent::insert($data, $returnID);
    // // }
    // // // public function insert($table, $data) {
    // // //     // return $this->db->table($table)->insert($data);
	// // // 	return $this->db->insert($table, $data);
	// // // }
	// // public function insert($data) {
	// // 	return $this->db->insert($data);
    // //     // return $this->db->insert($this->table, $data);
    // // }
  

	// // public function update($arr,$table,$value)
	// // {
	// // 	$this->db->where($arr);
	// // 	$this->db->update($table, $value); 
	// // }
	// public function delete($arr,$table)
	// {
	// 	$this->db->where($arr);
	// 	return $this->db->delete($table); 
	// }
	// public function empty_table($table)
	// {
	// 	$this->db->empty_table($table);
	// }
	// public function result_count($table_name)
	// {
	// 	$this->db->from($table_name);
	// 	return $this->db->count_all_results();
	// }	
	// public function page_records($limit,$start,$table,$order,$order_type)
	// {
	// 	$this->db->limit($limit,$start);
	// 	$this->db->order_by($order,$order_type);
    //     $result = $this->db->get($table);
    //     if ($result->num_rows() > 0) 
    //     {
    //         return $result->result_array();
    //     }
    //     return false;
	// }	
	// public function select($select,$table,$order,$order_type)
	// {	
	// 	$this->db->select($select);
	// 	$this->db->from($table);
	// 	$this->db->order_by($order,$order_type);
	// 	$query = $this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function select_where_one($select,$table,$where)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table);
	// 	$this->db->where($where);
	// 	$query = $this->db->get();
	// 	if($query->num_rows()==1)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function select_where_multi($select,$table,$where,$order,$order_type)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table);
	// 	$this->db->where($where);
	// 	$this->db->order_by($order,$order_type);
	// 	$query = $this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function select_where_multi_group($select,$table,$where,$order,$order_type,$group_by)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table);
	// 	$this->db->where($where);
	// 	$this->db->group_by($group_by);
	// 	$this->db->order_by($order,$order_type);
	// 	$query = $this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function select_where_multi_limit($select,$table,$where,$order,$order_type,$limit)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table);
	// 	$this->db->where($where);
	// 	$this->db->order_by($order,$order_type);
	// 	$this->db->limit($limit);
	// 	$query = $this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function select_multi_limit($select,$table,$order,$order_type,$limit)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table);
	// 	$this->db->order_by($order,$order_type);
	// 	$this->db->limit($limit);
	// 	$query = $this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function joinq($select,$table1,$table2,$join,$type,$where,$order,$order_type)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table1);
	// 	$this->db->join($table2,$join,$type);
	// 	$this->db->where($where);
	// 	$this->db->order_by($order,$order_type);
	// 	$query=$this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function joing($select,$table1,$table2,$join,$type,$where,$group_by,$order,$order_type)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table1);
	// 	$this->db->join($table2,$join,$type);
	// 	$this->db->where($where);
	// 	$this->db->group_by($group_by);
	// 	$this->db->order_by($order,$order_type);	
	// 	$query=$this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function groupby($select,$table,$group_by,$order,$order_type)
	// {
	// 	$this->db->select($select);
	// 	$this->db->from($table);
	// 	$this->db->group_by($group_by);
	// 	$this->db->order_by($order,$order_type);	
	// 	$query=$this->db->get();
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function query($qry)
	// {
	// 	$query = $this->db->query($qry);
	// 	if($query->num_rows()>0)
	// 	{
	// 		return $query->result_array();
	// 	}
	// 	else
	// 	{
	// 		return false;
	// 	}
	// }
	// public function run_query($qry)
	// {
	// 	$this->db->query($qry);
	// }

    // public function get_by_id($id) {
    //     return $this->db->get_where($this->table, array('id' => $id))
    //                     ->row();
    // }

    // public function get_where($where) {
    //     return $this->db->where($where)
    //                     ->get($this->table)
    //                     ->result();
    // } 

    // public function update($id, $data) {
    //     $this->db->where('id', $id);
    //     $this->db->update($this->table, $data);
    // }

    // public function delete($id) {
    //     $this->db->where('id', $id);
    //     $this->db->delete($this->table);
    // }
}

