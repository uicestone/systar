<?php
require_once APPPATH.'/models/people_model.php';
class Staff_model extends People_model{
	function __construct() {
		parent::__construct();
	}
	
	function getList(array $args=array()){
		$args['is_staff']=true;
		return parent::getList($args);
	}
	
	/**
	 * 根据部分名称返回一个职员id或其他信息
	 * @param $part_of_staff_name
	 * @param $data_type
	 */
	function check($part_of_staff_name,$data_type='id'){
		
		if(!$part_of_staff_name){
			$this->output->message('请输入职员名称','warning');
			throw new Exception;
		}

		$query="
			SELECT * 
			FROM `staff`
				INNER JOIN people USING(id)
			WHERE people.company={$this->company->id} AND people.display=1
				AND people.`name` LIKE '%$part_of_staff_name%'
		";
		$result=$this->db->query($query);

		if($result->num_rows()==0){
			$this->output->message('没有这个职员：'.$part_of_staff_name,'warning');
			throw new Exception;

		}elseif($result->num_rows()>1){
			$this->output->message($part_of_staff_name.' 匹配多个职员','warning');
			throw new Exception;

		}else{
			$data=$result->row_array();
			if($data_type=='array'){
				return $data;
				
			}elseif(isset($data[$data_type])){
				return $data[$data_type];
				
			}else{
				return false;
			}
		}
	}
	
	function fetch($staff_id,$field=NULL){
		$staff_id=intval($staff_id);
		
		$query="
			SELECT * 
			FROM staff 
				INNER JOIN people USING(id)
			WHERE people.id = $staff_id AND people.company = {$this->company->id}
		";
		
		$array=$this->db->query($query)->row_array();
		if(isset($field)){
			return isset($array[$field])?$array[$field]:false;
		}else{
			return $array;
		}
	}

	/**
	 * 根据部分客户名称返回匹配的客户id和名称列表
	 * @param $part_of_name
	 * @return array
	 */
	function match($part_of_name){
		$part_of_name=mysql_real_escape_string($part_of_name);
		
		$query="
			SELECT people.id,people.name 
			FROM people
			WHERE people.company={$this->company->id} AND people.display=1 
				AND type='职员'
				AND (name LIKE '%$part_of_name%' OR abbreviation LIKE '$part_of_name' OR name_en LIKE '%$part_of_name%')
			ORDER BY people.id DESC
		";

		return $this->db->query($query)->result_array();
	}

	function getMyManager($field=NULL){
		$manager=$this->db->query("SELECT * FROM people WHERE id = (SELECT relative FROM people_relationship WHERE people = {$this->user->id} AND relation='主管')")->row_array();
		if(is_null($field) && isset($manager['id'])){
			return $manager['id'];
		}elseif(isset($manager[$field])){
			return $manager[$field];
		}else{
			return false;
		}
	}
	
}
?>