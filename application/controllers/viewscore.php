<?php
class ViewScore extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="SELECT * FROM view_score INNER JOIN view_student ON view_student.id=view_score.student WHERE 1=1";
		
		if(!option('class') && !option('grade')){
			$manage_class=db_fetch_first("SELECT id,grade FROM class WHERE class_teacher='".$_SESSION['id']."'");
			if($manage_class){
				//将班主任的视图定位到自己班级
				option('class',$manage_class['id']);
				option('grade',$manage_class['grade']);
			}else{
				//默认显示的年级
				option('grade',$_SESSION['global']['highest_grade']);
			}
		}
		
		if(!option('exam')){
			option('exam',db_fetch_field("SELECT id FROM exam WHERE grade='".option('grade')."' ORDER BY id DESC LIMIT 1"));
		}
		
		addCondition($q,array('class'=>'view_student.class','grade'=>'view_student.grade','exam'=>'view_score.exam'),array('grade'=>'class'));
		
		$search_bar=processSearch($q,array('view_student.name'=>'学生'));
		
		processOrderby($q,'view_student.num');
		
		$field=array(
			'class'=>array('title'=>'班级','td_title'=>'width=103px','content'=>'{class_name}'),
			'name'=>array('title'=>'学生','content'=>'{name}','td_title'=>'width=61px'),
			'course_1'=>array('title'=>'语文','content'=>'{course_1}<br /><span class="rank">{rank_1}</span>'),
			'course_2'=>array('title'=>'数学','content'=>'{course_2}<br /><span class="rank">{rank_2}</span>'),
			'course_3'=>array('title'=>'英语','content'=>'{course_3}<br /><span class="rank">{rank_3}</span>'),
			'course_4'=>array('title'=>'物理','content'=>'{course_4}<br /><span class="rank">{rank_4}</span>'),
			'course_5'=>array('title'=>'化学','content'=>'{course_5}<br /><span class="rank">{rank_5}</span>'),
			'course_8'=>array('title'=>'历史','content'=>'{course_8}<br /><span class="rank">{rank_8}</span>'),
			'course_7'=>array('title'=>'地理','content'=>'{course_7}<br /><span class="rank">{rank_7}</span>'),
			'course_9'=>array('title'=>'政治','content'=>'{course_9}<br /><span class="rank">{rank_9}</span>'),
			'course_10'=>array('title'=>'信息','content'=>'{course_10}<br /><span class="rank">{rank_10}</span>'),
			'course_sum_3'=>array('title'=>'3总','content'=>'{course_sum_3}<br /><span class="rank">{rank_sum_3}</span>'),
			'course_sum_5'=>array('title'=>'5总','content'=>'{course_sum_5}<br /><span class="rank">{rank_sum_5}</span>')
		);
		
		$q_avg="
		SELECT *,
			ROUND(AVG(course_1),2) AS course_1,
			ROUND(AVG(course_2),2) AS course_2,
			ROUND(AVG(course_3),2) AS course_3,
			ROUND(AVG(course_4),2) AS course_4,
			ROUND(AVG(course_5),2) AS course_5,
			ROUND(AVG(course_8),2) AS course_8,
			ROUND(AVG(course_7),2) AS course_7,
			ROUND(AVG(course_9),2) AS course_9,
			ROUND(AVG(course_10),2) AS course_10,
			ROUND(AVG(course_sum_3),2) AS course_sum_3,
			ROUND(AVG(course_sum_5),2) AS course_sum_5
		FROM view_score INNER JOIN view_student ON view_student.id=view_score.student
		WHERE exam IN (SELECT id FROM exam WHERE is_on=1)";
		
		addCondition($q_avg,array('class'=>'view_student.class','grade'=>'view_student.grade','exam'=>'view_score.exam'),array('grade'=>'class'));
		
		$field_avg=array(
			'id'=>array('td_title'=>'width="204px"','content'=>'平均分'),
			'course_1'=>'',
			'course_2'=>'',
			'course_3'=>'',
			'course_4'=>'',
			'course_5'=>'',
			'course_8'=>'',
			'course_7'=>'',
			'course_9'=>'',
			'course_10'=>'',
			'course_sum_3'=>'',
			'course_sum_5'=>''
		);
		
		if(is_posted('export_to_excel')){
			$field=array(
				'class'=>array('title'=>'班级','content'=>'{class_name}'),
				'student_num'=>array('title'=>'学生','content'=>'{student_name}'),
				'course_1'=>array('title'=>'语文','content'=>'{course_1}'),
				'course_2'=>array('title'=>'数学','content'=>'{course_2}'),
				'course_3'=>array('title'=>'英语','content'=>'{course_3}'),
				'course_4'=>array('title'=>'物理','content'=>'{course_4}'),
				'course_5'=>array('title'=>'化学','content'=>'{course_5}'),
				'course_8'=>array('title'=>'历史','content'=>'{course_8}'),
				'course_7'=>array('title'=>'政治','content'=>'{course_7}'),
				'course_9'=>array('title'=>'地理','content'=>'{course_9}'),
				'course_10'=>array('title'=>'信息','content'=>'{course_10}'),
				'course_sum_3'=>array('title'=>'3总','content'=>'{course_sum_3}'),
				'course_sum_5'=>array('title'=>'5总','content'=>'{course_sum_5}')
			);
		}else{
			$listLocator=processMultipage($q);
		}
		
		$table=fetchTableArray($q,$field);
		
		if(is_posted('export_to_excel')){
			model('document');
			document_exportHead('成绩.xls');
			arrayExportExcel($table);
			exit;
		
		}else{
			$menu=array(
			'head'=>'<div class="left">'.
						'<button type="button" onclick="post(\'updateScore\',true)">更新</button>'.
						'<button type="button" onclick="post(\'export_to_excel\',true)" disabled="disabled" title="本功能将于近期开放">导出</button>'.
					'</div>'.
					'<div class="right">'.
						$listLocator.
					'</div>'
			);
		}
	}

	function update(){
		set_time_limit (500);
		
		if(is_null(array_dir('_SESSION/view_score/update/step'))){
			$_SESSION['view_score']['update']['step']=1;
		}
		
		if($_SESSION['view_score']['update']['step']==1){
		#计算试卷各科总分
			//更新一下已经计算过的总分
			mysql_query("
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
						GROUP BY score_sum.student,exam
					)score_result
				)score_view
				SET 
				view_score.course_1=score_view.course_1,view_score.course_2=score_view.course_2,
				view_score.course_3=score_view.course_3,view_score.course_4=score_view.course_4,
				view_score.course_5=score_view.course_5,view_score.course_6=score_view.course_6,
				view_score.course_7=score_view.course_7,view_score.course_8=score_view.course_8,
				view_score.course_9=score_view.course_9,view_score.course_10=score_view.course_10,
				view_score.time=UNIX_TIMESTAMP()
				WHERE view_score.student=score_view.student AND view_score.exam=score_view.exam
			");
			
			//尝试插入新登的分数（不替换现有分数）
			mysql_query("
				INSERT IGNORE INTO view_score (student,extra_course,exam,course_1,course_2,course_3,course_4,course_5,course_6,course_7,course_8,course_9,course_10,time)
				SELECT 
					student,extra_course,exam,
					course_1,course_2,course_3,course_4,course_5,course_6,course_7,course_8,course_9,course_10,'".$_G['timestamp']."'
				FROM
				(
					SELECT score_sum.student,student.extra_course,score_sum.exam,score_sum.exam_paper,
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
						INNER JOIN student ON student.id=score_sum.student
					GROUP BY score_sum.student,exam
				)score_result
			");
			showMessage('各科总分计算完成');
		}
		
		if($_SESSION['view_score']['update']['step']==2){
			#三门总分
			mysql_query("
			UPDATE view_score SET course_sum_3 = if((course_1*course_2*course_3) IS NOT NULL,course_1+course_2+course_3,NULL) WHERE exam IN (SELECT id FROM exam WHERE is_on=1)
			");
			
			#五门总分
			mysql_query("
			UPDATE view_score SET course_sum_5 = if((course_1*course_2*course_3*course_4*course_5) IS NOT NULL,
			course_1+course_2+course_3+course_4+course_5,NULL) WHERE extra_course IS NULL AND exam IN (SELECT id FROM exam WHERE is_on=1)
			");
			
			mysql_query("
			UPDATE view_score SET course_sum_5=
			if(
				(
					course_1*course_2*course_3*
					if(extra_course=4,course_4,
						if(extra_course=5,course_5,
							if(extra_course=6,course_6,
								if(extra_course=7,course_7,
									if(extra_course=8,course_8,NULL)
								)
							)
						)
					)
				) IS NOT NULL,
				course_1+course_2+course_3+
				if(extra_course=4,course_4,
					if(extra_course=5,course_5,
						if(extra_course=6,course_6,
							if(extra_course=7,course_7,
								if(extra_course=8,course_8,NULL)
							)
						)
					)
				),
				NULL
			)
			WHERE extra_course<>0 AND exam IN (SELECT id FROM exam WHERE is_on=1)
			");
			showMessage('总分计算完成');
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
				view_score
					WHERE course_".$p." > a.course_".$p."
				AND exam=a.exam
				) +1 AS rank
				FROM view_score a
				WHERE course_".$p." IS NOT NULL AND exam IN (SELECT id FROM exam WHERE is_on=1)
			)t
			LEFT JOIN view_score ON view_score.id=t.id
			SET view_score.rank_".$p." = t.rank
			
			");
			showMessage('学科'.$p.'的排名计算完成');
			
		}
		
		if($_SESSION['view_score']['update']['step']==15){
			unset($_SESSION['view_score']['update']);
			redirect('view_score.php','js');
		}else{
			$_SESSION['view_score']['update']['step']++;
			redirect('view_score.php?update','js');
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
		
		processOrderby($q,'student_num');
		
		$field=array(
			'type'=>array('title'=>'分类','td_title'=>'width=112px'),
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
		
		exportTable($q,$field,$menu,true);
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
		
		processOrderby($q_sum_3,'class');
		
		$field=array(
			'class'=>array('title'=>'班级','td_title'=>'width=112px','content'=>'{class_name}'),
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
		
		processOrderby($q_sum_5,'class',NULL,array(),false);
		
		$menu_sum_5=array(
			'head'=>'<div style="float:left;margin-top:20px;">'.
						'5门总分'.
						$rangeMenu.
					'</div>'
		);
		
		exportTable($q_sum_3,$field,$menu_sum_3,true);
		
		exportTable($q_sum_5,$field,$menu_sum_5,true);
	}
}
?>
