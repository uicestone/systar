<?php
class Query extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="
			SELECT 
				case.id,case.first_contact,case.num,case.query_type AS type,case.summary,case.comment,
				client.abbreviation AS client_name,case_client.client,
				GROUP_CONCAT(staff.name) AS staff_name,
				client_source.type AS source
			FROM `case`
				LEFT JOIN case_client ON case.id=case_client.case
				LEFT JOIN client ON client.id=case_client.client
				LEFT JOIN case_lawyer ON case.id=case_lawyer.case AND (case_lawyer.role IN ('接洽律师','接洽律师（次要）','督办合伙人'))
				LEFT JOIN staff ON staff.id=case_lawyer.lawyer
				LEFT JOIN client_source ON case.source=client_source.id 
			WHERE case.company='{$_G['company']}' AND case.display=1 AND case.is_query=1
		";
		
		//if(got('mine')){
			$q.=" AND case_lawyer.lawyer='".$_SESSION['id']."'";
		//}
		
		if(got('filed')){
			$q.=" AND case.filed=1";
		}else{
			$q.=" AND case.filed=0";
		}
		
		$search_bar=$this->processSearch($q,array('client.name'=>'咨询人'));
		
		$q.=" GROUP BY case.id";
		
		$this->processOrderby($q,'first_contact','DESC');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'first_contact'=>array('title'=>'日期','td_title'=>'width="95px"'),
			'num'=>array('title'=>'编号','td_title'=>'width="180px"','surround'=>array('mark'=>'a','href'=>'case?edit={id}')),
			'client_name'=>array('title'=>'咨询人','surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'client?edit={client}\')')),
			'type'=>array('title'=>'方式','td_title'=>'width="80px"'),
			'source'=>array('title'=>'来源'),
			'staff_name'=>array('title'=>'接洽人'),
			'summary'=>array('title'=>'概况','td'=>'class="ellipsis" title="{summary}"'),
			'comment'=>array('title'=>'备注','td'=>'class="ellipsis" title="{comment}"')
		);
		
		$submitBar=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$submitBar);
	}

	function add(){
		$this->edit();
	}

	function edit($id=NULL){
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
	}
}
?>