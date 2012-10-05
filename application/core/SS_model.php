<?php
class SS_Model extends CI_Model{
	function __construct(){
		parent::__construct();
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
	function fetchTableArray($query,$field){
		//if($_SESSION['username']=='陆秋石')showMessage($query,'notice');

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
	function variableReplace($content,$data){
		while(preg_match('/{(\S*?)}/',$content,$match)){
			if(!isset($data[$match[1]])){
				$data[$match[1]]=NULL;
			}
			$content=str_replace($match[0],$data[$match[1]],$content);
		}
		return $content;
	}

	function variableReplaceSelf(&$content,$key,$data){
		$content=$this->variableReplace($content,$data);
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