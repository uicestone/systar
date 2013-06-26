<?php
class Classes extends Team{
	
	function __construct(){
		parent::__construct();
		$this->load->model('classes_model','classes');
		$this->list_args=array(
			'class.id'=>array('heading'=>'名称','cell'=>'<a href = "#classes/{id}">{name}</a>','title'=>'编号：{id}'),
			'depart'=>array('heading'=>'部门'),
			'extra_course_name'=>array('heading'=>'加一'),
			'class_teacher_name'=>array('heading'=>'班主任')
		);
	}
	
	function edit($id){
		
		$this->load->model('staff_model','staff');
		
		$this->classes->data=$this->classes->fetch($id);
		
		$this->load->view('classes/edit');
	}
}
?>