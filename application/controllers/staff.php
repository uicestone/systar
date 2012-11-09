<?php
class Staff extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		if($this->input->post('grade')){
			option('grade',$this->input->post('grade'));
		}
		if($this->config->item('company_type')=='school'){
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
}
?>