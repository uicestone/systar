<?php
function company_fetchInfo(){
	$company_info=db_fetch_first("SELECT id AS company,name AS company_name,type AS company_type,syscode,sysname,ucenter,default_controller FROM company WHERE host='".$_SERVER['SERVER_NAME']."' OR syscode='{$_SERVER['SERVER_NAME']}'");
	if(is_array($company_info)){
		return $company_info;
	}else{
		return false;
	}
}

function uidTime(){
	$CI=&get_instance();
	$array=array(
		'uid'=>$_SESSION['id'],
		'username'=>$_SESSION['username'],
		'time'=>$CI->config->item('timestamp'),
		'company'=>$CI->config->item('company')
	);
	return $array;
}	

/*
 * 载入model的简写
 */
function model($model_name){
	if(is_file('model/'.$model_name.'.php')){
		require 'model/'.$model_name.'.php';
	}
}

/*
 * 在view中载入js的简写
 */
function javascript($js_file_path){
	$path='js/'.$js_file_path.'.js';
	$hash=filemtime($path);
	echo '<script type="text/javascript" src="/'.$path.'?'.$hash.'"></script>'."\n";
}

/*
 * 在view中载入外部css链接的简写
 */
function stylesheet($stylesheet_path){
	$path=$stylesheet_path.'.css';
	$hash=filemtime($path);
	echo '<link rel="stylesheet" href="/'.$path.'?'.$hash.'" type="text/css" />'."\n";
}

/*
 * 试探性地引入免缓存模板。之前的view采用html嵌入<?php ?>方式，比如<input name="client[name]" value="<?=post('client/name') ?>" />
 * 引入末版体系之后，view文件的书写将采用<input name="client[name]" value="{post client/name}" />
 * 慎用模板引擎，会降低运行效率
 */
function template($filename){
	$content='';
	if(is_file($filename)){
		$content=file_get_contents('view/'.$filename.'.htm');
	}

	$content=preg_replace('/{post (.*?)}/e',"post('$1')",$content);
	//将'{post string}'替换为post(string)的返回值
	
	return $content;
}

/*
 * 判断$_GET[$variable]是否定义，或者判断其是否等于$value
 * 比起直接用$this->input->get('foo')=='bar'来判断,got('foo','bar')更便于书写，而且在foo没有定义的时候不会报错
 */
function got($variable,$value=NULL){
	if(is_null($value))
		return isset($_GET[$variable])?true:false;
	if(!is_null($value))
		return (isset($_GET[$variable]) && $_GET[$variable]==$value)?true:false;
}

/*
 * 与got同理
 */
function is_posted($variableString,$value=NULL){
	if(is_null($value))
		return is_null(array_dir('_POST/'.$variableString))?false:true;
	if(!is_null($value))
		return (!is_null(array_dir('_POST/'.$variableString)) && array_dir('_POST/'.$variableString)==$value)?true:false;
}

/*
 * 此函数已弃用
 */
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
			return isset($_SESSION[CONTROLLER][$variable])?true:false;
		}elseif(isset($_SESSION[CONTROLLER][$variable]) && $_SESSION[CONTROLLER][$variable]==$value)
			return true;
		else
			return false;
	}
}

/*
 * 此函数已弃用
 */
function optioned($variable,$value=NULL){
	if(is_null($value)){
		return isset($_SESSION[CONTROLLER]['option'][$variable])?true:false;
	}elseif(isset($_SESSION[CONTROLLER]['option'][$variable]) && $_SESSION[CONTROLLER]['option'][$variable]==$value){
		return true;
	}else{
		return false;
	}
}

/*
 * 保存控制单元相关配置时候用，比如列表页的页码，搜索的关键词等
 */
function option($arrayindex,$set_to=NULL){
	global $_G;
	if(is_null($set_to)){
		return array_dir('_SESSION/'.CONTROLLER.'/'.METHOD.'/'.$arrayindex);

	}else{
		return array_dir('_SESSION/'.CONTROLLER.'/'.METHOD.'/'.$arrayindex,$set_to);
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

/*
 * 根据用户名或uid直接为其设置登录状态
 */
function session_login($uid=NULL,$username=NULL){
	if(isset($uid)){
		$q_user="SELECT user.id,user.`group`,user.username,staff.position FROM user LEFT JOIN staff ON user.id=staff.id WHERE user.id='".$uid."'";

	}elseif(!is_null($username)){
		$q_user="SELECT user.id,user.`group`,user.username,staff.position FROM user LEFT JOIN staff ON user.id=staff.id WHERE user.username='".$username."'";
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

/*
 * 登出当前用户
 */
function session_logout(){
	global $_G;
	session_unset();
	session_destroy();
	
	if($_G['ucenter']){
		//生成同步退出代码
		echo uc_user_synlogout();
	}
}

/*
 * 判断是否以某用户组登录
 * $check_type要检查的用户组,NULL表示只检查是否登录
 * $refresh_permission会刷新用户权限，只需要在每次请求开头刷新即可
 */
function is_logged($check_type=NULL,$refresh_permission=false){
	global $_G;
	if(is_null($check_type)){
		if(!isset($_SESSION['usergroup'])){
			return false;
		}
	}elseif(!isset($_SESSION['usergroup']) || !in_array($check_type,$_SESSION['usergroup'])){
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

/*
 * 根据当前用户组，将数据库中affair,group两表中的用户权限读入$_SESSION['permission']
 */
function preparePermission(){
	//准备权限参数，写入session
	
	global $CFG;
	
	$q_affair="
		SELECT
			affair.name AS affair,
			IF(group.affair_ui_name<>'', group.affair_ui_name, affair.ui_name) AS affair_name,
			affair.add_action,affair.add_target,
			`group`.action AS `action`, group.display_in_nav AS display
		FROM affair LEFT JOIN `group` ON affair.name=`group`.affair 
		WHERE group.company='".$CFG->item('company')."'
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

/*
 * 根据已保存的$_SESSION['permission']判断权限
 * $action未定义时，只验证是否具有访问当前controller的权限
 */
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

/*
 * 调用uc接口发送用户信息
 */
function sendMessage($receiver,$message,$title='',$sender=NULL){
	global $_G;
	if(is_null($sender)){
		$sender=$_SESSION['id'];
	}
	if($_G['ucenter']){
		uc_pm_send($sender,$receiver,$title,$message);
	}
}

/*
 * 直接返回客户端ip
 */
function getIP(){
	if(isset($_SERVER['HTTP_CLIENT_IP'])){
		 return $_SERVER['HTTP_CLIENT_IP'];
	}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		 return $_SERVER['REMOTE_ADDR'];
	}
}

/*
 * 重定向，对于站内跳转，url写成request_uri即可，如'user?browser'
 * 有php和js两种方式
 * 对于php跳转，采用发送301header的方式，因此之前整个系统不能输出任何内容
 * 对于js跳转，输出js代码交给浏览器完成跳转，因此会发生内容输出
 * $unsetPara目前只适用于js跳转，用以将原来url中的某个变量去除
 */
function redirect($url,$method='php',$unsetPara=NULL,$jump_to_top_frame=false){
	$CI=&get_instance();
	$base_url=$CI->config->item('base_url');
	
	if($method=='php'){
		if(is_null($unsetPara)){
			header("location:{$base_url}".$url);
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
		echo '<script>'.(is_null($unsetPara)?($jump_to_top_frame?'top.':'')."location.href='{$base_url}".$url."';":"location.href=unsetURLPar('".$url."','".$unsetPara."');").'</script>';
	}
	exit;
}

/*
 * 刷新opener内容
 * 例：在弹出窗口中点击'保存'按钮时执行，然后紧接着执行closeWindow()可以在关闭子窗口的同时刷新母窗口
 * 注意区分DOM中的parent和opener这两个概念，前者是上层框架，后者是弹出窗口的打开者
 */
function refreshParentContentFrame(){
	$CI=&get_instance();
	$CI->output->append_output('<script type="text/javascript">window.rootOpener.parent.contentFrame.location.reload();</script>');
}

function closeWindow(){
	$CI=&get_instance();
	$CI->output->append_output('<script type="text/javascript">window.close();</script>');
}

/*
 * 输出1K的空格来强制浏览器输出
 * 使用后在下文执行任何输出，再紧跟flush();即可即时看到
 */
function forceExport(){
	ob_end_clean();   //清空并关闭输出缓冲区
	echo str_repeat(' ',1024);
}

/*
 * 用于view视图中，直接将长期当前保存在当前控制器数组下的post数组中的某一项列出
 * 此项一般也是多极数组
 * 与view中input的name配合使用
 * 如<input name="client[name]" value="<?=post('client/name') ?>" />（见view/client_add.htm）
 */
function displayPost($fieldName,$strtotime=false,$date_form='Y-m-d'){
	$val=array_dir('_SESSION/'.CONTROLLER.'/post/'.$fieldName);
	if($strtotime && $val){
		$val=date($date_form,$val);
	}
	echo $val;
}

/*	生成一组<option>
 *	$options为给出的选项数组
 *  $options[0]=='_ENUM'时，根据$options[1]表，$options[2]字段的enum选项来定制选项
 *	$options是一个值时,尝试从type(可以指定)表中获得classification(可以指定)为$options的type(可以指定)
 *	指定$affair值时，优先根据$affair获得第一个classfication
 */
function html_option($options,$checked=NULL,$array_key_as_option_value=false,$type_table='type',$classification='classification',$type='type',$condition=NULL){
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

/*
 * 生成一组个单选框
 */
function displayRadio($options,$name,$checked,$array_key_as_option_value=false){
	foreach($options as $option_key=>$option){
		echo '<label><input name="'.$name.'" value="'.($array_key_as_option_value?$option_key:$option).'" type="radio"'.($checked==($array_key_as_option_value?$option_key:$option)?' checked="checked"':'').' />'.$option.'</label>';
	}
}

/*
 * 生成一个多选框
 */
function displayCheckbox($html,$name,$check_value,$value=NULL,$disabled=false){
	if(is_null($value)){
		$value=$html;
	}
	echo '<label><input name="'.$name.'" type="checkbox" value="'.$value.'" '.($check_value==$value?'checked="checked"':'').($disabled?' disabled="disabled"':'').' />'.$html.'</label>';
}

/*
 * 为了便于读取，和恢复失败的表单提交（如非法值）
 * 每一个controller/*_add.php文件对于表单的处理，都是将$_POST先保存到$_SESSION[当前控制器名]['post']下
 * 因此无论作为显示，还是编辑，还是编辑失败时保留原来提交的数据，都可以直接用post('array/path')来获得返回值
 * 
 * 接受1-2个参数，第一个是要读取的值离开$_SESSION/控制器/post的路径名
 * 第二个如果定义了，则是把这个值赋与上述路径那个变量
 */
function post($arrayindex){
	$args=func_get_args();
	if(count($args)==1){
		return array_dir('_SESSION/'.CONTROLLER.'/post/'.$arrayindex);
	}elseif(count($args)==2){
		return array_dir('_SESSION/'.CONTROLLER.'/post/'.$arrayindex,$args[1]);
	}
	
}

/*
 * 直接在页面输出提示
 * 本系统js下也有一个一样的函数
 */
function showMessage($message,$type='notice',$direct_export=false){
	$output='';
	if($direct_export){
		$output=$message;
	}else{
		if($type=='notice'){
			$notice_class='ui-state-highlight ';
			$notice_symbol='<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>';
		}elseif($type=='warning'){
			$notice_class='ui-state-error';
			$notice_symbol='<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>';
		}
		$output='<span class="message ui-corner-all '.$notice_class.'" title="点击隐藏提示">'.$notice_symbol.$message.'</span>';
	}
	$CI=&get_instance();
	$CI->output->append_output($output);
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

/*
 * 此函数已弃用
 */
function str_textToHtml($str){
	$str_para_array=explode("\n",$str);
	$str_paraed='';
	foreach($str_para_array as $para){
		$str_paraed.='<p>'.$para.'</p>';
	}
	return $str_paraed;
}

/*
 * 递归去除array中的空键
 */
function array_trim($array){
	$array=(array)$array;
	foreach($array as $k => $v){
		if($v=='' || $v==array()){
			unset($array[$k]);
		}elseif(is_array($v)){
			$array[$k]=array_trim($v);
		}
	}
	return $array;
}

/*
 * 此函数已弃用
 */
function array_numkey_to_strkey($array){
	foreach($array as $k=>$v){
		if(is_numeric($k)){
			unset($array[$k]);
		}
	}
}

/*
	用array_dir('/_SESSION/post/id')来代替$_SESSION['post']['id']
	**仅适用于全局变量如$_SESSION,$_POST
	用is_null(array_dir(String $arrayindex))来判断是否存在此变量
	若指定第二个参数$setto,则会改变$arrayindex的值
*/
function array_dir($arrayindex){
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

/*
 * php5.3开始已经自带
 */
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

/*
 * 根据一个包含所有合法键作为内容的$legalkeys数组，对一个数组$array进行过滤
 */
function array_keyfilter($array,$legalkeys){
    foreach($array as $key => $value){
        if(!in_array($key,$legalkeys)){
            unset($array[$key]);
        }
    }
	return $array;
}

/*
 * 判断某个值是否存在与某一数组的子数组下
 * 若指定$key_specified，则要判断子数组们的$key_specified键下是否有指定$needle值
 * 
 * 这在处理db_toArray的结果数组时十分有用，db_toArray的数组其中每一行又是一个数组
 */
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

/*
 * 接受一个SELECT类query返回的result对象
 * 返回结果集行数
 */
function db_rows($result){
	return mysql_num_rows($result);
}

/*
 * 返回上一条query影响的行数
 */
function db_affected_rows(){
	return mysql_affected_rows(DB_LINK);
}

/*
 * 返回上一条insert语句插入的行id
 */
function db_insert_id(){
	return mysql_insert_id(DB_LINK);
}

/*
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

/*
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

/*
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

/*
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

/*
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

/*
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

/*
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

/*
 * 返回某个字段定义中的ENUM选项
 */
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

function status($affair,$data_id,$field,$old_value,$new_value){
	$data=compact($affair,$data_id,$field,$old_value,$new_value);
	$data+=uidTime();
	db_insert('status',$data);
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

/*
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

/*
 * 仅用在fetchTableArray中
 * 将field->content等值中包含的变量占位替换为数据结果中他们的值
 */
function variableReplace($content,$row){
	while(preg_match('/{(\S*?)}/',$content,$match)){
		if(!isset($row[$match[1]])){
			$row[$match[1]]=NULL;
		}
		$content=str_replace($match[0],$row[$match[1]],$content);
	}
	return $content;
}

function variableReplaceSelf(&$content,$key,$row){
	$content=variableReplace($content,$row);
}

 //The function returns the no. of business days between two dates and it skips the holidays
function getWorkingDays($startDate, $endDate, $holidays = array(), $overtimedays=array(), $timestamp = true) {
	// do strtotime calculations just once
	if (!$timestamp) {
		$endDate = strtotime($endDate);
		$startDate = strtotime($startDate);
	}

	//The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
	//We add one to inlude both dates in the interval.
	$days = floor(($endDate - $startDate) / 86400) + 1;

	$no_full_weeks = floor($days / 7);
	$no_remaining_days = fmod($days, 7);

	//It will return 1 if it's Monday,.. ,7 for Sunday
	$the_first_day_of_week = date("N", $startDate);
	$the_last_day_of_week = date("N", $endDate);

	//---->The two can be equal in leap years when february has 29 days, the equal sign is added here
	//In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
	if ($the_first_day_of_week <= $the_last_day_of_week) {
		if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week)
			$no_remaining_days--;
		if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week)
			$no_remaining_days--;
	} else {
		// (edit by Tokes to fix an edge case where the start day was a Sunday
		// and the end day was NOT a Saturday)

		// the day of the week for start is later than the day of the week for end
		if ($the_first_day_of_week == 7) {
			// if the start date is a Sunday, then we definitely subtract 1 day
			$no_remaining_days--;

			if ($the_last_day_of_week == 6) {
				// if the end date is a Saturday, then we subtract another day
				$no_remaining_days--;
			}
		} else {
			// the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
			// so we skip an entire weekend and subtract 2 days
			$no_remaining_days -= 2;
		}
	}

	//The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
	//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
	$workingDays = $no_full_weeks * 5;
	if ($no_remaining_days > 0) {
		$workingDays += $no_remaining_days;
	}

	//We subtract the holidays
	foreach ($holidays as $holiday) {
		$time_stamp = strtotime($holiday);
		//If the holiday doesn't fall in weekend
		if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7)
			$workingDays--;
	}
	
	foreach ($overtimedays as $overtimeday) {
		$time_stamp = strtotime($overtimeday);
		//If the holiday doesn't fall in weekend
		if ($startDate <= $time_stamp && $time_stamp <= $endDate)
			$workingDays++;
	}

	return $workingDays;
}

function getHolidays(){
	return array_sub(db_toArray("SELECT date FROM holidays WHERE is_overtime=0 AND staff IS NULL"),'date');
}

function getOvertimedays(){
	return array_sub(db_toArray("SELECT date FROM holidays WHERE is_overtime=1 AND staff IS NULL"),'date');
}
?>