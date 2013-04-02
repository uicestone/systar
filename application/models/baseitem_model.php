<?php
class BaseItem_model extends SS_Model{
	
	var $id;
	
	var $data;//具体对象数据
	
	var $labels;//具体对象的标签数组
	
	var $profiles;//具体对象的资料项数组
	
	var $table;//具体对象存放于数据库的表名
	
	function __construct() {
		parent::__construct();
	}
	
	function fetch($id,$field=NULL,$query=NULL){
		
		$id=intval($id);
		
		$row=array();
		
		if(is_null($query)){
			$row=$this->db->get_where($this->table,array('id'=>$id,'company'=>$this->company->id))->row_array();
		}
		else{
			$row=$this->db->query($query)->row_array();
		}
		
		if(!$row){
			throw new Exception('item_not_found');
		}
		
		if(is_null($field)){
			return $row;
	
		}elseif(isset($row[$field])){
			return $row[$field];

		}else{
			return false;
		}
	}
	
	/**
	 * 根据部分名称返回匹配的id、名称和类别列表
	 * @param $part_of_name
	 * @return array
	 */
	function match($part_of_name){
		
		$this->db->select($this->table.'.*')
			->from($this->table)
			->where('company',$this->company->id)
			->like('name', $part_of_name);
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 
	 * @param $config
	 * array(
	 *	limit=>array(
	 *		显示行数[, 起始行]
	 *	),
	 *	limit=>SQL LIMIT STRING,
	 * 
	 *	orderby=>array(
	 *		'people.time DESC',
	 *		...
	 *	)
	 *	type=>'匹配类别',
	 *	labels=>array(
	 *		'匹配标签名',
	 *		'匹配标签名,
	 *		...
	 *	),
	 *	team=>int OR array
	 *	
	 * )
	 * @return array
	 */
	function getList($args=array()){
		
		/**
		 * 这是一个model方法，它具有配置独立性，即所有条件接口均通过参数$args来传递，不接受其他系统变量
		 */
		if(!$this->db->ar_select){
			$this->db->select($this->table.'.*');
		}
		
		$this->db->from($this->table);
		
		//使用INNER JOIN的方式来筛选标签，聪明又机灵
		if(isset($args['labels']) && is_array($args['labels'])){
			foreach($args['labels'] as $id => $label_name){
				//每次连接people_label表需要定一个唯一的名字
				$this->db->join("{$this->table}_label t_$id","{$this->table}.id = t_$id.{$this->table} AND t_$id.label_name = '$label_name'",'INNER');
			}
		}
		
		$this->db->where(array($this->table.'.company'=>$this->company->id,$this->table.'.display'=>true));
		
		if(isset($args['type']) && $args['type']){
			$this->db->where($this->table.'.type',$args['type']);
		}
		
		//复制一个DB对象用来计算行数，因为计算行数需要运行sql，将清空DB类中属性
		$num_rows=clone $this->db;
		
		if(!isset($args['orderby'])){
			$args['orderby']=$this->table.'.id DESC';
		}
		
		if(is_array($args['orderby'])){
			foreach($args['orderby'] as $orderby){
				$this->db->order_by($orderby[0],$orderby[1]);
			}
		}elseif($args['orderby']){
			$this->db->order_by($args['orderby']);
		}
		
		if(!isset($args['limit'])){
			$args['limit']=$this->limit($num_rows);
		}
		
		if($args['limit']!==false){
			if(is_array($args['limit'])){
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}else{
				call_user_func(array($this->db,'limit'), $args['limit']);
			}
			
		}
		
		return $this->db->get()->result_array();
	}
	
	
	function getArray($args=array(),$keyname='name',$keyname_forkey='id'){
		return array_sub($this->getList($args),$keyname,$keyname_forkey);
	}
	
	/**
	 * 添加标签，而不论标签是否存在
	 * @param type {item} id
	 * @param type $label_name 标签内容或标签id（须将下方input_as_id定义为true）
	 * @param type $type 标签内容在此类对象的应用的意义，如“分类”，“类别”，案件的”阶段“等
	 * @return type 返回{item}_label的insert_id
	 */
	function addLabel($item_id,$label_name,$type=NULL){
		$item_id=intval($item_id);
		$label_id=$this->label->match($label_name);
		$insert_string=$this->db->insert_string($this->table.'_label',array($this->table=>$item_id,'label'=>$label_id,'type'=>$type,'label_name'=>$label_name));
		$insert_string=str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_string);
		$this->db->query($insert_string);
		return $this->db->insert_id();
	}
	
	function removeLabel($item_id,$label_name){
		$item_id=intval($item_id);
		return $this->db->delete($this->table.'_label',array($this->table=>$item_id,'label_name'=>$label_name));
	}
	
	/**
	 * 获得一个对象的所有标签
	 * @param int $item_id
	 * @param string $type
	 * @return array([type=>]name,...)
	 */
	function getLabels($item_id,$type=NULL){
		$item_id=intval($item_id);
		
		$this->db->select("label.name,{$this->table}_label.type")
			->from('label')
			->join($this->table.'_label', "label.id={$this->table}_label.label", 'INNER');
		
		$this->db->where($this->table.'_label.'.$this->table, $item_id);
		
		if($type===true){
			$this->db->where("{$this->table}_label.type IS NOT NULL");
		}
		elseif(isset($type)){
			$this->db->where($this->table.'_label.type',$type);
		}
		
		$result=$this->db->get()->result_array();
		
		$labels=array_sub($result,'name','type');
		
		return $labels;
	}
	
	/**
	 * 获得所有或指定类别的标签名称，按热门程度排序
	 * @param $type
	 * @return array([$type=>]$label_name,...) 一个由标签类别为键名（如果标签类别存在），标签名称为键值构成的数组
	 */
	function getAllLabels($type=NULL){
		
		$this->db->select("{$this->table}_label.type,{$this->table}_label.label_name AS name,COUNT(*) AS hits")
			->from("{$this->table}_label")
			->join($this->table, "{$this->table}.id = {$this->table}_label.{$this->table}",'INNER')
			->where($this->table.'.company',$this->company->id);
		
		if(isset($type)){
			$this->db->where('type',$type);
		}
		
		$this->db->group_by($this->table.'_label.label')
			->order_by('hits', 'DESC');
		
		$result_array = $this->db->get()->result_array();
		
		$all_labels=array();
		
		foreach($result_array as $row_array){
			if(is_null($type) && $row_array['type']){
				$all_labels[$row_array['type']][]=$row_array['name'];
			}else{
				$all_labels[]=$row_array['name'];
			}
		}
		return $all_labels;
	}
	
	/**
	 * 对于指定{item}，在{item}_label中写入一组label
	 * 对于不存在的label，当场在label表中添加
	 * @param int {item}_id
	 * @param array $labels: array([$type=>]$name,...)
	 * 如果给定的$labels参数中有一个或更多的整数键名
	 * 那么本方法将首先删去该对象所有无type label，将$labels中的无type label添加到该对象下
	 */
	function updateLabels($item_id,$labels){
		$item_id=intval($item_id);
		
		//没有在参数列表中直接做出限制，用来兼容一些特殊情况
		if(!is_array($labels)){
			return;
		}
		
		//分离$labels中的整数键
		$labels_without_type=array();
		foreach($labels as $key => $name){
			if(is_integer($key)){
				$labels_without_type[]=$name;
				unset($labels[$key]);
			}
		}
		
		//首先删除本对象的所有不带类别的标签
		$this->db->delete($this->table.'_label',$this->table." = $item_id AND type IS NULL");

		//然后依次插入新的标签
		foreach($labels_without_type as $label_without_type){
			$this->addLabel($item_id, $label_without_type);
		}
		
		//剩下的$labels都是带类别的标签，根据类别来查找有无，然后插入或更新
		foreach($labels as $type => $name){
			
			$label_id=$this->label->match($name);
			$set=array('label'=>$label_id,'label_name'=>$name);
			$where=array($this->table=>$item_id,'type'=>$type);
			$result=$this->db->get_where($this->table.'_label',$where);
			if($result->num_rows()===0){
				$this->db->insert($this->table.'_label',$set+$where);
			}else{
				$this->db->update($this->table.'_label',$set,$where);
			}
		}
	}
	
	/**
	 * 根据id获得标签，进而生成描述性字符串
	 * @return string
	 */
	function getCompiledLabels($item_id){
		$item_id=intval($item_id);
		
		$this->db->select('label.id,label.name,label.order,label.color')
			->from($this->table.'_label')
			->join('label',$this->table."_label.label = label.id",'INNER')
			->where($this->table.'_label.'.$this->table,$item_id)
			->order_by('label.order','DESC');
		
		$labels=$this->db->get()->result_array();
		
		$labels_string='<div class="chzn-container-multi"><ul class="chzn-choices">';
		foreach($labels as $key=>$label){
			if(!is_array(option('search/labels')) || !in_array($key,option('search/labels'))){
				$labels_string.='<li class="search-choice" style="color:'.$label['color'].'">'.$label['name'].'</li>';
			}
		}
		
		$labels_string.='</ul></div>';
		
		return $labels_string;
	}

	function addProfile($item_id,$name,$content,$comment=NULL){
		$data=array(
			$this->table=>$item_id,
			'name'=>$name,
			'content'=>$content,
			'comment'=>$comment
		);
		
		$data+=uidTime(false);
		
		$this->db->insert($this->table.'_profile',$data);
		
		return $this->db->insert_id();
	}
	
	/**
	 * 返回一个item的资料项列表
	 * @param ${item}_id
	 * @return type
	 */
	function getProfiles($item_id){
		$item_id=intval($item_id);
		
		$query="
			SELECT 
				{$this->table}_profile.id,{$this->table}_profile.comment,{$this->table}_profile.content,{$this->table}_profile.name
			FROM {$this->table}_profile INNER JOIN {$this->table} ON {$this->table}_profile.{$this->table}={$this->table}.id
			WHERE {$this->table}_profile.{$this->table} = $item_id
		";
		return $this->db->query($query)->result_array();
	}
	
	/**
	 * 删除信息资料项
	 */
	function removeProfile($item_id,$profile_id){
		$item_id=intval($item_id);
		$profile_id=intval($profile_id);
		return $this->db->delete($this->table.'_profile',array('id'=>$profile_id,$this->table=>$item_id));
	}
	
	/**
	 * 对于指定对象，在{item}_profiles中写入一组资料项
	 * @param int $item_id
	 * @param array $profiles: array($name=>$content,...)
	 */
	function updateProfiles($item_id,$profiles){
		$item_id=intval($item_id);
		
		if(!is_array($profiles)){
			return true;
		}
		
		foreach($profiles as $name => $content){
			
			$set=array('content'=>$content);
			$where=array($this->table=>$item_id,'name'=>$name);
			
			if($this->db->from($this->table.'_profile')->where($where)->count_all_results()===0){
				$this->addProfile($item_id,$name,$content);
			}else{
				$this->db->update($this->table.'_profile',$set,$where);
			}
			
		}
	}
	
	/**
	 * 返回一个可用的profile name列表
	 */
	function getProfileNames(){
		
		$this->db->select('people_profile.name,COUNT(*) AS hits',false)
			->from($this->table.'_profile')
			->join('people',"people_profile.people = people.id AND people.company = {$this->company->id}")
			->group_by('people_profile.name')
			->order_by('hits', 'desc');
		
		$result=$this->db->get()->result_array();
		
		return array_sub($result,'name');
	}
}
?>
