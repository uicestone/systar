<?php
class Label extends SS_Controller{
	function __construct() {
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	function getRelatives($label_name,$relation=NULL){
		$this->load->require_head=false;
		$label_id=$this->label->match($label_name);
		echo json_encode($this->label->getRelatives($label_id));
	}
}
?>
