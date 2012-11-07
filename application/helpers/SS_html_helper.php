<?php
/*
 * 包围，生成html标签的时候很有用
 * $wrap=array(
 * 		'mark'=>'div',
 * 		'attrib1'=>'value1',
 * 		'attrib2'=>'value2'
 * );
 * 将生成<div attrib1="value1" attrib2="value2">$str</div>
 */
function wrap($str,$wrap){
	if($str=='')
		return '';

	$mark=$wrap['mark'];
	unset($wrap['mark']);
	$property=db_implode($wrap,' ',NULL,'=','"','"','','value',false);
	return '<'.$mark.' '.$property.'>'.$str.'</'.$mark.'>';

}
?>