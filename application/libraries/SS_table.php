<?php
class SS_Table extends CI_Table{
	
	protected $fields;//表格每列的输出方式
	protected $data;//表格的原始数据
	protected $menu;//表格头尾的菜单
	protected $wrap_form;//表格是否包围form标签
	protected $wrap_box;//表格是否包围div class="contentTableBox"标签，若是，表格位置将为absolute
	protected $attributes;//表格、box和首位菜单的html属性
	protected $show_line_id;//是否在表格第一列显示行号
	protected $trim_columns;//是否清空空列
	
	function __construct(){
		parent::__construct();
		$this->fields=$this->data=NULL;
		$this->menu=array(
			'head'=>NULL,
			'foot'=>NULL
		);
		$this->wrap_form=false;
		$this->wrap_box=NULL;
		$this->attributes=array();
		$this->show_line_id=false;
		$this->trim_columns=false;
		
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
		$field:输出表的列定义
			array(
				'查询结果的列名'=>array(
						'title'=>'列的显示标题'
						'wrap_title'=>array(
								'mark'=>'标签名，如 a',
								'标签的属性名如href'=>'标签的值如http://www.google.com',
							)标题单元格文字需要嵌套的HTML标签
						'wrap'=>同上
						'td_title'=>HTML String	该列标题单元格的html属性字符串
						'td'=>HTML String 该列所有内容单元格的html属性字符串
						'eval'=>false，'是否'将content作为源代码运行
						'content'=>'显示的内容，可以用如{client}来显示变量，{client}是数据库查询结果的字段名'
					)
			)
	*/
	function setFields(array $fields){
		//对于定义列显示方式的表格，默认包围div class="contentTableBox"
		//适用于完整生成表格的用法
		$this->fields=$fields;
		$heading=array();
		foreach($fields as $field_name=>$field){
			$cell=array();
			$cell['data']=$field['title'];
			$cell['field']=$field_name;
			
			if(isset($field['td_title'])){
				$cell+=$this->_parseAttributesToArray($field['td_title']);
			}
			
			if(isset($field['wrap_title'])){
				$cell['data']=wrap($cell['data'],$field['wrap_title']);
				
			}elseif(!isset($field['orderby']) || $field['orderby']){
				$cell['data']=wrap($cell['data'],array('mark'=>'a','href'=>"javascript:postOrderby('".$field_name."')"));
			}
			
			$heading[]=$cell;
		}
		$this->set_heading($heading);
		return $this;
	}
	
	function wrapBox($wrap_box=true){
		$this->wrap_box=$wrap_box;
		return $this;
	}
	
	function wrapForm($wrap_form=true){
		$this->wrap_form=$wrap_form;
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
	
	function trimColumns(){
		$this->trim_columns=true;
		return $this;
	}
	
	/**
	 * 根据$fields设置，将$this->data数据导入$rows
	 * 如果没有设置$fields，那么将$this->data全部导入$rows
	 */
	function generateData(){
		//如果在输出时尚未指定列显示方式$fields，也没有设置wrap_box，那么不包围div class="contentTableBox"
		//适用于生成表格的简写$this->table->generate($data);
		if(is_null($this->wrap_box)){
			$this->wrap_box=false;
		}
		//echo 'data:'.print_r($this->data,true);
		if(!empty($this->fields)){
			foreach($this->data as $row_data){
				$row=array();
				foreach($this->fields as $field_name => $field){
					$str=isset($field['content']) ? $field['content'] : (isset($row_data[$field_name])?$row_data[$field_name]:NULL);
					$str=variableReplace($str,$row_data);
					if(isset($field['eval']) && $field['eval']){
						$str=eval($str);
					}
					if(isset($field['wrap'])){
						array_walk($field['wrap'],'variableReplaceSelf',$row_data);
						$str=wrap($str,$field['wrap']);
					}
					if(is_null($str)){
						$str='<p></p>';
					}
					$cell=array();
					$cell['data']=$str;
					$cell['field']=$field_name;
					if(isset($field['td'])){
						$cell+=$this->_parseAttributesToArray(variableReplace($field['td'],$row_data));
					}
					$row[]=$cell;
				}
				$this->add_row($row);
			}
			$this->data=NULL;
		}
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
		if(isset($table_data)){
			$this->data=$table_data;
		}
		
		if(is_null($this->wrap_box)){
			$this->wrap_box=!isset($table_data);
		}
		
		$this->generateData();

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
						unset($this->rows[$row_id][$column_id]);
					}
				}
			}
			
			foreach($this->heading as $column_id => $column_title){
				if($column_is_empty[$column_id]){
					unset($this->heading[$column_id]);
				}
			}
		}

		$prepend=$append='';

		if($this->wrap_form){
			$prepend.='<form method="post">'."\n";
		}
		
		if($this->wrap_box){
			$this->setMenu($this->load->view('pagination',array(),true));
		}

		if(isset($this->menu['head'])){
			$prepend.='<div class="contentTableMenu"';

			foreach($this->attributes as $attribute_name => $attribute_value){
				$prepend.=' '.$attribute_name.'="'.$attribute_value.'"';
			}

			$prepend.='>'."\n";
			
			foreach($this->menu['head'] as $menu_div_class=>$menu_data){
				$prepend.='<div class="'.$menu_div_class.'">'.$menu_data.'</div>';
			}
			
			$prepend.='</div>'."\n";
		}

		if($this->wrap_box){
			$prepend.='<div class="contentTableBox">'."\n";
		}
		
		if($this->wrap_box){
			if(isset($this->menu['foot'])){
				$append.='<div class="contentTableFoot"';

				foreach($this->attributes as $attribute_name => $attribute_value){
					$append.=' '.$attribute_name.'="'.$attribute_value.'"';
				}

				$append.='>'."\n";

				foreach($this->menu['foot'] as $menu_div_class=>$menu_data){
					$append.='<div class="'.$menu_div_class.'">'.$menu_data.'</div>';
				}

				$append.='</div>'."\n";
			}

			$append.='</div>'."\n";
		}
		
		if($this->wrap_form){
			$append.='</form>'."\n";
		}

		$this->template['table_open']='<table class="contentTable" cellpadding="0" cellspacing="0">';
		$this->template['row_alt_start']='<tr class="oddLine">';

		$table=parent::generate($this->data);

		return $prepend.$table.$append;
	}

	// --------------------------------------------------------------------

	/**
	 * Clears the table arrays.  Useful if multiple tables are being generated
	 *
	 * @access	public
	 * @return	void
	 */
	function clear()
	{
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
	function generateExcel(){
		$this->generateData();

		require_once(APPPATH.'third_party/PHPExcel/PHPExcel.php');
		//创建EXCEL对象，并获取当前的工作表
		$php_excel=new PHPExcel();
		$current_sheet=$php_excel->getActiveSheet();

		//列最大字符单元数，用来存储每一列的最大字符单元
		$column_max_char_units=array_fill(0,count($this->heading),0);
		
		//对excel对象的每一行每一列写入相应的数据
		for($row=1;$row<=count($this->rows)+1;$row++){
			//第一行写列名
			if($row==1){
				foreach($this->heading as $column_index=>$column_array){
					$cell_value=strip_tags($column_array['data']);
					$this->compareAndSetColumnMaxCharUnit($column_max_char_units,$column_index,$cell_value);
					$current_sheet->setCellValueByColumnAndRow($column_index,$row,$cell_value);
				}
			}
			//其他行写该行每一列的数据
			else{
				$index_in_rows=$row-2;
				foreach($this->rows[$index_in_rows] as $column_index=>$cell){
					$cell_value=strip_tags($cell['data']);
					$this->compareAndSetColumnMaxCharUnit($column_max_char_units,$column_index,$cell_value);
					$current_sheet->setCellValueByColumnAndRow($column_index,$row,$cell_value);
				}		
			}
		}
		//根据最大字符单元和列宽因子计算列宽
		$widen_factor=2.5;
		foreach($column_max_char_units as $column_index=>$max_char_unit){
			$column_width=$max_char_unit*$widen_factor;
			$current_sheet->getColumnDimensionByColumn($column_index)->setWidth($column_width);
		}

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
	private function compareAndSetColumnMaxCharUnit(&$column_max_char_units,$column_index,$cell_value){
		$encoding=$this->config->item('charset');
		$char_unit=mb_strlen($cell_value,$encoding);
		if($char_unit>$column_max_char_units[$column_index]){
			$column_max_char_units[$column_index]=$char_unit;
		}	
	}

}
?>