<?php
/*
 * 包围，生成html标签的时候很有用
 * $surround=array(
 * 		'mark'=>'div',
 * 		'attrib1'=>'value1',
 * 		'attrib2'=>'value2'
 * );
 * 将生成<div attrib1="value1" attrib2="value2">$str</div>
 */
function wrap($str,$surround){
	if($str=='')
		return '';

	$mark=$surround['mark'];
	unset($surround['mark']);
	$property=db_implode($surround,' ',NULL,'=','"','"','','value',false);
	return '<'.$mark.' '.$property.'>'.$str.'</'.$mark.'>';

}
?>