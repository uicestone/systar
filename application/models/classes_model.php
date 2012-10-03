<?php
class Classes_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM class WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
	
	function check($class_name,$data_type='id',$show_error=true,$save_to=NULL){
		//$data_type:id,array
		if(!$class_name){
			if($show_error){
				showMessage('请输入班级名称','warning');
			}
			return -3;
		}
	
		$q_lawyer="SELECT * FROM `class` WHERE `name` LIKE '%".$class_name."%'";
		$r_lawyer=db_query($q_lawyer);
		$num_classes=db_rows($r_lawyer);
	
		if($num_classes==0){
			if($show_error){
				showMessage('没有这个班级','warning');
			}
			return -1;
			
		}elseif($num_classes>1){
			if($show_error){
				showMessage('此关键词存在多个符合班级','warning');
			}
			return -2;
	
		}else{
			$data=db_fetch_array($r_lawyer);
			if($data_type=='array'){
				$return=$data;
			}else{
				$return=$data[$data_type];
			}
			
			if(!is_null($save_to)){
				post($save_to,$return);
			}
			return $return;
		}
	}
	
	
	function fetchByStudentId($student_id){
		return db_fetch_first("SELECT * FROM class WHERE id = (SELECT class FROM student_class WHERE student = '".$student_id."' AND term='".$_SESSION['global']['current_term']."')");
	}
}
?>