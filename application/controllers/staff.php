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
				'id'=>array('heading'=>'姓名','cell'=>'{name}'),
				'course_name'=>array('heading'=>'学科'),
				'status'=>array('heading'=>'职称')
			);
		}else{
			$field=array(
				'id'=>array('heading'=>'姓名','cell'=>'{name}'),
				'position_name'=>array('heading'=>'职位','cell'=>'{position_name}'),
				'modulus'=>array('heading'=>'团奖系数'),
				'timing_fee_default'=>array('heading'=>'默认小时费率')
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