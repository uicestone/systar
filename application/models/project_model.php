<?php
class Project_model extends BaseItem_model{
	
	static $fields=array(
		'name'=>'名称',
		'num'=>'编号',
		'active'=>'是否有效',
		'type'=>'类型',
		'first_contact'=>'首次接洽时间',
		'time_contract'=>'签约时间',
		'time_end'=>'（预估）完结时间',
		'quote'=>'报价',
		'timing_fee'=>'是计时收费',
		'focus'=>'焦点',
		'summary'=>'概况',
		'comment'=>'备注',
		'display'=>'显示在列表中'
	);
	
	function __construct(){
		parent::__construct();
		$this->table='project';
	}
	
	function match($part_of_name){
		
		$this->db->select('project.id,project.num,project.name')
			->from('project')
			->where("
				project.company={$this->company->id} AND project.display=1 
				AND (name LIKE '%$part_of_name%' OR num LIKE '%$part_of_name%' OR name_extra LIKE '%$part_of_name%')
			")
			->order_by('project.id','desc');
		
		return $this->db->get()->result_array();
	}

	function add($data=array()){
		$data=array_intersect_key($data, self::$fields);
		
	    $data+=uidTime(true,true);
		
		$data['active']=true;
		
	    $this->db->insert('project',$data);
		return $this->db->insert_id();
	}
	
	function update($id,$data){
		$id=intval($id);
	    $data=array_intersect_key((array)$data,self::$fields);
		
		$data+=uidTime();
	    
		return $this->db->update('project',$data,array('id'=>$id));
	}
	
	function getCompiledPeople($project_id){
		$people_model = new People_model();
		
		$people=$people_model->getList(array('project'=>$project_id));
		$compiled='';
		foreach($people as $person){
			$compiled.='<span title="'.$person['role'].'"><a href="#people/edit/'.$person['id'].'">'.$person['abbreviation'].'</a></span> ';
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
	 * 获得一个所有可选的事务人员角色
	 */
	function getAllRoles(){
		$this->db->select('project_people.role,COUNT(*) AS hits',false)
			->from('project_people')
			->join('project',"project_people.project = project.id AND project.company = {$this->company->id}")
			->group_by('project_people.role')
			->order_by('hits', 'desc');
		
		$result=$this->db->get()->result_array();
		
		return array_sub($result,'role');
	}
	
	function addPeople($project_id,$people_id,$type=NULL,$role=NULL){
		
		$this->db->insert('project_people',array(
			'project'=>$project_id,
			'people'=>$people_id,
			'type'=>$type,
			'role'=>$role
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
	
	function removeDocument($project_id,$project_document_id){
		$project_id=intval($project_id);
		$project_document_id=intval($project_document_id);
		return $this->db->delete('project_document',array('id'=>$project_document_id,'project'=>$project_id));
	}
	
	function getDocumentList($project_id){
		$project_id=intval($project_id);
		
		$query="
			SELECT project_document.id,document.id AS document,document.name,extname,type.name AS type,document.comment,document.time,document.username
			FROM 
				document
				INNER JOIN project_document ON document.id=project_document.document
				LEFT JOIN (
					SELECT label.name,document_label.document
					FROM document_label 
						INNER JOIN label ON document_label.label=label.id
					WHERE document_label.type='类型'
				)type ON document.id=type.document
			WHERE display=1 AND project_document.project = $project_id
			ORDER BY time DESC";

		return $this->db->query($query)->result_array();
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
	function getList($args=array()){

		if(isset($args['people'])){
			$this->db->where("
				project.id IN (SELECT `project` FROM project_people WHERE people = {$args['people']})
			",NULL,false);
			
			if(isset($args['role'])){
				$this->db->where('project_people.role',$args['role']);
			}
		
		}

		if(isset($args['num'])){
			$this->db->where('project.num',$args['num']);
		}
		
		if(isset($args['active'])){
			$this->db->where('project.active',(bool)$args['active']);
		}
		
		if(isset($args['is_relative_of'])){
			$this->db->select('relationship.relation')
				->join('project_relationship relationship',"relationship.relative = project.id",'INNER')
				->where('relationship.project',intval($args['is_relative_of']));
		}
		
		if(isset($args['before'])){
			$this->db->where('project.id <',$args['before']);
		}
		
		if(isset($args['time_contract'])){
			
			if(isset($args['time_contract']['from']) && $args['time_contract']['from']){
				$this->db->where("TO_DAYS(project.time_contract) >= TO_DAYS('{$args['time_contract']['from']}')",NULL,FALSE);
			}
			
			if(isset($args['time_contract']['to']) && $args['time_contract']['to']){
				$this->db->where("TO_DAYS(project.time_contract) <= TO_DAYS('{$args['time_contract']['to']}')",NULL,FALSE);
			}
			
		}
		
		if(isset($args['first_contact'])){
			
			if(isset($args['first_contact']['from']) && $args['first_contact']['from']){
				$this->db->where("TO_DAYS(project.first_contact) >= TO_DAYS('{$args['first_contact']['from']}')",NULL,FALSE);
			}
			
			if(isset($args['first_contact']['to']) && $args['first_contact']['to']){
				$this->db->where("TO_DAYS(project.first_contact) <= TO_DAYS('{$args['first_contact']['to']}')",NULL,FALSE);
			}
			
		}
		
		if(isset($args['count'])){
			$this->db->select('COUNT(*) as `count`',false);
		}
		
		if(isset($args['group'])){
			if($args['group']==='team'){
				$this->db->join('team','team.id = project.team','inner')
					->group_by('project.team')
					->select('team.id AS team, team.name AS team_name');
			}
			
			if($args['group']==='people'){
				$this->db->join('project_people','project_people.project = project.id','inner')
					->join('people','people.id = project_people.people','inner')
					->group_by('project_people.people')
					->select('people.name AS people_name, people.id AS people');
				
				if(isset($args['role'])){
					$this->db->where('project_people.role',$args['role']);
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
		
		$this->db->insert('project_relationship',$data);
		
		return $this->db->insert_id();
	}
	
	function removeRelative($project_id,$relative_id){
		return $this->db->delete('project_relationship',array('project'=>intval($project_id),'relative'=>intval($relative_id)));
	}
	
}
?>