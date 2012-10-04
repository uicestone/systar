<?php
class Account extends SS_controller{
	function __construct(){
		parent::__construct();
		$this->load->model('account_model','model');
		$this->load->model('achievement_model');
	}
	
	function index(){
		$q="
			SELECT
				account.id,account.time,account.name,account.amount,account.time_occur,
				client.abbreviation AS client_name
			FROM account LEFT JOIN client ON account.client=client.id
			WHERE amount<>0
		";
		
		if(!is_logged('finance')){
			$q.=" AND account.case IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";
		}
		
		$search_bar=$this->processSearch($q,array('client.name'=>'客户','account.name'=>'名目','account.amount'=>'金额'));
		
		$date_range_bar=$this->dateRange($q,'account.time_occur');
		
		processOrderby($q,'time_occur','DESC');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'time_occur'=>array('title'=>'日期','eval'=>true,'content'=>"
				return date('Y-m-d',{time_occur});
			"),
			'name'=>array('title'=>'名目','surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'account?edit={id}\')')),
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
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$account_sum=array(
			'_field'=>array('总创收'),
			array(achievementSum('collected','total',option('date_range/from_timestamp'),option('date_range/to_timestamp'),false))
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q,$field);
		
		$data=compact('table','menu');
		
		$this->load->view('list',$data);
	}

	function add(){
		$this->edit();
	}

	function edit($id=NULL){
		$this->load->model('client_model');
		$this->load->model('case_model');
		
		getPostData($id,function(){
			if(got('case')){
				post(IN_UICE.'/case',intval($_GET['case']));
			}
			if(got('client')){
				post(IN_UICE.'/client',intval($_GET['client']));
			}
		
			post('account/name','律师费');
			post('account/time_occur',$this->config->item('timestamp'));
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
	}

	function caselist(){
		$this->load->model('case_model');
			
		$query="
		SELECT
			case.id,case.name,case.num,case.stage,case.time_contract,
			case.is_reviewed,case.apply_file,case.is_query,
			case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
			case.finance_review,case.info_review,case.manager_review,case.filed,
			contribute_allocate.contribute_sum,
			uncollected.uncollected,
			lawyers.lawyers
		FROM 
			`case`
			LEFT JOIN
			(
				SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
				FROM case_lawyer,staff 
				WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
				GROUP BY case_lawyer.`case`
			)lawyers
			ON `case`.id=lawyers.`case`
		
			LEFT JOIN 
			(
				SELECT `case`,SUM(contribute) AS contribute_sum
				FROM case_lawyer
				GROUP BY `case`
			)contribute_allocate
			ON `case`.id=contribute_allocate.case
			
			LEFT JOIN
			(
				SELECT `case`,IF(amount_sum IS NULL,fee_sum,fee_sum-amount_sum) AS uncollected FROM
				(
					SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' AND reviewed=0 GROUP BY `case`
				)case_fee_grouped
				LEFT JOIN
				(
					SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE reviewed=0 GROUP BY `case`
				)account_grouped
				USING (`case`)
			)uncollected
			ON case.id=uncollected.case
		
		WHERE case.display=1 AND case.id>=20
		";
		
		$search_bar=$this->processSearch($q,array('num'=>'案号','name'=>'名称','lawyers.lawyers'=>'主办律师'));
		
		processOrderby($q,'case.time_contract','DESC',array('case.name','lawyers'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="case?edit={id}">{num}</a>'),
			'name'=>array('title'=>'案名','content'=>'{name}<span class="right"><a href="javascript:showWindow(\'account?add&case={id}\')">+<span>'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
			'is_reviewed'=>array('title'=>'状态','td_title'=>'width="75px"','eval'=>true,'content'=>"
				return case_getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			")
		);
		
		$submitBar=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q,$field);
		
		$data=compact('table','menu');
		
		$this->load->view('list',$data);
	}
}
?>