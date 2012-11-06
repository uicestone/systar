<?php
class SS_Table extends CI_Table{
	
	var $fields;
	var $data;
	var $menu=array(
		'head'=>NULL,
		'foot'=>NULL
	);
	var $surround_form=false;
	var $surround_box=true;
	var $attributes=array();
	var $show_line_id=false;
	var $trim_columns=false; 
	
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * __get
	 *
	 * Allows tables to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param	string
	 * @access private
	 */
	function __get($key)
	{
		$CI =& get_instance();
		return $CI->$key;
	}

	/**
	 * 将字符串形式的html标签属性组转换为数组
	 */
	protected function _parseAttributesToArray($attributes_string){
		$attributes_array=array();
		preg_match_all('/(\S+\="[\s\S]*?")/',$attributes_string,$attributes);
		foreach($attributes[0] as $attribute){
			preg_match('/(\S+)\="([\s\S]*?)"/',$attribute,$match);
			$attribute_name=$match[1];
			$attribute_value=$match[2];
			$attributes_array[$attribute_name]=$attribute_value;
		}
		return $attributes_array;
	}
	
	/**
	 * 根据$fields设置，将$this->data数据导入$rows
	 */
	protected function _init(){
		
		if(is_null($this->data)){
			show_error('no data to init, we need to run setData() before _init() - uice 2012/10/31 '.__FILE__.':'.__LINE__);
		}
		
		if(is_null($this->fields)){
			show_error('i don\'t know how to display data, run setFields() before _init - uice 2012/10/31 '.__FILE__.':'.__LINE__);
		}
		
		foreach($this->data as $row_data){
			$row=array();
			foreach($this->fields as $field_name => $field){
				if(!is_array($field)){
					show_error('field option must be an array now, old style has expired -  uice 2012/10/31 '.__FILE__.':'.__LINE__);
				}else{
					$str=isset($field['content']) ? $field['content'] : (isset($row_data[$field_name])?$row_data[$field_name]:NULL);
					$str=variableReplace($str,$row_data);
					if(isset($field['eval']) && $field['eval']){
						$str=eval($str);
					}
					if(isset($field['surround'])){
						array_walk($field['surround'],'variableReplaceSelf',$row_data);
						$str=wrap($str,$field['surround']);
					}
					if(is_null($str)){
						$str='&nbsp;';
					}
					$cell=array();
					$cell['data']=$str;
					if(isset($field['td'])){
						$cell+=$this->_parseAttributesToArray(variableReplace($field['td'],$row_data));
					}
				}
				$row[]=$cell;
			}
			$this->add_row($row);
		}
		//print_r($this->rows);
	}

	/**
		$field:输出表的列定义
			array(
				'查询结果的列名'=>array(
						'title'=>'列的显示标题'
						'surround_title'=>array(
								'mark'=>'标签名，如 a',
								'标签的属性名如href'=>'标签的值如http://www.google.com',
							)标题单元格文字需要嵌套的HTML标签
						'surround'=>同上
						'td_title'=>HTML String	该列标题单元格的html属性字符串
						'td'=>HTML String 该列所有内容单元格的html属性字符串
						'eval'=>false，'是否'将content作为源代码运行
						'content'=>'显示的内容，可以用如{client}来显示变量，{client}是数据库查询结果的字段名'
					)
			)
	*/
	function setFields(array $fields){
		$this->fields=$fields;
		$heading=array();
		foreach($fields as $field_name=>$field){
			$cell=array();
			$cell['data']=$field['title'];

			if(isset($field['td_title'])){
				$cell+=$this->_parseAttributesToArray($field['td_title']);
			}
			
			if(isset($field['surround_title'])){
				$cell['data']=wrap($cell['data'],$v['surround_title']);
				
			}elseif(!isset($field['orderby']) || $field['orderby']){
				$cell['data']=wrap($cell['data'],array('mark'=>'a','href'=>"javascript:postOrderby('".$field_name."')"));
			}
			
			$heading[]=$cell;
		}
		$this->set_heading($heading);
		return $this;
	}
	
	function wrapBox($surround_box=true){
		$this->surround_box=$surround_box;
		return $this;
	}
	
	function wrapForm($surround_form=true){
		$this->surround_form=$surround_form;
		return $this;
	}
	
	function setMenu($html,$class='right',$position='head'){
		if(!isset($this->menu[$position][$class])){
			$this->menu[$position][$class]='';
		}
		
		$this->menu[$position][$class].=$html;
		return $this;
	}
	
	function setData($data){
		$this->data=$data;
		return $this;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Generate the table
	 *
	 * @access	public
	 * @param	mixed
	 * @return	string
	 */
	function generate($table_data = NULL)
	{
		$this->_init();

		if($this->trim_columns){
			$column_is_empty=array();

			foreach($this->heading as $column_id => $column_title){
				$column_is_empty[$column_id]=true;
			}

			foreach($this->rows as $row_id => $row){
				foreach($row as $column_id => $cell){
					if(((strip_tags($cell['data'])!=''))){
						$column_is_empty[$column_id]=false;
					}
				}
			}

			foreach($this->rows as $row_id => $row){
				foreach($row as $column_id => $cell){
					if($column_is_empty[$column_id]){
						unset($this->$row[$row_id][$column_id]);
					}
				}
			}
			
			foreach($this->heading as $column_id => $column_title){
				if($column_is_empty[$column_id]){
					unset($this->heading[$column_id]);
				}
			}
		}

		// The table data can optionally be passed to this function
		// either as a database result object or an array
		if ( ! is_null($table_data))
		{
			if (is_object($table_data))
			{
				$this->_set_from_object($table_data);
			}
			elseif (is_array($table_data))
			{
				$set_heading = (count($this->heading) == 0 AND $this->auto_heading == FALSE) ? FALSE : TRUE;
				$this->_set_from_array($table_data, $set_heading);
			}
		}

		// Is there anything to display?  No?  Smite them!
		if (count($this->heading) == 0 AND count($this->rows) == 0)
		{
			return 'Undefined table data';
		}

		// Compile and validate the template date
		$this->_compile_template();

		// set a custom cell manipulation function to a locally scoped variable so its callable
		$function = $this->function;

		// Build the table!
		
		$out='';

		if($this->surround_form){
			$out.='<form method="post">'."\n";
		}
		
		if($this->surround_box){
			$this->setMenu($this->load->view('pagination',array(),true));
		}

		if(isset($this->menu['head'])){
			$out.='<div class="contentTableMenu"';

			foreach($this->attributes as $attribute_name => $attribute_value){
				$out.=' '.$attribute_name.'="'.$attribute_value.'"';
			}

			$out.='>'."\n";
			
			foreach($this->menu['head'] as $menu_div_class=>$menu_data){
				$out.='<div class="'.$menu_div_class.'">'.$menu_data.'</div>';
			}
			
			$out.='</div>'."\n";
		}

		if($this->surround_box){
			$out.='<div class="contentTableBox">'."\n";
		}
		
		$this->template['table_open']='<table class="contentTable" cellpadding="0" cellspacing="0">';
		$this->template['row_alt_start']='<tr class="oddLine">';

		$out .= $this->template['table_open'];
		$out .= $this->newline;

		// Add any caption here
		if ($this->caption)
		{
			$out .= $this->newline;
			$out .= '<caption>' . $this->caption . '</caption>';
			$out .= $this->newline;
		}

		// Is there a table heading to display?
		if (count($this->heading) > 0)
		{
			$out .= $this->template['thead_open'];
			$out .= $this->newline;
			$out .= $this->template['heading_row_start'];
			$out .= $this->newline;

			foreach ($this->heading as $heading)
			{
				$temp = $this->template['heading_cell_start'];

				foreach ($heading as $key => $val)
				{
					if ($key != 'data')
					{
						$temp = str_replace('<th', "<th $key=\"$val\"", $temp);
						
					}
				}

				$out .= $temp;
				$out .= isset($heading['data']) ? $heading['data'] : '';
				$out .= $this->template['heading_cell_end'];
			}

			$out .= $this->template['heading_row_end'];
			$out .= $this->newline;
			$out .= $this->template['thead_close'];
			$out .= $this->newline;
		}

		// Build the table rows
		if (count($this->rows) > 0)
		{
			$out .= $this->template['tbody_open'];
			$out .= $this->newline;

			$i = 1;
			foreach ($this->rows as $row)
			{
				if ( ! is_array($row))
				{
					break;
				}

				// We use modulus to alternate the row colors
				$name = (fmod($i++, 2)) ? '' : 'alt_';

				$out .= $this->template['row_'.$name.'start'];
				$out .= $this->newline;

				foreach ($row as $cell)
				{
					$temp = $this->template['cell_'.$name.'start'];

					foreach ($cell as $key => $val)
					{
						if ($key != 'data')
						{
							$temp = str_replace('<td', "<td $key=\"$val\"", $temp);
						}
					}

					$cell = isset($cell['data']) ? $cell['data'] : '';
					$out .= $temp;

					if ($cell === "" OR $cell === NULL)
					{
						$out .= $this->empty_cells;
					}
					else
					{
						if ($function !== FALSE && is_callable($function))
						{
							$out .= call_user_func($function, $cell);
						}
						else
						{
							$out .= $cell;
						}
					}

					$out .= $this->template['cell_'.$name.'end'];
				}

				$out .= $this->template['row_'.$name.'end'];
				$out .= $this->newline;
			}

			$out .= $this->template['tbody_close'];
			$out .= $this->newline;
		}

		$out .= $this->template['table_close'];

		if($this->surround_box){
			$this->setMenu($this->load->view('pagination',array(),true));
		}

		if($this->surround_box){
			$out.='</div>'."\n";
		}
		
		if(isset($this->menu['foot'])){
			$out.='<div class="contentTableMenu"';

			foreach($this->attributes as $attribute_name => $attribute_value){
				$out.=' '.$attribute_name.'="'.$attribute_value.'"';
			}

			$out.='>'."\n";
			
			foreach($this->menu['foot'] as $menu_div_class=>$menu_data){
				$out.='<div class="'.$menu_div_class.'">'.$menu_data.'</div>';
			}
			
			$out.='</div>'."\n";
		}

		if($this->surround_form){
			$out.='</form>'."\n";
		}
		
		// Clear table class properties before generating the table
		$this->clear();

		return $out;
	}
}
?>