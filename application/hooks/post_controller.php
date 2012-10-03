<?php
function postController(){
	$CI=&get_instance();
	
	if($CI->config->item('require_export')){
		$CI->load->view('foot');
	}
}
?>
