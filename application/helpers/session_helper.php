<?php
/**
 * 保存控制单元相关配置时候用，比如列表页的页码，搜索的关键词等
 */
function option($arrayindex){
	
	$args=func_get_args();
	
	if(count($args)==1){
		return array_dir('_SESSION/'.CONTROLLER.'/'.METHOD.'/'.$arrayindex);

	}elseif(count($args==2)){
		return array_dir('_SESSION/'.CONTROLLER.'/'.METHOD.'/'.$arrayindex,$args[1]);
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
	
	if(count($args)==1){
		return array_dir(CONTROLLER.'/post/'.$CI->$controller->id.'/'.$arrayindex);
	}elseif(count($args)==2){
		return array_dir(CONTROLLER.'/post/'.$CI->$controller->id.'/'.$arrayindex,$args[1]);
	}
}

function unsetPost($arrayindex=''){
	$CI=&get_instance();
	
	$controller=CONTROLLER;
	
	return $CI->session->unset_userdata(CONTROLLER.'/post/'.$CI->$controller->id.($arrayindex===''?'':'/'.$arrayindex));
}
?>