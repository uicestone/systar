<?php
class Score extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	//阅卷结果（分数文件）上传
	function index(){
		$q_exam="
		SELECT 
			exam.id AS exam,exam.name AS name,
			exam_paper.id AS exam_paper,exam_paper.is_extra_course AS is_extra_course,
			if(exam_paper.is_extra_course,course.id,NULL) AS extra_course,
			grade.name AS grade_name,course.id AS course,course.name AS course_name,
			exam_paper.students AS students, exam_paper.teacher_group AS teacher_group 
		FROM 
			exam_paper 
			INNER JOIN exam ON (exam_paper.exam=exam.id)
			INNER JOIN course ON exam_paper.course=course.id
			INNER JOIN grade ON exam.grade=grade.id
		WHERE  exam_paper.is_scoring=1 
			AND exam.is_on=1
			AND (".db_implode($_SESSION['teacher_group'],' OR ','teacher_group').')';
		
		$examArray=db_toArray($q_exam);
		
		if($this->input->get('exam_paper')){
			foreach($examArray as $exam){
				if($exam['exam_paper']==intval($this->input->get('exam_paper'))){
					$currentExam=$exam;
				}
			}
			
		}elseif(!empty($examArray)>0){
			$currentExam=$examArray[0];
		
		}else{
			$currentExam=false;
		}
		
		while(is_posted('submit/score_table')){
			if ($_FILES['score_table']['error'] > 0){
				showMessage('文件上错错误：错误代码: '.$_FILES['score_table']['error'],'warning');break;
			}
			
			if(!(preg_match('/\.(\w*?)$/',$_FILES['score_table']['name'],$extname_match) && $extname_match[1]=='xls')){
				showMessage('文件格式错误，请上传xls格式的excel表格','warning');break;
			}
		
			require APPPATH.'third_party/PHP-ExcelReader/reader.php';
			$data = new Spreadsheet_Excel_Reader();
			$data->setOutputEncoding('utf-8');
			$data->setRowColOffset(0);
		
			$data->read($_FILES['score_table']['tmp_name']);
			
			$rows=$data->sheets[0]['numRows'];
			$cols=$data->sheets[0]['numCols'];
			
			$exam_part_array=array();
		
			$break=false;
			for($i=1;$i<$cols;$i++){
				if($data->sheets[0]['cells'][0][$i]=='' || is_numeric($data->sheets[0]['cells'][0][$i])){
					showMessage('某大题的名字是空的或是数字','warning');
					$break=true;break;
				}
				$exam_part_data_array[]=array('exam_paper'=>$currentExam['exam_paper'],'name'=>$data->sheets[0]['cells'][0][$i]);
			}
			if($break)break;
		
			for($i=1;$i<$rows;$i++){
				for($j=1;$j<$cols;$j++){
					$cell = isset($data->sheets[0]['cells'][$i][$j])?$data->sheets[0]['cells'][$i][$j]:'';
					if(!(is_numeric($cell) || $cell=='') || $cell<0){
						showMessage('第'.($i+1).'行 第'.($j+1).'列的数据'.$cell.'中包含错误字符，注意只能是数字或留空（缺考）','warning');
						$break=true;break;
					}
				}
				if($break)break;
				if(array_sum(array_slice($data->sheets[0]['cells'][$i],1))>150){
					showMessage('第'.$i.'行的小分和超过了150分！注意不用填写总分','warning');
					$break=true;break;
				}
			}
			if($break)break;
		
			if($rows-1<$currentExam['students']){
				//showMessage('本张试卷有'.$currentExam['students'].'人参考，上传的分数为'.($rows-1).'条，请检核数据重新上传','warning');break;
			}
			
			foreach($exam_part_data_array as $exam_part_data){
				//插入大题
				$exam_part_array[]=db_insert('exam_part',$exam_part_data);
			}
			
			//创建一张临时表
			$q_create_temp_table="CREATE TEMPORARY TABLE `t` (`id` INT NOT NULL AUTO_INCREMENT, `num` CHAR( 6 ) NOT NULL,";
			
			foreach($exam_part_array as $exam_part){
				$q_create_temp_table.="`".$exam_part."` DECIMAL( 10, 1 ) NULL,";
			}
			
			$q_create_temp_table.=" PRIMARY KEY (`id`) ,UNIQUE (`num`))";
			
			db_query($q_create_temp_table);
			
			//excel表格上传到临时表
			$q_insert_t_score='INSERT INTO t (num,`'.implode('`,`',$exam_part_array).'`) VALUES';
			for($i=1; $i<$rows; $i++) {
				$q_insert_t_score.="('".$data->sheets[0]['cells'][$i][0]."'";
				for($j=1; $j<$cols; $j++) {
					$cell = isset($data->sheets[0]['cells'][$i][$j])?$data->sheets[0]['cells'][$i][$j]:'';
					$q_insert_t_score.=",".($cell==''?'NULL':"'".$cell."'")."";
				}
				$q_insert_t_score.=')';
				if($i!=$rows-1){
					$q_insert_t_score.=',';
				}
			}
			if(!db_query($q_insert_t_score)){
				showMessage('上传错误，可能有重复学号或者错误的学号','warning');
				break;
			}
		
			$q_search_illegal_student="
				SELECT id,num FROM t WHERE num NOT IN
				(
					SELECT view_student.num 
					FROM exam_student INNER JOIN view_student ON view_student.id=exam_student.student
					WHERE exam_student.exam='".$currentExam['exam']."'".(isset($currentExam['extra_course'])?" AND exam_student.extra_course='".$currentExam['extra_course']."'":'')."
				)
				LIMIT 1
			";
		
			$r_search_illegal_student=db_query($q_search_illegal_student);
		
			if(db_rows($r_search_illegal_student)==1){
				$illegal_line=db_fetch_array($r_search_illegal_student);
				showMessage(($illegal_line['id']+1).'行的"'.$illegal_line['num'].'"学号不正确，可能填写错误或学生不属于本场考试','warning');
				break;
			}
			
			foreach($exam_part_array as $exam_part){
				//插入分数
				$q_insert_score="
				REPLACE INTO score (student,exam,exam_paper,exam_part,score,is_absent,scorer,scorer_username,time)
				SELECT view_student.id,'".$currentExam['exam']."','".$currentExam['exam_paper']."','".$exam_part."',t.`".$exam_part."`,if(t.`".$exam_part."` IS NULL,1,0),'".$_SESSION['id']."','".$_SESSION['username']."','".$this->config->item('timestamp')."'  
				FROM t INNER JOIN view_student ON t.num=view_student.num
				";
				mysql_query($q_insert_score);
			}
			
			showMessage('文件上传成功！');break;
		}
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
				'scorer'=>$_SESSION['id'],
				'scorer_username'=>$_SESSION['username'],
				'time'=>$this->config->item('timestamp')
			);
			
			if($this->input->post('score')!=$_SESSION['score']['currentScore']['score'] || isset($this->input->post('is_absent'))!=$_SESSION['score']['currentScore']['is_absent'])
				db_insert('score',$scoreData,false,true);//当前学生-大题-分数插入数据表，不返回insertid，使用replace
			
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
		if(db_rows($r_score)==0){
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
		
		
		$q_partArray="
			SELECT * FROM exam_part WHERE exam_paper='".$currentExam['exam_paper']."'
		";
		
		$partArray=db_toArray($q_partArray);
		
		$q_students_left="
			SELECT *
				FROM score
			WHERE score.exam_paper='".$currentExam['exam_paper']."'
			GROUP BY student
		";
		
		$r_students_left=mysql_query($q_students_left);
		$student_left=$currentExam['students']-db_rows($r_students_left);
	}
}
?>