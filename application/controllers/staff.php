<?php
class Staff extends People{
	function __construct(){
		parent::__construct();
		$this->people=$this->staff;
	}
	
	function index(){
		if(!option('search/type')){
			option('search/type','职员');
		}
		
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