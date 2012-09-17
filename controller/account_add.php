<?php
model('client');
model('case');

getPostData(function(){
	global $_G;
	if(got('case')){
		post(IN_UICE.'/case',intval($_GET['case']));
	}
	if(got('client')){
		post(IN_UICE.'/client',intval($_GET['client']));
	}

	post('account/name','律师费');
	post('account/time_occur',$_G['timestamp']);
});

//转换时间
post('account_extra/time_occur',date('Y-m-d',post('account/time_occur')));

if(got('case')){
	post('account/case',intval($_GET['case']));
}

$submitable=false;//可提交性，false则显示form，true则可以跳转

if(is_posted('submit')){
	$submitable=true;
	
	$_SESSION['account']['post']=array_replace_recursive($_SESSION['account']['post'],$_POST);
	
	if(is_posted('submit/recognizeOldClient')){
		$client_check=client_check(post('account_extra/client_name'),'array');

		if($client_check<0){
			$submitable=false;
		}else{
			post('account/client',$client_check['id']);
			post('account_extra/client_name',$client_check['name']);
			showMessage('已经识别为客户：'.$client_check['name']);
		}
	}
	//响应"识别"按钮

	if(post('account/name')==''){
		$submitable=false;
		showMessage('请填写名目','warning');
	}
	
	if(!strtotime(post('account_extra/time_occur'))){
		$submitable=false;
		showMessage('时间格式错误','warning');
	}else{
		post('account/time_occur',strtotime(post('account_extra/time_occur')));
	}
	
	if(post('account_extra/type')==1){
		post('account/amount',-post('account/amount'));
	}
	//根据type设置amount的正负号

	post('account/case',case_getIdByCaseFee(post('account/case_fee')));
	//根据提交的case_fee先找出case.id

	post('account',array_trim(post('account')));//imperfect 2012/5/25 uicestone 为了让没有case_fee 和case的account能够保存
	
	processSubmit($submitable);
}

if(post('account/client')){
	//若有客户，则获得相关客户的名称
	post('account_extra/client_name',client_fetch(post('account/client'),'name'));

	//根据客户ID获得收费array
	$case_fee_array=case_getFeeListByClient(post('account/client'));
}

if(post('account/case')){
	//指定案件时，根据案件id获得客户array
	$case_client_array=client_getListByCase(post('account/case'));

	//根据案件ID获得收费array
	$case_fee_array=case_getFeeOptions(post('account/case'));
}
?>