<?php
class Score extends SS_controller{
	function __construct(){
		parent::__construct();
		$this->load->model('score_model','score');
	}
	
	function index(){
		$field=array(
			'class'=>array('heading'=>array('data'=>'班级','width'=>'90px'),'cell'=>'{class_name}'),
			'name'=>array('heading'=>array('data'=>'学生','cell'=>'{name}','width'=>'50px')),
			'course_1'=>array('heading'=>'语文','cell'=>'{course_1}<br /><span class="rank">{rank_1}</span>'),
			'course_2'=>array('heading'=>'数学','cell'=>'{course_2}<br /><span class="rank">{rank_2}</span>'),
			'course_3'=>array('heading'=>'英语','cell'=>'{course_3}<br /><span class="rank">{rank_3}</span>'),
			'course_4'=>array('heading'=>'物理','cell'=>'{course_4}<br /><span class="rank">{rank_4}</span>'),
			'course_5'=>array('heading'=>'化学','cell'=>'{course_5}<br /><span class="rank">{rank_5}</span>'),
			'course_6'=>array('heading'=>'生物','cell'=>'{course_6}<br /><span class="rank">{rank_6}</span>'),
			'course_8'=>array('heading'=>'历史','cell'=>'{course_8}<br /><span class="rank">{rank_8}</span>'),
			'course_7'=>array('heading'=>'地理','cell'=>'{course_7}<br /><span class="rank">{rank_7}</span>'),
			'course_9'=>array('heading'=>'政治','cell'=>'{course_9}<br /><span class="rank">{rank_9}</span>'),
			'course_10'=>array('heading'=>'信息','cell'=>'{course_10}<br /><span class="rank">{rank_10}</span>'),
			'course_sum_3'=>array('heading'=>'3总','cell'=>'{course_sum_3}<br /><span class="rank">{rank_sum_3}</span>'),
			'course_sum_5'=>array('heading'=>'5总','cell'=>'{course_sum_5}<br /><span class="rank">{rank_sum_5}</span>')
		);
		$list=$this->table->setFields($field)
			->trimColumns()
			->generate($this->score->getList());
		$this->load->addViewData('list', $list);
		
		$field_avg=array(
			'id'=>array('heading'=>array('data'=>'','width'=>'154px'),'cell'=>'平均分'),
			'course_1'=>array('heading'=>''),
			'course_2'=>array('heading'=>''),
			'course_3'=>array('heading'=>''),
			'course_4'=>array('heading'=>''),
			'course_5'=>array('heading'=>''),
			'course_6'=>array('heading'=>''),
			'course_8'=>array('heading'=>''),
			'course_7'=>array('heading'=>''),
			'course_9'=>array('heading'=>''),
			'course_10'=>array('heading'=>''),
			'course_sum_3'=>array('heading'=>''),
			'course_sum_5'=>array('heading'=>'')
		);
		$avg=$this->table->setFields($field_avg)
			->trimColumns()
			->generate($this->score->getAvg());
		$this->load->addViewData('avg', $avg);
		
		if($this->input->post('export_to_excel')){
			model('document');
			document_exportHead('成绩.xls');
			arrayExportExcel($table);
			exit;
		}
	}

	function update(){
		set_time_limit(500);
		
		if(is_null(array_dir('_SESSION/view_score/update/step'))){
			$_SESSION['view_score']['update']['step']=1;
		}
		
		if($_SESSION['view_score']['update']['step']==1){
		#计算试卷各科总分
			//更新一下已经计算过的总分
			$this->db->query("
				UPDATE view_score,
				(
					SELECT 
						student,exam,
						course_1,course_2,course_3,course_4,course_5,course_6,course_7,course_8,course_9,course_10
					FROM
					(
						SELECT score_sum.student,score_sum.exam,score_sum.exam_paper,
							sum(if(exam_paper.course=1,score,NULL)) AS course_1,
							sum(if(exam_paper.course=2,score,NULL)) AS course_2,
							sum(if(exam_paper.course=3,score,NULL)) AS course_3,
							sum(if(exam_paper.course=4,score,NULL)) AS course_4,
							sum(if(exam_paper.course=5,score,NULL)) AS course_5,
							sum(if(exam_paper.course=6,score,NULL)) AS course_6,
							sum(if(exam_paper.course=7,score,NULL)) AS course_7,
							sum(if(exam_paper.course=8,score,NULL)) AS course_8,
							sum(if(exam_paper.course=9,score,NULL)) AS course_9,
							sum(if(exam_paper.course=10,score,NULL)) AS course_10
						FROM
						(
							SELECT student,exam,exam_paper,SUM(score) AS score 
							FROM score
							WHERE is_absent=0 AND exam IN (SELECT id FROM exam WHERE is_on=1)
							GROUP BY student,exam_paper
							#一张卷子的总分
						)score_sum
						LEFT JOIN exam_paper ON score_sum.exam_paper=exam_paper.id
						LEFT JOIN exam ON exam.id = exam_paper.exam
						GROUP BY score_sum.student,exam
					)score_result
				)score_view
				SET
				view_score.course_1=score_view.course_1,view_score.course_2=score_view.course_2,
				view_score.course_3=score_view.course_3,view_score.course_4=score_view.course_4,
				view_score.course_5=score_view.course_5,view_score.course_6=score_view.course_6,
				view_score.course_7=score_view.course_7,view_score.course_8=score_view.course_8,
				view_score.course_9=score_view.course_9,view_score.course_10=score_view.course_10,
				view_score.time='{time()}'
				WHERE view_score.student=score_view.student AND view_score.exam=score_view.exam
			");
			
			//尝试插入新登的分数（不替换现有分数）
			$this->db->query("
				INSERT IGNORE INTO view_score (student,extra_course,exam,exam_name,course_1,course_2,course_3,course_4,course_5,course_6,course_7,course_8,course_9,course_10,time)
				SELECT 
					student,extra_course,exam,exam_name,
					course_1,course_2,course_3,course_4,course_5,course_6,course_7,course_8,course_9,course_10,'{time()}'
				FROM
				(
					SELECT score_sum.student,student.extra_course,score_sum.exam,score_sum.exam_paper,exam.name AS exam_name,
						sum(if(exam_paper.course=1,score,NULL)) AS course_1,
						sum(if(exam_paper.course=2,score,NULL)) AS course_2,
						sum(if(exam_paper.course=3,score,NULL)) AS course_3,
						sum(if(exam_paper.course=4,score,NULL)) AS course_4,
						sum(if(exam_paper.course=5,score,NULL)) AS course_5,
						sum(if(exam_paper.course=6,score,NULL)) AS course_6,
						sum(if(exam_paper.course=7,score,NULL)) AS course_7,
						sum(if(exam_paper.course=8,score,NULL)) AS course_8,
						sum(if(exam_paper.course=9,score,NULL)) AS course_9,
						sum(if(exam_paper.course=10,score,NULL)) AS course_10
					FROM
					(
						SELECT student,exam,exam_paper,SUM(score) AS score 
						FROM score
						WHERE is_absent=0 AND exam IN (SELECT id FROM exam WHERE is_on=1)
						GROUP BY student,exam_paper
						#一张卷子的总分
					)score_sum
					INNER JOIN exam_paper ON score_sum.exam_paper=exam_paper.id
					INNER JOIN exam ON exam_paper.exam=exam.id
					INNER JOIN student ON student.id=score_sum.student
					GROUP BY score_sum.student,exam
				)score_result
			");
			echo ('各科总分计算完成');
		}
		
		if($_SESSION['view_score']['update']['step']==2){
			#三门总分
			mysql_query("
				UPDATE school_view_score SET 3总 = if((`语文`*`数学`*`英语`) IS NOT NULL,`语文`+`数学`+`英语`,NULL)
			");
			
			#五门总分
			mysql_query("
				UPDATE school_view_score SET 3总 = if((`语文`*`数学`*`英语`*`物理`*`化学`) IS NOT NULL,
				`语文`+`数学`+`英语`+`物理`+`化学`,NULL) WHERE extra_course IS NULL
			");
			
			mysql_query("
				UPDATE school_view_score SET 5总=
				if(
					(
						`语文`*`数学`*`英语`*
						if(extra_course=4,`物理`,
							if(extra_course=5,`化学`,
								if(extra_course=6,`生物`,
									if(extra_course=7,`地理`,
										if(extra_course=8,`历史`,
											if(extra_course=9,`政治`,NULL)
										)
									)
								)
							)
						)
					) IS NOT NULL,
					`语文`+`数学`+`英语`+
					if(extra_course=4,`物理`,
						if(extra_course=5,`化学`,
							if(extra_course=6,`生物`,
								if(extra_course=7,`地理`,
									if(extra_course=8,`历史`,
										if(extra_course=9,`政治`,NULL)
									)
								)
							)
						)
					),
					NULL
				)
				WHERE extra_course IS NOT NULL
			");
			echo ('总分计算完成');
		}
		
		if($_SESSION['view_score']['update']['step']>=3 && $_SESSION['view_score']['update']['step']<=14){
			if($_SESSION['view_score']['update']['step']==3)
				$p='sum_3';
			elseif($_SESSION['view_score']['update']['step']==4)
				$p='sum_5';
			else
				$p=$_SESSION['view_score']['update']['step']-4;
			
			mysql_query("
				UPDATE (
						SELECT id, (
								SELECT COUNT( 1 ) 
								FROM
						school_view_score
								WHERE `5总` > a.`5总`
						AND exam=a.exam -- AND extra_course = a.extra_course
						) +1 AS rank
						FROM school_view_score a
						WHERE `5总` IS NOT NULL AND exam>=1567
				)t
				LEFT JOIN school_view_score ON school_view_score.id=t.id
				SET school_view_score.`rank_5总` = t.rank
			");
			echo ('学科'.$p.'的排名计算完成');
			
		}
		
		if($_SESSION['view_score']['update']['step']==15){
			unset($_SESSION['view_score']['update']);
			redirect('score','js');
		}else{
			$_SESSION['view_score']['update']['step']++;
			redirect('score/update','js');
		}
	}

	function byType(){
		$q="
		SELECT
			type,
			count(*) AS amount,
			round(avg(course_1),2) AS course_1,
			round(avg(course_2),2) AS course_2,
			round(avg(course_3),2) AS course_3,
			round(avg(course_4),2) AS course_4,
			round(avg(course_5),2) AS course_5,
			round(avg(course_8),2) AS course_8,
			round(avg(course_7),2) AS course_7,
			round(avg(course_sum_3),2) AS course_sum_3,
			round(avg(course_sum_5),2) AS course_sum_5  
		FROM view_score INNER JOIN view_student ON view_student.id=view_score.student
		WHERE 1=1";
		
		$rangeMenu=processRange($q,array('grade'=>'view_student.grade'));
		
		$q.=' GROUP BY view_student.type';
		
		$this->processOrderby($q,'student_num');
		
		$field=array(
			'type'=>array('heading'=>array('data'=>'分类','width'=>'112px')),
			'amount'=>'人数',
			'course_1'=>'语文',
			'course_2'=>'数学',
			'course_3'=>'英语',
			'course_4'=>'物理',
			'course_5'=>'化学',
			'course_8'=>'历史',
			'course_7'=>'政治',
			'course_sum_3'=>'3总',
			'course_sum_5'=>'5总'
		);
		
		$menu=array(
			'head'=>'<div class="right">'.
						$rangeMenu.
					'</div>'
		);
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table','menu');
		
		$this->load->view('lists',$this->view_data);
	}
	
	function rankRange(){
		$q_sum_3="
		SELECT class,class_name,
			SUM(IF(rank_sum_3>=1 AND rank_sum_3<=50,1,0)) AS top_50,
			SUM(IF(rank_sum_3>=51 AND rank_sum_3<=100,1,0)) AS top_100,
			SUM(IF(rank_sum_3>=101 AND rank_sum_3<=200,1,0)) AS top_200,
			SUM(IF(rank_sum_3>=201 AND rank_sum_3<=300,1,0)) AS top_300,
			SUM(IF(rank_sum_3>=301 AND rank_sum_3<=400,1,0)) AS top_400,
			SUM(IF(rank_sum_3>=401,1,0)) AS top_rest
		FROM `view_score` 
		WHERE 1=1
		";
		
		$rangeMenu=processRange($q_sum_3,array('grade'=>'grade'));
		
		$q_sum_3.=' GROUP BY class';
		
		$this->processOrderby($q_sum_3,'class');
		
		$field=array(
			'class'=>array('heading'=>array('data'=>'班级','width'=>'112px'),'cell'=>'{class_name}'),
			'top_50'=>'1~50',
			'top_100'=>'51~100',
			'top_200'=>'101~200',
			'top_300'=>'201~300',
			'top_400'=>'301~400',
			'top_rest'=>'400+',
		);
		
		$menu_sum_3=array(
			'head'=>'<div class="left">'.
						'3门总分'.
					'</div>'.
					'<div class="right">'.
						$rangeMenu.
					'</div>',
		);
		
		$q_sum_5="
		SELECT class,class_name,
			SUM(IF(rank_sum_5>=1 AND rank_sum_5<=50,1,0)) AS top_50,
			SUM(IF(rank_sum_5>=51 AND rank_sum_5<=100,1,0)) AS top_100,
			SUM(IF(rank_sum_5>=101 AND rank_sum_5<=200,1,0)) AS top_200,
			SUM(IF(rank_sum_5>=201 AND rank_sum_5<=300,1,0)) AS top_300,
			SUM(IF(rank_sum_5>=301 AND rank_sum_5<=400,1,0)) AS top_400,
			SUM(IF(rank_sum_5>=401,1,0)) AS top_rest
		FROM `view_score` 
		WHERE 1=1
		";
		
		processRange($q_sum_5,array('grade'=>'grade'));
		
		$q_sum_5.=' GROUP BY class';
		
		$this->processOrderby($q_sum_5,'class',NULL,array(),false);
		
		$menu_sum_5=array(
			'head'=>'<div style="float:left;margin-top:20px;">'.
						'5门总分'.
						$rangeMenu.
					'</div>'
		);
		
/*
		exportTable($q_sum_3,$field,$menu_sum_3,true);
		
		exportTable($q_sum_5,$field,$menu_sum_5,true);
*/
	}
	
	//阅卷结果（分数文件）上传
	function uploadBoard(){
		$this->load->model('exam_model','exam');
		$examArray=$this->exam->getInfoList();

		$this->load->addViewData('examArray',$examArray);
		
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

		$this->load->addViewData('currentExam',$currentExam);
		
		while($submit=='score_table'){
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
				$this->db->insert('exam_part',$exam_part_data);
				$exam_part_array[]=$this->db->insert_id();
			}
			
			//创建一张临时表
			$q_create_temp_table="CREATE TEMPORARY TABLE `t` (`id` INT NOT NULL AUTO_INCREMENT, `num` CHAR( 6 ) NOT NULL,";
			
			foreach($exam_part_array as $exam_part){
				$q_create_temp_table.="`".$exam_part."` DECIMAL( 10, 1 ) NULL,";
			}
			
			$q_create_temp_table.=" PRIMARY KEY (`id`) ,UNIQUE (`num`))";
			
			$this->db->query($q_create_temp_table);
			
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
			if(!$this->db->query($q_insert_t_score)){
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
		
			$r_search_illegal_student=$this->db->query($q_search_illegal_student);
		
			if(/*db_rows($r_search_illegal_student)==*/1){
				$illegal_line=db_fetch_array($r_search_illegal_student);
				showMessage(($illegal_line['id']+1).'行的"'.$illegal_line['num'].'"学号不正确，可能填写错误或学生不属于本场考试','warning');
				break;
			}
			
			foreach($exam_part_array as $exam_part){
				//插入分数
				$q_insert_score="
				REPLACE INTO score (student,exam,exam_paper,exam_part,score,is_absent,scorer,scorer_username,time)
				SELECT view_student.id,'".$currentExam['exam']."','".$currentExam['exam_paper']."','".$exam_part."',t.`".$exam_part."`,if(t.`".$exam_part."` IS NULL,1,0),{$this->user->id},'".$_SESSION['username']."','".time()."'  
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
				'scorer'=>$this->user->id,
				'scorer_username'=>$_SESSION['username'],
				'time'=>time()
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