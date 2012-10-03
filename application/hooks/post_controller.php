<?php
class PostController extends SS_controller{
	function __construct() {
		parent::__construct();
	}
	
	function postController(){
		global $_G;

		if($_G['require_export']){
			$this->load->view('foot');
		}
	}
}
?>
