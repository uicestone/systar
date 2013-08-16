<?php
class Tag extends SS_Controller{
	function __construct() {
		parent::__construct();
	}
	
	function getRelatives($tag_name,$relation=NULL){
		$tag_name=urldecode($tag_name);
		$this->output->data=$this->tag->getRelatives($tag_name);
	}
}
?>
