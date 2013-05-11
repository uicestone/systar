<?php
class Team extends SS_Controller{
	
	var $section_title='团组';
	
	var $list_args;
	
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		
		$this->config->set_user_item('search/limit', 'pagination', false);
		
		$this->list_args=array(
			'name'=>array('heading'=>'名称'),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->team,'getCompiledLabels'),'args'=>array('id')))
		);

		if($this->input->post('name')){
			$this->config->set_user_item('search/name', $this->input->post('name'));
		}
		
		if($this->input->post('labels')){
			$this->config->set_user_item('search/labels', $this->input->post('labels'));
		}
		
		if($this->input->post('is_relative_of')){
			$this->config->set_user_item('search/is_relative_of',$this->input->post('is_relative_of'));
		}
		
		if($this->input->post('name')===''){
			$this->config->unset_user_item('search/name');
		}
		
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			$this->config->unset_user_item('search/labels');
		}
		
		if($this->input->post('submit')==='search' && $this->input->post('is_relative_of')===false){
			$this->config->unset_user_item('search/is_relative_of');
		}
		
		if($this->input->post('submit')==='search_cancel'){
			$this->config->unset_user_item('search/name');
			$this->config->unset_user_item('search/labels');
			$this->config->unset_user_item('search/is_relative_of');
		}
		
		$table=$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>CONTROLLER.'/{id}'))
			->setData($this->team->getList($this->config->user_item('search')))
			->generate();
		$this->load->addViewData('list', $table);
		
		if(file_exists(APPPATH.'/views/'.CONTROLLER.'/list'.EXT)){
			$this->load->view(CONTROLLER.'/list');
		}else{
			$this->load->view('list');
		}
		
		if(file_exists(APPPATH.'/views/'.CONTROLLER.'/list_sidebar'.EXT)){
			$this->load->view(CONTROLLER.'/list_sidebar',true,'sidebar');
		}else{
			$this->load->view('team/list_sidebar',true,'sidebar');
		}
	}
}
?>
