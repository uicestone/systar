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
			)collect INNER JOIN
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
		
		$this->view_data['months']=json_encode($months);
		$this->view_data['series']=json_encode($series,JSON_NUMERIC_CHECK);
		
	}
	
	function query(){
		//TODO 新的咨询统计
		$q_monthly_queries="
		SELECT month,queries,filed_queries,live_queries,cases
		FROM (
			SELECT LEFT(date_start,7) AS month, COUNT(id) AS queries, SUM(IF(filed=1,1,0)) AS filed_queries, SUM(IF(filed='洽谈',1,0)) AS live_queries
			FROM query 
			WHERE LEFT(date_start,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY LEFT(date_start,7)
		)query INNER JOIN (
			SELECT LEFT(time_contract,7) AS month, COUNT(id) AS cases
			FROM `case`
			WHERE LEFT(time_contract,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY LEFT(time_contract,7)
		)`case` USING(month)";
		$monthly_queries=db_toArray($q_monthly_queries);
		$this->view_data['chart_monthly_queries_catogary']=json_encode(array_sub($monthly_queries,'month'));
		$chart_monthly_queries_series=array(
			array('name'=>'总量','data'=>array_sub($monthly_queries,'queries')),
			array('name'=>'归档','color'=>'#AAA','data'=>array_sub($monthly_queries,'filed_queries')),
			array('name'=>'在谈','data'=>array_sub($monthly_queries,'live_queries')),
			array('name'=>'新增案件','data'=>array_sub($monthly_queries,'cases'))
		
		);
		$this->view_data['chart_monthly_queries_series']=json_encode($chart_monthly_queries_series,JSON_NUMERIC_CHECK);
		
		$q_personally_queries="
			SELECT staff.name AS staff_name, COUNT(query.id) AS queries, SUM(IF(filed='归档',1,0)) AS filed_queries, SUM(IF(filed='洽谈',1,0)) AS live_queries
			FROM query INNER JOIN staff ON staff.id=query.lawyer
			WHERE LEFT(date_start,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY lawyer
			ORDER BY live_queries DESC, queries DESC
		";
		$personally_queries=db_toArray($q_personally_queries);
		
		$this->view_data['chart_personally_queries_catogary']=json_encode(array_sub($personally_queries,'staff_name'));
		$chart_personally_queries_series=array(
			array('name'=>'归档','color'=>'#AAA','data'=>array_sub($personally_queries,'filed_queries')),
			array('name'=>'在谈','data'=>array_sub($personally_queries,'live_queries'))
		
		);
		$this->view_data['chart_personally_queries_series']=json_encode($chart_personally_queries_series,JSON_NUMERIC_CHECK);
		
		$q_personally_type_queries="
			SELECT staff.name AS staff_name, COUNT(query.id) AS queries, SUM(IF(type='面谈咨询',1,0)) AS face_queries, SUM(IF(type='电话咨询',1,0)) AS call_queries, SUM(IF(type='网上咨询',1,0)) AS online_queries
			FROM query INNER JOIN staff ON staff.id=query.lawyer
			WHERE LEFT(date_start,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY lawyer
			ORDER BY face_queries DESC, call_queries DESC, online_queries DESC
		";
		$personally_type_queries=db_toArray($q_personally_type_queries);
		
		$this->view_data['chart_personally_type_queries_catogary']=json_encode(array_sub($personally_type_queries,'staff_name'));
		$chart_personally_type_queries_series=array(
			array('name'=>'网上咨询','data'=>array_sub($personally_type_queries,'online_queries')),
			array('name'=>'电话咨询','data'=>array_sub($personally_type_queries,'call_queries')),
			array('name'=>'面谈咨询','data'=>array_sub($personally_type_queries,'face_queries'))
		
		);
		$this->view_data['chart_personally_type_queries_series']=json_encode($chart_personally_type_queries_series,JSON_NUMERIC_CHECK);
	}
}
?>