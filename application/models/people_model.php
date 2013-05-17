<?php
class People_model extends BaseItem_model{
	
	/**
	 * people表下的字段及其显示名
	 */
	static $fields=array(
		'character'=>'性质',
		'name'=>'名称',
		'name_en'=>'英文名',
		'abbreviation'=>'简称',
		'phone'=>'电话',
		'email'=>'电子邮件',
		'type'=>'分类',
		'gender'=>'性别',
		'id_card'=>'身份证号',
		'work_for'=>'工作单位',
		'position'=>'职位',
		'birthday'=>'生日',
		'city'=>'城市',
		'race'=>'民族',
		'staff'=>'首要关联职员',
		'comment'=>'备注',
		'display'=>'是否显示'
	);
	
	function __construct() {
		parent::__construct();
		$this->table='people';
	}

	/**
	 * 根据部分人员名称返回匹配的id、名称和；类别列表
	 * @param $part_of_name
	 * @return array
	 */
	function match($part_of_name){
		$this->db->select('people.id,people.name,people.type')
			->from('people')
			->where('people.company',$this->company->id)
			->where('people.display',true)
			->where("(name LIKE '%$part_of_name%' OR abbreviation LIKE '$part_of_name' OR name_en LIKE '%$part_of_name%')")
			->order_by('people.id','desc');
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 根据部分名称，返回唯一的人员id
	 * @param type $part_or_name
	 */
	function check($part_or_name){
		$result=$this->db->from($this->table)
			->where("company = {$this->company->id} AND people.display = 1 AND (
				name LIKE '%$part_or_name%'
				OR name_en LIKE '%$part_or_name%'
				OR abbreviation LIKE '%$part_or_name%'
			)",NULL,false)
			->get();

		if($result->num_rows()>1){
			throw new Exception('无法确定人员，多个名称匹配 '.$part_or_name);
		}
		elseif($result->num_rows===0){
			throw new Exception('找不到名称匹配 '.$part_or_name.' 的人员');
		}
		else{
			return $result->row()->id;
		}
	}
	
	function add(array $data=array()){
		$people=array_intersect_key($data,self::$fields);
		$people+=uidTime(true,true);
		
		$this->db->insert('people',$people);
		
		$new_people_id=$this->db->insert_id();
		
		if(isset($data['profiles'])){
			foreach($data['profiles'] as $name => $content){
				if(!is_null($content) && $content!==''){
					$this->addProfile($new_people_id,$name,$content);
				}
			}
		}
		
		if(isset($data['labels'])){
			foreach($data['labels'] as $type => $name){
				if(!is_null($name) && $name!==''){
					$this->addLabel($new_people_id,$name,$type);
				}
			}
		}
		
		return $new_people_id;
	}
	
	function update($people,$data){
		$people=intval($people);
		
		if(is_null($data)){
			return true;
		}
		
		$people_data=array_intersect_key($data, self::$fields);
		
		$people_data['display']=1;
		
		$people_data+=uidTime();
		
		$people_data['company']=$this->company->id;

		return $this->db->update('people',$people_data,array('id'=>$people));
	}
	
	function getTypes(){
		$this->db->select('people.type,COUNT(*) AS hits')
			->from('people')
			->where('people.company',$this->company->id)
			->group_by('people.type')
			->order_by('hits','desc');
		
		$types=array_sub($this->db->get()->result_array(),'type');
		
		$types_lang=array();
		
		foreach($types as $type){
			$types_lang[$type]=lang($type);
		}
		
		return $types_lang;
	}
	
	function addRelationship($people,$relative,$relation=NULL){
		$data=array(
			'people'=>$people,
			'relative'=>$relative,
			'relation'=>$relation
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('people_relationship',$data);
		
		return $this->db->insert_id();
	}
	
	/**
	 * 删除相关人
	 */
	function removeRelationship($people_id,$people_relaionship_id){
		$people_id=intval($people_id);
		$people_relaionship_id=intval($people_relaionship_id);
		return $this->db->delete('people_relationship',array('id'=>$people_relaionship_id,'people'=>$people_id));
	}
	
	/**
	 * 继承自SS_Model::getList()，具有基本的type,label,orderby和limit配置功能
	 * @param array $args:
	 * name string 匹配部分people.name, people.abbreviation, people.name_en
	 * project int 只获得指定事务中的人员列表
	 * 	project_people_type 指定事务，特定关联类型
	 * 	project_people_role 指定事务，特定角色
	 * 
	 * 'in_same_project_with' int user_id 有相同的相关案件的人员
	 * 
	 * 人员 - 人员关系查找
	 * is_relative_of =>user_id people_relationship 人员关联，根据本人获得相关人
	 * has_relative_like => user_id people_relationship 人员关联，根据相关人获得本人
	 * 
	 * 团队 - 人员关系查找
	 * in_team => array teams
	 * team_leader_of => array teams
	 * 
	 * 团队 -  团队关系查找
	 * lead_related_team_of => array teams 
	 * lead_team_which_has_relative_like => array teams
	 * 
	 * in_related_team_of => array teams
	 * in_team_which_has_relative_like => array teams
	 * 
	 * everyone => bool
	 *	team array or int 只获得指定团组中的人员列表
	 */
	function getList($args=array()){
		
		$this->db->select('
			people.id,people.type,people.name,people.phone,people.email,
			IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS abbreviation
		',false);
		
		if(isset($args['name'])){
			$this->db->where("(
				people.name LIKE '%{$args['name']}%' 
				OR people.abbreviation LIKE '%{$args['name']}%' 
				OR people.name_en LIKE '%{$args['name']}%'
			)",NULL,false);
			
			unset($args['name']);

		}
		
		if(isset($args['project'])){
			$this->db->select('project_people.id AS relationship_id,GROUP_CONCAT(project_people.role) AS role',false)
				->join('project_people',"project_people.people = people.id AND project_people.project = {$args['project']}",'INNER')
				->group_by('project_people.people');
			
			if(isset($args['project_people_type'])){
				$this->db->where('project_people.type',$args['project_people_type']);
			}
			if(isset($args['project_people_role'])){
				$this->db->where('project_people.role',$args['project_people_role']);
			}
		}
		
		if(isset($args['in_same_project_with'])){
			
			$this->db->where("
				people.id IN (
					SELECT people FROM project_people WHERE `project` IN (
						SELECT `project` FROM project_people WHERE people = {$args['in_same_project_with']}
					)
				)
			",NULL,false);
		}
		
		if(isset($args['team'])){
			$this->db->where("people.id IN (SELECT people FROM people_relationship INNER JOIN team ON team.id = people_relationship.people WHERE FALSE ".($args['team']?"OR team IN (".implode(',',$args['team']).")":'').")",NULL,FALSE);
		}
		
		if(isset($args['everyone']) && $args['everyone']===false){
		
			$where='(FALSE';

			if(isset($args['is_relative_of'])){
				$where.=" OR people.id IN (SELECT relative FROM people_relationship WHERE people = {$args['is_relative_of']})";
			}

			if(isset($args['has_relative_like'])){
				$where.=" OR people.id IN (SELECT people FROM people_relationship WHERE relative = {$args['has_relative_like']})";
			}

			if(isset($args['in_team'])){
				$where.=" OR people.id IN (SELECT people FROM people_relationship INNER JOIN team ON team.id = people_relationship.people WHERE FALSE ".($args['in_team']?"OR people_relationship.people IN (".implode(',',$args['in_team']).")":'').")";
			}

			if(isset($args['team_leader_of'])){
				$where.=" OR people.id IN (SELECT leader FROM team WHERE FALSE ".($args['team_leader_of']?"OR id IN (".implode(',',$args['team_leader_of']).")":'').")";
			}

			if(isset($args['lead_related_team_of'])){
				$where.=" OR people.id IN (SELECT leader FROM team WHERE id IN (SELECT relative FROM people_relationship WHERE FALSE ".($args['lead_related_team_of']?"OR people IN (".implode(',',$args['lead_related_team_of']).")":'')."))";
			}

			if(isset($args['lead_team_which_has_relative_like'])){
				$where.=" OR people.id IN (SELECT leader FROM team WHERE id IN (SELECT people FROM people_relationship WHERE FALSE ".($args['lead_team_which_has_relative_like']?"OR relative IN (".implode(',',$args['lead_team_which_has_relative_like']).")":'')."))";
			}

			if(isset($args['in_related_team_of'])){
				$where.=" OR people.id IN (SELECT relative FROM people_relationship WHERE people IN (SELECT relative FROM people_relationship WHERE FALSE ".($args['in_related_team_of']?"OR people IN (".implode(',',$args['in_related_team_of']).")":'')."))";
			}

			if(isset($args['in_team_which_has_relative_like'])){
				$where.=" OR people.id IN (SELECT relative FROM people_relationship WHERE people IN (SELECT people FROM people_relationship WHERE FALSE ".($args['in_team_which_has_relative_like']?"OR relative IN (".implode(',',$args['in_team_which_has_relative_like']).")":'')."))";
			}

			$where.=")";

			$this->db->where($where,NULL,false);
		}
		
		
		return parent::getList($args);
		
	}
	
	function isMobileNumber($number){
		if(is_numeric($number) && $number%1==0 && substr($number,0,1)=='1' && strlen($number)==11){
			return true;
		}else{
			return false;
		}
	}
	
	function getRegionByIdcard($idcard){
		$query="SELECT name FROM user_idcard_region WHERE num = '".substr($idcard,0,6)."'";
		$region = $this->db->query($query)->row()->name;
		if($region){
			return $region;
		}else{
			return false;
		}
	}
	
	function verifyIdCard($idcard){
		if(!is_string($idcard) || strlen($idcard)!=18){
			return false;
		}
		$sum=$idcard[0]*7+$idcard[1]*9+$idcard[2]*10+$idcard[3]*5+$idcard[4]*8+$idcard[5]*4+$idcard[6]*2+$idcard[7]+$idcard[8]*6+$idcard[9]*3+$idcard[10]*7+$idcard[11]*9+$idcard[12]*10+$idcard[13]*5+$idcard[14]*8+$idcard[15]*4+$idcard[16]*2;
		$mod = $sum % 11;
		$vericode_dic=array(1, 0, 'x', 9, 8, 7, 6, 5, 4, 3, 2);
		if($vericode_dic[$mod] == strtolower($idcard[17])){
			return true;
		}
	}
	
	function getGenderByIdcard($idcard){
		if(is_string($idcard) && strlen($idcard)==18){
			return $idcard[16] % 2 == 1 ? '男' : '女';
		}else{
			return false;
		}
	}
}
?>
