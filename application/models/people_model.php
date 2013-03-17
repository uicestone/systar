<?php
class People_model extends SS_Model{
	
	/**
	 * 当前编辑的“人”对象的id
	 */
	var $id;
	
	var $table='people';
	
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
	 * 
	 * @param $config
	 * array(
	 *	limit=>array(
	 *		显示行数,
	 *		起始行
	 *	),
	 *	orderby=>array(
	 *		'people.time DESC',
	 *		...
	 *	)
	 *	'in_my_case'=>FALSE //与当前用户有相同的相关案件
	 *	name=>'匹配部分people.name, people.abbreviation, people.name_en',
	 *	type=>'匹配类别',
	 *	labels=>array(
	 *		'匹配标签名',
	 *		'匹配标签名,
	 *		...
	 *	)
	 *	
	 * )
	 * @return array
	 */
	function getList($config=array()){
		
		/**
		 * 这是一个model方法，它具有配置独立性，即所有条件接口均通过参数$config来传递，不接受其他系统变量
		 */
		
		$q=isset($config['query'])?$config['query']:"
			SELECT people.id,people.name,IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS abbreviation,people.phone,people.email,
				labels.labels
			FROM people
				LEFT JOIN (
					SELECT `people`, GROUP_CONCAT(label_name) AS labels
					FROM people_label
					GROUP BY people_label.people
				)labels ON labels.people=people.id
		";
		
		$q_rows="SELECT COUNT(*) FROM people";
		
		$inner_join='';

		//使用INNER JOIN的方式来筛选标签，聪明又机灵
		if(isset($config['labels']) && is_array($config['labels'])){
			
			foreach($config['labels'] as $id => $label_name){
				
				//针对空表单的提交
				if($label_name===''){
					continue;
				}
				
				//每次连接people_label表需要定一个唯一的名字
				$inner_join.="
					INNER JOIN people_label `t_$id` ON people.id=`t_$id`.people AND `t_$id`.label_name = '$label_name'
				";
				
			}
			
		}
		
		$where=" WHERE company={$this->company->id} AND display=1";
		
		if(isset($config['type']) && $config['type']){
			$where.=" AND people.type = '{$config['type']}'";
		}
		
		if(isset($config['name']) && $config['name']!==''){
			$where.="
				AND people.name LIKE '%{$config['name']}%' OR people.abbreviation LIKE '%{$config['name']}%' OR people.name_en LIKE '%{$config['name']}%'
			";
		}
		
		if(isset($config['in_my_case']) && $config['in_my_case'] && !$this->user->isLogged('developer')){
			$where.="
				AND people.id IN (
					SELECT people FROM case_people WHERE `case` IN (
						SELECT `case` FROM case_people WHERE people = {$this->user->id}
					)
				)
			";
		}
		
		$q_rows.=$inner_join.$where.(isset($config['where'])?$config['where']:'');
		$q.=$inner_join.$where.(isset($config['where'])?$config['where']:'');
		
		if(!isset($config['orderby'])){
			$config['orderby']='people.id DESC';
		}
		
		$q.=" ORDER BY ";
		if(is_array($config['orderby'])){
			foreach($config['orderby'] as $orderby){
				$q.=$orderby;
			}
		}else{
			$q.=$config['orderby'];
		}
		
		if(!isset($config['limit'])){
			$config['limit']=$this->limit($q_rows);
		}
		
		if(is_array($config['limit']) && count($config['limit'])==2){
			$q.=" LIMIT {$config['limit'][1]}, {$config['limit'][0]}";
		}elseif(is_array($config['limit']) && count($config['limit'])==1){
			$q.=" LIMIT {$config['limit'][0]}";
		}elseif(!is_array($config['limit'])){
			$q.=" LIMIT ".$config['limit'];
		}
		
		//echo $this->db->_prep_query($q);
		return $this->db->query($q)->result_array();
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
