<?php
function postController(){
	global $class,$method;
	
	$CI=&get_instance();

	if($CI->config->item('require_export')){
		if(IN_UICE=='nav'){
			$CI->load->view('head_nav',$CI->data);
		}elseif(IN_UICE=='frame'){
			$CI->load->view('head_frame',$CI->data);
		}else{
			$CI->load->view('head',$CI->data);
		}

		if($CI->config->item('require_menu')){
			$CI->load->view('menu');
		}
	}
	
	if(is_file(APPPATH.'views/'.$class.'/'.$method.'.php')){echo "{$class}/{$method}";
		$CI->load->view("{$class}/{$method}");
	}
	
	if(is_file(APPPATH.'views/'.$class.'/'.$method.'_sidebar'.'.php')){
		$CI->load->view("{$class}/{$method}_sidebar");
	}
	
	if($CI->config->item('require_export')){
		$CI->load->view('foot');
	}
}
?>
