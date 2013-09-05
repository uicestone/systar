<?php
class Starsys_Index extends Index{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		//检查当前用户相关案件的收费情况并发送通知
		$this->load->model('account_model','account');
		$this->load->model('cases_model','cases');
		$this->load->model('schedule_model','schedule');

		if(!$this->config->user_item('receivable_notified')){
			$receivable_accounts=$this->account->getList(array(
				'people'=>$this->user->id,
				'project_is_active'=>true,
				'group_by'=>'account',
				'role'=>'主办律师',
				'having'=>'receivable_amount > 0 AND `receivable_date` <= CURDATE()',
			));

			foreach($receivable_accounts as $receivable_account){
				$case=$this->cases->fetch($receivable_account['project']);
				$message='案件<a href="#cases/'.$case['id'].'">'.$case['name']."</a>"
					.'有一笔<a href="#account/'.$receivable_account['id'].'">'.$receivable_account['total_amount'].'的应收账款</a>，最后预估日期为'
					.$receivable_account['receivable_date']
					.'，目前已到账'.$receivable_account['received_amount']
					.'，尚应催收'.$receivable_account['receivable_amount']
					.'。如需变更预估，请在案件下添加原预估日期的负应收，并重新添加新预估。注意负应收和新预估应使用与原预估相同的“账目编号”';

				$this->message->system($message);
			}
			$this->user->setConfig('receivable_notified',1);
		}

		parent::index();
	}
}
?>
