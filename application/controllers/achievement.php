<?php
class Achievement extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="
		SELECT case_fee_collected.*,
			GROUP_CONCAT(DISTINCT client.abbreviation) AS clients,
			case.name AS case_name,
			ROUND(case_fee_collected.collected*case_lawyer.contribute,2) AS contribute_collected,
			ROUND(case_fee_collected.collected*case_lawyer.contribute*0.15,2) AS bonus,
			case_lawyer.role
		FROM
		(
			SELECT case_fee.id,case_fee.case,case_fee.type,
				case_fee.fee,FROM_UNIXTIME(case_fee.pay_time,'%Y-%m-%d') AS pay_time,
				SUM(account.amount) AS collected,FROM_UNIXTIME(account.time_occur,'%Y-%m-%d') AS time_occur,
				IF(SUM(account.amount) IS NULL,case_fee.fee,case_fee.fee-SUM(account.amount)) AS uncollected
			FROM case_fee
			LEFT JOIN account ON case_fee.id=account.case_fee
			WHERE case_fee.type<>'办案费'
		";
		
		$date_range_bar=dateRange($q,'account.time_occur');
		
		$q.="	GROUP BY case_fee.id
		)case_fee_collected
			INNER JOIN case_client ON case_fee_collected.case=case_client.case
			INNER JOIN client ON case_client.client=client.id
			INNER JOIN case_lawyer ON case_fee_collected.case=case_lawyer.case
			INNER JOIN case_num ON case_fee_collected.case=case_num.case
			INNER JOIN `case` ON case_fee_collected.case=case.id
		WHERE case_lawyer.lawyer='".$_SESSION['id']."'
			AND client.classification='客户'
			AND case_lawyer.role NOT IN ('督办合伙人','律师助理')
		";
		
		$q.=' GROUP BY case_fee_collected.id,case_lawyer.lawyer,case_lawyer.role
			HAVING collected>0';
		
		processOrderby($q,'case_fee_collected.pay_time','DESC');
		
		$listLocator=processMultiPage($q);
		
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
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$month_start_timestamp=strtotime(date('Y-m',$_G['timestamp']).'-1');
		$month_end_timestamp=mktime(0,0,0,date('m',$_G['timestamp'])+1,1,date('Y',$_G['timestamp']));
		
		$achievement_sum=array(
			'_field'=>array(
				'field'=>'本月',
				'total'=>'全所',
				'my'=>'主办',
				'contribute'=>'贡献'
			),
			
			'contracted'=>array(
				'field'=>'签约',
				'total'=>achievementSum('contracted','total',$month_start_timestamp),
				'my'=>achievementSum('contracted','my',$month_start_timestamp),
				'contribute'=>achievementSum('contracted','contribute',$month_start_timestamp)
			),
			
			'estimated'=>array(
				'field'=>'预计',
				'total'=>achievementSum('estimated','total',$month_start_timestamp,$month_end_timestamp),
				'my'=>achievementSum('estimated','my',$month_start_timestamp,$month_end_timestamp),
				'contribute'=>achievementSum('estimated','contribute',$month_start_timestamp,$month_end_timestamp)
			),
			
			'collected'=>array(
				'field'=>'到账',
				'total'=>achievementSum('collected','total',$month_start_timestamp),
				'my'=>achievementSum('collected','my',$month_start_timestamp),
				'contribute'=>achievementSum('collected','contribute',$month_start_timestamp)
			)
		);
		
		$achievement=achievementSum('collected','contribute',option('date_range/from_timestamp'),option('date_range/to_timestamp'),false);
		$achievement_dashboard=array(
			'_field'=>array(
				'贡献'
			),
			array(
				$achievement
			)
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$menu,true);
	}

	function recent(){
		$q="SELECT case_fee.id,case_fee.type,case_fee.fee,FROM_UNIXTIME(case_fee.pay_time,'%Y-%m-%d') AS pay_time,
			case.name AS case_name,case.id AS `case`,
			IF(account_grouped.amount_sum IS NULL,case_fee.fee,case_fee.fee-account_grouped.amount_sum) AS uncollected,
			clients.clients,
			lawyers.lawyers
		FROM case_fee
			LEFT JOIN (
				SELECT `case_fee`,SUM(amount) AS amount_sum
				FROM account
				GROUP BY `case_fee`
			)account_grouped#根据case_fee分组求和的account
			ON case_fee.id=account_grouped.case_fee
			
			LEFT JOIN (
				SELECT case_client.case,GROUP_CONCAT(DISTINCT client.abbreviation) AS clients
				FROM case_client INNER JOIN client ON case_client.client=client.id
				WHERE client.classification='客户'
				GROUP BY case_client.case
			)clients
			ON clients.case=case_fee.case
			
			LEFT JOIN
			(
				SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
				FROM case_lawyer,staff 
				WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
				GROUP BY case_lawyer.`case`
			)lawyers
			ON `case_fee`.case=lawyers.`case`
				
			INNER JOIN `case` ON case.id=case_fee.case
			
		WHERE case_fee.type<>'办案费'
			AND case_fee.reviewed=0
			AND (account_grouped.amount_sum IS NULL OR case_fee.fee-account_grouped.amount_sum>0)#款未到/未到齐
			AND case_fee.case IN (
				SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'
			)
			AND case_fee.`case` NOT IN (
				SELECT id FROM `case` WHERE filed=1
			)
			AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')>='".$_G['date']."'
		";
		
		processOrderby($q,'case_fee.pay_time');
		
		$listLocator=processMultiPage($q);
		
		$field=array(
			'type'=>array('title'=>'类别','td_title'=>'width="85px"'),
			'case_name'=>array('title'=>'案件','td_title'=>'width="25%"','content'=>'<a href="case?edit={case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
			'lawyers'=>array('title'=>'主办律师'),
			'fee'=>array('title'=>'预估','td_title'=>'width="100px"'),
			'pay_time'=>array('title'=>'时间','td_title'=>'width="100px"'),
			'uncollected'=>array('title'=>'未收','td_title'=>'width="100px"'),
			'clients'=>array('title'=>'客户')
		);
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$menu,true);
	}
	
	function expired(){
		$q="SELECT case_fee.id,case_fee.type,case_fee.fee,FROM_UNIXTIME(case_fee.pay_time,'%Y-%m-%d') AS pay_time,
			case.name AS case_name,case.id AS `case`,
			IF(account_grouped.amount_sum IS NULL,case_fee.fee,case_fee.fee-account_grouped.amount_sum) AS uncollected,
			clients.clients,
			lawyers.lawyers
		FROM case_fee
			LEFT JOIN (
				SELECT `case_fee`,SUM(amount) AS amount_sum
				FROM account
				GROUP BY `case_fee`
			)account_grouped#根据case_fee分组求和的account
			ON case_fee.id=account_grouped.case_fee
			
			LEFT JOIN (
				SELECT case_client.case,GROUP_CONCAT(DISTINCT client.abbreviation) AS clients
				FROM case_client INNER JOIN client ON case_client.client=client.id
				WHERE client.classification='客户'
				GROUP BY case_client.case
			)clients
			ON clients.case=case_fee.case
			
			INNER JOIN `case` ON case.id=case_fee.case
			
			LEFT JOIN
			(
				SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
				FROM case_lawyer,staff 
				WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
				GROUP BY case_lawyer.`case`
			)lawyers
			ON `case_fee`.case=lawyers.`case`
				
		WHERE case_fee.type<>'办案费'
			AND case_fee.reviewed=0
			AND (account_grouped.amount_sum IS NULL OR case_fee.fee-account_grouped.amount_sum>0)#款未到/未到齐
			AND case_fee.case IN (
				SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'
			)
			AND case_fee.`case` NOT IN (
				SELECT id FROM `case` WHERE filed='已归档'
			)
			AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')<'".$_G['date']."'
			AND case.filed<>'已归档'
		";
		
		processOrderby($q,'case_fee.pay_time');
		
		$listLocator=processMultiPage($q);
		
		$field=array(
			'type'=>array('title'=>'类别','td_title'=>'width="85px"'),
			'case_name'=>array('title'=>'案件','td_title'=>'width="25%"','content'=>'<a href="case?edit={case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
			'lawyers'=>array('title'=>'主办律师'),
			'fee'=>array('title'=>'预估','td_title'=>'width="100px"'),
			'pay_time'=>array('title'=>'时间','td_title'=>'width="100px"'),
			'uncollected'=>array('title'=>'未收','td_title'=>'width="100px"'),
			'clients'=>array('title'=>'客户')
		);
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$menu,true);
	}
	
	function casebonus(){
		$q="
		SELECT staff.name AS staff_name, ROUND(SUM(case_collect.amount*case_contribute.contribute),2) AS contribute_sum,ROUND(SUM(case_collect.amount*case_contribute.contribute*0.15),2) AS bonus_sum
		FROM (
			SELECT  `case` , SUM( amount ) amount
			FROM account
			WHERE name <> '办案费'
				AND `case` IN (
					SELECT id FROM `case` WHERE lawyer_lock=1";
		
		if(got('contribute_type','actual')){
			$q.=" AND case.filed='已归档'";
			$date_range_bar=dateRange($q,'case.time_end',false);
		}else{
			$date_range_bar=dateRange($q,'account.time_occur');
		}
		
		$q.="		)#律师锁定的案子才能计算奖金";
		
		$q.="
			GROUP BY  `case`
		)case_collect
		INNER JOIN (
			SELECT  `case` , lawyer, SUM( contribute ) AS contribute
			FROM case_lawyer
			WHERE 1=1
		";
		
		if(got('contribute_type','actual')){
			$q.=" AND role = '实际贡献'";
		}else{
			$q.=" AND role<>'实际贡献'";
		}
		
		$q.="	GROUP BY  `case` , lawyer
		)case_contribute ON case_collect.case = case_contribute.case
		INNER JOIN staff ON staff.id = case_contribute.lawyer
		WHERE case_contribute.contribute>0
		";
		
		$q.="GROUP BY case_contribute.lawyer";
		
		processOrderby($q,'staff.id','ASC',array('staff_name'));
		
		$q_rows="SELECT COUNT(id) FROM staff";
		
		$listLocator=processMultiPage($q,$q_rows);
		
		$field=array(
			'staff_name'=>array('title'=>'人员'),
			'contribute_sum'=>array('title'=>'合计贡献'),
			'bonus_sum'=>array('title'=>'合计奖金')
		);
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$menu,true);
	}

	function teambonus(){
		$q="
		SELECT staff.name AS staff_name,ROUND((account_sum.sum-600000)*0.04*staff.modulus,2) AS bonus_sum
		FROM staff CROSS JOIN 
		(
			SELECT SUM(amount) AS sum 
			FROM account
			WHERE name IN('律师费','顾问费','咨询费')
		";
		
		$date_range_bar=dateRange($q,'time_occur');
		
		$q.="
		)account_sum
		WHERE (account_sum.sum-600000)*0.04*staff.modulus>0
		";
		processOrderby($q,'staff.id','ASC',array('staff_name'));
		
		$q_rows="SELECT COUNT(id) FROM staff WHERE modulus>0";
		
		$listLocator=processMultiPage($q,$q_rows);
		
		$field=array(
			'staff_name'=>array('title'=>'人员'),
			'bonus_sum'=>array('title'=>'团奖')
		);
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field);
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
			WHERE LEFT(month,4)='".date('Y',$_G['timestamp'])."'
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
	}
	
	function query(){
		//TODO 新的咨询统计
		$q_monthly_queries="
		SELECT month,queries,filed_queries,live_queries,cases
		FROM (
			SELECT LEFT(date_start,7) AS month, COUNT(id) AS queries, SUM(IF(filed=1,1,0)) AS filed_queries, SUM(IF(filed='洽谈',1,0)) AS live_queries
			FROM query 
			WHERE LEFT(date_start,4)='".date('Y',$_G['timestamp'])."'
			GROUP BY LEFT(date_start,7)
		)query INNER JOIN (
			SELECT LEFT(time_contract,7) AS month, COUNT(id) AS cases
			FROM `case`
			WHERE LEFT(time_contract,4)='".date('Y',$_G['timestamp'])."'
			GROUP BY LEFT(time_contract,7)
		)`case` USING(month)";
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
			SELECT staff.name AS staff_name, COUNT(query.id) AS queries, SUM(IF(filed='归档',1,0)) AS filed_queries, SUM(IF(filed='洽谈',1,0)) AS live_queries
			FROM query INNER JOIN staff ON staff.id=query.lawyer
			WHERE LEFT(date_start,4)='".date('Y',$_G['timestamp'])."'
			GROUP BY lawyer
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
			SELECT staff.name AS staff_name, COUNT(query.id) AS queries, SUM(IF(type='面谈咨询',1,0)) AS face_queries, SUM(IF(type='电话咨询',1,0)) AS call_queries, SUM(IF(type='网上咨询',1,0)) AS online_queries
			FROM query INNER JOIN staff ON staff.id=query.lawyer
			WHERE LEFT(date_start,4)='".date('Y',$_G['timestamp'])."'
			GROUP BY lawyer
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
}
?>