<?php
class Evaluation_model extends Project_model{
	function __construct(){
		parent::__construct();
	}
	
	function getList(array $args = array()) {
		!isset($args['type']) && $args['type']='evaluation';
		return parent::getList($args);
	}
	
	function applyModel($project,$model){
		return $this->db->query("
			INSERT IGNORE INTO evaluation_indicator (project,indicator,candidates,judges,weight)
			SELECT ".intval($project).",indicator,candidates,judges,weight 
			FROM `evaluation_model_indicator` 
			WHERE company = {$this->company->id} AND model = ".intval($model)."
		");
	}
	
	function getModels(){
		$this->db->from('evaluation_model')
			->where('company',$this->company->id);
		
		return array_sub($this->db->get()->result_array(),'name','id');
	}
	
	function getIndicatorList($project,$candidate=NULL,$judge=NULL, array $args=array()){
		$this->db->from('evaluation_indicator')
			->join('indicator','evaluation_indicator.indicator = indicator.id','inner')
			->where('evaluation_indicator.project',$project)
			->select('indicator.*, evaluation_indicator.candidates, evaluation_indicator.judges, evaluation_indicator.weight');
		
		if($candidate){
			if(is_null($judge)){
				$judge=$this->user->id;
			}
			
			$this->db->join('project_people evaluation_candidates','evaluation_candidates.role = evaluation_indicator.candidates','inner')
				->join('project_people evaluation_judges','evaluation_judges.role = evaluation_indicator.judges','inner')
				->where('evaluation_candidates.people',$candidate)
				->where('evaluation_judges.people',$judge);
			
		}
		
		$db_num_rows=clone $this->db;
		
		if(isset($args['order_by'])){
			if(is_array($args['order_by'])){
				foreach($args['order_by'] as $orderby){
					$this->db->order_by($orderby[0],$orderby[1]);
				}
			}elseif($args['order_by']){
				$this->db->order_by($args['order_by']);
			}
		}
		
		if(isset($args['limit'])){
			if($args['limit']==='pagination'){
				//复制一个DB对象用来计算行数，因为计算行数需要运行sql，将清空DB对象中属性
				$args['limit']=$this->pagination($db_num_rows);
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			elseif(is_array($args['limit'])){
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			else{
				call_user_func(array($this->db,'limit'), $args['limit']);
			}
		}
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 根据project_id和当前用户id
	 * 返回当前用户可评的所有用户
	 */
	function getCandidatesList($project,array $args=array()){
		$this->db->from('evaluation_indicator')
			->join('project_people evaluation_candidates','evaluation_candidates.role = evaluation_indicator.candidates','inner')
			->join('project_people evaluation_judges','evaluation_judges.role = evaluation_indicator.judges','inner')
			->join('people','people.id = evaluation_candidates.people','inner')
			->where('evaluation_indicator.project',$project)
			->where('evaluation_judges.people',$this->user->id);
		
		$db_num_rows=clone $this->db;
		
		$this->db->group_by('evaluation_candidates.people')
			->select('people.id, people.name, evaluation_candidates.role');
		
		if(isset($args['order_by'])){
			if(is_array($args['order_by'])){
				foreach($args['order_by'] as $orderby){
					$this->db->order_by($orderby[0],$orderby[1]);
				}
			}elseif($args['order_by']){
				$this->db->order_by($args['order_by']);
			}
		}
		
		if(isset($args['limit'])){
			if($args['limit']==='pagination'){
				//复制一个DB对象用来计算行数，因为计算行数需要运行sql，将清空DB对象中属性
				$args['limit']=$this->pagination($db_num_rows,true,'evaluation_candidates.people');
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			elseif(is_array($args['limit'])){
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			else{
				call_user_func(array($this->db,'limit'), $args['limit']);
			}
		}
		
		return $this->db->get()->result_array();
	}
	
	function addIndicator($project,$indicator,$evaluation_indicator){
		
		if($indicator['type']==='text'){
			$indicator['weight']=NULL;
		}
		
		$this->db->from('indicator')
			->where($indicator);
		
		$row=$this->db->get()->row();
		
		if(!$row){
			$this->db->insert('indicator',$indicator);
			$indicator_id=$this->db->insert_id();
		}else{
			$indicator_id=$row->id;
		}
		
		$this->db->insert('evaluation_indicator',$evaluation_indicator+array('project'=>intval($project),'indicator'=>$indicator_id));
		
		return $this->db->insert_id();
		
	}
	
	function getCommentList(){
		$q="
		SELECT evaluation_indicator.name,evaluation_indicator.weight,
			evaluation_score.comment,
			IF(position.id=1,position.ui_name,'-') AS position_name,
			IF(position.id=1,staff.name,'-') AS staff_name
		FROM evaluation_score 
			INNER JOIN evaluation_indicator ON evaluation_indicator.id=evaluation_score.indicator AND evaluation_score.quarter='".$this->date->quarter."'
			INNER JOIN staff ON staff.id=evaluation_score.uid
			INNER JOIN position ON evaluation_indicator.critic=position.id
		WHERE comment IS NOT NULL AND staff={$this->user->id}
		";
		
		$q=$this->orderBy($q,'evaluation_score.time','DESC');
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
				WHERE uid = '6356' AND evaluation_score.quarter='{$this->date->quarter}'
				GROUP BY uid,staff
			)manager
			LEFT JOIN(
				SELECT staff,AVG(sum_score) AS score,COUNT(sum_score) AS critics
				FROM (
					SELECT staff,SUM(score) AS sum_score
					FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
					WHERE uid <> '6356' AND staff<>uid AND evaluation_score.quarter='{$this->date->quarter}'
					GROUP BY uid,staff
				)sum
				GROUP BY staff
			)each_other USING (staff) 
			LEFT JOIN(
				SELECT staff,SUM(score) AS score
				FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
				WHERE uid = staff AND evaluation_score.quarter='{$this->date->quarter}'
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
			'quarter'=>$this->date->quarter,
			'company'=>$this->company->id,
			'uid'=>$this->user->id,
			'username'=>$this->user->name,
			'time'=>$this->date->now
		);
		
		//$data_score['anonymous']=$anonymous;
		
		if(isset($field)){
			$data_score[$field]=$value;
		}
		
		if(!$this->db->update('evaluation_score',$data_score,"indicator = '{$indicator}' AND staff = $staff AND quarter = {$this->date->quarter} AND uid = {$this->user->id} AND company = {$this->company->id}")){
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
			$staff=$this->user->id;
		}else{
			$staff=intval($staff);
		}
		
		$query="
			SELECT AVG(score) FROM(
				SELECT SUM(score) AS score FROM evaluation_score WHERE quarter={$this->date->quarter} AND staff='".$staff."' AND uid<>{$this->user->id} AND uid<>(SELECT manager FROM manager_staff WHERE staff = $staff) GROUP BY uid
			)score_sum
		";
		
		return round(db_fetch_field($query),2);
	}
	
	function getSelf($staff=NULL){
		if(is_null($staff)){
			$staff=$this->user->id;
		}
		
		$query="SELECT SUM(score) AS score FROM evaluation_score WHERE quarter={$this->date->quarter} AND staff='".$staff."' AND uid='".$staff."'";
		
		return round(db_fetch_field($query),2);
	}
	
	function getManager($staff=NULL){
		if(is_null($staff)){
			$staff=$this->user->id;
		}
		
		$query="SELECT SUM(score) AS score FROM evaluation_score WHERE quarter={$this->date->quarter} AND staff='".$staff."' AND uid = (SELECT manager FROM manager_staff WHERE staff = '".$staff."')";
		
		return round(db_fetch_field($query),2);
	}
}
?>