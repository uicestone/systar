<?php
class Exam extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		if($this->input->post('allocate_seat')){
			$this->exam->allocate_seat();
		}
		
		$field=array(
			'id'=>array('heading'=>'编号','td_title'=>'width="70px"'),
			'name'=>array('heading'=>'考试名称','eval'=>true,'cell'=>"
				return '<a href=\"exam/paperlist/{id}\" style=\"float:left;\">{depart}-{grade_name}-{name}</a>'.({seat_allocated}?' <a href=\"/exam/viewseat/{id}\" style=\"float:right;\">座位表</a>':'');
			"),
			'term'=>array('heading'=>'学期'),
			'is_on'=>array('heading'=>'激活','eval'=>true,'cell'=>"
				return '<input type=\"checkbox\" name=\"is_on\" id=\"{id}\" '.({is_on}?'checked=\"checked\"':'').' />';
			")
		);
		
		$list=$this->table->setFields($field)
			->setData($this->exam->getList())
			->wrapForm()
			->generate();
		
		$this->load->addViewData('list', $list);
		$this->load->view('exam/lists');
	}

	/**
	 * TODO 网页内阅卷登分，需要重新设计
	 */
	function listSave(){
		
		if($this->input->get('update') && $this->input->post('id')){
			$data=array($this->input->post('field')=>$this->input->post('value'));
			$this->db->update($this->input->post('table'),$data,array('id'=>intval($this->input->post('id'))));
			
		}elseif($this->input->get('action')=='exam'){
			$new_exam=array_trim($_POST);
			
			if(is_numeric($new_exam['grade_name'])){
				$new_exam['grade']=$new_exam['grade_name'];
			
			}else{
				$r=$this->db->query("SELECT id FROM grade WHERE name LIKE '%".$new_exam['grade_name']."%'");
				if(/*db_rows($r)==*/1){
					$new_exam['grade']=mysql_result($r,0,'id');
				}else{
					echo ' 没有这个年级或存在多个匹配';
				}
			}
			unset($new_exam['grade_name']);
			
			if(!array_diff(array('name','depart','grade','term','is_on'),array_keys($new_exam))){
				$this->db->insert('exam',$new_exam);
				$insert_id=$this->db->insert_id();
				$query="
					SELECT 
						exam.id,exam.name,exam.term,exam.is_on,exam.depart,
						grade.name AS grade_name
					FROM exam INNER JOIN grade ON exam.grade=grade.id 
					ORDER BY id DESC LIMIT 1
				";
				$exam=$this->db->query($query)->row_array();
				if($exam['id']==$insert_id){
					echo json_encode($exam);
				}
			}
		}elseif($this->input->get('action')=='exam_paper'){
			$new_line=array_trim($_POST);
		
			$r_course=$this->db->query("SELECT id FROM course WHERE name LIKE '".$new_line['course_name']."'");
			if(/*db_rows($r_course)==*/1){
				$new_line['course']=mysql_result($r_course,0,'id');
				unset($new_line['course_name']);
			}else{
				echo '没有这个学科';
			}
			
			$this->db->insert('exam_paper',$new_line);
			$new_line_id=$this->db->insert_id();
			
			//_imperfect 此二处采取了全部更新，理应更新一条即可 uicestone 2012/2/15
			#更新exam_paper的students
			$this->db->query("
				UPDATE 
				exam_paper,
				(
					SELECT exam_paper.id,exam_paper.exam,extra_course,COUNT(1) AS students 
					FROM exam_student 
						LEFT JOIN exam_paper ON (exam_paper.exam=exam_student.exam AND
					(exam_paper.is_extra_course=0 OR exam_paper.course=exam_student.extra_course)) 
					WHERE exam_paper.exam IN (SELECT id FROM exam WHERE is_on=1)
					GROUP BY exam_paper.id
				)exam_paper_students
				
				SET exam_paper.students=exam_paper_students.students
				WHERE exam_paper.id=exam_paper_students.id
			");
			
			#按照备课组分配试卷权限
			$this->db->query("
				UPDATE 
				exam_paper INNER JOIN exam ON exam_paper.exam=exam.id
				INNER JOIN staff_group ON
				(
				exam_paper.course=staff_group.course
				AND
				(exam.grade=staff_group.grade OR staff_group.grade=0)
				)
				AND exam_paper.is_scoring=1
				SET exam_paper.teacher_group=staff_group.id
			");
		
			$query="
				SELECT 
					course.name AS course_name,
					exam_paper.id AS id,exam_paper.is_extra_course,exam_paper.students,exam_paper.is_scoring,
					staff_group.name AS teacher_group_name
				FROM exam_paper
					INNER JOIN course ON course.id=exam_paper.course
					LEFT JOIN staff_group ON staff_group.id=exam_paper.teacher_group
				WHERE exam_paper.id='.$new_line_id.'
			";
			
			$exam=$this->db->query($query)->row_array();
		
			if($exam){
				echo json_encode($exam);
			}
		}
	}
	
	function paperList($exam_id){
		post('exam/id',intval($exam_id));
		
		$field=array(
			'id'=>array('heading'=>'编号','td_title'=>'width="70px"'),
			'course'=>array('heading'=>'学科','cell'=>'{course_name}'),
			'students'=>array('heading'=>'考试人数'),
			'teacher_group_name'=>array('heading'=>'备课组'),
			'is_extra_course'=>array('heading'=>'分科考试','eval'=>true,'cell'=>"
				return '<input type=\"checkbox\" '.({is_extra_course}?'checked=\"checked\"':'').' disabled=\"disabled\" />';
			"),
			'is_scoring'=>array('heading'=>'开启登分','eval'=>true,'cell'=>"
				return '<input type=\"checkbox\" name=\"is_scoring\" id=\"{id}\" '.({is_scoring}?'checked=\"checked\"':'').' />';
			")
		);
		
		$list=$this->table->setFields($field)
			->setData($this->exam->getPaperList($exam_id))
			->generate();
		
		$this->load->addViewData('list', $list);
		$this->load->view('exam/paperlist');
	}

	function viewSeat($exam_id){
		$field=array(
			'num'=>array('heading'=>'学号'),
			'student_name'=>array('heading'=>'姓名'),
			'room'=>array('heading'=>'教室'),
			'seat'=>array('heading'=>'座位'),
			'course_name'=>array('heading'=>'加科')
		);
		
		$list=$this->table->setFields($field)
			->setData($this->exam->getSeatList($exam_id))
			->setMenu('<button type="button" onclick="location.href=\'/exam\'">返回</button>','left')
			->generate();
		
		$this->load->addViewData('list', $list);
		$this->load->view('list',$this->view_data);

	}
}
?>