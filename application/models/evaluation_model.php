<?php
class Evaluation_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function insert_score($indicator,$staff,$field,$value/*,$anonymous*/){
		global $_G;
	
		if($field=='score'){
			$weight=db_fetch_field("SELECT weight FROM evaluation_indicator WHERE id = '".$indicator."'");
			
			if(($value>$weight || $value<0)){
				echo '请在0至满分间打分';
				return false;
			}
		}
		
		if(isset($field) && !in_array($field,array('score','comment'))){
			exit('request denied');
		}
		
		$data=array(
			'indicator'=>$indicator,
			'staff'=>$staff,
			'quarter'=>$_G['quarter'],
			'company'=>$this->config->item('company'),
			'uid'=>$_SESSION['id'],
			'username'=>$_SESSION['username'],
			'time'=>$this->config->item('timestamp')
		);
		
		//$data_score['anonymous']=$anonymous;
		
		if(isset($field)){
			$data_score[$field]=$value;
		}
		
		if(!$this->db_update('evaluation_score',$data_score,"indicator='".$indicator."' AND staff='".$staff."' AND quarter='".$_G['quarter']."' AND uid='".$_SESSION['id']."' AND company='".$this->config->item('company')."'")){
			return false;
		}
		
		if(db_affected_rows()==0){
			if(!$new_evaluate_score_id=db_insert('evaluation_score',array_merge($data,$data_score))){
				return false;
			}
		}
		
		if(isset($field)){
			return $data_score[$field];
		}
	}
	
	function getPeer($staff=NULL){
		global $_G;
		
		if(is_null($staff)){
			$staff=$_SESSION['id'];
		}
		
		$query="
			SELECT AVG(score) FROM(
				SELECT SUM(score) AS score FROM evaluation_score WHERE quarter='".$_G['quarter']."' AND staff='".$staff."' AND uid<>'".$_SESSION['id']."' AND uid<>(SELECT manager FROM manager_staff WHERE staff = '".$staff."') GROUP BY uid
			)score_sum
		";
		
		return round(db_fetch_field($query),2);
	}
	
	function getSelf($staff=NULL){
		global $_G;
		
		if(is_null($staff)){
			$staff=$_SESSION['id'];
		}
		
		$query="SELECT SUM(score) AS score FROM evaluation_score WHERE quarter='".$_G['quarter']."' AND staff='".$staff."' AND uid='".$staff."'";
		
		return round(db_fetch_field($query),2);
	}
	
	function getManager($staff=NULL){
		global $_G;
		
		if(is_null($staff)){
			$staff=$_SESSION['id'];
		}
		
		$query="SELECT SUM(score) AS score FROM evaluation_score WHERE quarter='".$_G['quarter']."' AND staff='".$staff."' AND uid = (SELECT manager FROM manager_staff WHERE staff = '".$staff."')";
		
		return round(db_fetch_field($query),2);
	}
}
?>