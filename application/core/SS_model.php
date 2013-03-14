<?php
class SS_Model extends CI_Model{
	function __construct(){
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
	 * 添加标签，而不论标签是否存在
	 * @param type {item} id
	 * @param type $label_name 标签内容或标签id（须将下方input_as_id定义为true）
	 * @param type $type 标签内容在此类对象的应用的意义，如“分类”，“类别”，案件的”阶段“等
	 * @return type 返回{item}_label的insert_id
	 */
	function addLabel($item_id,$label_name,$type=NULL){
		$item_id=intval($item_id);
		$label_id=$this->label->match($label_name);
		$this->db->insert($this->table.'_label',array($this->table=>$item_id,'label'=>$label_id,'type'=>$type,'label_name'=>$label_name));
		return $this->db->insert_id();
	}
	
	function removeLabel($item_id,$label_name){
		$item_id=intval($item_id);
		return $this->db->delete($this->table.'_label',array($this->table=>$item_id,'label_name'=>$label_name));
	}
	
	function getLabels($item_id,$type=NULL){
		$item_id=intval($item_id);
		
		$query="
			SELECT label.name,{$this->table}_label.type
			FROM label INNER JOIN {$this->table}_label ON label.id={$this->table}_label.label
			WHERE {$this->table}_label.{$this->table} = $item_id
		";
		
		if($type===true){
			$query.=" AND {$this->table}_label.type IS NOT NULL";
		}
		elseif(isset($type)){
			$query.=" AND {$this->table}_label.type = '$type'";
		}
		
		$result=$this->db->query($query)->result_array();
		
		$labels=array_sub($result,'name','type');
		
		return $labels;
	}
	
	/**
	 * 获得所有或指定类别的标签名称，按热门程度排序
	 * @param $type
	 * @return array([$type=>]$label_name,...) 一个由标签类别为键名（如果标签类别存在），标签名称为键值构成的数组
	 */
	function getAllLabels($type=NULL){
		$query="
			SELECT {$this->table}_label.type,{$this->table}_label.label_name AS name,COUNT(*) AS hits
			FROM {$this->table}_label INNER JOIN {$this->table} ON {$this->table}.id={$this->table}_label.{$this->table}
			WHERE {$this->table}.company={$this->company->id}
		";
		
		if(isset($type)){
			$query.=" AND type='$type";
		}
		
		$query.="
			GROUP BY {$this->table}_label.label
			ORDER BY hits DESC
		";
		
		$result_array = $this->db->query($query)->result_array();
		
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
	 * 注意，只有带type选项的label才可能被update，否则需要逐个remove或add
	 * @param int {item}_id
	 * @param array $labels: array($type=>$name,...)
	 */
	function updateLabels($item_id,$labels){
		$item_id=intval($item_id);
		
		//没有在参数列表中直接做出限制，用来兼容一些特殊情况
		if(!is_array($labels)){
			return;
		}
		
		foreach($labels as $type => $name){
			$label_id=$this->label->match($name);
			$set=array('label'=>$label_id,'label_name'=>$name);
			$where=array($this->table=>$item_id,'type'=>$type);
			$result=$this->db->get_where($this->table.'_label',$where);
			if($result->num_rows()===0){
				return $this->db->insert($this->table.'_label',$set+$where);
			}else{
				return $this->db->update($this->table.'_label',$set,$where);
			}
		}
	}
	
	//TODO 此处用来处理list的搜索条件及视图。这种做法不太科学。而且与label_model和各小model中的search方法（现在还叫match方法）重名。
	function search($query, array $search_fields, $generate_view=true){
		
		if(option('in_search_mod')){
			/*foreach($search_fields as $search_key => $ui_name){
				$keyword_array=preg_split('/[\s]+|,/',option('keyword/'.$search_key));
				foreach($keyword_array as $keyword){
					$query.=" AND ";
				}
			}*/
			
			$condition_search='';

			foreach(option('keyword') as $field => $keywords){

				$condition=preg_split('/[\s]+|,/',option('keyword/'.$field));

				$condition=' AND ('.db_implode($condition,' AND ',db_field_name($field),' LIKE ',"'%","%'",'').')';

				$condition_search.=$condition;

			}
			$query.=$condition_search;
		}
		
		if($generate_view){
			$this->load->addViewData('search_fields',$search_fields);
			$this->load->view('search',true,'sidebar');
		}

		return $query;
	}

	/*
	 * 为查询语句加上日期条件
	 */
	function dateRange($query,$date_field,$date_field_is_timestamp=true, $generate_view=true){

		if(option('in_date_range')){

			if($date_field_is_timestamp){
			$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from_timestamp')."' AND ".db_field_name($date_field)."<'".option('date_range/to_timestamp')."')";
			}else{
				$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from')."' AND ".db_field_name($date_field)."<='".option('date_range/to')."')";
			}

			$query.=$condition_date_range;
		}
		
		if($generate_view){
			$this->load->addViewData('date_field',$date_field);
			$this->load->view('daterange',true,'sidebar');
		}
		
		return $query;
	}

	function addCondition(&$q,$condition_array,$unset=array()){
		
		$method=METHOD;
		
		foreach($unset as $changed_variable => $unset_array){
			if(!is_array($unset_array)){
			  $unset_array=array($unset_array);
			}
			foreach($unset_array as $unset_variable){
				if($this->input->post($changed_variable)!==false){
					unset($_SESSION[CONTROLLER][METHOD][$this->$method->id][$unset_variable]);
				}
			}
		}

		foreach($condition_array as $variable=>$field){
			if($this->input->post($variable)!==false){
				option($variable,$_POST[$variable]);
			}

			if(!is_null(option($variable)) && option($variable)!=''){
				$q.=' AND '.db_field_name($field)."='".option($variable)."'";
			}
		}
		return $q;
	}

	/*
	 * 为sql语句添加排序依据，无反回值
	 */
	function orderBy($query,$default_field,$default_method='ASC',array $field_need_convert=array()){
		
		if (is_null(option('orderby'))){
			option('orderby',$default_field);
		}

		if (is_null(option('method'))){
			option('method',$default_method);
		}

		if($this->input->post('orderby') && !is_null(option('orderby')) && $this->input->post('orderby')==$_SESSION[CONTROLLER][METHOD]['orderby']){
			if(option('method')=='ASC'){
				option('method','DESC');
			}else{
				option('method','ASC');
			}
		}

		if($this->input->post('orderby')){
			option('orderby',$this->input->post('orderby'));
		}
		if($this->input->post('method')){
			option('method',$this->input->post('method'));
		}

		$need_convert=in_array(option('orderby'),$field_need_convert);

		$query.=' ORDER BY'.
			($need_convert?' convert(':'').
			db_field_name(option('orderby')).
			($need_convert?' USING GBK) ':' ').
			option('method');
		
		return $query;
	}

	function pagination($query,$q_rows=NULL){

		if(is_null($q_rows)){
			$q_rows=$query;
			if(preg_match('/GROUP BY[^()]*?[ORDER BY].*?$/',$q_rows)){
				$q_rows="SELECT COUNT(*) AS rows FROM (".$q_rows.")query";
			}else{
				$q_rows=preg_replace('/^[\s\S]*?FROM /','SELECT COUNT(1) AS rows FROM ',$q_rows);
				$q_rows=preg_replace('/GROUP BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
				$q_rows=preg_replace('/ORDER BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
			}
		}
		
		$rows=array_pop($this->db->query($q_rows)->row_array());

		if(option('pagination/start')>$rows || $rows==0){
			//已越界或空列表时，列表起点归零
			option('pagination/start',0);

		}elseif(option('pagination/start')+option('pagination/item')>=$rows && $rows>option('pagination/items')){
			//末页且非唯一页时，列表起点定位末页起点
			option('pagination/start',$rows - ($rows % option('pagination/items')));
		}

		if(!is_null(option('pagination/start')) && option('pagination/items')){
			if($this->input->post('start')!==false){
				option('pagination/start',$this->input->post('start'));
			}
			if($this->input->post('items')!==false){
				option('paginantion/items',$this->input->post('items'));
			}
		}else{
			option('pagination/start',0);
			option('pagination/items',25);
		}
		
		option('pagination/rows',$rows);
		
		option('pagination/pages', ceil(option('pagination/rows') / option('pagination/items')) );
		
		option('pagination/pagenum',option('pagination/start') / option('pagination/items') + 1);

		$query.=' LIMIT '.option('pagination/start').','.(option('pagination/items'));
		return $query;
	}
	
	function limit($q_rows){

		$rows=array_pop($this->db->query($q_rows)->row_array());

		if(option('pagination/start')>$rows || $rows==0){
			//已越界或空列表时，列表起点归零
			option('pagination/start',0);

		}elseif(option('pagination/start')+option('pagination/item')>=$rows && $rows>option('pagination/items')){
			//末页且非唯一页时，列表起点定位末页起点
			option('pagination/start',$rows - ($rows % option('pagination/items')));
		}

		if(!is_null(option('pagination/start')) && option('pagination/items')){
			if($this->input->post('start')!==false){
				option('pagination/start',$this->input->post('start'));
			}
			if($this->input->post('items')!==false){
				option('paginantion/items',$this->input->post('items'));
			}
		}else{
			option('pagination/start',0);
			option('pagination/items',25);
		}
		
		option('pagination/rows',$rows);
		
		option('pagination/pages', ceil(option('pagination/rows') / option('pagination/items')) );
		
		option('pagination/pagenum',option('pagination/start') / option('pagination/items') + 1);

		return array(option('pagination/items'),option('pagination/start'));
	}
	
}
?>