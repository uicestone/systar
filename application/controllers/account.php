<?php
class Account extends SS_controller{
	function __construct(){
		parent::__construct();
		$this->load->model('achievement_model','achievement');
	}
	
	function lists(){
		$this->session->set_userdata('last_list_action', $this->input->server('REQUEST_URI'));
		
		$field=array(
			'time_occur'=>array('title'=>'日期','eval'=>true,'content'=>"
				return date('Y-m-d',{time_occur});
			"),
			'name'=>array('title'=>'名目','wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'account/edit/{id}\')')),
			'_type'=>array('title'=>'方向','eval'=>true,'content'=>"
				if({amount}>0){
					return '<span style=\"color:#0F0\"><<</span>';
				}else{
					return '<span style=\"color:#F00\">>></span>';
				}
			",'td_title'=>'width="55px"','td'=>'style="text-align:center"'),
			'amount'=>array('title'=>'金额'),
			'client_name'=>array('title'=>'付款/收款人')
		);
		
		$list=$this->table->setFields($field)
				->setData($this->account->getList())
				->generate();
		$this->load->addViewData('list', $list);
		
		$this->load->view_data['account_sum']=array(
			'_field'=>array('总创收'),
			array($this->achievement->sum('collected','total',option('date_range/from_timestamp'),option('date_range/to_timestamp'),false))
		);
		
		$this->load->view('list');
	}

	function add(){
		$this->edit();
	}

	function edit($id=NULL){
		$this->load->model('client_model','client');
		$this->load->model('cases_model','cases');
		
		$this->getPostData($id,function($CI){
			if($this->input->get('case')){
				post(CONTROLLER.'/case',intval($this->input->get('case')));
			}
			if($this->input->get('client')){
				post(CONTROLLER.'/client',intval($this->input->get('client')));
			}
		
			post('account/name','律师费');
			post('account/time_occur',$CI->config->item('timestamp'));
		});
		
		//转换时间
		post('account_extra/time_occur',date('Y-m-d',post('account/time_occur')));
		
		if($this->input->get('case')){
			post('account/case',intval($this->input->get('case')));
		}
		
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if($this->input->post('submit')){
			$submitable=true;
			
			$_SESSION['account']['post']=array_replace_recursive($_SESSION['account']['post'],$_POST);
			
			if(is_posted('submit/recognizeOldClient')){
				$client_check=$this->client->check(post('account_extra/client_name'),'array');
		
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
		
			post('account/case',$this->cases->getIdByCaseFee(post('account/case_fee')));
			//根据提交的case_fee先找出case.id
		
			post('account',array_trim(post('account')));//imperfect 2012/5/25 uicestone 为了让没有case_fee 和case的account能够保存
			
			$this->processSubmit($submitable);
		}
		
		if(post('account/client')){
			//若有客户，则获得相关客户的名称
			post('account_extra/client_name',$this->client->fetch(post('account/client'),'name'));
		
			//根据客户ID获得收费array
			$case_fee_array=$this->cases->getFeeListByClient(post('account/client'));
		}
		
		if(post('account/case')){
			//指定案件时，根据案件id获得客户array
			$case_client_array=$this->client->getListByCase(post('account/case'));
		
			//根据案件ID获得收费array
			$case_fee_array=$this->cases->getFeeOptions(post('account/case'));
			
		}
		
		$this->load->addViewArrayData(compact('case_client_array','case_fee_array'));
		$this->load->view('account/edit');
		$this->load->main_view_loaded=true;
	}
}
?>