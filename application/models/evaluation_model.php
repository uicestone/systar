<?php
class Evaluation_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function getCommentList(){
		$q="
		SELECT evaluation_indicator.name,evaluation_indicator.weight,
			evaluation_score.comment,
			position.ui_name AS position_name,
			staff.name AS staff_name
		FROM evaluation_score 
			INNER JOIN evaluation_indicator ON evaluation_indicator.id=evaluation_score.indicator AND evaluation_score.quarter='".$this->config->item('quarter')."'
			INNER JOIN staff ON staff.id=evaluation_score.uid
			INNER JOIN position ON evaluation_indicator.critic=position.id
		WHERE comment IS NOT NULL AND staff='".$_SESSION['id']."'
		";
		
		$q=$this->orderBy($q,'evaluation_score.time','DESC');
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
	}
	
	function getStaffList(){
		$query="
			SELECT staff.id,staff.name,position.ui_name AS position_name
			FROM staff
				INNER JOIN position ON position.id=staff.position
		";
		
		$query=$this->orderby($query,'id');
		$query=$this->pagination($query);
		
		return $this->db->query($query)->result_array();
	}
	
	function getIndicatorList($staff){
		$staff=intval($staff);
		
		$position=$this->db->query("SELECT position FROM staff WHERE id = $staff")->row()->position;

		$q="	
			SELECT evaluation_indicator.id,evaluation_indicator.name,evaluation_indicator.weight,
				evaluation_score.id AS score_id,evaluation_score.score,evaluation_score.comment
			FROM evaluation_indicator 
				LEFT JOIN evaluation_score ON (
					evaluation_indicator.id=evaluation_score.indicator
					AND evaluation_score.quarter = {$this->config->item('quarter')}
					AND staff = $staff
					AND uid = {$_SESSION['id']}
				)
			WHERE critic = {$_SESSION['position']}
				AND position = $position
		";
		
		$q=$this->orderby($q,'id');
		
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
		
	}
	
	function getResultList(){
		$q="
			SELECT each_other.staff,staff.name AS staff_name,each_other.score AS each_other,each_other.critics,self.score AS self,manager.score AS manager
			FROM
			(
				SELECT staff,SUM(score) AS score
				FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
				WHERE uid = '6356' AND evaluation_score.quarter='{$this->config->item('quarter')}'
				GROUP BY uid,staff
			)manager
			LEFT JOIN(
				SELECT staff,AVG(sum_score) AS score,COUNT(sum_score) AS critics
				FROM (
					SELECT staff,SUM(score) AS sum_score
					FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
					WHERE uid <> '6356' AND staff<>uid AND evaluation_score.quarter='{$this->config->item('quarter')}'
					GROUP BY uid,staff
				)sum
				GROUP BY staff
			)each_other USING (staff) 
			LEFT JOIN(
				SELECT staff,SUM(score) AS score
				FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
				WHERE uid = staff AND evaluation_score.quarter='{$this->config->item('quarter')}'
				GROUP BY uid,staff
			)self USING(staff)
			INNER JOIN staff ON staff.id=each_other.staff	
		";

		$q=$this->orderby($q,'staff');

		return $this->db->query($q)->result_array();
	}
	
	function insertScore($indicator,$staff,$field,$value/*,$anonymous*/){
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
			'quarter'=>$this->config->item('quarter'),
			'company'=>$this->config->item('company'),
			'uid'=>$_SESSION['id'],
			'username'=>$_SESSION['username'],
			'time'=>$this->config->item('timestamp')
		);
		
		//$data_score['anonymous']=$anonymous;
		
		if(isset($field)){
			$data_score[$field]=$value;
		}
		
		if(!$this->db->update('evaluation_score',$data_score,"indicator = '{$indicator}' AND staff = $staff AND quarter = {$this->config->item('quarter')} AND uid = {$_SESSION['id']} AND company = {$this->config->item('company')}")){
			return false;
		}
		
		if($this->db->affected_rows()==0){
			if(!$new_evaluate_score_id=$this->db->insert('evaluation_score',array_merge($data,$data_score))){
				return false;
			}
		}
		
		if(isset($field)){
			return $data_score[$field];
		}
	}
	
	function getPeer($staff=NULL){
		if(is_null($staff)){
			$staff=$_SESSION['id'];
		}
		
		$query="
			SELECT AVG(score) FROM(
				SELECT SUM(score) AS score FROM evaluation_score WHERE quarter={$this->config->item('quarter')} AND staff='".$staff."' AND uid<>'".$_SESSION['id']."' AND uid<>(SELECT manager FROM manager_staff WHERE staff = '".$staff."') GROUP BY uid
			)score_sum
		";
		
		return round(db_fetch_field($query),2);
	}
	
	function getSelf($staff=NULL){
		if(is_null($staff)){
			$staff=$_SESSION['id'];
		}
		
		$query="SELECT SUM(score) AS score FROM evaluation_score WHERE quarter={$this->config->item('quarter')} AND staff='".$staff."' AND uid='".$staff."'";
		
		return round(db_fetch_field($query),2);
	}
	
	function getManager($staff=NULL){
		if(is_null($staff)){
			$staff=$_SESSION['id'];
		}
		
		$query="SELECT SUM(score) AS score FROM evaluation_score WHERE quarter={$this->config->item('quarter')} AND staff='".$staff."' AND uid = (SELECT manager FROM manager_staff WHERE staff = '".$staff."')";
		
		return round(db_fetch_field($query),2);
	}
}
?>