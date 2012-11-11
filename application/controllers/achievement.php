<?php
class Achievement extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		$field=array(
			'type'=>array('title'=>'类别','td_title'=>'width="85px"'),
			'case_name'=>array('title'=>'案件','td_title'=>'width="25%"','content'=>'<a href="case?edit={case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
			'fee'=>array('title'=>'预估','td_title'=>'width="100px"','td'=>'title="{pay_time}"'),
			'collected'=>array('title'=>'实收','td_title'=>'width="100px"','td'=>'title="{time_occur}"'),
			'role'=>array('title'=>'角色'),
			'contribute_collected'=>array('title'=>'贡献'),
			'bonus'=>array('title'=>'奖金'),
			'clients'=>array('title'=>'客户')
		);
		$month_start_timestamp=strtotime(date('Y-m',$this->config->item('timestamp')).'-1');
		$month_end_timestamp=mktime(0,0,0,date('m',$this->config->item('timestamp'))+1,1,date('Y',$this->config->item('timestamp')));
		
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
		
		$achievement=$this->achievement->sum('collected','contribute',option('date_range/from_timestamp'),option('date_range/to_timestamp'),false);
		$achievement_dashboard=array(
			'_field'=>array(
				'贡献'
			),
			array(
				$achievement
			)
		);
		$table=$this->table->setFields($field)
			->setData($this->achievement->getList())
			->generate();
		$this->load->addViewData('list',$table);
		$achievement_view_data=compact('achievement_dashboard','achievement_sum');
		$this->load->addViewArrayData($achievement_view_data);
		$this->load->view('list');
	}

	function recent(){
		
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
		
		$field=array(
			'type'=>array('title'=>'类别','td_title'=>'width="85px"'),
			'case_name'=>array('title'=>'案件','td_title'=>'width="25%"','content'=>'<a href="case?edit={case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
			'lawyers'=>array('title'=>'主办律师'),
			'fee'=>array('title'=>'预估','td_title'=>'width="100px"'),
			'pay_time'=>array('title'=>'时间','td_title'=>'width="100px"'),
			'uncollected'=>array('title'=>'未收','td_title'=>'width="100px"'),
			'clients'=>array('title'=>'客户')
		);
		
		
		$table=$this->table->setFields($field)
					->setData($this->achievement->getRecentList())
					->generate();
				
		$this->main_view_loaded=TRUE;
		$this->load->addViewData('list',$table);

		$this->load->view('list');	
	}
	
	function expired(){
			
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
		
		$field=array(
			'type'=>array('title'=>'类别','td_title'=>'width="85px"'),
			'case_name'=>array('title'=>'案件','td_title'=>'width="25%"','content'=>'<a href="case?edit={case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
			'lawyers'=>array('title'=>'主办律师'),
			'fee'=>array('title'=>'预估','td_title'=>'width="100px"'),
			'pay_time'=>array('title'=>'时间','td_title'=>'width="100px"'),
			'uncollected'=>array('title'=>'未收','td_title'=>'width="100px"'),
			'clients'=>array('title'=>'客户')
		);
		
		$table=$this->table->setFields($field)
					->setData($this->achievement->getExpiredList())
					->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
		
	}
	
	function caseBonus(){
		
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
		
		$field=array(
			'staff_name'=>array('title'=>'人员'),
			'contribute_sum'=>array('title'=>'合计贡献'),
			'bonus_sum'=>array('title'=>'合计奖金')
		);
		
		
		$table=$this->table->setFields($field)
					->setData($this->achievement->getCaseBonusList())
					->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
	}

	function teambonus(){
		
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
		
		$field=array(
			'staff_name'=>array('title'=>'人员'),
			'bonus_sum'=>array('title'=>'团奖')
		);
		
		
		$table=$this->table->setFields($field)
					->setData($this->achievement->getTeambonusList())
					->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
	}
	
	function summary(){
		$q_monthly_achievement="
			SELECT month,collect.sum AS collect,contract.sum AS contract
			FROM(
				SELECT FROM_UNIXTIME(time_occur,'%Y-%m') AS `month`,SUM(amount) AS sum
				FROM account 
				GROUP BY FROM_UNIXTIME(time_occur,'%Y-%m')
			)collect LEFT JOIN
			(
				SELECT LEFT(case.time_contract,7) AS month,SUM(case_fee.fee) AS sum
				FROM case_fee INNER JOIN `case` ON case.id=case_fee.case
				GROUP BY LEFT(case.time_contract,7)
			)contract USING (month)
			WHERE LEFT(month,4)='".date('Y',$this->config->item('timestamp'))."'
		";
		
		$monthly_collect=db_toArray($q_monthly_achievement);
		
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
		
	}
	
	function query(){
		$q_monthly_queries="
			SELECT month,queries,filed_queries,live_queries,cases
			FROM (
				SELECT LEFT(first_contact,7) AS month, COUNT(id) AS queries, SUM(IF(filed=1,1,0)) AS filed_queries, SUM(IF(filed=0,1,0)) AS live_queries
				FROM `case` 
				WHERE is_query=1 AND LEFT(first_contact,4)='".date('Y',$_G['timestamp'])."'
				GROUP BY LEFT(first_contact,7)
			)query INNER JOIN (
				SELECT LEFT(time_contract,7) AS month, COUNT(id) AS cases
				FROM `case`
				WHERE is_query=0 AND LEFT(time_contract,4)='".date('Y',$_G['timestamp'])."'
				GROUP BY LEFT(time_contract,7)
			)`case` USING(month)
		";
		$monthly_queries=db_toArray($q_monthly_queries);
		$chart_monthly_queries_catogary=json_encode(array_sub($monthly_queries,'month'));
		$chart_monthly_queries_series=array(
			array('name'=>'总量','data'=>array_sub($monthly_queries,'queries')),
			array('name'=>'归档','color'=>'#AAA','data'=>array_sub($monthly_queries,'filed_queries')),
			array('name'=>'在谈','data'=>array_sub($monthly_queries,'live_queries')),
			array('name'=>'新增案件','data'=>array_sub($monthly_queries,'cases'))

		);
		$chart_monthly_queries_series=json_encode($chart_monthly_queries_series,JSON_NUMERIC_CHECK);

		$q_personally_queries="
			SELECT staff.name AS staff_name, COUNT(case.id) AS queries, SUM(filed) AS filed_queries, SUM(NOT filed) AS live_queries
			FROM `case` 
				INNER JOIN case_lawyer ON case.id=case_lawyer.case 
				INNER JOIN staff ON staff.id=case_lawyer.lawyer AND case_lawyer.role = '接洽律师'
			WHERE is_query=1 AND LEFT(first_contact,4)='".date('Y',$_G['timestamp'])."'
			GROUP BY staff.id
			ORDER BY live_queries DESC, queries DESC
		";
		$personally_queries=db_toArray($q_personally_queries);

		$chart_personally_queries_catogary=json_encode(array_sub($personally_queries,'staff_name'));
		$chart_personally_queries_series=array(
			array('name'=>'归档','color'=>'#AAA','data'=>array_sub($personally_queries,'filed_queries')),
			array('name'=>'在谈','data'=>array_sub($personally_queries,'live_queries'))

		);
		$chart_personally_queries_series=json_encode($chart_personally_queries_series,JSON_NUMERIC_CHECK);

		$q_personally_type_queries="
			SELECT staff.name AS staff_name, COUNT(case.id) AS queries, SUM(IF(query_type='面谈咨询',1,0)) AS face_queries, SUM(IF(query_type='电话咨询',1,0)) AS call_queries, SUM(IF(query_type='网上咨询',1,0)) AS online_queries
			FROM `case` 
				INNER JOIN case_lawyer ON case.id=case_lawyer.case 
				INNER JOIN staff ON staff.id=case_lawyer.lawyer AND case_lawyer.role = '接洽律师'
			WHERE is_query=1 AND LEFT(first_contact,4)='".date('Y',$_G['timestamp'])."'
			GROUP BY staff.id
			ORDER BY face_queries DESC, call_queries DESC, online_queries DESC
		";
		$personally_type_queries=db_toArray($q_personally_type_queries);

		$chart_personally_type_queries_catogary=json_encode(array_sub($personally_type_queries,'staff_name'));
		$chart_personally_type_queries_series=array(
			array('name'=>'网上咨询','data'=>array_sub($personally_type_queries,'online_queries')),
			array('name'=>'电话咨询','data'=>array_sub($personally_type_queries,'call_queries')),
			array('name'=>'面谈咨询','data'=>array_sub($personally_type_queries,'face_queries'))

		);
		$chart_personally_type_queries_series=json_encode($chart_personally_type_queries_series,JSON_NUMERIC_CHECK);
	}
	
	function client(){
		$this_month_beginning=mktime(0,0,0,date('m'),1,date('Y'));
		$last_month_beginning=mktime(0,0,0,date('m')-1,1,date('Y'));
		$last_2_month_beginning=mktime(0,0,0,date('m')-2,1,date('Y'));

		$q_staffly_clients="
		SELECT staff.name AS staff_name,lastmonth.clients AS lastmonth,last2month.clients AS last2month
		FROM staff INNER JOIN (
			SELECT source_lawyer,COUNT(client.id) AS clients
			FROM client
			WHERE display=1 AND classification='客户' AND time >= '".$last_month_beginning."' AND time < '".($this_month_beginning)."'
			GROUP BY source_lawyer
		)lastmonth ON staff.id=lastmonth.source_lawyer
		INNER JOIN (
			SELECT source_lawyer,COUNT(client.id) AS clients
			FROM client
			WHERE display=1 AND classification='客户' AND time >= '".$last_2_month_beginning."' AND time < '".$last_month_beginning."'
			GROUP BY source_lawyer
		)last2month ON staff.id=last2month.source_lawyer
		ORDER BY lastmonth DESC"
		;

		$staffly_clients=db_toArray($q_staffly_clients);
		$chart_staffly_clients_catogary=json_encode(array_sub($staffly_clients,'staff_name'));
		$chart_staffly_clients_series=array(
			array('name'=>'上上月','data'=>array_sub($staffly_clients,'last2month')),
			array('name'=>'上月','data'=>array_sub($staffly_clients,'lastmonth'))
		);
		$chart_staffly_clients_series=json_encode($chart_staffly_clients_series,JSON_NUMERIC_CHECK);

		if(date('w')==1){//今天是星期一
			$start_of_this_week=strtotime($_G['date']);
		}else{
			$start_of_this_week=strtotime("-1 Week Monday");
		}
		$start_of_this_month=strtotime(date('Y-m',$_G['timestamp']).'-1');
		$start_of_this_year=strtotime(date('Y',$_G['timestamp']).'-1-1');
		$start_of_this_term=strtotime(date('Y',$_G['timestamp']).'-'.(floor(date('m',$_G['timestamp'])/3-1)*3+1).'-1');

		$days_passed_this_week=ceil(($_G['timestamp']-$start_of_this_week)/86400);
		$days_passed_this_month=ceil(($_G['timestamp']-$start_of_this_month)/86400);
		$days_passed_this_term=ceil(($_G['timestamp']-$start_of_this_term)/86400);
		$days_passed_this_year=ceil(($_G['timestamp']-$start_of_this_year)/86400);

		$q="
			SELECT staff.name aS staff_name,
				this_week.num AS this_week_sum,
				this_month.num AS this_month_sum,
				this_term.num AS this_term_sum,
				this_year.num AS this_year_sum
			FROM
			(
				SELECT source_lawyer,COUNT(id) AS num
				FROM client
				WHERE time>='".$start_of_this_week."'
					AND display=1 AND classification='客户'
				GROUP BY source_lawyer
			)this_week
			INNER JOIN
			(
				SELECT source_lawyer,COUNT(id) AS num
				FROM client
				WHERE time>='".$start_of_this_month."'
					AND display=1 AND classification='客户'
				GROUP BY source_lawyer
			)this_month USING(source_lawyer)
			INNER JOIN
			(
				SELECT source_lawyer,COUNT(id) AS num
				FROM client
				WHERE time>='".$start_of_this_term."'
					AND display=1 AND classification='客户'
				GROUP BY source_lawyer
			)this_term USING(source_lawyer)
			INNER JOIN
			(
				SELECT source_lawyer,COUNT(id) AS num
				FROM client
				WHERE time>='".$start_of_this_year."'
					AND display=1 AND classification='客户'
				GROUP BY source_lawyer
			)this_year USING(source_lawyer)
			INNER JOIN staff ON staff.id=this_week.source_lawyer
		";

		processOrderBy($q,'this_week_sum','DESC');

		$field=array(
			'staff_name'=>array('title'=>'姓名'),
			'this_week_sum'=>array('title'=>'本周','content'=>'{this_week_sum}'),
			'this_month_sum'=>array('title'=>'本月','content'=>'{this_month_sum}'),
			'this_term_sum'=>array('title'=>'本季','content'=>'{this_term_sum}'),
			'this_year_sum'=>array('title'=>'本年','content'=>'{this_year_sum}')
		);

		$client_collect_stat=fetchTableArray($q,$field);
	}
}
?>