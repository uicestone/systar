<?php
class Achievement extends SS_controller{
	
	var $section_title='业绩';
	
	var $list_args=array(
		'case_name'=>array('heading'=>array('data'=>'案件','width'=>'25%'),'cell'=>'<a href="#cases/edit/{case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
		'client_name'=>array('heading'=>'客户'),
		'account_time'=>array('heading'=>'到账时间'),
		'amount'=>array('heading'=>'创收'),
		'contribution'=>array('heading'=>'贡献'),
		'bonus'=>array('heading'=>'奖金'),
		'role'=>array('heading'=>'角色')
	);
	
	function __construct(){
		parent::__construct();
		$this->load->model('account_model','account');
	}
	
	/**
	 * 各团队业绩列表
	 */
	function teams(){
		
		$this->section_title='小组业绩统计';
		
		$this->config->set_user_item('date/from', $this->date->year_begin,false);
		
		$存量创收=$this->account->getList(array(
			'sum'=>true,
			'group'=>'team',
			'received'=>true,
			'contract_date'=>array('to'=>$this->date->last_year_end),
			'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
		));
		
		$新增创收=$this->account->getList(array(
			'sum'=>true,
			'group'=>'team',
			'received'=>true,
			'contract_date'=>array('from'=>$this->date->year_begin),
			'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
		));
		
		$签约=$this->account->getList(array(
			'sum'=>true,
			'group'=>'team',
			'received'=>false,
			'contract_date'=>array('from'=>$this->date->year_begin)
		));
		
		$category=array_sub($签约,'team_name');
		$series=array(
			array('name'=>'存量创收','data'=>array_sub($存量创收,'sum')),
			array('name'=>'新增创收','data'=>array_sub($新增创收,'sum')),
			array('name'=>'签约','data'=>array_sub($签约,'sum')),
		);
		
		$this->load->addViewData('category', json_encode($category));
		$this->load->addViewData('series', json_encode($series,JSON_NUMERIC_CHECK));
		
		$this->load->view('achievement/teams');
		$this->load->view('achievement/teams_sidebar',true,'sidebar');
	}

	function receivable($method=NULL){
		
	}
	
	function caseBonus(){
		
	}

	function teambonus(){
		
	}
	
	function index(){
		$monthly_collect=$this->achievement->getMonthlyAchievement();
		
		$months=array_sub($monthly_collect,'month');
		$collect=array_sub($monthly_collect,'collect');
		$contract=array_sub($monthly_collect,'contract');
		
		$series=array(
			array(
				'name'=>'创收',
				'data'=>$collect
			),
			array(
				'name'=>'签约',
				'data'=>$contract
			),
		);

		$months=json_encode($months);
		$series=json_encode($series,JSON_NUMERIC_CHECK);
		$this->load->addViewArrayData(compact('months','series'));
		$this->load->view('achievement/summary');
	}
	
	function query(){
		$monthly_queries=$this->achievement->getMonthlyQueries();
		$this->load->view_data['chart_monthly_queries_catogary']=json_encode(array_sub($monthly_queries,'month'));
		$chart_monthly_queries_series=array(
			array('name'=>'总量','data'=>array_sub($monthly_queries,'queries')),
			array('name'=>'归档','color'=>'#AAA','data'=>array_sub($monthly_queries,'filed_queries')),
			array('name'=>'在谈','data'=>array_sub($monthly_queries,'live_queries')),
			array('name'=>'新增案件','data'=>array_sub($monthly_queries,'cases'))

		);
		$this->load->view_data['chart_monthly_queries_series']=json_encode($chart_monthly_queries_series,JSON_NUMERIC_CHECK);

		$personally_queries=$this->achievement->getPersonallyQueries();
		$this->load->view_data['chart_personally_queries_catogary']=json_encode(array_sub($personally_queries,'staff_name'));
		$chart_personally_queries_series=array(
			array('name'=>'归档','color'=>'#AAA','data'=>array_sub($personally_queries,'filed_queries')),
			array('name'=>'成案','data'=>array_sub($personally_queries,'success_case')),
			array('name'=>'在谈','data'=>array_sub($personally_queries,'live_queries'))

		);
		$this->load->view_data['chart_personally_queries_series']=json_encode($chart_personally_queries_series,JSON_NUMERIC_CHECK);

		$personally_type_queries=$this->achievement->getPersonallyTypeQueries();
		$this->load->view_data['chart_personally_type_queries_catogary']=json_encode(array_sub($personally_type_queries,'staff_name'));
		$chart_personally_type_queries_series=array(
			array('name'=>'网上咨询','data'=>array_sub($personally_type_queries,'online_queries')),
			array('name'=>'电话咨询','data'=>array_sub($personally_type_queries,'call_queries')),
			array('name'=>'面谈咨询','data'=>array_sub($personally_type_queries,'face_queries'))

		);
		$this->load->view_data['chart_personally_type_queries_series']=json_encode($chart_personally_type_queries_series,JSON_NUMERIC_CHECK);
		$this->load->view('achievement/query');
	}
	
	function client(){
		//TODO 新增客户统计
	}
	
	function caseType(){
		$chart_casetype_income_data=$this->achievement->getCaseTypeIncome();
		$this->load->addViewData('chart_casetype_income_data', json_encode($chart_casetype_income_data,JSON_NUMERIC_CHECK));
		$this->load->view('achievement/casetype');
	}
}
?>