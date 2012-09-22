<?php 
model('staff');
model('client');
model('schedule');

getPostData(function(){
	global $_G;
	
	post('case/time_contract',$_G['date']);
	post('case/time_end',date('Y-m-d',$_G['timestamp']+100*86400));
	//默认签约时间和结案时间

	post('case_client_extra/show_add_form',true);
	post('case_lawyer_extra/show_add_form',true);
});

$case_role=case_getRoles(post('case/id'));

$responsible_partner=case_getPartner($case_role);
//获得本案督办合伙人的id

$lawyers=case_getLawyers($case_role);
//获得本案办案人员的id

$my_roles=case_getMyRoles($case_role);
//本人的本案职位

$submitable=false;

if(is_posted('submit')){
	$_SESSION['case']['post']=array_replace_recursive($_SESSION['case']['post'],$_POST);
	$submitable=true;
	
	if(is_posted('submit/case_client')){
		if(!is_posted('case_client_extra/character')){//[单位]不打钩则删除session对应变量
			unset($_SESSION[IN_UICE]['post']['case_client_extra']['character']);
		}
		$client_check=client_check(post('case_client_extra/name'),'array');
		if($client_check==-1){//如果case_client添加的客户不存在，则先添加客户
			$new_client=array(
				'name'=>post('case_client_extra/name'),
				'character'=>post('case_client_extra/character')=='单位'?'单位':'自然人',
				'classification'=>post('case_client_extra/classification'),
				'type'=>post('case_client_extra/type')
			);
		
			if(post('case_client_extra/classification')=='客户'){//客户必须输入来源
				$client_source=client_setSource(post('case_client_extra/source_type'),post('case_client_extra/source_detail'));
				
				$staff_check=staff_check(post('case_client_extra/source_lawyer_name'),'id',false);
				
				if($staff_check<0){
					showMessage('请输入正确的来源律师','warning');
				}
	
				if($staff_check>0 && $client_source>0){
					$new_client['source_lawyer']=$staff_check;
					$new_client['source']=$client_source;
					if(!post('case/source')){
						post('case/source',$client_source);
					}
				}else{
					$submitable=false;
				}
			}else{//非客户必须输入工作单位
				if(post('case_client_extra/work_for')){
					$new_client['work_for']=post('case_client_extra/work_for');
				}else{
					showMessage('请输入工作单位','warning');
					$submitable=false;
				}
			}
			
			if($submitable && $new_client_id=client_add($new_client)){
				post('case_client/client',$new_client_id);
	
				client_addContact_phone_email(post('case_client/client'),post('case_client_extra/phone'),post('case_client_extra/email'));
	
				showMessage(
					'<a href="javascript:showWindow(\''.
					(post('case_client_extra/classification')=='客户'?'client':'contact').
					'?edit='.post('case_client/client').'\')" target="_blank">新'.
					post('case_client_extra/classification').' '.post('case_client_extra/name').
					' 已经添加，点击编辑详细信息</a>',
				'notice');
			}

		}elseif($client_check>0){
			post('case_client/client',$client_check['id']);
			if(!post('case/source')){
				post('case/source',$client_check['source']);
			}
			showMessage('系统中已经存在 '.$client_check['name'].'，已自动识别');
		}else{
			//除了不存在意外的其他错误，如关键字多个匹配
			$submitable=false;
		}

		if($submitable && case_addClient(post('case/id'),post('case_client/client'),post('case_client/role'))){
			unset($_SESSION['case']['post']['case_client']);
			unset($_SESSION['case']['post']['case_client_extra']);
			post('case_client_extra/show_add_form',false);
		}

		post('case_client_extra/show_add_form',true);//无论插入是否成功，刷新后继续显示添加客户表单
	}
	
	if(is_posted('submit/case_lawyer')){
		if(post('case_lawyer/lawyer',staff_check(post('case_lawyer_extra/lawyer_name'),'id'))<0){
			//查找职员并保存至session，捕获错误
			$submitable=false;

		}elseif(post('case_lawyer/role')=='实际贡献' && !(in_array('督办合伙人',$my_roles) || in_array('主办律师',$my_roles))){
			//禁止非主办律师/合伙人分配实际贡献
			showMessage('你没有权限分配实际贡献');
			$submitable=false;
			
		}elseif(post('case_lawyer/contribute',case_lawyerRoleCheck(post('case/id'),post('case_lawyer/role'),post('case_lawyer_extra/actual_contribute')))===false){
			//检查并保存本条case_lawyer的contribute，若不可添加则返回false并终止过程
			$submitable=false;
			
		}else{
			post('case_lawyer/hourly_fee',(int)post('case_lawyer/hourly_fee'));
			
			if(!$responsible_partner && post('case_lawyer/role')!='督办合伙人'){
				//第一次插入督办合伙人后不显示警告，否则如果不存在督办合伙人则显示警告
				showMessage('未设置督办合伙人','warning');
				$submitable=false;
			}
			if($submitable && case_addLawyer(post('case/id'),post('case_lawyer'))){
				unset($_SESSION['case']['post']['case_lawyer']);
				unset($_SESSION['case']['post']['case_lawyer_extra']);
				if(post('case/is_reviewed') && post('case_lawyer/role')!='实际贡献' && in_array('督办合伙人',$my_roles)){
					post('case/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		}
		post('case_lawyer_extra/show_add_form',true);//无论是否插入成功，刷新后继续显示添加律师表单
		
		case_calcContribute(post('case/id'));
	}
	
	if(is_posted('submit/case_fee')){
		
		post('case_fee/pay_time',strtotime(post('case_fee/pay_time')));

		if(!post('case_fee/fee')){
			showMessage('请预估收费金额','warning');

		}elseif(!post('case_fee/pay_time')){
			showMessage('请预估收费时间','warning');

		}else{		
			if(case_addFee(post('case/id'),post('case_fee'))){
				unset($_SESSION['case']['post']['case_fee']);
				if(post('case/is_reviewed')){
					post('case/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		}
	}
	
	if(is_posted('submit/case_fee_review') && is_logged('finance')){
		//财务审核
		$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		$condition_account=db_implode(post('case_fee_check'), $glue = ' OR ','case_fee','=',"'","'", '`','key');
		
		if(db_update('case_fee',array('reviewed'=>1),$condition)
			&& db_update('account',array('reviewed'=>1),$condition_account)
		){
			showMessage('已完成案件收费到账审核');
		}
	}
	
	if(is_posted('submit/case_fee_timing')){
		
		if(!is_posted('case/timing_fee')){
			post('case/timing_fee',0);
			$q_delete_case_fee_timing="DELETE FROM case_fee_timing WHERE `case`='".post('case/id')."'";
			$q_delete_case_fee_timing_type="DELETE FROM case_fee WHERE `case`='".post('case/id')."' AND type='计时预付'";
			db_query($q_delete_case_fee_timing);
			db_query($q_delete_case_fee_timing_type);
		}

		post('case_fee_timing',array_trim(post('case_fee_timing')));
		
		post('case_fee_timing/case',post('case/id'));

		post('case_fee_timing/time_start',strtotime(post('case_fee_timing/time_start')));//imperfect 2012/5/23 uicestone
		
		post('case_fee_timing/timing_start');
		
		post('case_fee_timing/uid',$_SESSION['id']);
		post('case_fee_timing/time',$_G['timestamp']);
			
		if(
			post('case/timing_fee') && 
			(!post('case_fee_timing/time_start') || 
			post('case_fee_timing/included_hours')=='')
		){
			showMessage('账单起始日或包含小时数未填','warning');

		}else{
			if(db_insert('case_fee_timing',post('case_fee_timing'),true,true)){
				unset($_SESSION['case']['post']['case_fee_timing']);
			}
		}
	}

	if(is_posted('submit/case_fee_misc')){
		
		post('case_fee_misc/case',post('case/id'));
		
		post('case_fee_misc/type','办案费');
		
		post('case_fee_misc/pay_time',strtotime(post('case_fee_misc/pay_time')));
		
		if(!post('case_fee_misc/fee')){
			showMessage('请填写办案费约定金额','warning');

		}elseif(!post('case_fee_misc/pay_time')){
			showMessage('请填写收费时间','warning');

		}else{		
			if(db_insert('case_fee',post('case_fee_misc'))){
				unset($_SESSION['case']['post']['case_fee_misc']);
			}
		}
	}

	if(is_posted('submit/case_document')){
		if($_FILES["file"]["error"]>0){
			showMessage('文件上传错误:'.$_FILES["file"]["error"],'warning');
		}else{
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
			
			$_SESSION['case']['post']['case_document']['id']=case_addDocument(post('case/id'),$fileInfo);

			rename(iconv("utf-8","gbk",$_G['case_document_path']."/".$_FILES["file"]["name"]),iconv("utf-8","gbk",$_G['case_document_path']."/".post('case_document/id')));
		}
		
		unset($_SESSION['case']['post']['case_document']);
	}
	
	if(is_posted('submit/case_client_delete')){

		$condition = db_implode(post('case_client_check'), $glue = ' OR ','id','=',"'","'", '`','key');

		$q="DELETE FROM case_client WHERE (".$condition.")";

		db_query($q);

		if(post('case/is_reviewed')){
			post('case/is_reviewed',0);
			showMessage('案件关键信息已经更改，需要重新审核');
		}
	}

	if(is_posted('submit/case_lawyer_delete')){

		$condition = db_implode(post('case_lawyer_check'), $glue = ' OR ','id','=',"'","'", '`','key');

		$q="DELETE FROM case_lawyer WHERE (".$condition.")";

		db_query($q);
		
		case_calcContribute(post('case/id'));

		if(post('case/is_reviewed')){
			post('case/is_reviewed',0);
			showMessage('案件关键信息已经更改，需要重新审核');
		}
	}

	if(is_posted('submit/case_fee_delete')){

		$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		
		db_delete('case_fee',$condition);

		if(post('case/is_reviewed')){
			post('case/is_reviewed',0);
			showMessage('案件关键信息已经更改，需要重新审核');
		}
	}

	if(is_posted('submit/new_case')){
		post('case/filed','在办');
		post('case/num','');
		post('case/time_contract',$_G['date']);
		post('case/time_end',date('Y-m-d',$_G['timestamp']+100*86400));
		//默认签约时间和结案时间

		showMessage('已立案，请立即获得案号');
	}
	
	if(post('case/filed')=='咨询' && is_posted('submit/file')){
		post('case/filed','归档咨询');
	}
	
	if(is_posted('submit/send_message')){
		showMessage('本案已被退回');
		case_reviewMessage('被退回审核',$lawyers);
	}

	if(is_posted('submit/review')){
		post('case/is_reviewed',1);
		showMessage('本案已经审核通过');
		case_reviewMessage('通过审核',$lawyers);
	}
	
	if(is_posted('submit/apply_lock')){
		//申请锁定，发送一条消息给督办合伙人
		if($responsible_partner){
			$apply_lock_message=$_SESSION['username'].'申请锁定'.strip_tags(post('case/name')).'一案，[url=http://sys.lawyerstars.com/case?edit='.post('case/id').']点此进入[/url]';
			sendMessage($responsible_partner,$apply_lock_message,'caseLockApplication');//imperfect
			showMessage('锁定请求已经发送至本案督办合伙人');
		}else{
			showMessage('本案没有督办合伙人，无处发送申请','warning');
		}
	}
	
	if(is_posted('submit/lock_type')){
		post('case/type_lock',1);
	}

	if(is_posted('submit/lock_client')){
		post('case/client_lock',1);
	}

	if(is_posted('submit/lock_lawyer')){
		post('case/lawyer_lock',1);
	}

	if(is_posted('submit/lock_fee')){
		post('case/fee_lock',1);
	}
	
	if(is_posted('submit/apply_file')){
		post('case/time_end',$_G['date']);
		post('case/filed','财务审核');
		showMessage('归档申请已接受，案件被提交财务审核');
	}
	
	if(is_posted('submit/review_finance')){
		post('case/filed','信息审核');
		showMessage('结案财务状况已经审核，等待信息审核');
	}

	if(is_posted('submit/review_info')){
		post('case/filed','主管审核');
		showMessage('案件信息已经审核，等待主管审核');
	}
	
	if(is_posted('submit/review_manager')){
		db_insert('file_status',array('case'=>post('case/id'),'status'=>'在档','time'=>$_G['timestamp']));
		post('case/time_end',$_G['date']);
		post('case/filed','已归档');
		showMessage('案件已经审核，已正式归档');
	}
	
	$case_client_role = case_getClientRole(post('case/id'));
	
	if(is_posted('submit/apply_case_num') && post('case/num')==''){
		//准备插入案号
		
		post('case/num',case_getNum(post('case'),$case_client_role));
	}

	if(isset($case_client_role['client']) && post('case/filed')!='已归档'){
		//根据案件类别和客户、相对方更新案名
		$case_client_role['client_name']='<a href="javascript:showWindow(\'client?edit='.$case_client_role['client'].'\')">'.$case_client_role['client_name'].'</a>';

		$case_client_role['opposite_name']='<a href="javascript:showWindow(\'client?edit='.$case_client_role['opposite'].'\')">'.$case_client_role['opposite_name'].'</a>';

		//更新案名
		if(post('case/classification')=='诉讼' && ($case_client_role['client_role']=='原告' || $case_client_role['client_role']=='申请人') && ($case_client_role['opposite_role']=='被告' || $case_client_role['opposite_role']=='被申请人')){
				post('case/name',$case_client_role['client_name'].' 诉 '.$case_client_role['opposite_name'].'('.post('case/type').')');
				
		}elseif(post('case/classification')=='诉讼' && ($case_client_role['client_role']=='被告' || $case_client_role['client_role']=='被申请人') && ($case_client_role['opposite_role']=='原告' || $case_client_role['opposite_role']=='申请人')){
				post('case/name',$case_client_role['client_name'].' 应诉 '.$case_client_role['opposite_name'].'('.post('case/type').')');
	
		}elseif(post('case/classification')=='诉讼' && $case_client_role['client_role']=='上诉人'){
			post('case/name',$case_client_role['client_name'].' 上诉 '.$case_client_role['opposite_name'].'('.post('case/type').')');
			
		}elseif(post('case/classification')=='诉讼' && $case_client_role['client_role']=='被上诉人'){
			post('case/name',$case_client_role['client_name'].' 应 '.$case_client_role['opposite_name'].' 上诉('.post('case/type').')');
			
		}elseif(post('case/classification')=='诉讼' && $case_client_role['client_role']=='第三人'){
			post('case/name',$case_client_role['client_name'].' 与 '.$case_client_role['opposite_role'].' '.$case_client_role['opposite_name'].'('.post('case/type').')');
			
		}elseif(post('case/classification')=='法律顾问'){
			post('case/name',$case_client_role['client_name'].'(法律顾问)');
			
		}else{
			post('case/name',$case_client_role['client_name'].(post('case/type')?'('.post('case/type').')':''));
	
		}
	}

	post('case/name',post('case/name'));

	if(post('case/name_extra')){
		post('case/name',post('case/name').' '.post('case/name_extra'));
	}
	
	if(is_posted('submit/case') && !post('case/num')){
		$submitable=false;
		showMessage('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
	}
	
	if(is_posted('submit/case') && post('case/classification')!='法律顾问' && post('case/filed')!='咨询' && !post('case/focus')){
		$submitable=false;
		showMessage('请填写案件争议焦点','warning');
	}
	
	processSubmit($submitable,NULL,NULL,false,true,false);
}

//计算本案有效日志总时间
$schedule_time=schedule_calculateTime(post('case/id'));

$case_status=case_getStatusById(post('case/id'));

$case_type_array=db_enumArray('case','stage');

if(post('case/filed')=='咨询'){
	$case_lawyer_role_array=array('督办合伙人','接洽律师','接洽律师（次要）','律师助理');
}else{
	$case_lawyer_role_array=db_enumArray('case_lawyer','role');
}

$case_client_table=case_getClientList(post('case/id'),post('case/client_lock'));

if(post('case/filed')=='咨询'){
	post('case_client_extra/type','潜在客户');
}else{
	post('case_client_extra/type','成交客户');//让案下客户添加默认为成交客户
}

$case_staff_table=case_getStaffList(post('case/id'),post('case/lawyer_lock'),post('case/timing_fee'));

$case_fee_table=case_getFeeList(post('case/id'),post('case/fee_lock'));

if(post('case/timing_fee')){
	$case_fee_timing_string=case_getTimingFeeString($case_id);
}

$case_fee_misc_table=case_getFeeMiscList(post('case/id'),post('case/fee_lock'));

$case_document_table=case_getDocumentList(post('case/id'));

$case_schedule_table=case_getScheduleList(post('case/id'));

$case_plan_table=case_getPlanList(post('case/id'));
?>