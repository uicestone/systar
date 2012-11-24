<?php
class Staff_model extends SS_Model{
	function __construct() {
		parent::__construct();
	}
	
	function check($staff_name,$data_type='id',$show_error=true){
		//$data_type:id,array
		if(!$staff_name){
			if($show_error){
					showMessage('请输入职员名称','warning');
			}
			return -3;
		}

		$q_lawyer="SELECT * FROM `staff` WHERE company='".$this->config->item('company')."' AND `name` LIKE '%".$staff_name."%'";
		$r_lawyer=db_query($q_lawyer);
		$num_lawyers=db_rows($r_lawyer);

		if($num_lawyers==0){
			if($show_error){
					showMessage('没有这个职员：'.$staff_name,'warning');
			}
			return -1;

		}elseif($num_lawyers>1){
			if($show_error){
					showMessage('此关键词存在多个符合职员','warning');
			}
			return -2;

		}else{
			$data=db_fetch_array($r_lawyer);
			if($data_type=='array'){
					$return=$data;
			}else{
					$return=$data[$data_type];
			}
			return $return;
		}
	}

	function fetch($staff_id,$field=NULL){
		$query="
			SELECT * 
			FROM staff 
			WHERE id='{$staff_id}' AND company='{$this->config->item('company')}'";
		$array=$this->db->query($query)->row_array();
		if(isset($field)){
			return isset($array[$field])?$array[$field]:false;
		}else{
			return $array;
		}
	}

	function getMyManager($field=NULL){
		$manager=$this->db->query("SELECT * FROM staff WHERE id = (SELECT manager FROM manager_staff WHERE staff = '{$this->user->id}')")->row_array();
		if(is_null($field)){
			return $manager['id'];
		}else{
			return $manager[$field];
		}
	}
	
	function getList(){
		$q="SELECT staff.id,staff.name,staff.title,staff.modulus,staff.timing_fee_default,
				course.name AS course_name,
				position.ui_name AS position_name
			FROM staff LEFT JOIN course ON staff.course=course.id
				LEFT JOIN position ON staff.position=position.id
			WHERE staff.company='".$this->config->item('company')."'
		";
		$q=$this->search($q,array('name'=>'姓名'));
		$q=$this->orderBy($q,'staff.id','ASC');
		$q=$this->pagination($q);
		return $this->db->query($q)->result_array();
	}
}
?>