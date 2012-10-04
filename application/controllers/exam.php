<?php
class Exam extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		if(is_posted('allocate_seat')){
			exam_allocate_seat();
		}
		
		$q="SELECT 
				exam.id AS id,exam.name AS name,exam.term AS term,exam.is_on,exam.seat_allocated,exam.depart,
				grade.name AS grade_name
			FROM exam INNER JOIN grade ON exam.grade=grade.id
			WHERE
				1=1
			";
				
		$this->processOrderby($q,'exam.id','DESC',array('exam.name'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'id'=>array('title'=>'编号','td_title'=>'width="70px"'),
			'name'=>array('title'=>'考试名称','eval'=>true,'content'=>"
				return '<a href=\"exam.php?exam={id}\" style=\"float:left;\">{depart}-{grade_name}-{name}</a>'.({seat_allocated}?' <a href=\"exam.php?exam={id}&view_seat\" style=\"float:right;\">座位表</a>':'');
			"),
			'term'=>array('title'=>'学期'),
			'is_on'=>array('title'=>'激活','eval'=>true,'content'=>"
				return '<input type=\"checkbox\" name=\"is_on\" id=\"{id}\" '.({is_on}?'checked=\"checked\"':'').' />';
			")
		);
		
		$menu=array(
			'head'=>'<div style="float:left;">'.
						'<button type="button" id="addExam">添加</button>'.
						'<input type="submit" name="allocate_seat" value="排座位" title="根据当前教室设置，为已激活的考试生成座位表" />'.
					'</div>'.
					'<div style="float:right;">'.
						$listLocator.
					'</div>'
		);

		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);
	}

	function listSave(){
		if(got('update') && is_posted('id')){
			$data=array($_POST['field']=>$_POST['value']);
			db_update($_POST['table'],$data,"id='".intval($_POST['id'])."'");
			
		}elseif(got('action','exam')){
			$new_exam=array_trim($_POST);
			
			if(is_numeric($new_exam['grade_name'])){
				$new_exam['grade']=$new_exam['grade_name'];
			
			}else{
				$r=db_query("SELECT id FROM grade WHERE name LIKE '%".$new_exam['grade_name']."%'");
				if(db_rows($r)==1){
					$new_exam['grade']=mysql_result($r,0,'id');
				}else{
					echo ' 没有这个年级或存在多个匹配';
				}
			}
			unset($new_exam['grade_name']);
			
			if(!array_diff(array('name','depart','grade','term','is_on'),array_keys($new_exam))){
				$insert_id=db_insert('exam',$new_exam);
				$r=db_query('
					SELECT 
						exam.id,exam.name,exam.term,exam.is_on,exam.depart,
						grade.name AS grade_name
					FROM exam INNER JOIN grade ON exam.grade=grade.id 
					ORDER BY id DESC LIMIT 1
				');
				$exam=db_fetch_array($r);
				if($exam['id']==$insert_id){
					echo json_encode($exam);
				}
			}
		}elseif(got('action','exam_paper')){
			$new_line=array_trim($_POST);
		
			$r_course=db_query("SELECT id FROM course WHERE name LIKE '".$new_line['course_name']."'");
			if(db_rows($r_course)==1){
				$new_line['course']=mysql_result($r_course,0,'id');
				unset($new_line['course_name']);
			}else{
				echo '没有这个学科';
			}
		
			$new_line_id=db_insert('exam_paper',$new_line);
			
			//_imperfect 此二处采取了全部更新，理应更新一条即可 uicestone 2012/2/15
			#更新exam_paper的students
			db_query("
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
			db_query("
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
		
			$r=db_query('
				SELECT 
					course.name AS course_name,
					exam_paper.id AS id,exam_paper.is_extra_course,exam_paper.students,exam_paper.is_scoring,
					staff_group.name AS teacher_group_name
				FROM exam_paper
					INNER JOIN course ON course.id=exam_paper.course
					LEFT JOIN staff_group ON staff_group.id=exam_paper.teacher_group
				WHERE exam_paper.id='.$new_line_id.'
			');
		
			if($exam=db_fetch_array($r)){
				echo json_encode($exam);
			}
		}
	}
	
	function paperList(){
		post('exam/id',intval($_GET['exam']));
		
		$q="SELECT 
				course.id AS course,course.name AS course_name,
				exam_paper.id AS id,exam_paper.is_extra_course,exam_paper.students,exam_paper.is_scoring,exam_paper.term,
				grade.name,
				staff_group.name AS teacher_group_name
			FROM exam_paper
				INNER JOIN course ON course.id=exam_paper.course
				INNER JOIN exam ON exam_paper.exam=exam.id
				INNER JOIN grade ON grade.id=exam.grade
				LEFT JOIN staff_group ON staff_group.id=exam_paper.teacher_group
			WHERE
				exam.id='".post('exam/id')."'
			";
				
		$this->processOrderby($q,'course.id');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'id'=>array('title'=>'编号','td_title'=>'width="70px"'),
			'course'=>array('title'=>'学科','content'=>'{course_name}'),
			'students'=>array('title'=>'考试人数'),
			'teacher_group_name'=>array('title'=>'备课组'),
			'is_extra_course'=>array('title'=>'分科考试','eval'=>true,'content'=>"
				return '<input type=\"checkbox\" '.({is_extra_course}?'checked=\"checked\"':'').' disabled=\"disabled\" />';
			"),
			'is_scoring'=>array('title'=>'开启登分','eval'=>true,'content'=>"
				return '<input type=\"checkbox\" name=\"is_scoring\" id=\"{id}\" '.({is_scoring}?'checked=\"checked\"':'').' />';
			")
		);
		
		$menu=array(
			'head'=>'<div class="left">'.
						'<button type="button" id="addExamPaper">添加</button>'.
						'<button type="button" onclick="redirectPara(this,\'exam\')">返回</button>'.
					'</div>'.
					'<div style="float:right;">'.
						$listLocator.
					'</div>'
		);
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);
	}

	function viewSeat(){
		$exam=intval($_GET['exam']);
		
		$q="SELECT 
				view_student.num,view_student.class_name,view_student.name AS student_name,
				exam_student.room,exam_student.seat,course.name AS course_name
			FROM exam_student INNER JOIN view_student ON exam_student.student=view_student.id
				LEFT JOIN course ON exam_student.extra_course=course.id
			WHERE
				exam_student.exam='".$exam."'
		";
				
		$this->processOrderby($q,'view_student.num','ASC');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'num'=>array('title'=>'学号'),
			'student_name'=>array('title'=>'姓名'),
			'room'=>array('title'=>'教室'),
			'seat'=>array('title'=>'座位'),
			'course_name'=>array('title'=>'加科')
		);
		
		$menu=array(
			'head'=>'<div style="float:left;">'.
						'<button type="button" onclick="location.href=\'exam.php\'">返回</button>'.
					'</div>'.
					'<div style="float:right;">'.
						$listLocator.
					'</div>'
		);
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);

	}
}
?>