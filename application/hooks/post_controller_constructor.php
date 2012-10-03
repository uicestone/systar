<?php
function postControllerConstructor(){
	global $class,$method;

	$CI=&get_instance();
	
	//使用controller中自定义的默认method
	if(isset($CI->default_method) && $method=='index'){
		$method=$CI->default_method;
	}
	
	$CI->load->model('company_model','company');
}
?>
