<?php
model('client');
model('staff');

getPostData(function(){
	global $_G;
	post('query/partner',6356);//默认合伙人 _imperfect_uicestone 2012/5/3
	post('query/date_start',$_G['date']);
});

$q_source="SELECT type,detail FROM client_source WHERE id='".post('query/source')."'";
post('source',db_fetch_first($q_source));
//取得当前咨询的"来源"数据

$submitable=false;

if(is_posted('submit')){
	$submitable=true;
	$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
	
	if(post(IN_UICE.'/querier')==''){
		$submitable=false;
		showMessage('请填写咨询人','warning');
	}
	
	if(!post('query/source',client_setSource(post('source/type'),post('source/detail')))){
		$submitable=false;
	}

	if(is_posted('submit/new_client')){
		//响应“保存为客户”按钮
		
		if((!post('query/phone') && !post('query/email')) || !post('query/source')){
			showMessage('确定联系方式和来源，才能保存为新客户！','warning');

		}else{
			$new_client=array(
				'name'=>post('query/querier'),
				'character'=>'自然人',
				'classification'=>'客户',
				'type'=>'潜在客户',
				'source'=>post('query/source'),
				'comment'=>post('query/summary')."\n".post('query/comment')."\n".
					'咨询方式：'.post('query/type')."\n".(post('query/quote')!=0?'报价：'.post('query/quote'):''),
				'uid'=>$_SESSION['id'],
				'username'=>$_SESSION['username'],
				'time'=>$_G['timestamp']
			);
		
			if($new_client_id=client_add($new_client)){
				post('query/client',$new_client_id);
	
				client_addContact_phone_email(post('query/client'),post('query/phone'),post('query/email'));
					
				showMessage('<a href="javascript:showWindow(\'client?edit='.post('query/client').'\')" target="_blank">新客户 '.$new_client['name'].' 已经添加，点击编辑详细信息</a>','notice');

			}else{
				$submitable=false;
				showMessage('保存客户错误，可能已经存在同名客户','warning');
			}
		}
	}
	
	if(is_posted('submit/recognizeOldClient')){
		$client_check=client_check(post('query/querier'),'array');
		
		if($client_check<0){
			$submitable=false;
		}else{
			post('query/source',$client_check['source']);
			post('query/client',$client_check['id']);
			post('query/querier',$client_check['abbreviation']);
			showMessage('已经识别为客户：'.$client_check['name']);
		}
	}
	//响应“识别”按钮
	
	if(!post('query/num')){
		$q_get_last_num="SELECT MAX(num) AS last_num FROM query WHERE LEFT(date_start,7)='".date('Y-m',strtotime(post('query/date_start')))."'";
		$max_last_num=db_fetch_field($q_get_last_num);

		if(!$max_last_num){
			post('query/num',date('Ym',strtotime(post('query/date_start'))).'001');
		}else{//imperfect 每月咨询超过999时会出现问题
			post('query/num',$max_last_num+1);
		}
	}
	
	if(post('query_staff/partner_name')){
		if(!post('query/partner',staff_check(post('query_staff/partner_name')))){
			$submitable=false;
		}
	}

	if(post('query_staff/lawyer_name')){
		if(!post('query/lawyer',staff_check(post('query_staff/lawyer_name')))){
			$submitable=false;
		}
	}

	if(post('query_staff/assistant_name')){
		if(!post('query/assistant',staff_check(post('query_staff/assistant_name')))){
			$submitable=false;
		}
	}
	
	if(is_posted('submit/newcase')){
		if(!post('query/client') || !post('query/lawyer') || !post('query/partner') || !post('query/source')){
			$submitable=false;
			showMessage('要立案，请将咨询人存为客户，填写督办合伙人和主办律师','warning');
			
		}else{
			$new_case=array(
				'name'=>post('query/querier'),
				'source'=>post('query/source'),
				'summary'=>post('query/summary'),
				'comment'=>post('query/comment')."\n".
						'咨询方式：'.post('query/type')."\n".(post('query/quote')?'报价：'.post('query/quote'):''),
				'time_contract'=>date('Y-m-d',$_G['timestamp']),
				'time_end'=>date('Y-m-d',$_G['timestamp']+100*86400),

				'uid'=>$_SESSION['id'],
				'username'=>$_SESSION['username'],
				'time'=>$_G['timestamp']
			);
			
			$case_id=db_insert('case',$new_case);

			post('query/case',$case_id);
			post('query/filed','归档');
			
			$new_case_client=array(
				'case'=>$case_id,
				'client'=>post('query/client')
			);

			$new_case_lawyer=array();
			$new_case_lawyer[]=array(
				'case'=>$case_id,
				'lawyer'=>post('query/partner'),
				'role'=>'督办合伙人'
			);

			$new_case_lawyer[]=array(
				'case'=>$case_id,
				'lawyer'=>post('query/lawyer'),
				'role'=>'接洽律师'
			);
			
			if(post('query/assistant')){
				$new_case_lawyer[]=array(
					'case'=>$case_id,
					'lawyer'=>post('query/assistant'),
					'role'=>'接洽律师（次要）'
				);
			}
			
			db_insert('case_client',$new_case_client);

			db_multiinsert('case_lawyer',$new_case_lawyer);

			db_update('schedule',array('case'=>$case_id),"`case`=13 AND client='".post('query/client')."'");
			
		}
	}
	processSubmit($submitable,function(){
		if(is_posted('submit/newcase')){
			redirect('case?edit='.post('query/case'));
		}
	});
}

if(post('query/client')){
	$q_client_contact="
		SELECT 
			client_contact.id,client_contact.comment,client_contact.content,client_contact.type
		FROM client_contact INNER JOIN client ON client_contact.client=client.id
		WHERE client_contact.client='".post('query/client')."'
	";
	
	$field_client_contact=array(
		'type'=>array('title'=>'类别','orderby'=>false),
		'content'=>array('title'=>'内容','orderby'=>false),
		'comment'=>array('title'=>'备注','orderby'=>false)
	);
}

if(post('query/client')){
	$q_client="SELECT abbreviation FROM client WHERE id = '".post('query/client')."'";
	post('query/querier',db_fetch_field($q_client));
}

if(post('query/partner')){
	$q_partner="SELECT * FROM staff WHERE id='".post('query/partner')."'";
	post('query_staff/partner_name',db_fetch_field($q_partner,'name'));
}
if(post('query/lawyer')){
	$q_lawyer="SELECT * FROM staff WHERE id='".post('query/lawyer')."'";
	post('query_staff/lawyer_name',db_fetch_field($q_lawyer,'name'));
}
if(post('query/assistant')){
	$q_assistant="SELECT * FROM staff WHERE id='".post('query/assistant')."'";
	post('query_staff/assistant_name',db_fetch_field($q_assistant,'name'));
}
//取得咨询跟进律师的姓名

$q_schedule="SELECT *
	FROM 
		schedule
	WHERE display=1 AND `case`='13' AND client='".post('query/client')."'
	ORDER BY time_start DESC
	LIMIT 10
";

$field_schedule=array(
	'name'=>array('title'=>'标题','td_title'=>'width="150px"','surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'schedule?edit={id}\')'),'orderby'=>false),
	'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
		return date('m-d H:i',{time_start});
	"),
	'username'=>array('title'=>'填写人','td_title'=>'width="90px"')
);

$q_schedule_time="SELECT SUM(IF(hours_checked=-1,0,hours_checked)) AS time FROM schedule WHERE `case`='13' AND client='".post('query/client')."'";
$schedule_time=db_fetch_field($q_schedule_time);
//计算案下日志总时间
?>