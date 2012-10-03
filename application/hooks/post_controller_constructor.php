<?php
function postControllerConstructor(){
	$CI=&get_instance();
	
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

/*
	if(is_file('controller/'.$_G['action'].'.php')){
		require 'controller/'.$_G['action'].'.php';
	}

	if($_G['require_export'] && is_file('view/'.$_G['action'].'.htm')){
		require 'view/'.$_G['action'].'.htm';
	}

	if(is_file('view/'.$_G['action'].'_sidebar.htm')){
		echo '<div id="toolBar" '.(array_dir('_SESSION/minimized')?'class="minimized"':'').'>'.
			'<span class="minimize-button">-</span>';
		require 'view/'.$_G['action'].'_sidebar.htm';
		echo '</div>';
	}
*/
}
?>
