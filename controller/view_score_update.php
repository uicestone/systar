<?php
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
?>