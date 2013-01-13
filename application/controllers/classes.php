<?php
class Classes extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));
		
		if($this->input->post('grade')){
			option('grade',$this->input->post('grade'));
		}
		
		$field=array(
			'class.id'=>array('title'=>'名称','td'=>'title="编号：{id}"','content'=>'{name}','wrap'=>array('mark'=>'a','href'=>'/classes/edit/{id}')),
			'depart'=>array('title'=>'部门'),
			'extra_course_name'=>array('title'=>'加一'),
			'class_teacher_name'=>array('title'=>'班主任')
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

	function edit($id=NULL){
		$this->load->model('staff_model','staff');
		
		$this->getPostData($id);
		
		$submitable=false;
		
		if($this->input->post('submit')){
			$submitable=true;
		
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$this->input->post());
			
			if(post('classes/class_teacher',$this->staff->check(post('classes_extra/class_teacher_name')))<0){
				$submitable=false;
			}
		
			$this->processSubmit($submitable);
		}
		
		post('classes/class_teacher')>0 && post('classes_extra/class_teacher_name',$this->classes->fetchLeader(post('classes/id'),'name'));
		
		$field_class_leadership=array(
			'student.name'=>array('title'=>'学生'),
			'student_class.position'=>array('title'=>'职务')
		);
		$leaders=$this->table->setFields($field_class_leadership)
			->generate($this->classes->getLeadersList(post('classes/id')));

		$this->load->addViewData('leaders',$leaders);
	}
}
?>