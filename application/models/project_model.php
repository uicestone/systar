<?php
class Project_model extends BaseItem_model{
	
	static $fields=array(
		'name'=>'名称',
		'num'=>'编号',
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
		$query="
			SELECT project.id,project.num,project.name
						FROM `project`
			WHERE project.company={$this->company->id} AND project.display=1 
				AND (name LIKE '%$part_of_name%' OR num LIKE '%$part_of_name%' OR name_extra LIKE '%$part_of_name%')
			ORDER BY project.id DESC
		";

		return $this->db->query($query)->result_array();
	}

	function add($data=array()){
		$data=array_intersect_key($data, self::$fields);
		
	    $data+=uidTime(true,true);
		
	    $this->db->insert('project',$data);
		return $this->db->insert_id();
	}
	
	function update($id,$data){
		$id=intval($id);
	    $data=array_intersect_key((array)$data,self::$fields);
		
		$data['display']=true;

		$data+=uidTime();
	    
		return $this->db->update('project',$data,array('id'=>$id));
	}
	
	function getCompiledPeople($project_id){
		$this->load->model('people_model','people');
		$people=$this->people->getList(array('project'=>$project_id,'limit'=>false));
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
			if($role['weight'] && $role['weight']<1){
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
	
	function getList($args=array()){
		$this->db->select("
			project.id,project.name,project.num,project.time_contract
		",false);
		
		if(isset($args['people'])){
			$this->db->where("
				project.id IN (SELECT `project` FROM project_people WHERE people = {$args['people']})
			",NULL,false);
		}

		//当前用户作为某种角色的项目
		if(isset($args['role'])){
			$this->db->where("
				project.id IN (SELECT `project` FROM project_people WHERE people = {$this->user->id} AND role = '{$args['role']}')
			",NULL,false);
		}
		
		if(isset($args['num'])){
			$this->db->where('project.num',$args['num']);
		}
		
		if(isset($args['name'])){
			$this->db->like('project.name',$args['name']);
		}
		
		if(isset($args['is_relative_of'])){
			$this->db->select('relationship.relation')
				->join('project_relationship relationship',"relationship.relative = project.id",'INNER')
				->where('relationship.project',intval($args['is_relative_of']));
		}
		
		if(isset($args['before'])){
			$this->db->where('project.id <',$args['before']);
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
	
	function getIdByCaseFee($case_fee_id){
		$case_fee_id=intval($case_fee_id);
		
		$query="SELECT `project` FROM project_account WHERE id = $case_fee_id";
		
		$result = $this->db->get_where('project_account',array('id'=>$case_fee_id))->row();
		
		if(!$result){
			return false;
		}
		
		return $result->project;
	}
	
	/**
	 * 获得与一个客户相关的所有案件
	 * @param type $client_id
	 * @return 一个案件列表，包含案件名称，案号和主办律师
	 */
	function getListByPeople($people_id){
		$people_id=intval($people_id);
		
		$query="
			SELECT project.id,project.name AS project_name,project.num,	
				GROUP_CONCAT(DISTINCT staff.name) AS lawyers
			FROM `project`
				LEFT JOIN project_people ON project.id=project_people.project AND project_people.type='律师' AND project_people.role='主办律师'
				LEFT JOIN people staff ON staff.id=project_people.people
			WHERE project.id IN (
				SELECT `project` FROM project_people WHERE people = $people_id
			)
			GROUP BY project.id
		";
		
		return $this->db->query($query)->result_array();

	}

	//根据客户id获得其参与案件的收费
	function getFeeListByClient($client_id){
		$client_id=intval($client_id);
		
		$option_array=array();
		
		$q_option_array="
			SELECT project_account.id,project_account.type,project_account.fee,project_account.pay_date,project_account.receiver,project.name
			FROM project_account INNER JOIN `project` ON project_account.project=project.id
			WHERE project.id IN (SELECT `project` FROM project_people WHERE people=$client_id)";
		
		$r_option_array=$this->db->query($q_option_array);
		
		foreach($r_option_array->result_array() as $a_option_array){
			$option_array[$a_option_array['id']]=strip_tags($a_option_array['name']).'案 '.$a_option_array['type'].' ￥'.$a_option_array['fee'].' '.$a_option_array['pay_date'].($a_option_array['type']=='办案费'?' '.$a_option_array['receiver'].'收':'');
		}
	
		return $option_array;	
	}
	
	//根据案件ID获得收费array
	function getFeeOptions($project_id){
		$project_id=intval($project_id);
		
		$option_array=array();
		
		$q_option_array="
			SELECT project_account.id,project_account.type,project_account.fee,project_account.pay_date,project_account.receiver,project.name
			FROM project_account INNER JOIN `project` ON project_account.project=project.id
			WHERE project.id=$project_id";
		
		$result=$this->db->query($q_option_array)->result_array();
		
		foreach($result as $a_option_array){
			$option_array[$a_option_array['id']]=strip_tags($a_option_array['name']).'案 '.$a_option_array['type'].' ￥'.$a_option_array['fee'].' '.$a_option_array['pay_date'].($a_option_array['type']=='办案费'?' '.$a_option_array['receiver'].'收':'');
		}
	
		return $option_array;	
	}
	
	function getRoles($project_id){
		$project_id=intval($project_id);
		
		$project_role=$this->db->query("SELECT people lawyer,role FROM project_people WHERE type='律师' AND `project`=$project_id")->result_array();
		
		if($project_role){
			return $project_role;
		}else{
			return false;
		}
	}
	
	function getPartner($project_role){
		if(empty($project_role)){
			return false;
		}
		foreach($project_role as $lawyer_role){
			if($lawyer_role['role']=='督办人'){
				return $lawyer_role['lawyer'];
			}
		}
		return false;
	}
	
	function getlawyers($project_role){
		if(empty($project_role)){
			return false;
		}
		$lawyers=array();
		foreach($project_role as $lawyer_role){
			if(!in_array($lawyer_role['lawyer'],$lawyers) && $lawyer_role['role']!='督办人'){
				$lawyers[]=$lawyer_role['lawyer'];
			}
		}
		return $lawyers;
	}
	
	function getMyRoles($project_role){
		if(empty($project_role)){
			return false;
		}
		$my_role=array();
		foreach($project_role as $lawyer_role){
			if($lawyer_role['lawyer']==$this->user->id){
				$my_role[]=$lawyer_role['role'];
			}
		}
		return $my_role;
	}
	
	/*
	 * 根据案件信息，获得案号
	 * $project参数为array，需要包含is_query,filed,classification,type,type_lock,first_contact/time_contract键
	 */
	function getNum($project_id,$classification,$type,$is_query=false,$first_contact=NULL,$time_contract=NULL){
		$project_num=array();
		
		if($is_query){
			$project_num['classification_code']='询';
			$project_num['type_code']='';
		}else{
			switch($classification){
				case '争议':$project_num['classification_code']='诉';break;
				case '非争议':$project_num['classification_code']='非';break;
				case '法律顾问':$project_num['classification_code']='顾';break;
				default:'';
			}
			switch($type){
				case '公司':$project_num['type_code']='（公）';break;
				case '房产建筑':$project_num['type_code']='（房）';break;
				case '婚姻家庭':$project_num['type_code']='（家）';break;
				case '劳动人事':$project_num['type_code']='（劳）';break;
				case '知识产权':$project_num['type_code']='（知）';break;
				case '诉讼':$project_num['type_code']='（诉）';break;
				case '刑事行政':$project_num['type_code']='（刑）';break;
				case '涉外':$project_num['type_code']='（外）';break;
				case '韩日':$project_num['type_code']='（韩）';break;
				default:$project_num['type_code']='';
			}
		}
		$project_num['project']=$project_id;
		$project_num+=uidTime();
		$project_num['year_code']=substr($is_query?$first_contact:$time_contract,0,4);
		$this->db->insert('project_num',$project_num);
		$project_num['number']=$this->db->query("SELECT number FROM project_num WHERE `project` = $project_id")->row()->number;

		$num=$project_num['classification_code'].$project_num['type_code'].$project_num['year_code'].'第'.$project_num['number'].'号';
		return $num;
	}
}
?>