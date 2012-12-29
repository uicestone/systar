<?php
function postController(){
	global $class,$method;
	
	$CI=&get_instance();

	//自动载入主视图
	if(!$CI->load->main_view_loaded && is_file(APPPATH.'views/'.$class.'/'.$method.'.php')){
		$CI->load->view("{$class}/{$method}");
	}

	//在当前准备好的输出内容基础上加上页头，页尾，并自动加载边栏
	if($CI->load->require_head){

		$CI->load->view('pagehead');

		if($CI->load->require_menu){
			$CI->output->prepend_output($CI->load->view('menu',array(),true));
		}

		//$CI->output->prepend_output($CI->load->view('head',array(),true));

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
		//$CI->output->append_output($CI->load->view('foot',array(),true));
	}

}
?>
