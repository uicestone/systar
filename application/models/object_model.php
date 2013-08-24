<?php
class Object_model extends CI_Model{
	
	var $id;
	var $data;//具体对象数据
	var $meta;//具体对象的元数据
	var $mod=false;
	var $mod_list=array(//开关参数意义
		'read'=>1,
		'write'=>2,
		'distribute'=>4
	);
	var $relative;
	var $relative_mod_list;//相关对象开关参数意义
	var $status;
	var $tags;//具体对象的标签
	
	var $table='object';//具体对象存放于数据库的表名
	
	static $fields;//存放对象的表结构
	static $fields_meta;
	static $fields_mod;
	static $fields_relationship;
	static $fields_status;
	static $fields_tag;
	

	function __construct() {
		parent::__construct();
		
		$CI=&get_instance();
		
		self::$fields=array(
			'name'=>NULL,
			'type'=>'',
			'num'=>NULL,
			'display'=>false,
			'company'=>isset($CI->company)?$CI->company->id:NULL,
			'uid'=>isset($CI->user)?$CI->user->id:NULL,
			'time_insert'=>time(),
			'time'=>time()
		);
		
		self::$fields_meta=array(
			'object'=>NULL,
			'name'=>'',
			'content'=>NULL,
			'comment'=>NULL,
			'uid'=>isset($CI->user)?$CI->user->id:NULL,
			'time'=>time()
		);
		
		self::$fields_mod=array(
			'object'=>NULL,
			'user'=>NULL,
			'mod'=>0,
			'uid'=>isset($CI->user)?$CI->user->id:NULL,
			'time'=>time()
		);
		
		self::$fields_relationship=array(
			'object'=>NULL,
			'relative'=>NULL,
			'relation'=>NULL,
			'mod'=>0,
			'weight'=>NULL,
			'till'=>NULL,
			'num'=>NULL,
			'accepted'=>NULL,
			'uid'=>isset($CI->user)?$CI->user->id:NULL,
			'time'=>time()
		);
		
		self::$fields_status=array(
			'object'=>NULL,
			'name'=>'',
			'type'=>NULL,
			'datetime'=>NULL,
			'content'=>NULL,
			'comment'=>NULL,
			'group'=>NULL,
			'uid'=>isset($CI->user)?$CI->user->id:NULL,
			'time'=>time()
		);
		
		self::$fields_tag=array(
			'object'=>NULL,
			'tag'=>NULL,
			'tag_name'=>'',
			'type'=>NULL,
			'uid'=>isset($CI->user)?$CI->user->id:NULL,
			'time'=>time()
		);
		
	}
	
	/**
	 * 
	 * @throws Exception 'not_found'
	 */
	function fetch($id=NULL, array $args=array()){
		
		if(is_null($id)){
			$id=$this->id;
		}else{
			$this->id=$id;
		}
		
		$this->db
			->from('object')
			->where(array(
				'object.id'=>$this->id,
				'object.company'=>$this->company->id,
				'object.display'=>true
			));
		
		if($this->table!=='object'){
			$this->db->join($this->table,"object.id = {$this->table}.id",'inner');
		}

		//验证读权限
		if($this->mod && !$this->user->isLogged($this->table.'admin')){
			$this->db->where("object.id IN (
				SELECT object FROM object_mod
				WHERE 
					( object_mod.people IS NULL OR object_mod.people{$this->db->escape_int_array($this->user->groups)} )
					AND ( (object_mod.mod & 1) = 1 )
				)
			");
		}

		$object=$this->db->get()->row_array();
		
		if(!$object){
			throw new Exception(lang($this->table).' '.$this->id.' '.lang('not_found'));
		}
		
		foreach(array('meta','mod','relative','status','tag') as $field){
			if(array_key_exists('get_'.$field,$args) && $args['get_'.$field]){
				$object[$field]=call_user_func(array($this,'get'.$field));
			}
		}
		
		return $object;

	}
	
	function add(array $data){
		
		$data+=array(
			'company'=>$this->company->id,
			'user'=>$this->user->id,
			'time'=>time(),
			'time_insert'=>time()
		);
		
		$data=array_merge(self::$fields,array_intersect_key($data,self::$fields));
		
		$this->db->insert('object',$data);
		$insert_id=$this->db->insert_id();
		
		if($this->mod){
			$this->addMod(7, $this->user->id, $insert_id);
		}
		
		$this->id=$insert_id;
		
		return $this->id;
	}
	
	function update(array $data, $condition=NULL){

		$data=array_intersect_key($data, self::$fields);
		
		if(empty($data)){
			return false;
		}
		
		if(isset($condition)){
			$this->db->where($condition);
		}else{
			$this->db->where('id',$this->id);
		}
		
		$this->db->set($data)->update('object');
		
		return $this->db->affected_rows();
	}
	
	function remove($condition=NULL){

		$this->db->start_cache();
		
		if(isset($condition)){
			$this->db->where($condition);
		}else{
			$this->db->where('id',$this->id);
		}
		
		$this->db->stop_cache();
		
		if($this->table!=='object'){
			$this->db->delete($this->table);
		}
		
		$this->db->delete('object');
		
		$this->db->flush_cache();
		
	}
	
	/**
	 * 根据部分名称返回匹配的id、名称和类别列表
	 * @param $part_of_name
	 * @return array
	 */
	function match($part_of_name){
		
		$this->db
			->from('object')
			->where('object.company',$this->company->id)
			->like('object.name', $part_of_name);
		
		if($this->table!=='object'){
			$this->db->join($this->table,"object.id = {$this->table}.id",'inner');
		}
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 根据部分名称，返回唯一的id
	 * @param type $part_of_name
	 * @return type
	 * @throws Exception 'not_found','duplicated_matches'
	 */
	function check($part_of_name){
		$result=$this->db
			->from('object')
			->where('object.company',$this->company->id)
			->where('object.display',true)
			->like('name',$part_of_name)
			->get();

		if($result->num_rows()>1){
			throw new Exception(lang('duplicated_matches').' '.$part_of_name);
		}
		elseif($result->num_rows===0){
			throw new Exception($part_of_name.' '.lang('not_found'));
		}
		else{
			return $result->row()->id;
		}
	}
	
	/**
	 * 
	 * @param array $args
	 *	id_in
	 *	id_less_than
	 *	id_greater_than
	 *	name
	 *	type
	 *	num
	 *	display
	 *	company
	 *	uid
	 * 
	 *	tags array
	 *	without_tags array
	 *	get_tags
	 * 
	 *	has_meta
	 *		array('电话','来源'=>'网站')
	 *		以上例子将搜素'来源'为'网站'并且有'电话'的对象
	 *	get_meta array
	 * 
	 *	is_relative_of =>user_id object_relationship  根据本对象获得相关对象
	 *		is_relative_of__role
	 *	has_relative_like => user_id object_relationship  根据相关对象获得本对象
	 *		has_relative_like__role
	 *	is_secondary_relative_of 右侧相关对象的右侧相关对象，“下属的下属”
	 *		is_secondary_relative_of__media
	 *	is_both_relative_with 右侧相关对象的左侧相关对象，“具有共同上司的同事”
	 *		is_both_relative_with__media
	 *	has_common_relative_with 左侧相关对象的右侧相关对象，“具有共同下属的上司”
	 *		has_common_relative_with__media
	 *	has_secondary_relative_like 左侧相关对象的左侧相关对象，“上司的上司”
	 *		has_secondary_relative_like__media
	 * 
	 *	status
	 *		array(
	 *			'status_name'=>array('from'=>'from_syntax','to'=>'to_syntax','format'=>'timestamp/date/datetime')/bool
	 *			'首次接洽'=>array('from'=>1300000000,'to'=>1300100000,'format'=>'timestamp'),
	 *			'立案'=>array('from'=>'2013-01-01','to'=>'2013-06-30'),
	 *			'结案'=>true
	 *		)
	 * 
	 *	orderby string or array
	 *	limit string, array
	 * @return array
	 */
	function getList(array $args=array()){

		if(!$this->db->ar_select){
			$this->db->select('object.*');
		}
		
		$this->db->from('object');
		
		if($this->table!=='object'){
			$this->db->select("{$this->table}.*")->join($this->table,"object.id = {$this->table}.id",'inner');
		}
		
		//对具体object表的join需要放在其他join前面
		if($this->db->ar_join){
			array_unshift($this->db->ar_join,array_pop($this->db->ar_join));
		}
		
		if(array_key_exists('name',$args)){
			$this->db->like('object.name',$args['name']);
		}
		
		if(array_key_exists('id_in',$args)){
			if(!$args['id_in']){
				$this->db->where('FALSE',NULL,false);
			}else{
				$this->db->where_in('object.id',$args['id_in']);
			}
		}
		
		if(array_key_exists('id_less_than',$args)){
			$this->db->where('object.id <',$args['id_less_than']);
		}
		
		if(array_key_exists('id_greater_than',$args)){
			$this->db->where('object.id >',$args['id_greater_than']);
		}
		
		if(array_key_exists('type',$args) && $args['type']){
			$this->db->where('object.type',$args['type']);
		}
		
		if(array_key_exists('num',$args)){
			$this->db->like('object.num',$args['num']);
		}
		
		if(!array_key_exists('display',$args) || $args['display']===true){
			$this->db->where('object.display',true);
		}

		if(!array_key_exists('company',$args) || $args['company']===true){
			$this->db->where('object.company',$this->company->id);
		}
		
		if(array_key_exists('uid',$args) && $args['uid']){
			$this->db->where('object.uid',$args['uid']);
		}
		
		//使用INNER JOIN的方式来筛选标签，聪明又机灵。//TODO 总觉得哪里不对- -||
		if(array_key_exists('tags',$args) && is_array($args['tags'])){
			foreach($args['tags'] as $id => $tag_name){
				//每次连接object_tag表需要定一个唯一的名字
				$on="object.id = `t_$id`.object AND `t_$id`.tag_name = {$this->db->escape($tag_name)}";
				if(!is_integer($id)){
					$on.=" AND `t_$id`.type = {$this->db->escape($id)}";
				}
				$this->db->join("object_tag `t_$id`",$on,'inner',false);
			}
		}
		
		if(array_key_exists('without_tags',$args)){
			foreach($args['without_tags'] as $id => $tag_name){
				$query_with="SELECT object FROM object_tag WHERE tag_name = {$this->db->escape($tag_name)}";
				if(!is_integer($id)){
					$query_with.=" AND type = {$this->db->escape($id)}";
				}
				$where="object.id NOT IN ($query_with)";
				$this->db->where($where, NULL, false);
			}
		}
		
		if(array_key_exists('mod', $args)){
			$positive=$negative=0;
			foreach($args['mod'] as $mod_name => $status){
					
				if(!array_key_exists($mod_name, $this->mod_list)){
					log_message('error','mod name not found: '.$mod_name);
					continue;
				}

				$mod=$this->mod_list[$mod_name];
				$status?($positive|=$mod):($negative|=$mod);
			}
			
			$this->db
				->join('object_mod',"object_mod.object = object.id AND object_mod.user = {$this->user->id}",'inner')
				->where("object_mod.mod & $positive = $positive AND object_mod.mod & $negative = 0",NULL,false);
		}
		
		if(array_key_exists('has_meta',$args) && is_array($args['has_meta'])){
			foreach($args['has_meta'] as $name => $content){
				$name=$this->db->escape($name);
				$content=$this->db->escape($content);

				if(is_integer($name)){
					$this->db->where("object.id IN (SELECT object FROM object_meta WHERE name = $content)");
				}
				else{
					$this->db->where("object.id IN (SELECT object FROM object_meta WHERE name = $name AND content = $content)");
				}
			}
		}
		
		if(array_key_exists('is_relative_of',$args)){
			
			$on="object.id = object_relationship__is_relative_of.relative AND object_relationship__is_relative_of.object{$this->db->escape_int_array($args['is_relative_of'])}";
			
			if(array_key_exists('is_relative_of__role',$args)){
				$on.=" object_relationship__is_relative_of.role = {$this->db->escape($args['is_relative_of__role'])}";
			}
			
			$this->db->join('object_relationship object_relationship__is_relative_of',$on,'inner',false)
				->select('object_relationship__is_relative_of.id relationship_id, object_relationship__is_relative_of.relation, object_relationship__is_relative_of.accepted, object_relationship__is_relative_of.time relationship_time');
			
		}

		if(array_key_exists('has_relative_like',$args)){
			
			$on="object.id = object_relationship__has_relative_like.object AND object_relationship__has_relative_like.relative{$this->db->escape_int_array($args['has_relative_like'])}";
			
			if(array_key_exists('has_relative_like__role',$args)){
				$on.=" object_relationship__has_relative_like.role = {$this->db->escape($args['has_relative_like__role'])}";
			}
			
			$this->db->join('object_relationship object_relationship__has_relative_like',$on,'inner',false)
				->select('object_relationship__has_relative_like.id relationship_id, object_relationship__has_relative_like.relation, object_relationship__has_relative_like.accepted, object_relationship__has_relative_like.time relationship_time');
		}
		
		if(array_key_exists('is_secondary_relative_of',$args)){
			$this->db->where("object.id IN (
				SELECT relative FROM object_relative WHERE object IN (
					SELECT relative FROM object_relative
					".(empty($args['is_secondary_relative_of__media'])?'':" INNER JOIN `{$args['is_secondary_relative_of__media']}` ON `{$args['is_secondary_relative_of__media']}`.id = object_relationship.relative")."
					WHERE object{$this->db->escape_int_array($args['is_secondary_relative_of'])}
				)
			)");
		}

		if(array_key_exists('is_both_relative_with',$args)){
			$this->db->where("object.id IN (
				SELECT relative FROM object_relative WHERE object IN (
					SELECT object FROM object_relative
					".(empty($args['is_both_relative_with__media'])?'':" INNER JOIN `{$args['is_both_relative_with__media']}` ON `{$args['is_both_relative_with__media']}`.id = object_relationship.object")."
					WHERE relative{$this->db->escape_int_array($args['is_both_relative_with'])}
				)
			)");
		}

		if(array_key_exists('has_common_relative_with',$args)){
			$this->db->where("object.id IN (
				SELECT object FROM object_relative WHERE relative IN (
					SELECT relative FROM object_relative
					".(empty($args['has_common_relative_with__media'])?'':" INNER JOIN `{$args['has_common_relative_with__media']}` ON `{$args['has_common_relative_with__media']}`.id = object_relationship.relative")."
					WHERE object{$this->db->escape_int_array($args['has_common_relative_with'])}
				)
			)");
		}

		if(array_key_exists('has_secondary_relative_like',$args)){
			$this->db->where("object.id IN (
				SELECT object FROM object_relative WHERE relative IN (
					SELECT object FROM object_relative
					".(empty($args['has_secondary_relative_like__media'])?'':" INNER JOIN `{$args['has_secondary_relative_like__media']}` ON `{$args['has_secondary_relative_like__media']}`.id = object_relationship.object")."
					WHERE relative{$this->db->escape_int_array($args['has_secondary_relative_like'])}
				)
			)");
		}
		
		$args['status']=array_prefix($args,'status');
		if($args['status']){
			
			$args['status']=array_merge($args['status'], array_prefix($args['status'], '.*?', true));
			
			foreach($args['status'] as $status){
				
				if(isset($status['from']) && $status['from']){
					if(isset($status['format']) && $status['format']!=='timestamp'){
						$status['from']=strtotime($status['from']);
					}
					$this->db->where('UNIX_TIMESTAMP(schedule.start) >=',$status['from']);
				}

				if(isset($status['to']) && $status['to']){

					if(isset($status['format']) && $status['format']!=='timestamp'){
						$status['to']=strtotime($status['to']);
					}

					if(isset($status['format']) && $status['format']==='date'){
						$status['to']=strtotime(date('Y-m-d 00:00:00',$status['to']));
					}

					$this->db->where('schedule.end <',$status['to']);

				}
			}
		}
		
		if(array_key_exists('order_by',$args) && $args['order_by']){
			if(is_array($args['order_by'])){
				foreach($args['order_by'] as $orderby){
					$this->db->order_by($orderby[0],$orderby[1]);
				}
			}else{
				$this->db->order_by($args['order_by']);
			}
		}
		
		//使用两种方式来对列表分页
		if(array_key_exists('per_page',$args) && array_key_exists('page', $args)){
			//页码-每页数量方式，转换为sql limit
			$args['limit']=array($args['per_page'],($args['per_page']-1)*$args['page']);
		}
		
		if(!array_key_exists('limit', $args)){
			//默认limit
			$args['limit']=25;//$this->config->user_item('per_page');
		}
		
		if(is_array($args['limit'])){
			//sql limit方式
			call_user_func_array(array($this->db,'limit'), $args['limit']);
		}
		else{
			call_user_func(array($this->db,'limit'), $args['limit']);
		}

		$result_array=$this->db->get()->result_array();
		
		foreach(array('meta','mod','relative','status','tag') as $field){
			if(array_key_exists('get_'.$field,$args) && $args['get_'.$field]){
				array_walk($result_array,function(&$row){
					$row[$field]=call_user_func(array($this,'get'.$field));
				});
			}
		}

		$result = Array();
		$result["total"] = 1234;
		$result["data"] = $result_array;
		return $result;
	}
	
	
	function getArray($args=array(),$keyname='name',$keyname_forkey='id'){
		return array_column($this->getList($args),$keyname,$keyname_forkey);
	}
	
	function getRow($args=array()){
		!array_key_exists('limit',$args) && $args['limit']=1;
		$result=$this->getList($args);
		if(isset($result[0])){
			return $result[0];
		}else{
			return array();
		}
	}
	
	/**
	 * 获得一个对象的所有标签
	 * @param string $type
	 * @return array([type=>]name,...)
	 */
	function getTag($type=NULL){
		
		$this->db
			->select('tag.name,object_tag.type')
			->from('tag')
			->join('object_tag', 'tag.id = object_tag.tag', 'inner');
		
		$this->db->where('object_tag.object', $this->id);
		
		if($type===true){
			$this->db->where('object_tag.type IS NOT NULL');
		}
		elseif(isset($type)){
			$this->db->where('object_tag.type',$type);
		}
		
		$result=$this->db->get()->result_array();
		
		$tags=array_column($result,'name','type');
		
		return $tags;
	}
	
	/**
	 * 返回当前类型的对象中，包含$tags标签的对象，所包含的其他标签
	 * @param array $tags
	 * @param string $type
	 * @todo 按匹配度（具有尽量多相同的标签）和匹配量（匹配对象的数量）排序
	 */
	function getRelatedTags(array $tags, $type=NULL){
		
		$this->db->from('object_tag')
			->where("object IN (SELECT object FROM object_tag WHERE tag_name{$this->db->escape_array($tags)})",NULL,false)
			->group_by('tag');
		
		if(!is_null($type)){
			$this->db->where('type',$type);
		}
		
		return array_column($this->db->get()->result_array(),'tag_name');
	}
	
	/**
	 * 为一个对象添加标签一个标签
	 * 不在tag表中将被自动注册
	 * 重复标签被将忽略
	 * 同type标签将被更新
	 * @param string $name
	 * @param string $type default: NULL 标签内容在此类对象的应用的意义，如案件的”阶段“等
	 */
	function addTag($name,$type=NULL){
		$tag_id=$this->tag->match($name);
		$this->tags=$this->getTag();
		
		if(!in_array($name,$this->tags)){
			$this->db->insert('object_tag',array_merge(self::$fields_tag,array('object'=>$this->id,'tag'=>$tag_id,'type'=>$type,'tag_name'=>$name)));
		}
		elseif(isset($type) && array_key_exists($type, $this->tags)){
			$this->updateTags(array($type=>$name));
		}
		
		return $this;
	}
	
	/**
	 * 为一个对象添加一组标签
	 * @param array $tags
	 *	array(
	 *		[type=>]name,
	 *		...
	 *	)
	 */
	function addTags(array $tags){
		foreach($tags as $type => $name){
			if(is_integer($type)){
				$this->addTag($name);
			}else{
				$this->addTag($name, $type);
			}
		}
		
		return $this;
	}
	
	/**
	 * 为一个对象更新一组带类型的标签
	 * 不存在的标签将被添加
	 * @param array $tags
	 * array(
	 *	[type=>]name,
	 *	...
	 * )
	 * @param bool $delete_other default: false 将输入数组作为所有标签，删除其他标签
	 */
	function updateTags(array $tags, $delete_other=false){
		
		//按类别更新标签
		foreach($tags as $type => $name){
			if(is_integer($type)){
				continue;
			}
			$tag_id=$this->tag->match($name);
			$set=array('tag'=>$tag_id,'tag_name'=>$name);
			$where=array('object'=>$this->id,'type'=>$type);
			$this->db->update('object_tag',array_merge(self::$fields_tag,$set,$where),$where);
		}
		
		$origin_tags=$this->getTag($this->id);
		
		//添加新的标签
		$this->addTags(array_diff($origin_tags,$tags));
		
		//删除其他标签
		if($delete_other){
			$other_tags=array_diff($origin_tags,$tags);
			$this->db->where_in('tag_name',$other_tags)->delete('object_tag');
		}
		
		return $this;
		
	}
	
	function removeTag($name){
		$this->db->delete('object_tag',array('object'=>$this->id,'tag_name'=>$name));
		return $this;
	}
	
	/**
	 * 返回一个对象的资料项列表
	 * @param array $args
	 *	show_author
	 * @return type
	 */
	function getMeta(array $args=array()){
		
		$this->db->select('object_meta.*')
			->from('object_meta')
			->join('object','object_meta.object = object.id','inner')
			->where("object_meta.object",$this->id);
			
		if(array_key_exists('show_author',$args) && $args['show_author']){
			$this->db->join('object author','author.id = object_meta.uid','inner')
				->select('author.id author, author.name author_name');
		}
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 给当前对象添加一个资料项
	 * @param string $name
	 * @param string $content
	 * @param string $comment default: NULL
	 * @return \Object_model
	 */
	function addMeta($name,$content,$comment=NULL){
		
		$data=array_merge(self::$fields_meta,array(
			'object'=>$this->id,
			'name'=>$name,
			'content'=>$content,
			'comment'=>$comment
		));
		
		$this->db->insert('object_meta',$data);
		
		return $this->db->insert_id();
	}
	
	function addMetas(array $data){
		foreach($data as $row){
			$this->addMeta($row['name'], $row['content'], array_key_exists('comment', $row)?$row['comment']:NULL);
		}
		
		return $this;
	}
	
	/**
	 * 删除对象元数据
	 */
	function removeMeta($meta_id){
		$this->db->delete('object_meta',array('id'=>$meta_id,'object'=>$this->id));
		return $this;
	}
	
	/**
	 * 为指定对象写入一组资料项
	 * 遇不存在的meta name则插入，遇存在的meta name则更新
	 * 虽然一个对象可以容纳多个相同meta name的content
	 * 但使用此方法并遇到存在的meta name时进行更新操作
	 * @param array $meta: array($name=>$content,...)
	 */
	function updateMetas(array $data){
		
		$this->meta=array_column($this->getMeta(),'content','name');
		
		foreach($data as $name => $content){
			
			if(array_key_exists($name, $this->meta)){
				$set=self::$fields_meta;
				$set['content']=$content;
				unset($set['comment']);
				$this->db->update('object_meta',array('content'=>$content),array('name'=>$name));
			}
			else{
				$this->addMeta($name, $content);
			}
		}
	}
	
	/**
	 * 更新对象的单条meta，须已知object_meta.id
	 * @param int $meta_id
	 * @param string $name
	 * @param string $content
	 * @param string $comment default: NULL
	 * @return \Object_model
	 */
	function updateMeta($meta_id,$data){
		
		$data=array_merge(
			array('uid'=>$this->user->id,'time'=>time()),
			array_intersect_key($data, self::$fields_meta)
		);
		
		$this->db->update('object_meta',$data,array('id'=>$meta_id));
		
		return $this;
	}
	
	/**
	 * @todo 返回当前对象同type，同标签对象的meta name
	 */
	function getRelatedMetaNames(){
	}
	
	/**
	 * 获得对象的当前状态或者状态列表
	 */
	function getStatus(){
		$this->db->select('object_status.*')
			->select('UNIX_TIMESTAMP(datetime) timestamp')
			->select('DATE(datetime) date')
			->from('object_status')
			->where('object',$this->id);
		
		return $this->db->get()->result_array();
	}

	function addStatus($name,$datetime=NULL,$content=NULL,$group=NULL,$comment=NULL){
		
		if(is_null($datetime)){
			$datetime=time();
		}
		
		if(is_integer($datetime)){
			$datetime=date('Y-m-d H:i:s',$datetime);
		}
		
		$data=array_merge(self::$fields_status,array(
			'object'=>$this->id,
			'name'=>$name,
			'datetime'=>$datetime,
			'content'=>$content,
			'group'=>$group,
			'comment'=>$comment,
		));
		
		$this->db->insert('object_status',$data);
		
		return $this->db->insert_id();
	}
	
	function removeStatus($status_id){
		$this->db->delete('object_status',array('id'=>$status_id));
		return $this;
	}
	
	function getRelative($relation=NULL,array $mod_set=array()){
		
		$this->db->select('object.*, object_relationship.*')
			->from('object_relationship')
			->join('object','object.id = object_relationship.relative','inner')
			->where('object_relationship.object',$this->id);
		
		if(isset($relation)){
			$this->db->where('object_relationship.relation',$relation);
		}
		
		if($mod_set){
			$positive=$negative=0;
			foreach($mod_set as $relative_type => $mods){
				
				if(!array_key_exists($relative_type, $this->relative_mod_list)){
					log_message('error','relation type not found: '.$relative_type);
					continue;
				}
				
				foreach($mods as $mod_name => $status){
					
					if(!array_key_exists($mod_name, $this->relative_mod_list[$relative_type])){
						log_message('error','mod name not found: '.$mod_name);
						continue;
					}
					
					$mod=$this->relative_mod_list[$relative_type][$mod_name];
					$status?($positive|=$mod):($negative|=$mod);
				}
				
			}
			
			$this->db->where("object_relationship.mod & $positive = $positive AND object_relationship.mod & $negative = 0",NULL,false);
			
		}
		return $this->db->get()->result_array();
	}
	
	function addRelative($relative,array $data=array()){
		
		$data=array_merge(
			self::$fields_relationship,
			array_intersect_key($data, self::$fields_relationship),
			array('object'=>$this->id,'relative'=>$relative)
		);
		
		$this->db->insert('object_relationship',$data);
		
		return $this->db->insert_id();
	}
	
	function updateRelative($relationship_id,array $data){
		
		$data=array_merge(
			array('uid'=>$this->user->id,'time'=>time()),
			array_intersect_key($data, self::$fields_relationship)
		);
		
		$this->db->where('object',$this->id)
			->where('id',$relationship_id)
			->set($data)
			->update('object_relationship');
		
		return $this;
	}
	
	/**
	 * 给当前对象与一个相关对象的关系设定一个开关参数
	 * @param int $relationship_id
	 * @param string $mod_name
	 * @param string $relative_type
	 * @return boolean|\Object_model
	 */
	function addRelativeMod($relationship_id, $mod_name, $relative_type='self'){
		
		if(!array_key_exists($relative_type, $this->relative_mod_list) || !array_key_exists($mod_name, $this->relative_mod_list[$relative_type])){
			log_message('error','relation type/mod name not found: '.$relation_type.' '.$mod_name);
			return false;
		}
		
		$this->db->where('id',$relationship_id)
			->set('mod',"`mod` | {$this->relative_mod_list[$relative_type][$mod_name]}",false)
			->update('object_relationship');
		
		return $this;
	}
	
	/**
	 * 给当前对象与一个相关对象的关系去除一个开关参数
	 * @param int $relationship_id
	 * @param string $mod_name
	 * @param string $relative_type
	 * @return boolean|\Object_model
	 */
	function removeRelativeMod($relationship_id, $mod_name, $relative_type='self'){

		if(!array_key_exists($relative_type, $this->relative_mod_list) || !array_key_exists($mod_name, $this->relative_mod_list[$relative_type])){
			log_message('error','relation type/mod name not found: '.$relation_type.' '.$mod_name);
			return false;
		}
		
		$this->db->where('id',$relationship_id)
			->set('mod',"`mod` & ~ {$this->relative_mod_list[$relative_type][$mod_name]}",false)
			->update('object_relationship');
		
		return $this;
	}
	
	/**
	 * 更新关系对象的关系开关参数
	 * @param int $relationship_id
	 * @param array $set
	 *	array(
	 *		'in_todo_list'=>true,
	 *		'deleted'=>false
	 *	)
	 * @param string $relative_type
	 * @return boolean|\Object_model
	 */
	function updateRelativeMod($relationship_id, $set, $relative_type='self'){
		
		if(
			!array_key_exists($relative_type,$this->relative_mod_list)
			|| array_diff_key($set,$this->relative_mod_list[$relative_type])
		){
			log_message('error','not all relation type/mod name found: '.$relative_type.': '.implode(', ',array_keys($set)));
			return false;
		}
		
		$add=$remove=0;
		
		foreach($set as $mod_name => $status){
			$mod=$this->relative_mod_list[$relative_type][$mod_name];
			$status?($add|=$mod):($remove|=$mod);
		}
		
		$this->db->where('id',$relationship_id)
			->set('mod',"( `mod` | $add ) & ~ $remove",false)
			->update('object_relationship');
		
		return $this;
		
	}
	
	function removeRelative($relative,$relation=false){
		
		$this->db->where('object',$this->id)
			->where('relative',$relative);
		
		if($relation!==false){
			$this->db->where('relation',$relation);
		}
		
		$this->db->delete('object_relationship');
		
		return $this;
				
	}
	
	function getMod(){
		$this->db->select('user,mod')
			->from('object_mod')
			->where('object',$this->id);
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 给当前对象增加一个权限(开关参数)
	 * @todo 没必要搞得这么复杂，写成单行写入即可，供多次调用
	 * @param string $mod
	 * @param int $user
	 * @return \Object_model
	 */
	function addMod($mod,$user){
		
		$mod=$this->mod_list[$mod];
		
		if(!is_array($user)){
			$user=array($user);
		}
		
		$result_mod=$this->db->from('object_mod')
			->where('object',$this->id)
			->where_in('user',$user)
			->get()->result_array();
		
		$user_with_mod=array_column($result_mod,'user');
		
		$user_without_mod=array_diff($user,$user_with_mod);
		
		foreach($user_with_mod as $person_with_mod){
			$this->db
				->where('object',$this->id)
				->where('user',$person_with_mod)
				->set('mod','`mod` | '.intval($mod),false)
				->update('object_mod');
		}
		
		$set=array();
		
		foreach($user_without_mod as $person_without_mod){
			$set[]=array(
				'object'=>$this->id,
				'user'=>$person_without_mod,
				'mod'=>$mod,
				'uid'=>$this->user->id,
				'time'=>time()
			);
		}
		
		!empty($set) && $this->db->insert_batch('object_mod',$set);
		
		return $this;
		
	}
	
	/**
	 * 给当前对象取消一个权限(开关参数)
	 * @param string $mod
	 * @param int $people
	 * @return \Object_model
	 */
	function removeMod($mod,$people){
		
		$mod=$this->mod_list[$mod];
		
		$this->db
			->where('object',$this->id)
			->where('people',$people)
			->set('mod','`mod` & ~'.intval($mod),false)
			->update('object_mod');
		
		return $this;
	}
	
}
?>
