<?php
class BaseItem_model extends SS_Model{
	
	var $id;
	var $data;//具体对象数据
	var $labels;//具体对象的标签数组
	var $profiles;//具体对象的资料项数组
	var $table;//具体对象存放于数据库的表名
	var $fields;//存放对象的表结构
	var $mod=false;//本模型是否带权限验证
	
	function __construct() {
		parent::__construct();
		$CI=&get_instance();
		$this->fields=array(
			'name'=>NULL,
			'display'=>false,
			'company'=>isset($CI->company)?$CI->company->id:NULL,
			'uid'=>isset($CI->user)?$CI->user->id:NULL,
			'time_insert'=>0,
			'time'=>$this->date->now
		);
	}
	
	function fetch($id,$field=NULL,$query=NULL,$mod=true){
		
		$id=intval($id);
		
		$row=array();
		
		if(is_null($query)){
			$this->db
				->from($this->table)
				->where(array('id'=>$id,'company'=>$this->company->id));
			
			//验证读权限
			if($this->mod && !$this->user->isLogged($this->table.'admin')){
				$this->db->where("document.id IN (
					SELECT document FROM document_mod
					WHERE (document_mod.people IS NULL OR document_mod.people{$this->db->escape_int_array(array_merge(array_keys($this->user->teams),array($this->user->id)))})
						AND ((document_mod.mod & 1) = 1)
					)
				");
			}
			
			$row=$this->db->get()->row_array();
		}
		else{
			$row=$this->db->query($query)->row_array();
		}
		
		if(!$row){
			echo $this->db->last_query();
			throw new Exception($this->table.' '.$id.' not found');
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
	 * 根据部分名称，返回唯一的id
	 * @param type $part_or_name
	 */
	function check($part_or_name){
		$result=$this->db->from($this->table)
			->where($this->table.'.company',$this->company->id)
			->where($this->table.'.display',true)
			->like('name',$part_or_name)
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
	
	/**
	 * 
	 * @param array $args
	 *	labels array
	 *	without_labels array
	 *	name
	 *	company
	 *	display
	 *	type
	 *	type_is_not
	 *	id_in
	 *	orderby string or array
	 *	limit string, array or 'pagination'
	 *	with_profiles bool or array()
	 *	@todo team array or int
	 *	profiles array()
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
		
		if(array_key_exists('count', $args) && $args['count']){
			$this->db->select('COUNT(*) as `count`',false);
		}
		
		//验证读权限
		if($this->mod && !$this->user->isLogged($this->table.'admin')){
			$this->db->where("document.id IN (
				SELECT document FROM document_mod
				WHERE (document_mod.people IS NULL OR document_mod.people{$this->db->escape_int_array(array_merge(array_keys($this->user->teams),array($this->user->id)))})
					AND ((document_mod.mod & 1) = 1)
				)
			");
		}
		
		//使用INNER JOIN的方式来筛选标签，聪明又机灵。
		if(isset($args['labels']) && is_array($args['labels'])){
			foreach($args['labels'] as $id => $label_name){
				//每次连接people_label表需要定一个唯一的名字
				$this->db->join("{$this->table}_label t_$id","{$this->table}.id = t_$id.{$this->table} AND t_$id.label_name = '$label_name'",'INNER');
			}
		}
		
		if(isset($args['without_labels'])){
			foreach($args['without_labels'] as $id => $label_name){
				$this->db->where("{$this->table}.id NOT IN (SELECT {$this->table} FROM {$this->table}_label WHERE label_name = '$label_name')");
			}
		}
		
		if(!isset($args['company']) || $args['company']===true){
			$this->db->where($this->table.'.company',$this->company->id);
		}
		
		if(!isset($args['display']) || $args['display']===true){
			$this->db->where($this->table.'.display',true);
		}

		if(isset($args['name'])){
			$this->db->like($this->table.'.name',$args['name']);
		}
		
		if(isset($args['type']) && $args['type']){
			$this->db->where($this->table.'.type',$args['type']);
		}
		
		if(isset($args['type_is_not']) && $args['type_is_not']){
			$this->db->where($this->table.'.type !=',$args['type_is_not']);
		}
		
		if(isset($args['uid']) && $args['uid']){
			$this->db->where($this->table.'.uid',$args['uid']);
		}
		
		foreach(array('time','time_insert') as $date_args){
			if(!isset($args[$date_args])){
				$args[$date_args]=array_prefix($args, $date_args);
			}
		}
		
		foreach(array('time','time_insert') as $time_field){
			
			if(isset($args[$time_field]['from']) && $args[$time_field]['from']){
				
				//默认以日期处理输入
				if(strtotime($args[$time_field]['from'])){
					$args[$time_field]['from']=strtotime($args[$time_field]['from']);
				}
			
				$this->db->where($this->table.'.'.$time_field.' >= ',$args[$time_field]['from']);
			}

			if(isset($args[$time_field]['to']) && $args[$time_field]['to']){
				
				if(strtotime($args[$time_field]['to'])){
					$args[$time_field]['to']=strtotime($args[$time_field]['to']);
				}
				
				//默认以纯日期处理输入
				if(strtotime($args[$time_field]['to']) && (empty($args[$time_field]['input_form']) || $args[$time_field]['input_form']!=='datetime')){
					$args[$time_field]['to']+=86400;
				}
			
				$this->db->where($this->table.'.'.$time_field.' < ',$args[$time_field]['to']);
			}
		}
		
		if(array_key_exists('id_in',$args)){
			if(!$args['id_in']){
				$this->db->where('FALSE',NULL,false);
			}else{
				$this->db->where_in($this->table.'.id',$args['id_in']);
			}
		}
		
		if(isset($args['has_profiles']) && is_array($args['has_profiles'])){
			foreach($args['has_profiles'] as $name => $content){
				
				if(is_integer($name)){
					$on="{$this->table}.id = `$name`.{$this->table} AND `$name`.name{$this->db->escape_array($content)}";
				}else{
					$on="{$this->table}.id = `$name`.{$this->table} AND `$name`.name = {$this->db->escape($name)} AND `$name`.content{$this->db->escape_array($content)}";
				}
				
				$this->db->join("{$this->table}_profile `".$name.'`',$on,'inner',false);
			}
		}
		
		if(isset($args['where']) && $args['where']){
			$this->db->where($args['where'],NULL,FALSE);
		}
		
		//复制一个DB对象用来计算行数，因为计算行数需要运行sql，将清空DB对象中属性
		$db_num_rows=clone $this->db;
		
		if(isset($args['order_by']) && $args['order_by']){
			if(is_array($args['order_by'])){
				foreach($args['order_by'] as $orderby){
					$this->db->order_by($orderby[0],$orderby[1]);
				}
			}else{
				$this->db->order_by($args['order_by']);
			}
		}
		
		if(isset($args['limit']) && $args['limit']){
			if($args['limit']==='pagination'){
				if(array_key_exists('group_by', $args)){
					$args['limit']=$this->pagination($db_num_rows,true,$this->table.'.id');
				}else{
					$args['limit']=$this->pagination($db_num_rows);
				}
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			elseif(is_array($args['limit'])){
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			else{
				call_user_func(array($this->db,'limit'), $args['limit']);
			}
		}
		
		$result_array=$this->db->get()->result_array();
		
		if(isset($args['get_profiles']) && $args['get_profiles']){
			foreach($result_array as &$row){
				$profiles=array_sub($this->getProfiles($row['id']),'content','name');
				if($args['get_profiles']===true){
					$row['profiles']=$profiles;
				}
				elseif(is_array($args['get_profiles'])){
					foreach($args['get_profiles'] as $key => $value){
						$profile_content=array_key_exists($value, $profiles)?$profiles[$value]:NULL;
						if(is_integer($key)){
							$row[$value]=$profile_content;
						}
						else{
							$row[$key]=$profile_content;
						}
					}
				}
			}
		}
		
		if(isset($args['get_labels']) && $args['get_labels']){
			foreach($result_array as &$row){
				$labels=$this->getlabels($row['id']);
				if($args['get_labels']===true){
					$row['labels']=$labels;
				}
				elseif(is_array($args['get_labels'])){
					foreach($args['get_labels'] as $key => $value){
						$label_content=array_key_exists($value, $labels)?$labels[$value]:NULL;
						if(is_integer($key)){
							$row[$value]=$label_content;
						}
						else{
							$row[$key]=$label_content;
						}
					}
				}
			}
		}
		
		return $result_array;
	}
	
	
	function count(array $args=array()){
		$args['count']=true;
		$result=$this->getList($args);
		return isset($result[0]['count']) ? $result[0]['count'] : 0;
	}
	
	function getArray($args=array(),$keyname='name',$keyname_forkey='id'){
		return array_sub($this->getList($args),$keyname,$keyname_forkey);
	}
	
	function getRow($args=array()){
		
		if(array_key_exists('id', $args)){
			$this->db->where($this->table.'.id',$args['id']);
		}
		
		!isset($args['limit']) && $args['limit']=1;
		
		$result=$this->getList($args);
		
		if(isset($result[0])){
			return $result[0];
		}
		else{
			return array();
		}
	}
	
	function getAddingItem(){
		
		if(isset($this->fields['type'])){
			$this->db->where('type',$this->fields['type']);
		}
		
		$row=$this->db->select('id')
			->from($this->table)
			->where('display',false)
			->where('uid',$this->user->id)
			->order_by('id', 'desc')
			->limit(1)
			->get()->row();
		
		if($row){
			return $row->id;
		}else{
			return false;
		}
	}
	
	function add(array $data=array()){
		$data+=uidTime(true,true);
		$data=array_merge($this->fields,array_intersect_key($data,$this->fields));
		
		$this->db->insert($this->table,$data);
		$insert_id=$this->db->insert_id();
		
		if($this->mod){
			$this->addMod(7, $this->user->id, $insert_id);
		}
		
		return $insert_id;
	}
	
	function update($item_id, array $data){
		$data=array_intersect_key($data, $this->fields);
		
		if(empty($data)){
			return 0;
		}
		
		if(is_array($item_id)){
			$this->db->where($item_id);
		}else{
			$this->db->where('id',$item_id);
		}
		
		$this->db->set($data)->update($this->table);
		
		return $this->db->affected_rows();
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
	function getAllLabels($type=NULL,$including=NULL){
		
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
		
		if(is_array($including)){
			$all_labels+=$including;
		}
		
		return $all_labels;
	}
	
	/**
	 * 返回当前类型的对象中，包含$labels标签的对象，所包含的其他标签
	 * @param array $labels
	 * @param string $type
	 */
	function getRelatedLabels($labels, $type=NULL){
		
		if(empty($labels) || !is_array($labels)){
			return array();
		}
		$this->db->from($this->table.'_label')
			->where("{$this->table} IN (SELECT {$this->table} FROM {$this->table}_label WHERE label_name{$this->db->escape_array($labels)})")
			->group_by('label');
		
		if(!is_null($type)){
			$this->db->where('type',$type);
		}
		
		return array_sub($this->db->get()->result_array(),'label_name');
	}
	
	/**
	 * 为一个对象添加一组标签
	 * 已存在的标签不会被改变
	 * 新标签会先在label表中注册
	 * @param int $item_id
	 * @param array $labels
	 * array(
	 *	[type=>]name,
	 *	...
	 * )
	 */
	function addLabels($item_id,$labels){
		$item_id=intval($item_id);
		
		//没有在参数列表中直接做出限制，用来兼容一些特殊情况
		if(!is_array($labels)){
			return;
		}
		
		foreach($labels as $type => $name){
			$label_id=$this->label->match($name);
			$set=array('label'=>$label_id,'label_name'=>$name);
			$where=array($this->table=>$item_id);
			$result=$this->db->get_where($this->table.'_label',$where+$set);
			if(!is_integer($type)){
				$where['type']=$type;
			}
			if($result->num_rows()===0){
				$this->db->insert($this->table.'_label',$set+$where);
			}
		}
	}
	
	/**
	 * 为一个对象更新一组带类型的标签
	 * 不存在的标签添加
	 * @param int $item_id
	 * @param array $labels
	 * array(
	 *	[type=>]name,
	 *	...
	 * )
	 * @param bool $delete_other 将输入数组作为所有标签，删除其他标签
	 */
	function updateLabels($item_id, $labels, $delete_other=false){
		
		if(!is_array($labels)){
			$labels=array();
		}
		
		//按类别更新标签
		foreach($labels as $type => $name){
			if(!is_integer($type)){
				$label_id=$this->label->match($name);
				$set=array('label'=>$label_id,'label_name'=>$name);
				$where=array($this->table=>$item_id,'type'=>$type);
				$this->db->update($this->table.'_label',$set,$where);
			}
		}
		
		$origin_labels=$this->getLabels($item_id);
		
		//添加新的标签
		$this->addLabels($item_id, $labels);
		
		//删除其他标签
		if($delete_other){
			$other_labels=array_diff($origin_labels,$labels);
			$this->db->where_in('label_name',$other_labels)->delete($this->table.'_label');
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
		
		$labels_string='<div class="select2-container-multi"><ul class="select2-choices">';
		foreach($labels as $key=>$label){
			if(!$this->config->user_item('search/labels') || !in_array($label['name'],$this->config->user_item('search/labels'))){
				$labels_string.='<a href="#'.CONTROLLER.'?labels='.urlencode($label['name']).'"><li class="select2-search-choice" style="color:'.$label['color'].'">'.$label['name'].'</li></a>';
			}
		}
		
		$labels_string.='</ul></div>';
		
		return $labels_string;
	}

	function addProfile($item_id,$name,$content,$comment=''){
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
	 * @param array $args
	 *	show_author
	 * @return type
	 */
	function getProfiles($item_id,array $args=array()){
		$item_id=intval($item_id);
		
		$this->db->select("{$this->table}_profile.id,{$this->table}_profile.comment,{$this->table}_profile.content,{$this->table}_profile.name")
			->from("{$this->table}_profile")
			->join($this->table,"{$this->table}_profile.{$this->table}={$this->table}.id",'inner')
			->where("{$this->table}_profile.{$this->table}",$item_id);
			
		if(isset($args['show_author']) && $args['show_author']){
			$this->db->join('people author',"author.id = {$this->table}_profile.uid",'inner')
				->select('author.id author, author.name author_name');
		}
		
		return $this->db->get()->result_array();
	}
	
	function getProfile($item_id, $profile_name, array $args=array()){
		$item_id=intval($item_id);
		
		$this->db->select("{$this->table}_profile.id,{$this->table}_profile.comment,{$this->table}_profile.content,{$this->table}_profile.name")
			->from("{$this->table}_profile")
			->join($this->table,"{$this->table}_profile.{$this->table}={$this->table}.id",'inner')
			->where("{$this->table}_profile.{$this->table}",$item_id)
			->where("{$this->table}_profile.name",$profile_name);
			
		$result = $this->db->get()->result_array();
		
		return implode(', ',array_sub($result, 'content'));
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
	 * 遇不存在的profile.name则插入，遇存在的profile.name则更新
	 * @param int $item_id
	 * @param array $profiles: array($name=>$content,...)
	 */
	function updateProfiles($item_id,$profiles){
		$item_id=intval($item_id);
		
		if(!is_array($profiles)){
			return true;
		}
		
		foreach($profiles as $name => $content){
			if($content===''){
				continue;
			}
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
	
	function addStatus($item_id,$name,$date,$content=NULL,$team=NULL,$comment=NULL){
		$data=array(
			$this->table=>$item_id,
			'name'=>$name,
			'date'=>$date,
			'content'=>$content,
			'team'=>$team,
			'comment'=>$comment
		);
		
		$data+=uidTime(false);
		
		$this->db->insert($this->table.'_status',$data);
		
		return $this->db->insert_id();
	}
	
	function getStatus($item_id){
		$item_id=intval($item_id);
		
		$this->db->select("{$this->table}_status.id,{$this->table}_status.name,{$this->table}_status.content,{$this->table}_status.date,{$this->table}_status.comment")
			->from("{$this->table}_status")
			->join($this->table,"{$this->table}_status.{$this->table}={$this->table}.id",'inner')
			->where("{$this->table}_status.{$this->table}",$item_id);
		
		return $this->db->get()->result_array();
	}

	function removeStatus($item_id,$status_id){
		$item_id=intval($item_id);
		$status_id=intval($status_id);
		return $this->db->delete($this->table.'_status',array('id'=>$status_id,$this->table=>$item_id));
	}
}
?>
