<?php
class Group extends People{
	
	function __construct() {
		parent::__construct();
		$this->people=$this->group;
		
		$this->search_items=array('name','tags','is_relative_of');

		$this->list_args=array(
			'name'=>array('heading'=>'名称'),
			'tags'=>array('heading'=>'标签','parser'=>array('function'=>array($this->group,'getCompiledTags'),'args'=>array('id')))
		);
		
		$this->load->view_path['list_aside']='team/list_sidebar';

	}
}
?>
