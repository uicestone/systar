<?php
class PostControllerConstructor extends SS_controller{
	function __construct() {
		parent::__construct();
	}
	
	function postControllerConstructor(){
		global $_G;

		$data=compact('_G');

		if($_G['require_export']){
			if(IN_UICE=='nav'){
				$this->load->view('head_nav',$data);
			}elseif(IN_UICE==''){
				$this->load->view('head_frame',$data);
			}else{
				$this->load->view('head',$data);
			}

			if($_G['require_menu']){
				$this->load->view('menu');
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
}
?>
