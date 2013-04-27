<?php
class Classes extends Team{
	
	var $section_title='班级';
	
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		
		if($this->input->post('grade')){
			option('grade',$this->input->post('grade'));
		}
		
		$field=array(
			'class.id'=>array('heading'=>'名称','cell'=>'<a href = "#classes/{id}">{name}</a>','title'=>'编号：{id}'),
			'depart'=>array('heading'=>'部门'),
			'extra_course_name'=>array('heading'=>'加一'),
			'class_teacher_name'=>array('heading'=>'班主任')
		);
		
		$list=$this->table->setFields($field)
			->setData($this->classes->getList())
			->generate();
		
		$this->load->addViewData('list', $list);
		$this->load->view('list');
	}

	function add(){
		$this->edit();
	}

	function edit($id){
		
		$this->load->model('staff_model','staff');
		
		$this->classes->data=$this->classes->fetch($id);
		
		$this->load->view('classes/edit');
	}
}
?>