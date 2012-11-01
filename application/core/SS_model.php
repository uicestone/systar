<?php
class SS_Model extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	function search(){
		
		$search_fields=$this->table->search_fields;

		if($this->input->post('search_cancel')){
			unset($_SESSION[CONTROLLER][METHOD]['in_search_mod']);
			unset($_SESSION[CONTROLLER][METHOD]['keyword']);
		}

		if($this->input->post('search')){
			option('keyword',array_trim($this->input->post('keyword')));
			option('in_search_mod',true);
		}

		if(option('in_search_mod')){
			foreach($search_fields as $search_key => $ui_name){
				$keyword_array=preg_split('/[\s]+|,/',option('keyword/'.$search_key));
				foreach($keyword_array as $keyword){
					$this->db->like($search_key,$keyword);
				}
			}
		}
	}

	/*
	 * 为查询语句加上日期条件
	 */
	function dateRange(&$q,$date_field,$date_field_is_timestamp=true){
		if(is_posted('date_range_cancel')){
			unset($_SESSION[CONTROLLER][METHOD]['in_date_range']);
			unset($_SESSION[CONTROLLER][METHOD]['date_range']);
		}

		if(is_posted('date_range')){
			if(!strtotime($_POST['date_from']) || !strtotime($_POST['date_to'])){
				showMessage('日期格式错误','warning');

			}else{
				option('date_range/from_timestamp',strtotime($_POST['date_from']));
				option('date_range/to_timestamp',strtotime($_POST['date_to'])+86400);

				option('date_range/from',date('Y-m-d',option('date_range/from_timestamp')));
				option('date_range/to',date('Y-m-d',option('date_range/to_timestamp')-86400));

				option('in_date_range',true);
			}
		}

		if(option('in_date_range')){

			if($date_field_is_timestamp){
			$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from_timestamp')."' AND ".db_field_name($date_field)."<'".option('date_range/to_timestamp')."')";
			}else{
				$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from')."' AND ".db_field_name($date_field)."<='".option('date_range/to')."')";
			}

			$q.=$condition_date_range;
		}

		$date_range_bar=
		'<form method="post" name="date_range">'.
			'<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">'.
			'<thead><tr><td width="60px">日期</td><td>&nbsp;</td></tr></thead>'.
			'<tbody>'.
			'<tr><td>开始：</td><td><input type="text" name="date_from" value="'.option('date_range/from').'" class="date" /></td></tr>'.
			'<tr><td>结束：</td><td><input type="text" name="date_to" value="'.option('date_range/to').'" class="date" /></td></tr>'.
			'<input style="display:none;" name="date_field" value="'.$date_field.'" />';

		$date_range_bar.='<tr><td colspan="2"><input type="submit" name="date_range" value="提交" />';
		if(option('in_date_range')){
			$date_range_bar.='<input type="submit" name="date_range_cancel" value="取消" tabindex="1" />';
		}
		$date_range_bar.='</td></tr></tbody></table></form>';
	}

	/*
	 * TODO 添加addCondition()的描述
	 */
	function addCondition(&$q,$condition_array,$unset=array()){
		foreach($unset as $changed_variable => $unset_variable){
			if(is_posted($changed_variable)){
				unset($_SESSION[CONTROLLER][METHOD][$unset_variable]);
			}
		}

		foreach($condition_array as $variable=>$field){
			if(is_posted($variable)){
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
	function orderBy(){
		
		if (is_null(option('orderby'))){
			option('orderby',$this->table->order_by['default_field']);
		}

		if (is_null(option('method'))){
			option('method',$this->table->order_by['default_method']?'ASC':$this->table->order_by['default_method']);
		}

		if(is_posted('orderby') && !is_null(option('orderby')) && $_POST['orderby']==$_SESSION[CONTROLLER][METHOD]['orderby']){
			if(option('method')=='ASC'){
				option('method','DESC');
			}else{
				option('method','ASC');
			}
		}

		if(is_posted('orderby')){
			option('orderby',$this->input->post('orderby'));
		}
		if(is_posted('method')){
			option('method',$this->input->post('method'));
		}

		$need_convert=in_array(option('orderby'),$this->table->order_by['field_need_convert']);

		$this->db->order_by(
			($need_convert?'convert(':'').
			db_field_name(option('orderby')).
			($need_convert?' USING GBK) ':' ').
			option('method'));
	}

	/**
	 * 为sql语句添加LIMIT字段，达到分页目的
	 */
/*
	function pagination($query_rows=NULL){
		if(isset($_SESSION[CONTROLLER.'/'.METHOD.'/pagination'])){
			$this->pagination=  unserialize($_SESSION[CONTROLLER.'/'.METHOD.'/pagination']);
		}else{
			$this->pagination->base_url=$this->config->item('base_url').CONTROLLER.'/'.METHOD.'/';
			$this->pagination->per_page=25;
		}

		if(isset($query_rows)){
			$this->pagination->total_rows=db_fetch_field($q_rows);

		}else{
			$db=clone $this->db;
			$this->pagination->total_rows=$db->count_all_results();
		}

		$this->pagination->links=$this->pagination->create_links();

		if($this->pagination->cur_page>$this->pagination->total_rows || $this->pagination->total_rows==0){
			//已越界或空列表时，列表起点归零
			$this->pagination->cur_page=0;

		}elseif($this->pagination->cur_page+$this->pagination->per_page>=$this->pagination->total_rows && $this->pagination->total_rows>$this->pagination->per_page){
			//末页且非唯一页时，列表起点定位末页起点
			$this->pagination->cur_page=$this->pagination->total_rows - ($this->pagination->total_rows % $this->pagination->per_page);
		}

		if(is_posted('previousPage')){
			$this->pagination->cur_page-=$this->pagination->per_page;
			if($this->pagination->cur_page<0){
				$this->pagination->cur_page=0;
			}
		}elseif(is_posted('nextPage')){
			if($this->pagination->cur_page+$this->pagination->per_page<$this->pagination->total_rows){
				$this->pagination->cur_page+=$this->pagination->per_page;
			}
		}elseif(is_posted('firstPage')){
			$this->pagination->cur_page=0;
		}elseif(is_posted('finalPage')){
			if($this->pagination->total_rows % $this->pagination->per_page==0){
				$this->pagination->cur_page=$this->pagination->total_rows - $this->pagination->per_page;
			}else{
				$this->pagination->cur_page=$this->pagination->total_rows - ($this->pagination->total_rows % $this->pagination->per_page);
			}
		}
			
		$this->db->limit($this->pagination->per_page,$this->pagination->cur_page);

		$_SESSION[CONTROLLER.'/'.METHOD.'/pagination']=serialize($this->pagination);
	}
 */
	function pagination($query_rows=NULL){
		if(isset($query_rows)){
			$rows=db_fetch_field($query_rows);

		}else{
			$db=clone $this->db;
			$rows=$db->count_all_results();
		}

/*
 		if(is_null($q_rows)){
			$q_rows=$q;
			if(preg_match('/GROUP BY[^()]*?[ORDER BY].*?$/',$q_rows)){
				$q_rows="SELECT COUNT(*) AS number FROM (".$q_rows.")query";
			}else{
				$q_rows=preg_replace('/^[\s\S]*?FROM /','SELECT COUNT(1) AS number FROM ',$q_rows);
				$q_rows=preg_replace('/GROUP BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
				$q_rows=preg_replace('/ORDER BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
			}
		}

		$rows=db_fetch_field($q_rows);
*/
		if(option('list/start')>$rows || $rows==0){
			//已越界或空列表时，列表起点归零
			option('list/start',0);

		}elseif(option('list/start')+option('list/item')>=$rows && $rows>option('list/items')){
			//末页且非唯一页时，列表起点定位末页起点
			option('list/start',$rows - ($rows % option('list/items')));
		}

		if(!is_null(option('list/start')) && option('list/items')){
			if(is_posted('previousPage')){
				option('list/start',option('list/start')-option('list/items'));
				if(option('list/start')<0){
					option('list/start',0);
				}
			}elseif(is_posted('nextPage')){
				if(option('list/start')+option('list/items')<$rows){
					option('list/start',option('list/start')+option('list/items'));
				}
			}elseif(is_posted('firstPage')){
				option('list/start',0);
			}elseif(is_posted('finalPage')){
				if($rows % option('list/items')==0){
					option('list/start',$rows - option('list/items'));
				}else{
					option('list/start',$rows - ($rows % option('list/items')));
				}
			}
		}else{
			option('list/start',0);
			option('list/items',25);
		}
		
		option('list/rows',$rows);

		$this->db->limit(option('list/items'),option('list/start'));
	}

	/**
		输出一个数组，包含表格中的所有单元格数据
		$q_data:数据库查询语句,必须包含WHERE条件,留空为WHERE 1=1
		$field:输出表的列定义
			array(
				'查询结果的列名'=>'显示的列名',//此为简写
				'查询结果的列名'=>array(
						'title'=>'列的显示标题'
						'surround_title'=>array(
								'mark'=>'标签名，如 a',
								'标签的属性名如href'=>'标签的值如http://www.google.com',
							)标题单元格文字需要嵌套的HTML标签
						'surround'
						'eval'=>false，'是否'将content作为源代码运行
						'content'=>'显示的内容，可以用如{client}来显示变量，{client}是数据库查询结果的字段名'
					)
			)
	*/
	function fetchTable(){

		$this->search();//为当前sql对象添加搜索条件
		$this->pagination();//为当前sql对象添加limit从句
		$this->orderBy();//为当前sql对象添加orderby从句
		
		return $this->db->get()->result_array();

	}

	function fetchTableArray($query,$field){

		$result=db_query($query);

		if($result===false){
			return false;
		}

		$table=array('_field'=>array());

		foreach($field as $k=>$v){
			if(!is_array($v))
				$table['_field'][$k]=$v;
			else{
				$str='';
				if(isset($v['title'])){
					$str=$v['title'];
				}
				if(isset($v['surround_title'])){
					$str=$this->surround($str,$v['surround_title']);
				}elseif(!isset($v['orderby']) || $v['orderby']){
					$str=$this->surround($str,array('mark'=>'a','href'=>"javascript:postOrderby('".$k."')"));
				}
				$table['_field'][$k]['html']=$str;
				if(isset($v['td_title'])){
					$table['_field'][$k]['attrib']=$v['td_title'];
				}
			}
		}

		while($data=db_fetch_array($result)){
			$line_data=array();
			foreach($field as $k => $v){
				if(!is_array($v))
					$line_data[$k]=$this->variableReplace(isset($data[$k])?$data[$k]:NULL,$data);
				else{
					$str=isset($v['content']) ? $v['content'] : (isset($data[$k])?$data[$k]:NULL);
					$str=$this->variableReplace($str,$data);
					if(isset($v['eval']) && $v['eval']){
						$str=eval($str);
					}
					if(isset($v['surround'])){
						array_walk($v['surround'],array($this,'variableReplaceSelf'),$data);
						$str=$this->surround($str,$v['surround']);
					}
					$line_data[$k]['html']=$str;
					if(isset($v['td'])){
						$line_data[$k]['attrib']=$this->variableReplace($v['td'],$data);
					}
				}
			}
			$table[]=$line_data;
		}

		return $table;
	}

	/*
	 * 仅用在fetchTableArray中
	 * 将field->content等值中包含的变量占位替换为数据结果中他们的值
	 */
	function variableReplace($content,$row){
		while(preg_match('/{(\S*?)}/',$content,$match)){
			if(!isset($row[$match[1]])){
				$row[$match[1]]=NULL;
			}
			$content=str_replace($match[0],$row[$match[1]],$content);
		}
		return $content;
	}

	function variableReplaceSelf(&$content,$key,$row){
		$content=$this->variableReplace($content,$row);
	}

	/*
	 * 包围，生成html标签的时候很有用
	 * $surround=array(
	 * 		'mark'=>'div',
	 * 		'attrib1'=>'value1',
	 * 		'attrib2'=>'value2'
	 * );
	 * 将生成<div attrib1="value1" attrib2="value2">$str</div>
	 */
	function surround($str,$surround){
		if($str=='')
			return '';

		$mark=$surround['mark'];
		unset($surround['mark']);
		$property=db_implode($surround,' ',NULL,'=','"','"','','value',false);
		return '<'.$mark.' '.$property.'>'.$str.'</'.$mark.'>';

	}

}
?>