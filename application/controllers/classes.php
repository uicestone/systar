<?php
class Classes extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		if($this->input->post('grade')){
			option('grade',$this->input->post('grade'));
		}
		
		$q="
			SELECT class.id, class.name, grade.name AS grade_name, depart, course.name AS extra_course_name,
				staff.name AS class_teacher_name
			FROM class INNER JOIN grade ON class.grade = grade.id
				LEFT JOIN course ON course.id = class.extra_course
				LEFT JOIN staff ON staff.id = class.class_teacher
			WHERE grade>='".$_SESSION['global']['highest_grade']."'
		";
		
		addCondition($q,array('grade'=>'class.grade'));
				
		$search_bar=$this->processSearch($q,array('name'=>'班级','depart'=>'部门'));
		
		$this->processOrderby($q,'class.id','ASC');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'class.id'=>array('title'=>'名称','td'=>'title="编号：{id}"','content'=>'{name}','wrap'=>array('mark'=>'a','href'=>'class?edit={id}')),
			'depart'=>array('title'=>'部门'),
			'extra_course_name'=>array('title'=>'加一'),
			'class_teacher_name'=>array('title'=>'班主任')
		);
		
		$menu=array(
			'head'=>'<div class="right">'.
						$listLocator.
					'</div>'
		);
		
		$_SESSION['last_list_action']=$this->input->server('REQUEST_URI');
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table','menu');
		
		$this->load->view('lists',$this->view_data);
	}

	function add(){
		$this->edit();
	}

	function edit($id=NULL){
		model('staff');
		
		getPostData($id);
		
		$submitable=false;
		
		if($this->input->post('submit')){
			$submitable=true;
		
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$_POST);
			
			if(post('class/class_teacher',staff_check(post('class_extra/class_teacher_name')))<0){
				$submitable=false;
			}
		
			$this->processSubmit($submitable);
		}
		
		post('class/class_teacher')>0 && post('class_extra/class_teacher_name',db_fetch_field("
			SELECT name 
			FROM staff 
			WHERE id=(SELECT class_teacher FROM class WHERE id='".post('class/id')."')
		"));
		
		$q_class_leadership="
			SELECT student.name,student_class.position 
			FROM student INNER JOIN student_class 
				ON (student.id=student_class.student AND student_class.term='".$_SESSION['global']['current_term']."')
			WHERE student_class.class='".post('class/id')."'
				AND student_class.position IS NOT NULL
		";
		$field_class_leadership=array(
			'student.name'=>array('title'=>'学生'),
			'student_class.position'=>array('title'=>'职务')
		);
	}
}
?>