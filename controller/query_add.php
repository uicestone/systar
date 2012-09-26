<?php
model('client');
model('staff');
model('case');

getPostData(function(){
	global $_G;
	post('case_lawyer_extra/partner_name',staff_getMyManager('name'));
	post('case_lawyer_extra/lawyer_name',$_SESSION['username']);
	post('query/first_contact',$_G['date']);
	post('query/is_query',1);
	post('client_extra/source_lawyer_name',$_SESSION['username']);
	post('client/gender','男');
});

if(is_posted('submit')){
	if(is_posted('submit/advanced')){
		$case_id=post('query/id');
		unset($_SESSION[IN_UICE]['post']);
		redirect('case?edit='.$case_id);
	}
	try{
		$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
		
		if(!post('client/id')){
			if(post('client/name')==''){
				throw new Exception('请填写咨询人');
			}
			
			if(client_check(post('client/name'),'id',false,false)>0){
				throw new Exception('新咨询人名称与现有客户重复');
			}
		
			if(post('client/source_lawyer',staff_check(post('client_extra/source_lawyer_name'),'id',false))<0){
				throw new Exception('来源律师名称错误：'.post('client_extra/source_lawyer_name'));
			}

			if(!post('query/source',client_setSource(post('source/type'),post('source/detail')))){
				throw new Exception('客户来源错误');
			}
		}
	
		if(!post('client_contact_extra/phone') && !post('client_contact_extra/email')){
			throw new Exception('至少需要填写一种联系方式');
		}
		
		if((post('case_lawyer_extra/partner_name') && post('case_lawyer_extra/partner',staff_check(post('case_lawyer_extra/partner_name')))<0)
			|| (post('case_lawyer_extra/lawyer_name') && post('case_lawyer_extra/lawyer',staff_check(post('case_lawyer_extra/lawyer_name')))<0)
			|| (post('case_lawyer_extra/assistant_name') && post('case_lawyer_extra/assistant',staff_check(post('case_lawyer_extra/assistant_name')))<0)){
			throw new Exception('接待人员名称错误');
		}
	
		if(!post('client/id')){
			post('client',post('client')+array(
				'character'=>'自然人',
				'classification'=>'客户',
				'type'=>'潜在客户',
				'source'=>post('query/source'),
				'display'=>1
			));
			
			post('client/id',client_add(post('client')));
		
			client_addContact_phone_email(post('client/id'),post('client_contact_extra/phone'),post('client_contact_extra/email'));
		}

		
		post('case',array_merge(post('query'),array(
			'is_query'=>1
		)));
		
		case_update(post('query/id'),post('case'));
		
		post('case/id',post('query/id'));
		
		case_addClient(post('case/id'),post('client/id'),'');
        
		case_addLawyer(post('case/id'),array('lawyer'=>post('case_lawyer_extra/partner'),'role'=>'督办合伙人'));
		case_addLawyer(post('case/id'),array('lawyer'=>post('case_lawyer_extra/lawyer'),'role'=>'接洽律师'));
		case_addLawyer(post('case/id'),array('lawyer'=>post('case_lawyer_extra/assistant'),'role'=>'接洽律师（次要）'));
		case_calcContribute(post('case/id'));
		
		post('query/num',case_getNum(post('case')));
		
		$new_case_id=post('case/id');
		
		processSubmit(true);

	}catch(exception $e){
		showMessage($e->getMessage(),'warning');
	}
}
?>