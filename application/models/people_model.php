<?php
class People_model extends Object_model{
	
	static $fields;

	function __construct() {
		parent::__construct();
		$this->table='people';
		parent::$fields['type']=$this->table;
		self::$fields=array(
			'character'=>'个人',//性质
			'name_en'=>NULL,//英文名
			'abbreviation'=>NULL,//简称
			'phone'=>NULL,//电话
			'email'=>NULL,//电子邮件
			'gender'=>NULL,//性别
			'id_card'=>NULL,//身份证号
			'work_for'=>NULL,//工作单位
			'position'=>NULL,//职位
			'birthday'=>NULL,//生日
			'city'=>NULL,//城市
			'race'=>NULL,//民族
			'staff'=>NULL,//首要关联职员
			'comment'=>NULL,//备注
		);

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
	 * @param string $part_of_name
	 * @return people.id
	 * @throws Exception 无匹配/无唯一匹配
	 */
	function check($part_of_name){
		
		$part_of_name=$this->db->escape_like_str($part_of_name);
		
		$result=$this->db->from($this->table)
			->where("company = {$this->company->id} AND people.display = 1 AND (
				name LIKE '%$part_of_name%'
				OR name_en LIKE '%$part_of_name%'
				OR abbreviation LIKE '%$part_of_name%'
			)",NULL,false)
			->get();

		if($result->num_rows()>1){
			throw new Exception('无法确定人员，多个名称匹配 '.$part_of_name);
		}
		elseif($result->num_rows===0){
			throw new Exception('找不到名称匹配 '.$part_of_name.' 的人员');
		}
		else{
			return $result->row()->id;
		}
	}
	
	function add(array $data){
		
		$insert_id=parent::add($data);
		
		if(isset($data['meta'])){
			foreach($data['meta'] as $name => $content){
				if(!is_null($content) && $content!==''){
					$this->addMeta($insert_id,$name,$content);
				}
			}
		}
		
		if(isset($data['tags'])){
			foreach($data['tags'] as $type => $name){
				if(!is_null($name) && $name!==''){
					$this->addTag($insert_id,$name,$type);
				}
			}
		}
		
		$data=array_merge(self::$fields,array_intersect_key($data,self::$fields));
		$data['id']=$insert_id;
		$this->db->insert($this->table,$data);

		return $insert_id;
	}
	
	function getTypes(){
		$this->db->select('people.type,COUNT(*) AS hits')
			->from('people')
			->where('people.company',$this->company->id)
			->group_by('people.type')
			->order_by('hits','desc');
		
		$types=array_column($this->db->get()->result_array(),'type');
		
		$types_lang=array();
		
		foreach($types as $type){
			$types_lang[$type]=lang($type);
		}
		
		return $types_lang;
	}
	
	/**
	 * 
	 * @param int $people
	 * @param int $relative
	 * @param string $relation
	 * @param bool $accepted
	 * @return insert id
	 */
	function addRelative($people,$relative,$relation=NULL,$accepted=true){
		$data=array(
			'relation'=>$relation,
			'mod'=>$accepted
		);
		
		return parent::addRelative($people, $relative, $data);
	}
	
	/**
	 * 继承自CI_Model::getList()，具有基本的type,tag,orderby和limit配置功能
	 * @param array $args:
	 * name string 匹配部分people.name, people.abbreviation, people.name_en
	 * 
	 * is_team=>bool
	 * 
	 * in_project int 只获得指定事务中的人员列表
	 * 	project_people_type 指定事务，特定关联类型
	 * 	project_people_role 指定事务，特定角色
	 * 
	 * in_same_project_with, int or array, people.id 有相同的相关案件的人员
	 * 
	 * is_staff
	 * 
	 * 团队 - 人员关系查找
	 * in_team => array or int, is_relative_of的别名
	 * team_leader_of => array or int team(s)
	 * 
	 */
	function getList($args=array()){
		
		$this->db->select('object.*, people. *,
			IF(people.abbreviation IS NULL,object.name,people.abbreviation) AS abbreviation
		',false);
		
		if(isset($args['name'])){
			$args['name']=$this->db->escape_like_str($args['name']);
			$this->db->where("(object.name LIKE '%{$args['name']}%' 	OR people.abbreviation LIKE '%{$args['name']}%' OR people.name_en LIKE '%{$args['name']}%')",NULL,false);
			unset($args['name']);
		}
		
		if(isset($args['is_team'])){
			if($args['is_team']){
				$this->db->where("people.id IN (SELECT id FROM team)",NULL,false);
			}else{
				$this->db->where("people.id NOT IN (SELECT id FROM team)",NULL,false);
			}
		}
		
		if(isset($args['in_project'])){
			
			$args['is_relative_of']=$args['in_project'];
			
			if(isset($args['project_people_role'])){
				$args['is_relative_of__role']=$args['project_people_role'];
			}
			
		}
		
		if(isset($args['in_same_project_with'])){
			$args['is_both_relative_with']=$args['in_same_project_with'];
		}
		
		if(isset($args['in_team'])){
			$args['is_relative_of']=$args['in_team'];
		}
		
		if(isset($args['is_staff'])){
			if($args['is_staff']){
				$this->db->where('people.id IN (SELECT id FROM staff)',NULL,false);
			}else{
				$this->db->where('people.id NOT IN (SELECT id FROM staff)',NULL,false);
			}
		}

		if(isset($args['team_leader_of'])){
			$this->db->where("people.id IN (SELECT leader FROM team WHERE id{$this->db->escape_int_array($args['team_leader_of'])})",NULL,false);
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
