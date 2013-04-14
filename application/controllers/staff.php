<?php
class Staff extends People{
	
	var $section_title='职员';
	
	function __construct(){
		parent::__construct();
		$this->people=$this->staff;
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