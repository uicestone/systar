<?php
/**
 * 用一个对象来存储要传给视图的数据
 */
class ViewData{
	var $data=array();

	function get($param=NULL){
		if(isset($param)){
			return $this->data[$param];
		}else{
			return $this->data;
		}
	}
	
	function add($name,$value){
		$this->data+=array($name=>$value);
	}
	
	function addArray(array $array){
		$this->data+=$array;
	}
}
?>