<?php
/**
 * 重定向，对于站内跳转，url写成REQUEST_URI即可，如'user?browser'
 * 有php和js两种方式
 * 对于php跳转，采用发送301header的方式，因此之前整个系统不能输出任何内容
 * 对于js跳转，输出js代码交给浏览器完成跳转，因此会发生内容输出
 * $unsetPara目前只适用于js跳转，用以将原来url中的某个变量去除
 */
function redirect($url='',$method='php',$unsetPara=NULL,$jump_to_top_frame=false){
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

/**
 * 输出1K的空格来强制浏览器输出
 * 使用后在下文执行任何输出，再紧跟flush();即可即时看到
 */
function forceExport(){
	ob_end_clean();   //清空并关闭输出缓冲区
	echo str_repeat(' ',1024);
}
?>