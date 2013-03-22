<?php
/**
 * 内置的implode函数可以把array的值用一个符号一一隔开输出成字符串
 * 这个增强版可以吧数组输出成几乎任意的字符串
 * 对于array('foo'=>'bar','foo1'=>'bar1')
 * 默认情况下会输出`foo`= 'bar',`foo1`=>'bar1'
 * $glue是分隔符可以使用' AND ',' OR '等
 * $keyname指定情况下，将使用其代替数组中那些key，形成`name`='zhangsan' OR `name`='lisi'这样的字符串
 * $value_type为key时，将用array中key作为字符串中的后项使用，配合$keyname，可以将
 * array(1=>'on',5=>'on')转化为`id`='1' OR `id`='5'
 * escape_string操作将给特殊字符加上\转义
 */
function db_implode($array, $glue = ',',$keyname=NULL,$equalMark=' = ',$mark_for_v_l="'",$mark_for_v_r="'", $mark_for_k='`',$value_type='value',$db_escape_real_string=true,$treat_special_type=true) {
	if($equalMark=='='){
		$equalMark=' = ';
	}
	if(!is_null($keyname)){
		$keyname_array=explode('.',$keyname);
		$keyname=$glue_keyname='';
		foreach($keyname_array as $k=>$v){
			$keyname.=$glue_keyname.$mark_for_k.$v.$mark_for_k;
			$glue_keyname='.';
		}
	}
	$sql = $comma = '';
	foreach ((array)$array as $k => $v) {
		$mark_for_v_l_t=$mark_for_v_l;
		$mark_for_v_r_t=$mark_for_v_r;
		
		$value=$value_type=='value'?$v:$k;
		
		if($treat_special_type && strlen($value)>2 && substr($value,0,1)=='_' && substr($value,-1)=='_'){
			//值被_包围_并不是字符串，而是sql代码，不包围值引号，如NULL
			$mark_for_v_l_t=$mark_for_v_r_t='';
			$value=substr($value,1,-1);
		}elseif($treat_special_type && strlen($value)>2 && substr($value,0,1)=='#' && substr($value,-1)=='#'){
			//值被#包围#代表值是字段，包围字段号而不是值引号
			$mark_for_v_l_t=$mark_for_k;
			$mark_for_v_r_t=$mark_for_k;
			$value=substr($value,1,-1);
		}elseif($db_escape_real_string){
			$value=mysql_real_escape_string($value);
		}
		
		$sql.=$comma.
			(is_null($keyname)?$mark_for_k.$k.$mark_for_k:$keyname).
			$equalMark.
			$mark_for_v_l_t.$value.$mark_for_v_r_t;
		$comma = $glue;
	}
	return $sql;
}

/* 
 * 将student.name转换成`student`.`name`
 */
function db_field_name($fieldNameStr){
	if(!preg_match('/\./',$fieldNameStr)){
		return '`'.$fieldNameStr.'`';
	}elseif(substr_count($fieldNameStr,'.')>1){
		return false;
	}else{
		preg_match('/(.*)\.(.*)/',$fieldNameStr,$match);
		return '`'.$match[1].'`.`'.$match[2].'`';
	}
}
?>
