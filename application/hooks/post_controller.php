<?php
function postController(){
	global $class,$method;
	
	$CI=&get_instance();

	if(!$CI->main_view_loaded && is_file(APPPATH.'views/'.$class.'/'.$method.'.php')){
		$CI->load->view("{$class}/{$method}",$CI->data);
	}
	
	if(is_file(APPPATH.'views/'.$class.'/'.$method.'_sidebar'.'.php')){
		$CI->load->view('sidebar_head');
		$CI->load->view("{$class}/{$method}_sidebar");
		$CI->load->view('sidebar_foot');
	}
	
	if($CI->config->item('require_export')){
		$CI->load->view('foot');
	}
}
?>
