<?php
function postController(){
	global $class,$method;
	
	$CI=&get_instance();
	
	if($CI->require_export){
		if(!$CI->main_view_loaded && is_file(APPPATH.'views/'.$class.'/'.$method.'.php')){
			$CI->load->view("{$class}/{$method}",$CI->view_data);
		}
	
		if(!$CI->sidebar_loaded && is_file(APPPATH.'views/'.$class.'/'.$method.'_sidebar'.'.php')){
			$CI->load->view('sidebar_head');
			$CI->load->view("{$class}/{$method}_sidebar");
			$CI->load->view('sidebar_foot');
		}
		
		$CI->load->view('foot');
	}

}
?>
