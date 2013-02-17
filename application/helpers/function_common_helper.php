<?php
/**
 * 保存控制单元相关配置时候用，比如列表页的页码，搜索的关键词等
 */
function option($arrayindex,$set_to=NULL){
	if(is_null($set_to)){
		return array_dir('_SESSION/'.CONTROLLER.'/'.METHOD.'/'.$arrayindex);

	}else{
		return array_dir('_SESSION/'.CONTROLLER.'/'.METHOD.'/'.$arrayindex,$set_to);
	}
}

/**
 * 为了便于读取，和恢复失败的表单提交（如非法值）
 * 每一个controller/*_add.php文件对于表单的处理，都是将$_POST先保存到$_SESSION[当前控制器名]['post']下
 * 因此无论作为显示，还是编辑，还是编辑失败时保留原来提交的数据，都可以直接用post('array/path')来获得返回值
 * 
 * 接受1-2个参数，第一个是要读取的值离开$_SESSION/控制器/post的路径名
 * 第二个如果定义了，则是把这个值赋与上述路径那个变量
 */
function post($arrayindex){
	$args=func_get_args();
	
	$CI=&get_instance();
	
	$controller=CONTROLLER;
	
	if(is_null($CI->$controller->id)){
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file']; $line = $backtrace[0]['line'];
		return;
	}
	
	if(count($args)==1){
		return array_dir('_SESSION/'.CONTROLLER.'/post/'.$CI->$controller->id.'/'.$arrayindex);
	}elseif(count($args)==2){
		return array_dir('_SESSION/'.CONTROLLER.'/post/'.$CI->$controller->id.'/'.$arrayindex,$args[1]);
	}
	
}

/*数据库操作封装*/

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

/**
 * 标准差
 */
function std($data,$avg=null,$is_swatch=false){
	if(is_null($avg))
		$avg=array_sum($data)/count($data);

	$arrayCount=count($data);
	if($arrayCount==1 && $is_swatch)
		return false;
	elseif($arrayCount>0){
		$total_var=0;
		foreach($data as $lv)
			$total_var+=pow(($lv-$avg),2);
		if($arrayCount==1 && $is_swatch)
			return false;
		return $is_swatch?($total_var/(count($data)-1)):($total_var/count($data));
	}else
		return false;
}

function codeLines(){
	$dir='../';
	$src = APPPATH.'third_party/line-counter/';
	require $src . 'Folder.php';
	require $src . 'File.php';
	require $src . 'Option.php';
	require $src . 'Html.php';
	
	//Use GET so this script could be reused elsewhere
	//Set to user defined options or default one
	$options = array(
		'ignoreFolders' => explode(',','_doc,system,temp,config,errors,third_party,redmond,jQuery,api'),
		'ignoreFiles' => explode(',','jquery-ui.js,jquery.js,lunar.php'),
		'extensions' => explode(',','php,js,css')
	);
	
	//Scan user defined directory
	$folder = new Folder($dir, new Option($options));
	$folder->init();
	
	$lines = $folder->getLines();
	$whitespace = $folder->getWhitespace();
	$comments = $folder->getComments();
	
	return $lines.' lines';
}
?>