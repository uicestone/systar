<?php
class Project_model extends Object_model{
	
	static $fields;

	function __construct(){
		parent::__construct();
		$this->table='project';
		parent::$fields['type']=$this->table;
		self::$fields=array(
			'num'=>NULL,//编号
			'active'=>true,//是否有效
			'first_contact'=>NULL,//首次接洽时间
			'time_contract'=>NULL,//签约时间
			'end'=>NULL,//（预估）完结时间
			'quote'=>NULL,//报价
			'focus'=>NULL,//焦点
			'summary'=>NULL,//概况
			'comment'=>NULL,//备注
		);
	}
	
	function add(array $data){
		
		$insert_id=parent::add($data);
		
		foreach(array('first_contact','time_contract','end') as $date_field){
			if(empty($data[$date_field])){
				$data[$date_field]=NULL;
			}
		}
		
		$data['active']=true;
		
		$data=array_merge(self::$fields,array_intersect_key($data,self::$fields));
		$data['id']=$insert_id;
		$this->db->insert($this->table,$data);

		return $insert_id;
	}
	
	function update($id,array $data){
		
		foreach(array('first_contact','time_contract','end') as $date_field){
			if(empty($data[$date_field])){
				$data[$date_field]=NULL;
			}
		}
		
		return parent::update($id,$data);
	}
	
	function getCompiledPeople($project_id){
		
		$people=$this->people->getList(array('in_project'=>$project_id));
		$compiled='';
		foreach($people as $person){
			$compiled.='<span title="'.$person['role'].'"><a href="#'.$person['type'].'/'.$person['id'].'">'.$person['abbreviation'].'</a></span> ';
		}
		
		return $compiled;
	}
	
	function getCompiledPeopleRoles($project_id,$people_id){
		
		$roles=$this->getPeopleRoles($project_id, $people_id);
		
		$compiled='';
		foreach($roles as $role){
			$compiled.='<span role="'.$role['role'].'">'.$role['role'];
			if($role['weight']){
				$compiled.='('.($role['weight']*100).'%)';
			}
			$compiled.='</span> ';
		}
		
		return $compiled;
	}
	
	/**
	 * 获得一个项目下某个相关人员或所有人员的所有角色和其他属性
	 * @param int $project_id
	 * @param int $people_id, optional
	 * @return 
	 * array(
	 *	array(
	 *		role=>role_name
	 *		weight=>weight_in_role
	 *	),
	 *	...
	 * )
	 * or if people_id unspecified:
	 * array(
	 *	people_id=>array(
	 *		array(
	 *			role=>role_name
	 *			weight=>weight_in_role
	 *		)
	 *		...
	 *	)
	 *	...
	 * )
	 */
	function getPeopleRoles($project_id,$people_id=NULL){
		$this->db->from('project_people')
			->where(array('project'=>intval($project_id)))
			->select('role,weight');
		
		if($people_id){
			$this->db->where(array('people'=>intval($people_id)));
		}else{
			$this->db->select('people');
		}
		
		$result_array=$this->db->get()->result_array();
		
		if($people_id){
			return $result_array;
		}else{
			$people_roles=array();
			foreach($result_array as $row){
				$people_roles[$row['people']][]=$row;
			}
			return $people_roles;

		}
	}
	
	function removePeopleRole($project_id,$people_id,$role){
		return $this->db->delete('project_people',array(
			'project'=>$project_id,
			'people'=>$people_id,
			'role'=>$role
		));
	}
	
	/**
	 * 获得一个项目下某个角色或所有角色的所有相关人员的id和其他属性
	 * @param int $project_id
	 * @param string $role, optional
	 * @return 
	 * array(
	 *	array(
	 *		people=>people_id
	 *		weight=>weight_in_role
	 *	),
	 *	...
	 * )
	 * or if role unspecified:
	 * array(
	 *	role=>array(
	 *		array(
	 *			people=>people_id
	 *			weight=>weight_in_role
	 *		)
	 *		...
	 *	)
	 *	...
	 * )
	 */
	function getRolesPeople($project_id,$role=NULL){
		$this->db->from('project_people')
			->where(array('project'=>intval($project_id)))
			->select('people,weight');
		
		if($role){
			$this->db->where(array('role'=>$role));
		}else{
			$this->db->select('role');
		}
		
		$result_array=$this->db->get()->result_array();
		
		if($role){
			return $result_array;
		}else{
			$roles_people=array();
			foreach($result_array as $row){
				$roles_people[$row['role']][]=$row;
			}
			return $roles_people;

		}
	}
	
	/**
	 * @param array $tags
	 * @return array
	 */
	function getRelatedRoles($tags=NULL){
		$this->db->select('project_people.role, COUNT(*) AS hits',false)
			->from('project_people')
			->join('project',"project_people.project = project.id AND project.company = {$this->company->id}")
			->where('project_people.role IS NOT NULL',NULL,FALSE)
			->group_by('project_people.role')
			->order_by('hits', 'desc');
		
		if($tags){
			$this->db->join('project_tag',"project_tag.project = project_people.project AND project_tag.tag_name{$this->db->escape_array($tags)}");
		}
		
		$result=$this->db->get()->result_array();
		
		return array_column($result,'role');
	}
	
	function addPeople($project_id,$people_id,$type=NULL,$role=NULL,$weight=NULL){
		
		$this->db->insert('project_people',array(
			'project'=>$project_id,
			'people'=>$people_id,
			'type'=>$type,
			'role'=>$role,
			'weight'=>$weight
		));
		
		return $this->db->insert_id();
	}
	
	function removePeople($project_id,$people_id){
		$people_id=intval($people_id);
		return $this->db->delete('project_people',array('project'=>$project_id,'people'=>$people_id));
	}
	
	function addDocument($project_id,$document_id){
		$project_id=intval($project_id);
		$document_id=intval($document_id);
		
		$data=array(
			'project'=>$project_id,
			'document'=>$document_id
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('project_document',$data);
		
		return $this->db->insert_id();
	}
	
	function removeDocument($project_id,$document_id){
		return $this->db->delete('project_document',array('document'=>$document_id,'project'=>$project_id));
	}
	
	function count(array $args=array()){
		$args['count']=true;
		$result=$this->getList($args);
	}
	
	/**
	 * @param array $args
	 * people
	 *	role
	 * num
	 * active
	 * is_relative_of
	 * before
	 * time_contract
	 *	from
	 *	to
	 * first_contact
	 *	from
	 *	to
	 * count
	 * group
	 *	team
	 *	people
	 *		role
	 * 
	 */
	function getList(array $args=array()){

		if(isset($args['people'])){
			
			$args['has_relative_like']=$args['people'];
			
			if(isset($args['role'])){
				$args['has_relative_like__role']=$args['role'];
			}
		}
		
		if(isset($args['people_is_relative_of'])){
			$where="project.id IN (SELECT project FROM project_people WHERE people IN (SELECT relative FROM people_relationship WHERE people{$this->db->escape_int_array($args['people_is_relative_of'])})";
			if(isset($args['role'])){
				$where.=" AND role = '{$args['role']}'";
			}
			$where.=')';
			
			$this->db->where($where,NULL,FALSE);
		}
		
		if(isset($args['people_has_relative_like'])){
			$args['has_secondary_relative_like']=$args['people_has_relative_like'];
			$args['has_secondary_relative_like__media']='people';
		}

		if(isset($args['people_is_relative_of'])){
			$args['has_secondary_relative_like']=$args['people_has_relative_like'];
			$args['has_secondary_relative_like__media']='people';
		}

		if(isset($args['active'])){
			$this->db->where('project.active',(bool)$args['active']);
		}
		
		if(isset($args['before'])){
			$args['id_less_than']=$args['before'];
		}
		
		if(isset($args['count'])){
			$this->db->select('COUNT(*) as `count`',false);
		}
		
		if(isset($args['group_by'])){
			if($args['group_by']==='team'){
				$this->db->join('team','team.id = project.team','inner')
					->group_by('object.team')
					->select('team.id `team`, team.name `team_name`');
			}
			
			if($args['group_by']==='people'){
				$this->db->join('object_relationship','object_relationship.object = object.id','inner')
					->join('object people','people.id = object_relationship.relative','inner')
					->group_by('object_relationship.relative')
					->select('people.name AS people_name, people.id AS people');
				
				if(isset($args['role'])){
					$this->db->where('object_relationship.role',$args['role']);
				}
			}
		}
		
		return parent::getList($args);
		
	}
	
	function addRelative($project,$relative,$relation=NULL){
		$data=array(
			'project'=>intval($project),
			'relative'=>intval($relative),
			'relation'=>$relation
		);
		
		$this->db->replace('project_relationship',$data);
		
		return $this->db->insert_id();
	}
	
	function removeRelative($project_id,$relative_id){
		return $this->db->delete('project_relationship',array('project'=>intval($project_id),'relative'=>intval($relative_id)));
	}
	
}
?>