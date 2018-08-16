<?php
class Cases_model extends Project_model{
	function __construct() {
		parent::__construct();
		$this->fields['type']='cases';
	}
	
	/*
	 * 根据案件信息，获得案号
	 */
	function getNum($labels){
		$num=$this->company->config('cases/num/'.$this->date->year);

		if($num===false){
			$num=1;
		}else{
			$num++;
		}
		
		$num=substr($num+1000, 1, 3);

		$this->company->config('cases/num/'.$this->date->year, $num);

		return $num;
	}
	
	function addStaff($project,$people,$role,$weight=NULL){
		return $this->addPeople($project, $people, '律师', $role, $weight);
	}
	
	/**
	 * 获得一个案件的主办律师名
	 * @return string
	 */
	function getResponsibleStaffNames($case_id){
		$case_id=intval($case_id);
		
		$this->db->select("GROUP_CONCAT(people.name) names",false)
			->from('project_people')
			->join('people',"project_people.people = people.id AND project_people.role = '主办律师'",'INNER')
			->where('project_people.project',$case_id);
		
		return $this->db->get()->row()->names;
	}
	
	/**
	 * 根据案件id获得标签，进而生成描述性字符串
	 * @return string
	 */
	function getCompiledLabels($case_id){
		$case_id=intval($case_id);
		
		$this->db->select('label.id,label.name,label.order,label.color')
			->from('project_label')
			->join('label',"project_label.label = label.id",'INNER')
			->where('project_label.project',$case_id)
			->order_by('label.order','DESC');
		
		$result=$this->db->get()->result_array();
		
		$labels=array();
		
		foreach($result as $row){
			$labels[$row['name']]=$row;
		}
		
		if(isset($labels['客户已锁定']) && isset($labels['职员已锁定']) && isset($labels['费用已锁定'])){
			unset($labels['客户已锁定']);unset($labels['职员已锁定']);unset($labels['费用已锁定']);
			$labels['已锁定']=array('name'=>'已锁定','color'=>'#080');
		}
		
		if(isset($labels['通过财务审核']) && isset($labels['通过信息审核']) && isset($labels['通过主管审核']) && isset($labels['案卷已归档'])){
			unset($labels['通过财务审核']);unset($labels['通过信息审核']);unset($labels['通过主管审核']);unset($labels['案卷已归档']);
			$labels['已归档']=array('name'=>'已归档','color'=>'#888');
		}
		
		$labels_string='<div class="select2-container-multi"><ul class="select2-choices">';
		
		foreach($labels as $key=>$label){
			if(!is_array($this->config->user_item('search/labels')) || !in_array($key,$this->config->user_item('search/labels'))){
				$labels_string.='<li class="select2-search-choice" style="color:'.$label['color'].'">'.$label['name'].'</li>';
			}
		}
		
		$labels_string.='</ul></div>';
		
		return $labels_string;
	}
	
	function getList(array $args=array()){
		!isset($args['type']) && $args['type']='cases';
		return parent::getList($args);
	}
	
}
?>
