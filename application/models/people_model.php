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
		'type'=>'分类',
		'gender'=>'性别',
		'id_card'=>'身份证号',
		'work_for'=>'工作单位',
		'position'=>'职位',
		'birthday'=>'生日',
		'city'=>'城市',
		'race'=>'民族',
		'staff'=>'首要关联职员',
		'source'=>'来源',
		'comment'=>'备注'
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
		$query="
			SELECT people.id,people.name,people.type
			FROM people
			WHERE people.company={$this->company->id} AND people.display=1 
				AND (name LIKE '%$part_of_name%' OR abbreviation LIKE '$part_of_name' OR name_en LIKE '%$part_of_name%')
			ORDER BY people.id DESC
		";
		
		return $this->db->query($query)->result_array();
	}

	function add(array $data=array()){
		$people=array_intersect_key($data,self::$fields);
		$people+=uidTime(true,true);
		$people['display']=1;
		
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
		$query="
			SELECT people.type,COUNT(*) AS hits
			FROM people
			WHERE people.company={$this->company->id}
			GROUP BY people.type
			ORDER BY hits DESC
		";
		
		return array_sub($this->db->query($query)->result_array(),'type');
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
	
	function getRelatives($people_id){
		$people_id=intval($people_id);
		
		$query="
			SELECT 
				people_relationship.id AS id,people_relationship.relation,people_relationship.relative,
				IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS name,
				people.phone,people.email
			FROM 
				people_relationship INNER JOIN people ON people_relationship.relative=people.id
			WHERE people_relationship.people = $people_id
			ORDER BY relation
		";
		return $this->db->query($query)->result_array();
	}
	
	/**
	 * 继承自SS_Model::getList()，具有基本的type,label,orderby和limit配置功能
	 * 'in_my_project'=>FALSE //与当前用户有相同的相关案件
	 * name=>'匹配部分people.name, people.abbreviation, people.name_en',
	 */
	function getList($args=array()){
		$this->db->select('
			people.id,people.name,IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS abbreviation,people.phone,people.email
		',false);

		if(isset($args['name']) && $args['name']!==''){
			
			$this->db->where("
				people.name LIKE '%{$args['name']}%' 
					OR people.abbreviation LIKE '%{$args['name']}%' 
					OR people.name_en LIKE '%{$args['name']}%
			",NULL,false);

		}
		
		if(isset($args['in_my_project']) && $args['in_my_project'] && !$this->user->isLogged('developer')){
			
			$this->db->where("
				people.id IN (
					SELECT people FROM project_people WHERE `project` IN (
						SELECT `project` FROM project_people WHERE people = {$this->user->id}
					)
				)
			",NULL,false);
		}
		
		//根据people_team关系来查找
		if(isset($args['team']) && $args['team']){
			if(is_array($args['team'])){
				$teams=implode(',',$args['team']);
				$this->db->where("people.id IN (SELECT people FROM team_people WHERE team IN ($teams))",NULL,false);
			}else{
				$team=intval($args['team']);
				
				$this->db->where("
					people.id IN (
						SELECT people FROM team_people WHERE 
							team = $team OR team IN (
								SELECT team FROM team_relationship WHERE relative = $team
							)
					)
				",NULL,false);//@TODO 需要写成递归
			}
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
