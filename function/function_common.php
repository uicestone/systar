<?php
function model($model_name){
	if(is_file('model/'.$model_name.'.php')){
		require 'model/'.$model_name.'.php';
	}
}

function javascript($js_file_path){
	$path='js/'.$js_file_path.'.js';
	$hash=filemtime('web/'.$path);
	echo '<script type="text/javascript" src="'.$path.'?'.$hash.'"></script>'."\n";
}

function stylesheet($stylesheet_path){
	$path=$stylesheet_path.'.css';
	$hash=filemtime('web/'.$path);
	echo '<link rel="stylesheet" href="'.$path.'?'.$hash.'" type="text/css" />'."\n";
}

function template($filename){
	$content='';
	if(is_file($filename)){
		$content=file_get_contents('view/'.$filename.'.htm');
	}

	$content=preg_replace('/{post (.*?)}/e',"post('$1')",$content);
	//将'{post string}'替换为post(string)的返回值
	
	return $content;
}

function got($variable,$value=NULL){
	if(is_null($value))
		return isset($_GET[$variable])?true:false;
	if(!is_null($value))
		return (isset($_GET[$variable]) && $_GET[$variable]==$value)?true:false;
}

function is_posted($variableString,$value=NULL){
	if(is_null($value))
		return is_null(array_dir('_POST/'.$variableString))?false:true;
	if(!is_null($value))
		return (!is_null(array_dir('_POST/'.$variableString)) && array_dir('_POST/'.$variableString)==$value)?true:false;
}

function sessioned($variable,$value=NULL,$global=true){
	if($global){
		if(is_null($value)){
			return isset($_SESSION[$variable])?true:false;
		}elseif(isset($_SESSION[$variable]) && $_SESSION[$variable]==$value)
			return true;
		else
			return false;
	}else{
		if(is_null($value)){
			return isset($_SESSION[IN_UICE][$variable])?true:false;
		}elseif(isset($_SESSION[IN_UICE][$variable]) && $_SESSION[IN_UICE][$variable]==$value)
			return true;
		else
			return false;
	}
}

function optioned($variable,$value=NULL){
	if(is_null($value)){
		return isset($_SESSION[IN_UICE]['option'][$variable])?true:false;
	}elseif(isset($_SESSION[IN_UICE]['option'][$variable]) && $_SESSION[IN_UICE]['option'][$variable]==$value){
		return true;
	}else{
		return false;
	}
}

function option($arrayindex,$set_to=NULL){
	global $_G;
	if(is_null($set_to)){
		return array_dir('_SESSION/'.IN_UICE.'/'.$_G['action'].'/'.$arrayindex);

	}else{
		return array_dir('_SESSION/'.IN_UICE.'/'.$_G['action'].'/'.$arrayindex,$set_to);
	}
}

function is_serialized($string){
	if(@unserialize($string)){
		return true;
	}else{
		return false;
	}
}

function isMobileNumber($number){
	if(is_numeric($number) && $number%1==0 && substr($number,0,1)=='1' && strlen($number)==11){
		return true;
	}else{
		return false;
	}
}

function session_login($uid=NULL,$username=NULL){
	if(!is_null($uid)){
		$q_user="SELECT user.id,user.`group`,user.username,staff.position FROM user INNER JOIN staff ON user.id=staff.id WHERE user.id='".$uid."'";

	}elseif(!is_null($username)){
		$q_user="SELECT user.id,user.`group`,user.username,staff.position FROM user INNER JOIN staff ON user.id=staff.id WHERE user.username='".$username."'";
	}
	$r_user=db_query($q_user);

	if($user=mysql_fetch_array($r_user)){
		$_SESSION['id']=$user['id'];
		$_SESSION['usergroup']=explode(',',$user['group']);
		$_SESSION['username']=$user['username'];
		$_SESSION['position']=$user['position'];
		return true;
	}
	return false;
}

function session_logout(){
	global $_G;
	session_unset();
	session_destroy();
	
	if($_G['ucenter']){
		//生成同步退出代码
		echo uc_user_synlogout();
	}
}

function is_logged($checkType=NULL,$refresh_permission=false){
	global $_G;
	if(is_null($checkType)){
		if(!isset($_SESSION['usergroup'])){
			return false;
		}
	}elseif(!isset($_SESSION['usergroup']) || !in_array($checkType,$_SESSION['usergroup'])){
		return false;
	}

	if($refresh_permission){
		preparePermission();
		if($_G['ucenter']){
			$_SESSION['new_messages']=uc_pm_checknew($_SESSION['id']);
		}
	}

	return true;
}

function preparePermission(){
	//准备权限参数，写入session
	
	global $_G;
	
	$q_affair="
		SELECT
			affair.name AS affair,
			IF(group.affair_ui_name<>'', group.affair_ui_name, affair.ui_name) AS affair_name,
			affair.add_action,affair.add_target,
			`group`.action AS `action`, group.display_in_nav AS display
		FROM affair LEFT JOIN `group` ON affair.name=`group`.affair 
		WHERE group.company='".$_G['company']."'
			AND affair.is_on=1
			AND (".db_implode($_SESSION['usergroup'], $glue = ' OR ',$keyname='group.name').") 
		GROUP BY affair,action
		ORDER BY affair.order,group.order
	";

	$r_affair=db_query($q_affair);
	
	$_SESSION['permission']=array();
	while($a=mysql_fetch_array($r_affair)){
		if(!isset($_SESSION['permission'][$a['affair']])){
			$_SESSION['permission'][$a['affair']]=array();
		}
		if($a['action']==''){
			//一级菜单
			$_SESSION['permission'][$a['affair']]
			=array_replace_recursive($_SESSION['permission'][$a['affair']],array('_affair_name'=>$a['affair_name'],'_add_action'=>$a['add_action'],'_add_target'=>$a['add_target'],'_display'=>$a['display']));
		}else{
			//二级菜单
			$_SESSION['permission'][$a['affair']][$a['action']]=array('_affair_name'=>$a['affair_name'],'_display'=>$a['display']);
		}
	}
}

function is_permitted($controller,$action=NULL){
	if(isset($_SESSION['permission'][$controller])){
		if(is_null($action)){
			return true;
		}else{
			return isset($_SESSION['permission'][$controller][$action])?true:false;
		}
	}else{
		return false;
	}
}

function sendMessage($receiver,$message,$title='',$sender=NULL){
	global $_G;
	if(is_null($sender)){
		$sender=$_SESSION['id'];
	}
	if($_G['ucenter']){
		uc_pm_send($sender,$receiver,$title,$message);
	}
}

function getIP(){
	if(isset($_SERVER['HTTP_CLIENT_IP'])){
		 return $_SERVER['HTTP_CLIENT_IP'];
	}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		 return $_SERVER['REMOTE_ADDR'];
	}
}

function redirect($url,$method='php',$unsetPara=NULL,$jump_to_top_frame=false){
	if($method=='php'){
		if(is_null($unsetPara)){
			header("location:".$url);
		}else{
			$query_string='?';
			$glue='';
			foreach($_GET as $k=>$v){
				if($k!=$unsetPara){
					$query_string.=$glue.$k.'='.$v;
					$glue='&';
				}
			}
			header('location:'.$q);//待开发
		}
	}elseif($method=='js'){
		echo '<script>'.(is_null($unsetPara)?($jump_to_top_frame?'top.':'')."location.href='".$url."';":"location.href=unsetURLPar('".$url."','".$unsetPara."');").'</script>';
	}
	exit;
}

function refreshParentContentFrame(){
	echo '<script type="text/javascript">window.rootOpener.parent.contentFrame.location.reload();</script>';
}

function closeWindow(){
	echo '<script type="text/javascript">window.close();</script>';
}

function forceExport(){
	ob_end_clean();   //清空并关闭输出缓冲区
	echo str_repeat(' ',1024);
}

function displayPost($fieldName,$strtotime=false,$date_form='Y-m-d'){
	$val=array_dir('_SESSION/'.IN_UICE.'/post/'.$fieldName);
	if($strtotime && $val){
		$val=date($date_form,$val);
	}
	echo $val;
}

function html_option($options,$checked=NULL,$array_key_as_option_value=false,$type_table='type',$classification='classification',$type='type',$condition=NULL){
	/*	输出一组<option>
	 *	$options为给出的选项数组
	 *  $options[0]=='_ENUM'时，根据$options[1]表，$options[2]字段的enum选项来定制选项
	 *	$options是一个值时,尝试从type(可以指定)表中获得classification(可以指定)为$options的type(可以指定)
	 *	指定$affair值时，优先根据$affair获得第一个classfication
	 */
	 $html_option='';
	if(!is_array($options)){
		//$options 作为形成选项的因子
		if(is_null($options) || is_null($checked)){
			$html_option.='<option value="">全部</option>';
		}

		$q_get_option="
			SELECT ".($array_key_as_option_value?'id,':'')." `".$type."` 
			FROM `".$type_table."` 
			WHERE 1=1".
				((is_null($classification) || is_null($options))?'':" AND `".$classification."`='".$options."' ").
				(is_null($condition)?'':' AND '.$condition)
		;
		$options=db_toArray($q_get_option);
		$options=$array_key_as_option_value?array_sub($options,$type,'id'):array_sub($options,$type);

	}elseif(isset($options[0]) && $options[0]=='_ENUM'){
		$options=db_enumArray($options[1],$options[2]);

	}
	
	foreach($options as $option_key=>$option){
		$value=$array_key_as_option_value?$option_key:$option;
		$html_option.='<option value="'.$value.'"'.($value==$checked?' selected="selected"':'').'>'.$option.'</option>';
	}
	
	return $html_option;
}

function displayOption($options,$checked=NULL,$array_key_as_option_value=false,$type_table='type',$classification='classification',$type='type',$condition=NULL){
	echo html_option($options,$checked,$array_key_as_option_value,$type_table,$classification,$type,$condition);
}
function displayRadio($options,$name,$checked,$array_key_as_option_value=false){
	foreach($options as $option_key=>$option){
		echo '<label><input name="'.$name.'" value="'.($array_key_as_option_value?$option_key:$option).'" type="radio"'.($checked==($array_key_as_option_value?$option_key:$option)?' checked="checked"':'').' />'.$option.'</label>';
	}
}

function displayCheckbox($html,$name,$check_value,$value=NULL,$disabled=false){
	if(is_null($value)){
		$value=$html;
	}
	echo '<label><input name="'.$name.'" type="checkbox" value="'.$value.'" '.($check_value==$value?'checked="checked"':'').($disabled?' disabled="disabled"':'').' />'.$html.'</label>';
}

function post($arrayindex){
	$args=func_get_args();
	if(count($args)==1){
		return array_dir('_SESSION/'.IN_UICE.'/post/'.$arrayindex);
	}elseif(count($args)==2){
		return array_dir('_SESSION/'.IN_UICE.'/post/'.$arrayindex,$args[1]);
	}
	
}

function showMessage($message,$type='notice',$direct_export=false){
	if($direct_export){
		echo $message;
	}else{
		if($type=='notice'){
			$notice_class='ui-state-highlight ';
			$notice_symbol='<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>';
		}elseif($type=='warning'){
			$notice_class='ui-state-error';
			$notice_symbol='<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>';
		}
		echo '<span class="message ui-corner-all '.$notice_class.'" title="点击隐藏提示">'.$notice_symbol.$message.'</span>';
	}
}

function str_getSummary($str,$length=28){
	/*
	 * $length，宽度计量的长度，1为一个ASCII字符的宽度，汉字为2
	 * $char_length，字符计量的长度，UTF8的汉字为3
	 */
	$char_length=$length/2*3;
	$str_origin=$str;
	for($i=0,$j=0;$i<$char_length && $j<$length;$i++,$j++){
		$temp_str=substr($str,0,1);
		if(ord($temp_str)>127){//非ASCII
			$i+=2;//补足汉字的字节数
			$j++;//汉字宽度，只要补1即可
			if($i<$char_length && $j<$length){
				$new_str[]=substr($str,0,3);//取出汉字字节数
				$str=substr($str,3);
			}
		}else{
			$new_str[]=substr($str,0,1);
			$str=substr($str,1);
		}
	}
	$new_str=join($new_str);
	if($new_str==$str_origin){
		return $new_str;
	}else{
		return $new_str.'…';
	}
}

function str_textToHtml($str){
	$str_para_array=explode("\n",$str);
	$str_paraed='';
	foreach($str_para_array as $para){
		$str_paraed.='<p>'.$para.'</p>';
	}
	return $str_paraed;
}

function array_trim($array){//递归去除array中的空键
	foreach($array as $k => $v){
		if($v=='' || $v==array()){
			unset($array[$k]);
		}elseif(is_array($v)){
			$array[$k]=array_trim($v);
		}
	}
	return $array;
}

function array_numkey_to_strkey($array){
	foreach($array as $k=>$v){
		if(is_numeric($k)){
			unset($array[$k]);
		}
	}
}

function array_dir($arrayindex){
/*
	用array_dir('/_SESSION/post/id')来代替$_SESSION['post']['id']
	**仅适用于全局变量如$_SESSION,$_POST
	用is_null(array_dir(String $arrayindex))来判断是否存在此变量
	若指定$setto,则会改变$arrayindex的值
*/
	global $_G;
	
	preg_match('/^[^\/]*/',$arrayindex,$match);
	$arraystr=$match[0];
	
	preg_match('/\/.*$/',$arrayindex,$match);
	$indexstr=$match[0];

	$indexstr=str_replace('/',"']['",$indexstr);
	$indexstr=substr($indexstr,2).substr($indexstr,0,2);
	
	$args=func_get_args();
	if(count($args)==1){
		return @eval('return $'.$arraystr.$indexstr.';');
	}elseif(count($args)==2){
		return @eval('return $'.$arraystr.$indexstr.'=$args[1];');
	}
}

if(!function_exists('array_replace_recursive')){
	function array_replace_recursive(&$array_target,$array_source){
	
		if(!isset($array_target)){
			$array_target=$array_source;
		}else{
			foreach($array_source as $k=>$v){
				if(is_array($v)){
					array_replace_recursive($array_target[$k],$v);
				}else{
					$array_target[$k]=$v;
				}
			}
		}
		return $array_target;
	}
}
/*
 * 将数组的下级数组中的某一key抽出来构成一个新数组
 * $keyname_forkey是母数组中用来作为子数组键名的键值的键名
 */
function array_sub($array,$keyname,$keyname_forkey=NULL){
	$array_new=array();
	foreach($array as $key => $sub_array){
		if(isset($sub_array[$keyname])){
			if(is_null($keyname_forkey)){
				$array_new[$key]=$sub_array[$keyname];
			}else{
				$array_new[$sub_array[$keyname_forkey]]=$sub_array[$keyname];
			}
		}
	}
	return $array_new;
}

function array_keyfilter($array,$legalkeys){
    foreach($array as $key => $value){
        if(!in_array($key,$legalkeys)){
            unset($array[$key]);
        }
    }
	return $array;
}

function in_subarray($needle,array $array,$key_specified=NULL){
	foreach($array as $key => $subarray){
		if(isset($key_specified)){
			if(is_array($subarray) && isset($subarray[$key_specified]) && $subarray[$key_specified]==$needle){
				return $key;
			}
		}else{
			if(in_array($needle,$subarray)){
				return $key;
			}
		}
	}
	return false;
}

function db_query($query,$show_error=true){
	global $db_link,$_G;
	$execution_start_time=microtime(true);
	$result=mysql_query($query,$db_link);
	$_G['db_execute_time']+=(microtime(true)-$execution_start_time);
	$_G['db_executions']++;
	//showMessage($query);
	//showMessage(microtime(true)-$execution_start_time);
	
	$error='';
	if($error=mysql_error($db_link)){
		if($show_error){
			global $_G;
			if($_G['require_export']){
				showMessage(db_parseError($error),'warning');
				if($_G['debug_mode']){
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

function db_rows($result){
	return mysql_num_rows($result);
}

function db_affected_rows(){
	global $db_link;
	return mysql_affected_rows($db_link);
}

function db_insert_id(){
	global $db_link;
	return mysql_insert_id($db_link);
}

function db_fetch_array($result){
	$array=mysql_fetch_array($result,MYSQL_ASSOC);
	foreach((array)$array as $key => $value){
		if(!isset($array[$key])){
			unset($array[$key]);
		}
	}
	return $array;
}

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

/*
 * $strict: true: 没有数据时警告并中止程序
 */
function db_fetch_first($query,$strict=false){
	$result=db_query($query);
	
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

function db_implode($array, $glue = ',',$keyname=NULL,$equalMark='=',$mark_for_v_l="'",$mark_for_v_r="'", $mark_for_k='`',$value_type='value',$db_escape_real_string=true,$treat_special_type=true) {
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

function db_field_name($fieldNameStr){
	/* 
	 * 将student.name转换成`student`.`name`
	 */
	if(!preg_match('/\./',$fieldNameStr)){
		return '`'.$fieldNameStr.'`';
	}elseif(substr_count($fieldNameStr,'.')>1){
		return false;
	}else{
		preg_match('/(.*)\.(.*)/',$fieldNameStr,$match);
		return '`'.$match[1].'`.`'.$match[2].'`';
	}
}

function db_toArray($query){
	$result=db_query($query);
	
	if($result===false){
		return false;
	}
	
	$array=array();
	while($a=db_fetch_array($result)){
		$array[]=$a;
	}
	return $array;
}

function db_enumArray($table,$field){
	$q="SHOW columns FROM `".$table."` LIKE '".$field."'";
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

function db_parseError($error){
	global $_G;
	
	if(preg_match('/^Cannot delete or update a parent row: a foreign key constraint fails \((.*?),.*$/',$error,$match)){
		$error='无法删除，已在'.$match[1].'中引用';	

	}elseif(preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/",$error,$match)){
		$error='重复项 '.$match[1].' ('.$match[2].')';

	}elseif(preg_match("/^Incorrect .*? value: '(.*?)' for column '(.*?)'/",$error,$match)){
		if($match[1]==''){
			$match[1]='空';
		}
		$error=$match[2].'不能为'.$match[1];

	}elseif(!$_G['debug_mode']){
		$error='数据库出错，本次出错已被系统记录，感谢您的使用，给您带来的不便请谅解';
	}
	
	return $error;
}

function status($affair,$data_id,$field,$old_value,$new_value){
	$data=compact($affair,$data_id,$field,$old_value,$new_value);
	$data+=uidTime();
	db_insert('status',$data);
}

function codeLines(){
	$dir='.';
	$src = 'plugin/line-counter/';
	require $src . 'Folder.php';
	require $src . 'File.php';
	require $src . 'Option.php';
	require $src . 'Html.php';
	
	//Use GET so this script could be reused elsewhere
	//Set to user defined options or default one
	$options = array(
		'ignoreFolders' => explode(',','_notes,.documents,class,plugin,redmond,fullcalendar,Jeditable,jHtmlArea,qtip2,highcharts'),
		'ignoreFiles' => explode(',','jquery-ui.js,jquery.js'),
		'extensions' => explode(',','php,js,css,htm')
	);
	
	//Scan user defined directory
	$folder = new Folder($dir, new Option($options));
	$folder->init();
	
	$lines = $folder->getLines();
	$whitespace = $folder->getWhitespace();
	$comments = $folder->getComments();
	
	return $lines.' lines';
}

/*计算标准差*/
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
?>