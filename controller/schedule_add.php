<?php
model('case');
model('client');

getPostData(function(){
	if(got('case')){
		post('schedule/case',intval($_GET['case']));
	}
	if(got('client')){
		post('schedule/client',intval($_GET['client']));
	}

	if(got('completed')){
		post('schedule/completed',(int)(bool)$_GET['completed']);

	}else{
		post('schedule/completed',1);//默认插入的是日志，不是提醒
	}
});


if(!post('schedule/time_start')){
	post('schedule/time_start',$_G['timestamp']);
	post('schedule/time_end',$_G['timestamp']+3600);
}

$submitable=false;//可提交性，false则显示form，true则可以跳转

if(is_posted('submit')){
	$submitable=true;
	
	$_SESSION['schedule']['post']=array_replace_recursive($_SESSION['schedule']['post'],$_POST);
	
	if(array_dir('_POST/schedule/name')==''){
		$submitable=false;
		showMessage('请填写日志名称','warning');
	}
	
	if(post('schedule/case')>10 && post('schedule/case')<=20 && !post('schedule/client')){
		$submitable=false;
		showMessage('没有选择客户','warning');
	}
	
	if(!strtotime(post('schedule/time_start'))){
		$submitable=false;
		showMessage('开始时间格式错误','warning');
	}else{
		post('schedule/time_start',strtotime(post('schedule/time_start')));
	}

	post('schedule/time_end',post('schedule/time_start')+post('schedule/hours_own')*3600);
	
	if($_FILES['file']['name']){
		$storePath=iconv("utf-8","gbk",$_G['case_document_path']."/".$_FILES["file"]["name"]);//存储路径转码
		
		move_uploaded_file($_FILES['file']['tmp_name'], $storePath);
	
		if(preg_match('/\.(\w*?)$/',$_FILES['file']['name'], $extname_match)){
			$_FILES['file']['type']=$extname_match[1];
		}else{
			$_FILES["file"]["type"]='none';
		}
		
		$fileInfo=array(
			'name'=>$_FILES["file"]["name"],
			'type'=>$_FILES["file"]["type"],
			'doctype'=>post('case_document/doctype'),
			'size'=>$_FILES["file"]['size'],
			'comment'=>post('case_document/comment'),
		);
		
		if(post('schedule/case')){
			if(!post('schedule/document',case_addDocument(post('schedule/case'),$fileInfo))){
				$submitable=false;
			}
		}

		rename(iconv("utf-8","gbk",$_G['case_document_path']."/".$_FILES["file"]["name"]),iconv("utf-8","gbk",$_G['case_document_path']."/".post('schedule/document')));

		unset($_SESSION['case']['post']['case_document']);
	}
	
	if(post('schedule/document')){
		db_update('case_document',post('case_document'),"id='".post('schedule/document')."'");
	}

	processSubmit($submitable);
}

//为scheduleType的Radio准备值
if(post('schedule/case')<=10 && post('schedule/case')>0){
	post('schedule_extra/type',1);

}elseif(post('schedule/case')>10 && post('schedule/case')<20){
	post('schedule_extra/type',2);

}else{
	post('schedule_extra/type',0);

}

//准备案件数组
$case_array=case_getListByScheduleType(post('schedule_extra/type'));

//准备客户数组
$client_array=client_getListByCase(post('schedule/case'));

//获得案名
$q_case="SELECT name FROM `case` WHERE id='".post('schedule/case')."'";
post('schedule_extra/case_name',db_fetch_field($q_case));

if(post('schedule/client')){
	$q_client="SELECT abbreviation FROM client WHERE id = '".post('schedule/client')."'";
	post('schedule_extra/client_name',db_fetch_field($q_client));	
}

if(post('schedule/document')){
	post('case_document',db_fetch_first("SELECT name,doctype,comment FROM case_document WHERE id = '".post('schedule/document')."'"));
}
?>