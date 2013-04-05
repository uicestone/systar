<?php
class Staff extends People{
	
	var $section_title='职员';
	
	function __construct(){
		parent::__construct();
		$this->people=$this->staff;
	}
	
	function index(){
		$this->config->set_user_item('search/is_staff', true, false);
		
		parent::index();
	}
	
	function match(){

		$term=$this->input->post('term');
		
		$result=$this->staff->match($term);

		$array=array();

		foreach ($result as $row){
			$array[]=array(
				'label'=>$row['name'],
				'value'=>$row['id']
			);
		}
		$this->output->data=$array;
	}
}
?>