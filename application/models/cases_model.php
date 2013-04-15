<?php
class Cases_model extends Project_model{
	function __construct() {
		parent::__construct();
	}
	
	function add($data=array()){
		$data['type']='业务';
		$this->id=parent::add($data);
		$this->addLabel($this->id, '等待立案审核');
		$this->addLabel($this->id, '案件');
		$this->addLabel($this->id, '所内案源');
		return $this->id;
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

		if(in_array('争议', $labels)){
			$symbol='诉';
		}
		elseif(in_array('法律顾问',$labels)){
			$symbol='顾';
		}else{
			$symbol='非';
		}
		
		if(isset($labels['领域'])){
			switch($labels['领域']){
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
		$project=intval($project);
		$people=intval($people);
		
		$data=array(
			'project'=>$project,
			'people'=>$people,
			'role'=>$role,
			'weight'=>$weight,
			'type'=>'律师'
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('project_people',$data);
		
		return $this->db->insert_id();
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

}
?>
