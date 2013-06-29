<?php
class Exam extends Evaluation{
	function __construct(){
		parent::__construct();
		$this->load->model('exam_model','exam');
		$this->project=$this->exam;
		$this->evaluation=$this->exam;
	}
	
	function index(){
		$list_args=array(
			'name'=>array('heading'=>'考试名称'),
			'term'=>array('heading'=>'学期'),
			'active'=>array('heading'=>'激活','parser'=>array('function'=>function($active){
				return $active?'是':'否';
			},'args'=>array('active')))
		);
		
		if($this->user->inTeam('教导处')){
			$list_args['active']=array('heading'=>'激活','parser'=>array('function'=>function($id,$active){
				return '<input type="checkbox" name="active" id="'.$id.'"'.($active?' checked="checked"':'').' />';
			},'args'=>array('id','active')));
		}
		
		$this->table->setFields($list_args)
			->setRowAttributes(array('hash'=>'exam/paperlist/{id}'))
			->setData($this->exam->getList(array('orderby'=>'id desc','limit'=>'pagination','get_profiles'=>array('term'=>'学期'))));
		
		$this->load->view('exam/lists');
	}

	function paperList($exam_id=NULL){
		
		$this->output->title='试卷';
		
		$list_args=array(
			'course'=>array('heading'=>'学科','cell'=>'<a href="#exam/{id}">{name}</a>'),
			'students'=>array('heading'=>'考试人数'),
			'active'=>array('heading'=>'登分中','parser'=>array('function'=>function($active){
				return $active?'是':'否';
			},'args'=>array('active')))
		);
		
		$this->table->setFields($list_args)
			->setRowAttributes(array('hash'=>'exam/candidates/{id}'))
			->setData($this->exam->getList(array('orderby'=>'id desc','limit'=>'pagination','type'=>'exam_paper','is_relative_of'=>$exam_id,'get_profiles'=>array('students'=>'人数'),'get_labels'=>array('course'=>'学科'))));
		
		$this->load->view('exam/paperlist');
	}
	
	
	//阅卷结果（分数文件）上传
	function uploadScore($exam_paper){
		$this->exam->id=$exam_paper;

		if($this->input->post('submit')){
			try{
				if ($_FILES['score_table']['error'] > 0){
					throw new Exception('文件上错错误：错误代码: '.$_FILES['score_table']['error']);
				}
				
				if(!(preg_match('/\.(\w*?)$/',$_FILES['score']['name'],$extname_match) && $extname_match[1]=='xls')){
					throw new Exception('文件格式错误，请上传xls格式的excel表格');
				}

				require APPPATH.'third_party/PHP-ExcelReader/reader.php';
				$data = new Spreadsheet_Excel_Reader();
				$data->setOutputEncoding('utf-8');
				$data->setRowColOffset(0);

				$data->read($_FILES['score']['tmp_name']);
				
				$this->exam->uploadScore($data);
				
			}catch(Exception $e){
				$this->output->message($e->getMessage(),'warning');
			}
		}
		$this->load->view('exam/uploadscore');
	}
	
	function board(){
		if($this->input->post('partChooseSubmit')){
			//刚从试卷选择界面到登分,界面获得当前大题和所属试卷信息
			$q_exam_part="SELECT 
				exam.id AS exam,exam.name AS name,
				exam_paper.id AS exam_paper,exam_paper.is_extra_course,exam_paper.course AS course,
				exam_part.id AS exam_part, exam_part.name AS part_name,
				grade.name AS grade_name,course.name AS course_name,
				exam_paper.students AS students, exam_paper.teacher_group AS teacher_group 
			FROM 
				(
					(
						(
							exam_paper INNER JOIN exam ON (exam_paper.id='".$this->input->post('exam_paper')."' AND exam_paper.exam=exam.id AND exam_paper.is_scoring=1)
						)
						INNER JOIN exam_part ON (exam_part.id='".$this->input->post('part')."' AND exam_paper.id=exam_part.exam_paper)
					)
					INNER JOIN course ON exam_paper.course=course.id
				)
				INNER JOIN grade ON exam.grade=grade.id
			WHERE 
				".db_implode($_SESSION['teacher_group'],' OR ','teacher_group');
			$r_exam_part=mysql_query($q_exam_part);
			$_SESSION['score']['currentExam']=mysql_fetch_array($r_exam_part);
		}
		
		if(is_null(array_dir('_SESSION/score/currentStudent_id_in_exam'))){
			array_dir('_SESSION/score/currentStudent_id_in_exam',1);
		}
		
		if($this->input->post('nextScore') || $this->input->post('previousScore') || $this->input->post('backToPartChoose')){
			
			$scoreData=array(
				'student'=>$_SESSION['score']['currentStudent']['student'],
				'exam'=>$_SESSION['score']['currentExam']['exam'],
				'exam_paper'=>$_SESSION['score']['currentExam']['exam_paper'],
				'exam_part'=>$_SESSION['score']['currentExam']['exam_part'],
				'score'=>$this->input->post('is_absent')?'0':$this->input->post('score'),
				'is_absent'=>$this->input->post('is_absent')?'1':'0',
				'scorer'=>$this->user->id,
				'scorer_username'=>$_SESSION['username'],
				'time'=>$this->date->now
			);
			
			if($this->input->post('score')!=$_SESSION['score']['currentScore']['score'] || $this->input->post('is_absent')!=$_SESSION['score']['currentScore']['is_absent'])
				$this->db->replace('score',$scoreData);//当前学生-大题-分数插入数据表
			
			if($this->input->post('nextScore'))
				$_SESSION['score']['currentStudent_id_in_exam']++;
			if($this->input->post('previousScore'))
				$_SESSION['score']['currentStudent_id_in_exam']--;
			if($this->input->post('backToPartChoose')){
				unset($_SESSION['score']['currentExam']);
				redirect('score.php');
			}
		}
		
		if($this->input->post('studentSearch')){
			$q_student="
				SELECT * FROM exam_student,view_student 
				WHERE view_student.num='".$this->input->post('studentNumForSearch')."'
					AND exam_student.student=view_student.id
					AND exam_student.exam='".$_SESSION['score']['currentExam']['exam']."'
			";
		}else{
			$q_student="
				SELECT * FROM
					(
						SELECT * 
						FROM exam_student
						WHERE exam='".$_SESSION['score']['currentExam']['exam']."'
						AND (".(int)!$_SESSION['score']['currentExam']['is_extra_course']." OR extra_course='".$_SESSION['score']['currentExam']['course']."')
						ORDER BY room, seat
						LIMIT ".($_SESSION['score']['currentStudent_id_in_exam']-1).",1
					)current_exam_student
				LEFT JOIN view_student ON view_student.id = current_exam_student.student
			";
		}
		$r_student=mysql_query($q_student);
		$_SESSION['score']['currentStudent']=mysql_fetch_array($r_student);
		
		$q_score="SELECT * FROM score WHERE student='".$_SESSION['score']['currentStudent']['student']."' AND exam_part='".$_SESSION['score']['currentExam']['exam_part']."' LIMIT 1";
		$r_score=mysql_query($q_score);
		if(/*db_rows($r_score)==*/0){
			$_SESSION['score']['currentScore']=array();
		}else{
			$_SESSION['score']['currentScore']=mysql_fetch_array($r_score);
		}
	}
	
	function partChoose(){
		$q_exam="SELECT 
			exam.id AS exam,exam.name AS name,exam_paper.id AS exam_paper,exam_paper.is_extra_course AS is_extra_course,
			grade.name AS grade_name,course.id AS course,course.name AS course_name,
			exam_paper.students AS students, exam_paper.teacher_group AS teacher_group 
		FROM 
			(
				(
					exam_paper LEFT JOIN exam ON (exam_paper.exam=exam.id)
				)
				LEFT JOIN course ON exam_paper.course=course.id
			)
			LEFT JOIN grade ON exam.grade=grade.id
		WHERE  exam_paper.is_scoring=1
			AND (".db_implode($_SESSION['teacher_group'],' OR ','teacher_group').')';
		
		$examArray=db_toArray($q_exam);
		
		if($this->input->get('exam_paper')){
			foreach($examArray as $exam){
				if($exam['exam_paper']==$this->input->get('exam_paper')){
					$currentExam=$exam;
				}
			}
			
		}elseif(count($examArray)>0){
			$currentExam=$examArray[0];
		}else{
			$currentExam=false;
		}

		$this->load->addViewData('currentExam',$currentExam);
		
		$q_partArray="
			SELECT * FROM exam_part WHERE exam_paper='".$currentExam['exam_paper']."'
		";
		
		$partArray=db_toArray($q_partArray);
		
		$this->load->addViewData('partArray', $partArray);
		
		$q_students_left="
			SELECT *
				FROM score
			WHERE score.exam_paper='".$currentExam['exam_paper']."'
			GROUP BY student
		";
		
		$r_students_left=mysql_query($q_students_left);
		$student_left=$currentExam['students']/*-db_rows($r_students_left)*/;
		
		$this->load->addViewData('student_left', $student_left);
	}
}
?>