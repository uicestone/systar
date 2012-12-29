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
		show_error("调用post方法时，必须定义当前控制器对应model的id属性，如\$this->student->id	File:$file Line:$line ——uice");
	}
	
	if(count($args)==1){
		return array_dir('_SESSION/'.CONTROLLER.'/post/'.$CI->$controller->id.'/'.$arrayindex);
	}elseif(count($args)==2){
		return array_dir('_SESSION/'.CONTROLLER.'/post/'.$CI->$controller->id.'/'.$arrayindex,$args[1]);
	}
	
}

/*数据库操作封装*/

function db_query($query,$show_error=true){
	$CI=&get_instance();
	global $CFG;
	$execution_start_time=microtime(true);
	$result=mysql_query($query,DB_LINK);
	$CFG->set_item('db_execute_time',$CFG->item('db_execute_time')+(microtime(true)-$execution_start_time));
	$CFG->set_item('db_executions',$CFG->item('db_executions')+1);
	if($CFG->item('debug_mode') && (microtime(true)-$execution_start_time)>0.2){
		showMessage((microtime(true)-$execution_start_time).' - '.$query);
	}
	
	$error='';
	if($error=mysql_error(DB_LINK)){
		if($show_error){
			if($CI->load->require_head){
				showMessage(db_parseError($error),'warning');
				if($CFG->item('debug_mode')){
					showMessage('发生错误的sql语句：'.$query,'warning');
				}
			}else{
				echo 'MySQL error: '.$error."\n".$query;
			}
		}
		return false;
	}else{
		return $result;
	}
}

/**
 * 接受一个SELECT类query返回的result对象
 * 返回结果集行数
 */
function db_rows($result){
	return mysql_num_rows($result);
}

/**
 * 返回上一条query影响的行数
 */
function db_affected_rows(){
	return mysql_affected_rows(DB_LINK);
}

/**
 * 返回上一条insert语句插入的行id
 */
function db_insert_id(){
	return mysql_insert_id(DB_LINK);
}

/**
 * 从SELECT类query返回的result对象中抓去一行，返回成数组，并将result的指针向下移动一行
 */
function db_fetch_array($result,$num_as_line_key=false){
	$array=mysql_fetch_array($result,$num_as_line_key?MYSQL_NUM:MYSQL_ASSOC);
	foreach((array)$array as $key => $value){
		if(!isset($array[$key])){
			unset($array[$key]);
		}
	}
	return $array;
}

/**
 * 直接抓去sql语句执行结果的第一行的field_name字段的值
 */
function db_fetch_field($q,$field_name=0){
	$result=db_query($q);
	
	if($result===false){
		return false;
	}
	
	if(db_affected_rows()==0){
		return NULL;
	}
	
	return mysql_result($result,0,$field_name);
}

/**
 * 执行sql语句并返回第一行
 * $strict: true: 没有数据时警告并中止程序
 */
function db_fetch_first($query,$strict=false){
	$result=db_query($query,true);
	
	if($result===false){
		return false;
	}
	
	$array=db_fetch_array($result);
	
	if($strict && empty($array)){
		showMessage('数据不存在或没有权限','warning');
		exit;
	}
	
	return $array;
}

/**
 * 将一个数组的key，value对应数据表中的字段名和字段值插入
 * 如果replace==true 将执行REPLACE语句，这会首先删除具有重复唯一键的行
 */
function db_insert($table,$data,$return_insert_id=true,$replace=false,$treat_special_type=false) {
	
	$data=db_implode($data,',',NULL,'=',"'","'",'`','value',true,$treat_special_type);

	$cmd=$replace ? 'REPLACE INTO' : 'INSERT INTO';
	
	$query=$cmd.' `'.$table.'` SET '.$data;
	
	$result=db_query($query);
	
	if($result===false){
		return false;
	}
	
	return $return_insert_id ? db_insert_id() : true;
}

function db_multiinsert($table,array $data){
	if(empty($data)){
		return false;
	}

	//测试第一行的列名，存为$field
	$field=array();
	foreach($data as $firstline){
		foreach($firstline as $key => $value){
			$field[]=$key;
		}
		break;
	}
	$sql_field='(`'.implode($field,'`,`').'`)';

	foreach($data as $line){
		foreach($line as $key => $value){
			if(!in_array($key,$field)){
				exit('multiinsert data input error');
			}
			$line[$key]=mysql_real_escape_string($value);
		}
		$sql_dataline[]="('".implode($line,"','")."')";
	}
	$sql_data=implode($sql_dataline,',');
	
	$query="INSERT INTO `".$table."`".$sql_field." VALUES ".$sql_data;
	
	$result=db_query($query);
	
	if($result===false){
		return false;
	}
}

/**
 * 将一个数组的key，value对应数据表中的字段名和字段值更新，务必定义condition
 * $treat_spacial_type是一个神奇的识别，开启后，如果遇上value为'_foo_'的值，将作为sql语句运行
 * 如array('_score+1_','id=1')将被处理为"UPDATE `table` SET `score`=score+1 WHERE id=1"
 */
function db_update($table,$data,$condition,$treat_special_type=true){
	
	if(!$condition){
		showMessage('未指定条件，也许应该先勾上几项，再点击按钮','warning');
		return false;
	}

	$data=db_implode($data,',',NULL,'=',"'","'",'`','value',true,$treat_special_type);

	$cmd='UPDATE';

	$query=$cmd." `".$table."` SET ".$data." WHERE ".$condition;
	
	//showMessage($query);

	$result=db_query($query);
	
	if($result===false){
		return false;
	}
	
	return true;
}

function db_delete($table, $condition) {

	if(!$condition){
		showMessage('未指定条件，也许应该先勾上几项，再点击按钮','warning');
		return false;
	}

	$cmd = 'DELETE FROM';
	
	$query=$cmd." `".$table."` WHERE ".$condition;

	$result=db_query($query);
	
	if($result===false){
		return false;
	}
	
	return true;
}

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
 * 运行一个sql语句并直接将所有结果返回为数组。
 * 第一层为行号＝>行内容($id_field_as_key==true时，行号＝行内id字段值)
 * 第二层为字段名=>值
 */
function db_toArray($query,$id_field_as_key=false,$num_as_line_key=false){
	$result=db_query($query);
	
	if($result===false){
		return false;
	}
	
	$array=array();
	while($a=db_fetch_array($result,$num_as_line_key)){
		if($id_field_as_key && isset($a['id'])){
			$array[$a['id']]=$a;
		}else{
			$array[]=$a;
		}
	}
	return $array;
}

/**
 * 返回某个字段定义中的ENUM选项
 */
function db_enumArray($table,$field){
	$q="SHOW columns FROM `$table` LIKE '$field'";
	$columns=db_fetch_first($q);

	$enum=$columns['Type'];
	$enum=str_replace("'",'',$enum);
	$enum_arr=explode("(",$enum);
	$enum=$enum_arr[1];
	$enum_arr=explode(")",$enum);
	$enum=$enum_arr[0];
	$enum_arr=explode(",",$enum);

	return $enum_arr;
}

function db_list_fields($table_name){
	global $db;
	$fields = mysql_list_fields($db['name'], $table_name, DB_LINK);
	$columns = mysql_num_fields($fields);
	
	$table_field=array();
	
	for ($i = 0; $i < $columns; $i++) {
	    $table_field[]=mysql_field_name($fields, $i);
	}
	
	return $table_field;
}

function db_parseError($error){
	global $CFG;
	
	if(preg_match('/^Cannot delete or update a parent row: a foreign key constraint fails \((.*?),.*$/',$error,$match)){
		$error='无法删除，已在'.$match[1].'中引用';	

	}elseif(preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/",$error,$match)){
		$error='重复项 '.$match[1].' ('.$match[2].')';

	}elseif(preg_match("/^Incorrect .*? value: '(.*?)' for column '(.*?)'/",$error,$match)){
		if($match[1]==''){
			$match[1]='空';
		}
		$error=$match[2].'不能为'.$match[1];

	}elseif(!$CFG->item('debug_mode')){
		$error='数据库出错，本次出错已被系统记录，感谢您的使用，给您带来的不便请谅解';
	}
	
	return $error;
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
		'ignoreFolders' => explode(',','_doc,system,class,third_party,redmond,fullcalendar,Jeditable,jHtmlArea,qtip2,highcharts'),
		'ignoreFiles' => explode(',','jquery-ui.js,jquery.js'),
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