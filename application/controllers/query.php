<?php
class Query extends SS_controller{
	function __construct(){
		parent::__construct();
		$this->actual_table='case';
	}
	
	function filed(){
		$this->lists('filed');
	}
	
	function lists($para=NULL){
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));

		$field=array(
			'first_contact'=>array('title'=>'日期','td_title'=>'width="95px"'),
			'num'=>array('title'=>'编号','td_title'=>'width="143px"','wrap'=>array('mark'=>'a','href'=>'cases/edit/{id}')),
			'client_name'=>array('title'=>'咨询人','wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'client/edit/{client}\')')),
			'type'=>array('title'=>'方式','td_title'=>'width="80px"'),
			'source'=>array('title'=>'来源'),
			'staff_names'=>array('title'=>'接洽人'),
			'summary'=>array('title'=>'概况','td'=>'class="ellipsis" title="{summary}"'),
			'comment'=>array('title'=>'备注','td'=>'class="ellipsis" title="{comment}"')
		);
		$table=$this->table->setFields($field)
			->setData($this->query->getList($para))
			->generate();

		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}

	function add(){
		$this->edit();
	}

	function edit($id=NULL){
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		$this->load->model('cases_model','cases');
		
		$this->getPostData($id,function($CI){
			post('case_lawyer_extra/partner_name',$CI->staff->getMyManager('name'));
			post('case_lawyer_extra/lawyer_name',$_SESSION['username']);
			post('query/first_contact',$CI->config->item('date'));
			post('query/is_query',1);
			post('client_extra/source_lawyer_name',$_SESSION['username']);
			post('client/gender','男');
		});
		
		if($this->input->post('submit')){
			if($submit=='advanced'){
				$case_id=post('query/id');
				unset($_SESSION[CONTROLLER]['post']);
				redirect('cases/edit/'.$case_id);
			}
			try{
				$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$this->input->post());
				
				if(!post('client/id')){
					if(post('client/name')==''){
						throw new Exception('请填写咨询人');
					}
					
					$client_check=$this->client->check(post('client/name'),'array',false,false);
					if($client_check['id']>0){
						post('client/id',$client_check['id']);
						post('client/source_lawyer',$client_check['source_lawyer']);
						post('query/source',$client_check['source']);
					}
				
					if(!post('client/source_lawyer') && post('client/source_lawyer',$this->staff->check(post('client_extra/source_lawyer_name'),'id',false))<0){
						throw new Exception('来源律师名称错误：'.post('client_extra/source_lawyer_name'));
					}
		
					if(!post('client/id') && !post('query/source') && !post('query/source',$this->client->setSource(post('source/type'),post('source/detail')))){
						throw new Exception('客户来源错误');
					}
				}
			
				if(!post('client/id') && !post('client_contact_extra/phone') && !post('client_contact_extra/email')){
					throw new Exception('至少需要填写一种联系方式');
				}
				
				if(!post('query/summary')){
				  throw new Exception('请填写咨询概况');
				}

				if((post('case_lawyer_extra/partner_name') && post('case_lawyer_extra/partner',$this->staff->check(post('case_lawyer_extra/partner_name')))<0)
					|| (post('case_lawyer_extra/lawyer_name') && post('case_lawyer_extra/lawyer',$this->staff->check(post('case_lawyer_extra/lawyer_name')))<0)
					|| (post('case_lawyer_extra/assistant_name') && post('case_lawyer_extra/assistant',$this->staff->check(post('case_lawyer_extra/assistant_name')))<0)){
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
					
					post('client/id',$this->client->add(post('client')));
				
					$this->client->addContact_phone_email(post('client/id'),post('client_contact_extra/phone'),post('client_contact_extra/email'));
				}
		
				
				post('case',array_merge(post('query'),array('is_query'=>1)));
				
				$this->cases->update(post('query/id'),post('case'));
				
				post('case/id',post('query/id'));
				
				$this->cases->addClient(post('case/id'),post('client/id'),'');
		        
				$this->cases->addLawyer(post('case/id'),array('lawyer'=>post('case_lawyer_extra/partner'),'role'=>'督办合伙人'));
				$this->cases->addLawyer(post('case/id'),array('lawyer'=>post('case_lawyer_extra/lawyer'),'role'=>'接洽律师'));
				$this->cases->addLawyer(post('case/id'),array('lawyer'=>post('case_lawyer_extra/assistant'),'role'=>'接洽律师（次要）'));
				$this->cases->calcContribute(post('case/id'));
				
				post('query/num',$this->cases->getNum(post('case')));
				
				$case_client_role=array('client_name'=>post('client/name'));
				post('query/name',$this->cases->getName($case_client_role,true));
				
				$new_case_id=post('case/id');
				
				$this->processSubmit(true);
		
			}catch(exception $e){
				showMessage($e->getMessage(),'warning');
			}
		}
		$this->load->view('query/edit');
	}
}
?>