<?php
class SS_Model extends CI_Model{
	function __construct(){
		parent::__construct();
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
			$this->load->view('search',array(),'sidebar');
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
			$this->load->view('daterange',array(),'sidebar');
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
				$q_rows="SELECT COUNT(*) AS number FROM (".$q_rows.")query";
			}else{
				$q_rows=preg_replace('/^[\s\S]*?FROM /','SELECT COUNT(1) AS number FROM ',$q_rows);
				$q_rows=preg_replace('/GROUP BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
				$q_rows=preg_replace('/ORDER BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
			}
		}

		$rows=db_fetch_field($q_rows);
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

		$query.=' LIMIT '.option('pagination/start').','.(option('pagination/items'));$this->output->message($query);
		return $query;
	}
	
	/**
	 * 在每个add/edit页面之前获得数据ID，插入新数据或者根据数据ID获得数据数组
	 * @param $id 需要获得的数据id，如果是添加新数据，那么为NULL
	 */
	function getPostData($id=NULL){
		if(is_null($id)){
			$id=$this->add();
		}
	
		if($id){
			$this->id=$id;
		}else{
			show_error('获得数据条目id失败');
		}

		return $this->fetch($id);
	}
}
?>