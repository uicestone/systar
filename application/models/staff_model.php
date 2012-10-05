<?php
class Staff_model extends CI_Model{
	function __construct() {
		parent::__construct();
	}
	
	function check($staff_name,$data_type='id',$show_error=true){
			//$data_type:id,array
			global $_G;

			if(!$staff_name){
					if($show_error){
							showMessage('请输入职员名称','warning');
					}
					return -3;
			}

			$q_lawyer="SELECT * FROM `staff` WHERE company='".$_G['company']."' AND `name` LIKE '%".$staff_name."%'";
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

	function fetch($staff_id){
			return db_fetch_first("SELECT * FROM staff WHERE id='".$staff_id."'");
	}

	function getMyManager($field=NULL){
			$manager=db_fetch_first("SELECT * FROM staff WHERE id = (SELECT manager FROM manager_staff WHERE staff = '".$_SESSION['id']."')");
			if(is_null($field)){
					return $manager['id'];
			}else{
					return $manager[$field];
			}
	}  
}
?>