<?php
class PostController extends SS_controller{
	function __construct() {
		parent::__construct();
	}
	
	function postController(){
		if($this->config->item('require_export')){
			$this->load->view('foot');
		}
	}
}
?>
