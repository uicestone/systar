<?php
class Achievement extends SS_controller{
	
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
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		
		if($this->input->post('date_from')){
			option('search/date_from',$this->input->post('date_from'));
		}

		if($this->input->post('date_to')){
			option('search/date_to',$this->input->post('date_to'));
		}
		
		if($this->input->post('submit')=='date_range_cancel'){
			option('search/date_from',NULL);
			option('search/date_ro',NULL);
		}
		
		$table=$this->table->setFields($this->list_args)
			->setData($this->achievement->getList(option('search')))
			->generate();
		
		$this->load->addViewData('list',$table);
		
		$month_start_timestamp=strtotime(date('Y-m',$this->date->now).'-1');
		$month_end_timestamp=mktime(0,0,0,date('m',$this->date->now)+1,1,date('Y',$this->date->now));
		
		$achievement_sum=array(
			'_field'=>array(
				'field'=>'本月',
				'total'=>'全所',
				'my'=>'主办',
				'contribute'=>'贡献'
			),
			
			'contracted'=>array(
				'field'=>'签约',
				'total'=>$this->achievement->sum('contracted','total',$month_start_timestamp),
				'my'=>$this->achievement->sum('contracted','my',$month_start_timestamp),
				'contribute'=>$this->achievement->sum('contracted','contribute',$month_start_timestamp)
			),
			
			'estimated'=>array(
				'field'=>'预计',
				'total'=>$this->achievement->sum('estimated','total',$month_start_timestamp,$month_end_timestamp),
				'my'=>$this->achievement->sum('estimated','my',$month_start_timestamp,$month_end_timestamp),
				'contribute'=>$this->achievement->sum('estimated','contribute',$month_start_timestamp,$month_end_timestamp)
			),
			
			'collected'=>array(
				'field'=>'到账',
				'total'=>$this->achievement->sum('collected','total',$month_start_timestamp),
				'my'=>$this->achievement->sum('collected','my',$month_start_timestamp),
				'contribute'=>$this->achievement->sum('collected','contribute',$month_start_timestamp)
			)
		);
		
		option('search/contribute_type',$this->input->get('contribute_type')=='actual'?'actual':'fixed');
		
		$achievement=$this->achievement->myBonus(array('case',option('search/contribute_type')),option('date_range/from_timestamp'),option('date_range/to_timestamp'));

		$achievement_dashboard=array(
			'_field'=>array(
				'奖金'
			),
			array(
				$achievement
			)
		);

		$achievement_view_data=compact('achievement_dashboard','achievement_sum');
		$this->load->addViewArrayData($achievement_view_data);
		$this->load->view('list');
		$this->load->view('achievement/lists_sidebar',true,'sidebar');
	}

	function receivable($method=NULL){
		
		$field=array(
			'type'=>array('heading'=>array('data'=>'类别','width'=>'85px')),
			'case_name'=>array('heading'=>array('data'=>'案件','width'=>'25%'),'cell'=>'<a href="/cases/edit/{case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
			'lawyers'=>array('heading'=>'主办律师'),
			'fee'=>array('heading'=>array('data'=>'预估','width'=>'100px')),
			'pay_time'=>array('heading'=>array('data'=>'时间','width'=>'100px')),
			'uncollected'=>array('heading'=>array('data'=>'未收','width'=>'100px')),
			'clients'=>array('heading'=>'客户')
		);
		
		$table=$this->table->setFields($field)
					->setData($this->achievement->getReceivableList($method))
					->generate();
				
		
		$this->load->addViewData('list',$table);

		$receivable_sum=$this->achievement->receivableSum($method,option('date_range/from'),option('date_range/to'));
		$this->load->addViewData('receivable_sum', $receivable_sum['sum']);

		$this->load->view('list');	
		$this->load->view('achievement/receivable_sidebar',true,'sidebar');
	}
	
	function caseBonus(){
		
		$field=array(
			'staff_name'=>array('heading'=>'人员'),
			'contribute_sum'=>array('heading'=>'合计贡献'),
			'bonus_sum'=>array('heading'=>'合计奖金')
		);
		
		$table=$this->table->setFields($field)
					->setData($this->achievement->getCaseBonusList())
					->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
		$this->load->view('achievement/casebonus_sidebar',true,'sidebar');
	}

	function teambonus(){
		
		
		
		$field=array(
			'staff_name'=>array('heading'=>'人员'),
			'bonus_sum'=>array('heading'=>'团奖')
		);
		
		
		$table=$this->table->setFields($field)
					->setData($this->achievement->getTeambonusList())
					->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
	}
	
	function summary(){
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