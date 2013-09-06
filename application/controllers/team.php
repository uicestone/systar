<?php
class Team extends People{
	
	function __construct() {
		parent::__construct();
		$this->people=$this->team;
		
		$this->search_items=array('name','labels','is_relative_of');

		$this->list_args=array(
			'name'=>array('heading'=>'名称'),
			'leader_name'=>array('heading'=>'组长'),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->team,'getCompiledLabels'),'args'=>array('id')))
		);
		
		$this->load->view_path['list_aside']='team/list_sidebar';
		$this->load->view_path['edit']='team/edit';

	}
	
	function index() {
		$this->config->set_user_item('search/get_leader', true);
		parent::index();
	}
}
?>
