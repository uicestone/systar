<?php
class Staff extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		

		if($this->input->post('grade')){
			option('grade',$this->input->post('grade'));
		}
		if($this->company->type=='school'){
			$field=array(
				'id'=>array('title'=>'姓名','content'=>'{name}'),
				'course_name'=>array('title'=>'学科'),
				'status'=>array('title'=>'职称')
			);
		}else{
			$field=array(
				'id'=>array('title'=>'姓名','content'=>'{name}'),
				'position_name'=>array('title'=>'职位','content'=>'{position_name}'),
				'modulus'=>array('title'=>'团奖系数'),
				'timing_fee_default'=>array('title'=>'默认小时费率')
			);
		}
		$table=$this->table->setFields($field)
			->setData($this->staff->getList())
			->generate();
		$this->load->addViewData('list',$table);
		$this->load->view('list');		
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