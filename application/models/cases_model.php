<?php
class Cases_model extends Project_model{
	function __construct() {
		parent::__construct();
		parent::$field['type']='cases';
	}
	
	/*
	 * 根据案件信息，获得案号
	 */
	function getNum($tags){
		$num=$this->company->config('cases/num/'.$this->date->year);

		if($num===false){
			$num=1;
		}else{
			$num++;
		}
		
		$num=substr($num+1000, 1, 3);

		$this->company->config('cases/num/'.$this->date->year, $num);

		if(in_array('争议', $tags)){
			$symbol='诉';
		}
		elseif(in_array('法律顾问',$tags)){
			$symbol='顾';
		}else{
			$symbol='非';
		}
		
		if(isset($tags['领域'])){
			switch($tags['领域']){
				case '公司':$symbol.='（公）'.$this->date->year.'-1-';break;
				case '房产建筑':$symbol.='（房）'.$this->date->year.'-2-';;break;
				case '婚姻家庭':$symbol.='（家）'.$this->date->year.'-3-';;break;
				case '劳动人事':$symbol.='（劳）'.$this->date->year.'-4-';;break;
				case '知识产权':$symbol.='（知）'.$this->date->year.'-5-';;break;
				case '诉讼':$symbol.='（诉）'.$this->date->year.'-6-';;break;
				case '刑事行政':$symbol.='（刑）'.$this->date->year.'-7-';;break;
				case '涉外':$symbol.='（外）'.$this->date->year.'-8-';;break;
				case '韩日':$symbol.='（韩）'.$this->date->year.'-9-';;break;
				default:$symbol.='';
			}
		}
		
		return $symbol.$num;
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
	function getCompiledTags($case_id){
		$case_id=intval($case_id);
		
		$this->db->select('tag.id,tag.name,tag.order,tag.color')
			->from('project_tag')
			->join('tag',"project_tag.tag = tag.id",'INNER')
			->where('project_tag.project',$case_id)
			->order_by('tag.order','DESC');
		
		$result=$this->db->get()->result_array();
		
		$tags=array();
		
		foreach($result as $row){
			$tags[$row['name']]=$row;
		}
		
		if(isset($tags['客户已锁定']) && isset($tags['职员已锁定']) && isset($tags['费用已锁定'])){
			unset($tags['客户已锁定']);unset($tags['职员已锁定']);unset($tags['费用已锁定']);
			$tags['已锁定']=array('name'=>'已锁定','color'=>'#080');
		}
		
		if(isset($tags['通过财务审核']) && isset($tags['通过信息审核']) && isset($tags['通过主管审核']) && isset($tags['案卷已归档'])){
			unset($tags['通过财务审核']);unset($tags['通过信息审核']);unset($tags['通过主管审核']);unset($tags['案卷已归档']);
			$tags['已归档']=array('name'=>'已归档','color'=>'#888');
		}
		
		$tags_string='<div class="select2-container-multi"><ul class="select2-choices">';
		
		foreach($tags as $key=>$tag){
			if(!is_array($this->config->user_item('search/tags')) || !in_array($key,$this->config->user_item('search/tags'))){
				$tags_string.='<li class="select2-search-choice" style="color:'.$tag['color'].'">'.$tag['name'].'</li>';
			}
		}
		
		$tags_string.='</ul></div>';
		
		return $tags_string;
	}
	
	function getList(array $args=array()){
		!isset($args['type']) && $args['type']='cases';
		return parent::getList($args);
	}
	
}
?>
