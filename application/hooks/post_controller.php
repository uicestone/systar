<?php
function postController(){
	global $class,$method;
	
	$CI=&get_instance();

	//自动载入主视图
	/*
	 * 这似乎是一处过渡设计
	if(!$CI->load->main_view_loaded && is_file(APPPATH.'views/'.$class.'/'.$method.'.php')){
		$CI->load->view("{$class}/{$method}");
	}
	 */

	if(!$CI->load->sidebar_loaded){
		$sidebar=$CI->load->sidebar_data.
			(is_file(APPPATH.'views/'.$class.'/'.$method.'_sidebar'.'.php')?$CI->load->view("{$class}/{$method}_sidebar",array(),true):'');
		if($sidebar){
			$CI->output->append_output(
				$CI->load->view('sidebar_head',array(),true).
				$sidebar.
				$CI->load->view('sidebar_foot',array(),true)
			);
		}
	}

}
?>
