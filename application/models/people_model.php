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
		'race'=>'民族'
	);
	
	function __construct() {
		parent::__construct();
	}

	function fetch($id){
		$id=intval($id);
		
		$query="
			SELECT * 
			FROM people
			WHERE company={$this->config->item('company/id')}
				AND id=$id";
		
		return $this->db->query($query)->row_array();
	}
	
	function add(array $data=array()){
		
		$this->db->insert('people',$data);
	}
	
	function update($people,array $data=array()){
		
		$people_data=array_intersect_key($data, $this->fields);
		
		return $this->db->update('people',$people_data,array('id'=>$people));
	}
}
?>
