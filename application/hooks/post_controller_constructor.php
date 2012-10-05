<?php
function postControllerConstructor(){
	global $class,$method;

	$CI=&get_instance();
	
	//使用controller中自定义的默认method
	if(isset($CI->default_method) && $method=='index'){
		$method=$CI->default_method;
	}
	
	$CI->load->model('company_model','company');
	
	if(is_file(APPPATH.'models/'.$class.'_model.php')){
		$CI->load->model($class.'_model',$class);
	}

	if($CI->config->item('require_export')){
		if(IN_UICE=='nav'){
			$CI->load->view('head_nav');
		}elseif(IN_UICE=='frame'){
			$CI->load->view('head_frame');
		}else{
			$CI->load->view('head');
		}

		if($CI->config->item('require_menu')){
			$CI->load->view('menu');
		}
	}
}
?>
