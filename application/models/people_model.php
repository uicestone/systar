<?php
class People_model extends SS_Model{
	
	/**
	 * 当前编辑的“人”对象的id
	 */
	var $id;
	
	/**
	 * people表下的字段及其显示名
	 */
	var $fields=array(
		'character'=>'性质',
		'name'=>'名称',
		'name_en'=>'英文名',
		'abbreviation'=>'简称',
		'gender'=>'性别',
		'id_card'=>'身份证号',
		'work_for'=>'工作单位',
		'position'=>'职位',
		'birthday'=>'生日',
		'city'=>'城市',
		'race'=>'民族',
		'staff'=>'首要关联职员',
		'source'=>'来源'
	);
	
	function __construct() {
		parent::__construct();
	}

	/**
	 * 抓取一条人员信息
	 * @param int $id 人员id
	 * @param mixed $field 需要指定抓取的字段，留空则返回整个数组
	 * @return 一条信息的数组，或者一个字段的值，如果指定字段且字段不存在，返回false
	 */
	function fetch($id,$field=NULL){
		$id=intval($id);
		
		$query="
			SELECT * 
			FROM people
			WHERE id = $id AND company={$this->company->id}
		";
		
		$row=$this->db->query($query)->row_array();

		if(is_null($field)){
			return $row;
	
		}elseif(isset($row[$field])){
			return $row[$field];

		}else{
			return false;
		}
		
	}
	
	/**
	 * 根据部分客户名称返回匹配的客户id和名称列表
	 * @param $part_of_name
	 * @return array
	 */
	function match($part_of_name){
		$query="
			SELECT people.id,people.name 
			FROM people
			WHERE people.company={$this->company->id} AND people.display=1 
				AND (name LIKE '%$part_of_name%' OR abbreviation LIKE '$part_of_name' OR name_en LIKE '%$part_of_name%')
			ORDER BY people.id DESC
			";
		
		return $this->db->query($query)->result_array();
	}

	function add(array $data=array()){
		$data=array_intersect_key($data,$this->fields);
		$data+=uidTime();
		$data['display']=1;
		
		$this->db->insert('people',$data);
		return $this->db->insert_id();
	}
	
	function update($people,$data){
		
		if(is_null($data)){
			return true;
		}
		
		$people_data=array_intersect_key($data, $this->fields);
		
		$people_data['display']=1;
		
		$people_data+=uidTime();
		
		$people_data['company']=$this->company->id;

		return $this->db->update('people',$people_data,array('id'=>$people));
	}
	
	function addProfile($people,$profile_name,$profile_content){
		$data=array(
			'people'=>$people,
			'name'=>$profile_name,
			'content'=>$profile_content
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('people_profile',$data);
		
		return $this->db->insert_id();
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
	 * 为人添加标签，而不论标签是否存在，输入是标签内容还是标签id
	 * @param type $people people.id
	 * @param type $label_name 标签内容或标签id（须将下方input_as_id定义为true）
	 * @param type $type 标签内容在此类对象的应用的意义，如“分类”，“类别”，案件的”阶段“等
	 * @param type $input_as_id 是否将$label_name作为label_id直接插入到people_label
	 * @return type 返回people_label的insert_id
	 */
	function addLabel($people,$label_name,$type=NULL,$input_as_id=false){
		if($input_as_id && is_integer($label_name)){
			$label_id=$label_name;
		}else{
			$result=$this->db->get_where('label',array('name'=>$label_name));

			$label_id=0;

			if($result->num_rows()==0){
				$this->db->insert('label',array('name'=>$label_name));
				$label_id=$this->db->insert_id();
			}else{
				$label_id=$result->row()->id;
			}
		}
		
		$this->db->insert('people_label',array('people'=>$people,'label'=>$label_id,'type'=>$type));
		
		return $this->db->insert_id();
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
