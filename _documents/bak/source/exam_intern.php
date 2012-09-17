<?php 
if(!defined('IN_UICE'))
	exit('no permission');
	
//session变量初始化
$r_exam=mysql_query("SELECT * FROM exam WHERE name ='intern'",$db_link);//获得exam信息
$exam=mysql_fetch_array($r_exam);
	
if(!sessioned('mod',NULL,false)){//新开始页面
	$q_result="SELECT * FROM exam_result WHERE uid ='".$_SESSION['id']."' AND exam='".$exam['id']."'";
	$r_result=mysql_query($q_result,$db_link);//从result表中找此人是否已完成测试
	if(mysql_num_rows($r_result)==1){
		$result=mysql_fetch_array($r_result);
		$result['status']=unserialize($result['status']);
		$result['result']=unserialize($result['result']);
		$_SESSION['exam']['mod']=$result['status']['mod'];
		$_SESSION['exam']['status']=$result['status'];
		$_SESSION['exam']['result']=$result['result'];
	}else{
		$_SESSION['exam']['mod']='title';
	}

}

//处理各种POST信息
if(sessioned('mod','title',false) && is_posted('enterExam')){//点击开始按钮，跳到第一题
	$_SESSION['exam']['mod']='question';
	$_SESSION['exam']['result']=array();$_SESSION['exam']['status']=array();
	$_SESSION['exam']['status']['currentPart']=1;$_SESSION['exam']['status']['currentQuestion']=1;
	$_SESSION['exam']['status']['part'.$_SESSION['exam']['status']['currentPart']]['start']=time();
}

if(sessioned('mod','question',false) && is_posted('questionSubmit')){
	unset($_POST['questionSubmit']);
	if(count($_POST)==0){//检测是否没有答题
		showMessage('请答题','warning');
	}else{
		$_SESSION['exam']['result']=array_merge($_SESSION['exam']['result'],$_POST);//将本题答案并入result变量中
		$_SESSION['exam']['status']['currentQuestion']++;//下一题号

		$q_question="SELECT * FROM exam_question WHERE id_in_exam ='".$_SESSION['exam']['status']['currentQuestion']."' AND exam='".$exam['id']."' ";
		$r_question=mysql_query($q_question,$db_link);
		$question=mysql_fetch_array($r_question);//获得下一题信息
		
		if($question['part']!=$_SESSION['exam']['status']['currentPart']){//若下一题是否不属于本部分，执行本部分时间小结
			$_SESSION['exam']['status']['part'.$_SESSION['exam']['status']['currentPart']]['end']=time();
			$_SESSION['exam']['status']['part'.$_SESSION['exam']['status']['currentPart']]['time']=
			$_SESSION['exam']['status']['part'.$_SESSION['exam']['status']['currentPart']]['end']-
			$_SESSION['exam']['status']['part'.$_SESSION['exam']['status']['currentPart']]['start'];
	
			if(mysql_num_rows($r_question)==0){//如果下一题号无题目，则结束测试
				$_SESSION['exam']['mod']='end';
			}else{
				$_SESSION['exam']['status']['currentPart']=$question['part'];//如果开始新部分，则更新当前部分
				$_SESSION['exam']['status']['part'.$_SESSION['exam']['status']['currentPart']]['start']=time();
			}
		}

		$_SESSION['exam']['status']['mod']=$_SESSION['exam']['mod'];//将当前mod同步到status下的mod以便存储
		$exam_result=array(
			'exam'=>$exam['id'],
			'status'=>serialize($_SESSION['exam']['status']),
			'result'=>serialize($_SESSION['exam']['result']),
			'uid'=>$_SESSION['id'],
			'username'=>$_SESSION['username'],
			'time'=>time()
		);
		db_insert('exam_result',$exam_result,false,true);//当前全部结果插入result表
	}
}

if(sessioned('mod','end',false)){
	require 'html/exam_title.php';

}elseif(sessioned('mod','question',false)){

	$q_question="SELECT * FROM exam_question WHERE id_in_exam ='".$_SESSION['exam']['status']['currentQuestion']."' AND exam='".$exam['id']."' ";
	$r_question=mysql_query($q_question,$db_link);
	$question=mysql_fetch_array($r_question);
	
	$q_part="SELECT * FROM exam_part WHERE id_in_exam='".$_SESSION['exam']['status']['currentPart']."' AND exam='".$exam['id']."'";
	$r_part=mysql_query($q_part,$db_link);
	$part=mysql_fetch_array($r_part);
	
	$q_questions="SELECT * FROM exam_question WHERE part<='".$_SESSION['exam']['status']['currentPart']."'";
	$r_questions=mysql_query($q_questions,$db_link);
	$questions=mysql_num_rows($r_questions);
	
	require 'html/exam_question.php';
	
	}elseif(sessioned('mod','title',false)){
		require 'html/exam_title.php';
	
	}
?>