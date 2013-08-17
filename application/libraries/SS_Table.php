<?php
class SS_Table extends CI_Table{
	
	var $fields;//表格每列的输出方式
	var $attributes;//表格、box和首位菜单的html属性
	var $row_attr;//行html元素属性
	var $show_line_id;//是否在表格第一列显示行号
	var $trim_columns;//是否清空空列
	
	function __construct(){
		parent::__construct();
		$this->fields=NULL;
		$this->attributes=array(
			'class'=>'contentTable',
			'cellspacing'=>0,
			'cellspadding'=>0
		);
		$this->template=NULL;
		$this->row_attr=array();
		$this->show_line_id=false;
		$this->trim_columns=false;
		
	}
	
	/**
	 * 允许Table类内访问CI控制器中加载的其他对象
	 */
	function __get($key)
	{
		$CI =& get_instance();
		return $CI->$key;
	}

	/**
	 * 将字符串形式的html标签属性组转换为数组
	 */
	function _parseAttributesToArray($attributes_string){
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
	
	function _set_from_object($query) {
		parent::_set_from_object($query);
		$this->_compile_rows();
	}
	
	function _set_from_array($data, $set_heading = TRUE) {
		parent::_set_from_array($data, $set_heading);
		$this->_compile_rows();
	}
	
	/**
	 * 根据$this->fields和输入的$data数据，返回一个新的$data_compiled
	 */
	function _compile_rows(){
		
		if(!is_array($this->fields)){
			return;
		}

		$rows_compiled=array();
		foreach($this->rows as $row){
			$row_compiled=array();
			
			if(isset($row['id'])){
				$row_compiled['_attr']['id']=$row['id']['data'];
			}
			
			foreach($this->row_attr as $attr_name => $attr_value){
				$row_compiled['_attr'][$attr_name]=$this->parser->parse_string($attr_value, array_column($row,'data'), true);
			}
			
			foreach($this->fields as $field_name => $field){
				$cell=array('data'=>NULL);
				
				//如果列设定中没有cell,或者cell是数组但没有data键，那么使用原始数据
				if(!isset($field['cell']) || (is_array($field['cell']) && !isset($field['cell']['data']))){
					if(array_key_exists($field_name, $row) && array_key_exists('data', $row[$field_name])){
						$cell['data']=$row[$field_name]['data'];
					}
				}
				else{
					if(is_array($field['cell'])){
						$cell['data']=$field['cell']['data'];
					}else{
						$cell['data']=$field['cell'];
					}
					
				}
				
				if(array_key_exists('cell', $field) && is_array($field['cell'])){
					$cell+=$field['cell'];
				}
				
				foreach($cell as $attr_name => $attr_value){
					$cell[$attr_name]=$this->parser->parse_string($attr_value,array_column($row,'data'),true);
				}
				
				//用指定函数来处理$cell[data]
				if(isset($field['parser'])){
					foreach($field['parser']['args'] as $key => $value){
						if(array_key_exists($value, $row)){
							$field['parser']['args'][$key]=array_key_exists('data', $row[$value])?$row[$value]['data']:$row[$value];
						}
					}
					$cell['data']=call_user_func_array($field['parser']['function'], $field['parser']['args']);
				}
				
				$row_compiled[$field_name]=$cell;
			}
			$rows_compiled[]=$row_compiled;
		}
		
		$this->rows=$rows_compiled;
	}

	/**
		$field:输出表的列定义
			array(
				'_attr'=>array(
					行属性名=>行属性值
				),
				'查询结果的列名'=>array(
						'heading'=>表头单元格元素，可以是html，也可以是数组，其中data键(如果有)是html内容，其余是表头元素的的html属性
						'cell'=>单元格元素
						'eval'=>false，'是否'将生成的cell的data代码作为源代码运行@deprecated
					)
			)
	*/
	function setFields(array $fields){
		$this->fields=$fields;
		
		$heading=array();
		
		foreach($this->fields as $field_name => $field){
			
			if(isset($field['heading'])){
				if(is_array($field['heading']) && isset($field['heading']['data'])){
					$heading[$field_name]=$field['heading'];
				}else{
					$heading[$field_name]=array('data'=>$field['heading']);
				}
			}
		}
		
		$this->set_heading($heading);
		
		unset($this->heading[0]);
		//set_heading()方法在处理字符串键的表头时，误多处理了一个0键，其中又包含了整个表头。故将其释放。
		
		return $this;
	}
	
	function setAttribute($name,$value){
		$this->attributes[$name]=$value;
		return $this;
	}
	
	function setRowAttributes(array $attributes){
		$this->row_attr=$attributes;
		return $this;
	}
	
	function setData($table_data){
		if (is_object($table_data))
		{
			$this->_set_from_object($table_data);
		}
		elseif (is_array($table_data))
		{
			$set_heading = (count($this->heading) == 0 AND $this->auto_heading == FALSE) ? FALSE : TRUE;
			$this->_set_from_array($table_data, $set_heading);
		}

		return $this;
	}
	
	/**
	 * 删除全空列
	 */
	function trimColumns(){
		$column_is_empty=array();

		foreach($this->heading as $column_name => $column_title){
			$column_is_empty[$column_name]=true;
		}

		foreach($this->rows as $row_id => $row){
			foreach($row as $column_id => $cell){
				if($column_id==='_attr'){
					continue;
				}
				$cell['data']=strip_tags($cell['data']);
				if($cell['data']!=='' && !is_null($cell['data'])){
					$column_is_empty[$column_id]=false;
				}
			}
		}

		foreach($this->rows as $row_id => $row){
			foreach($row as $column_id => $cell){
				if($column_id==='_attr'){
					continue;
				}
				if($column_is_empty[$column_id]){
					unset($this->rows[$row_id][$column_id]);
				}
			}
		}

		foreach($this->heading as $column_id => $column_title){
			if($column_is_empty[$column_id]){
				unset($this->heading[$column_id]);
			}
		}

		return $this;
	}
	
	function generate($table_data = NULL){
		
		// The table data can optionally be passed to this function
		// either as a database result object or an array
		if ( ! is_null($table_data))
		{
			$this->setData($table_data);
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

		$out = $this->template['table_open'];
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
						//$temp = str_replace('<th', "<th $key='$val'", $temp);	uicestone 2012/11/7
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

				$temp = $this->template['row_'.$name.'start'];
				
				if(isset($row['_attr'])){
				
					foreach ($row['_attr'] as $key => $val){
						$temp = str_replace('<tr', "<tr $key=\"$val\"", $temp);
					}

					unset($row['_attr']);
				
				}

				$out .= $temp;
				$out .= $this->newline;

				foreach ($row as $cell)
				{
					$temp = $this->template['cell_'.$name.'start'];

					foreach ($cell as $key => $val)
					{
						if ($key != 'data')
						{
							//$temp = str_replace('<td', "<td $key='$val'", $temp);	uicestone 2012/11/7
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

		// Clear table class properties before generating the table
		$this->clear();

		return $out;
	}
	
	function _default_template() {
		$default_template=parent::_default_template();
		
		$default_template['table_open']='<table';
		foreach($this->attributes as $attr=>$value){
			$default_template['table_open'].=" $attr=\"$value\"";
		}
		$default_template['table_open'].='>';
		
		$default_template['row_alt_start']='<tr class="oddLine">';
		return $default_template;
	}

	function clear(){
		parent::clear();
		$this->__construct();
	}
	
	/**
	 * 生成exel表格并向浏览器输出
	 * 
	 * @access public
	 * @return void
	 */
	//@TODO 缩进存在空格，建议使用$this->generateData处理根据$fields和$data处理$rows，然后根据后者输出表格。（输出时可以对每格数据执行一下strip_tags()）
	function generateExcel($table_data=NULL){
		if (!is_null($table_data)){
			$this->setData($table_data);
		}
		
		//print_r($this->rows);exit;

		require_once(APPPATH.'third_party/PHPExcel/PHPExcel.php');
		//创建EXCEL对象，并获取当前的工作表
		$php_excel=new PHPExcel();
		$current_sheet=$php_excel->getActiveSheet();

		//列最大字符单元数，用来存储每一列的最大字符单元
		$column_max_char_units=array_fill(0,count($this->heading),0);
		
		//对excel对象的每一行每一列写入相应的数据
		$column_index=0;
		foreach($this->heading as $heading_cell){
			$cell_value=strip_tags($heading_cell['data']);
			$this->_compareAndSetColumnMaxCharUnit($column_max_char_units,$column_index,$cell_value);
			$current_sheet->setCellValueByColumnAndRow($column_index,1,$cell_value);
			$column_index++;
		}
		
		for($row_id=0;$row_id<count($this->rows);$row_id++){
			//其他行写该行每一列的数据
			$column_index=0;
			foreach($this->rows[$row_id] as $cell_name => $cell){
				
				//过滤掉$this->rows中每一行数据中的行属性
				if($cell_name==='_attr'){
					continue;
				}

				$cell_value=strip_tags($cell['data']);
				$this->_compareAndSetColumnMaxCharUnit($column_max_char_units,$column_index,$cell_value);
				$current_sheet->setCellValueByColumnAndRow($column_index,$row_id+2,$cell_value);
				$column_index++;
			}		
		}
		
		//根据最大字符单元和列宽因子计算列宽
		/*
		$widen_factor=2.5;
		foreach($column_max_char_units as $column_index=>$max_char_unit){
			$column_width=$max_char_unit*$widen_factor;
			$current_sheet->getColumnDimensionByColumn($column_index)->setWidth($column_width);
		}
		*/

		//output excel file to browser directly
		$excel_writer=PHPExcel_IOFactory::createWriter($php_excel, 'Excel5');
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Content-Disposition:inline;filename="output.xls"');
		header("Content-Transfer-Encoding: binary");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
		$excel_writer->save("php://output");
	}
	
	/**
	 * 和当前的列的最大字符单元数比较，如果比它大就将其覆盖
	 * @access private
	 * @param array $column_max_char_units 所有列的最大字符单元
	 * @param int $column_index 当前列的索引
	 * @param string $cell_value 单元格的值
	 * @return void
	 */
	private function _compareAndSetColumnMaxCharUnit(&$column_max_char_units,$column_index,$cell_value){
		$encoding=$this->config->item('charset');
		$char_unit=mb_strlen($cell_value,$encoding);
		if($char_unit>$column_max_char_units[$column_index]){
			$column_max_char_units[$column_index]=$char_unit;
		}	
	}

}
?>