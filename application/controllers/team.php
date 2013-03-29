<?php
class Team extends SS_Controller{
	
	var $section_title='团组';
	
	var $list_args;
	
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		
		$this->list_args=array(
			'name'=>array('heading'=>'名称'),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->team,'getCompiledLabels'),'args'=>array('{id}')))
		);

		if($this->input->post('name')!==false && $this->input->post('name')!==''){
			option('search/name',$this->input->post('name'));
		}
		
		if(is_array($this->input->post('labels'))){
			
			if(is_null(option('search/labels'))){
				option('search/labels',array());
			}
			
			option('search/labels',array_trim($this->input->post('labels'))+option('search/labels'));
		}
		
		if($this->input->post('is_relative_of')){
			option('search/is_relative_of',$this->input->post('is_relative_of'));
		}
		
		//提交了搜索项，但搜索项中没有labels项，我们将session中搜索项的labels项清空
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			option('search/labels',array());
		}
		
		if($this->input->post('submit')==='search' && $this->input->post('is_relative_of')===false){
			option('search/is_relative_of',array());
		}
		
		//点击了取消搜索按钮，则清空session中的搜索项
		if($this->input->post('submit')==='search_cancel'){
			option('search/name',NULL);
			option('search/labels',array());
			option('search/is_relative_of',array());
		}
		
		$table=$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>CONTROLLER.'/edit/{id}'))
			->setData($this->team->getList(option('search')))
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
