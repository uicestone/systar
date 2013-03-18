<?php
class Achievement_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	/**
	 * 计算各项业绩总值
	 * $type:contracted,estimated,collected
	 * $range:total,my,contribute
	 * $time_start,$time_end采用timestamp格式
	 * $ten_thousand_unit:万元为单位
	 */
	function sum($type,$range=NULL,$time_start=NULL,$time_end=NULL,$ten_thousand_unit=true){
		if(is_null($time_start)){
			//$time_start默认为本年年初的timestamp
			$time_start=strtotime(date('Y',$this->config->item('timestamp')).'-01-01');
		}
		
		if(is_null($time_end)){
			//$time_end默认为次年年初的timestamp
			$time_end=strtotime((date('Y',$this->config->item('timestamp'))+1).'-01-01');
		}
		
		$date_start=date('Y-m-d',$time_start);
		$date_end=date('Y-m-d',$time_end);
		
		$q='';
		
		if($type=='contracted'){
			//时间范围内，签约之案的预估收费和
			$q="
				SELECT SUM(fee) AS sum FROM case_fee 
				WHERE type<>'办案费' 
					AND `case` IN (
						SELECT case.id FROM `case`
							INNER JOIN case_label reviewed ON reviewed.label_name='在办' AND reviewed.case=case.id
						WHERE
							time_contract>='$date_start' 
							AND time_contract<'$date_end'
					)
			";
			
			if($range=='my'){
				//我主办的签约
				$q.=" 
					AND `case` IN (
						SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role='主办律师'
					)
				";
			}
			
			if($range=='contribute'){
				//我贡献的签约
				$q="
					SELECT SUM(contribute_fee) AS sum
					FROM
					(
						SELECT case_fee.fee*SUM(case_people.contribute) AS contribute_fee
						FROM case_fee 
							INNER JOIN case_people ON case_fee.case=case_people.case AND case_people.type='律师'
						WHERE case_fee.type<>'办案费' 
							AND case_fee.`case` IN (
								SELECT case.id 
								FROM `case`
									INNER JOIN case_label reviewed ON reviewed.label_name='在办' AND reviewed.case=case.id
								WHERE
									time_contract>='$date_start' 
									AND time_contract<'$date_end'
							)
							AND case_people.people={$this->user->id}
						GROUP BY case_fee.id
					)case_fee_contribute";
				}
		}elseif($type=='estimated_inprocess'){
			//在办案件的预估收费
			$q="
				SELECT SUM(fee) AS sum 
				FROM case_fee 
				WHERE type<>'办案费' AND `case` IN (
						SELECT unfiled.case 
						FROM case_label unfiled
							INNER JOIN case_label fee_lock
							ON fee_lock.case=
								AND unfiled.label_name<>'案卷已归档'
								AND fee_lock.label_name='费用已锁定'
					)
					AND reviewed=0
					AND pay_date>='$date_start'
					AND pay_date<'$date_end'
			";
			
			if($range=='my'){
				$q.=" AND `case` IN (
					SELECT `case` 
					FROM case_people
					WHERE type='律师' AND people={$this->user->id} AND role='主办律师'
				)";
			}
			
			if($range=='contribute'){
				$q="
					SELECT SUM(contribute_fee) AS sum
					FROM
					(
						SELECT case_fee.fee*SUM(case_people.contribute) AS contribute_fee
						FROM case_fee 
							INNER JOIN case_people ON case_fee.case=case_people.case AND case_people.type='律师'
						WHERE case_fee.type<>'办案费' 
							AND case_fee.pay_date>='$date_start' AND case_fee.pay_date<'$date_end'
							AND case_people.people={$this->user->id}
							AND `case` IN (
								SELECT unfiled.case 
								FROM case_label unfiled
									INNER JOIN case_label fee_lock
									ON fee_lock.case=
										AND unfiled.label_name<>'案卷已归档'
										AND fee_lock.label_name='费用已锁定'
							) 
						GROUP BY case_fee.id
					)case_fee_contribute
				";
			}
		}elseif($type=='collected'){
			//已到帐的收费
			$q="
				SELECT SUM(amount) AS sum 
				FROM account 
				WHERE name <> '办案费'
					AND date>='$date_start'
					AND date<'$date_end'
			";
			
			if($range=='my'){
				$q.=" AND `case` IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role='主办律师')";
			}
			
			if($range=='contribute'){
				$q="
					SELECT SUM(contribute_amount) AS sum
					FROM
					(
						SELECT account.amount*SUM(case_people.contribute) AS contribute_amount
						FROM account 
							INNER JOIN case_people ON account.case=case_people.case AND case_people.type='律师'
						WHERE account.name <> '办案费'
							AND date>='$date_start'
							AND date<'$date_end'
							AND case_people.people={$this->user->id}
						GROUP BY account.id
					)account_contribute
				";
			}
		}elseif($type=='filed_collect'){
			//已归档案件的实际业绩
			$q="
				SELECT SUM(amount) AS sum 
				FROM account 
				WHERE name <> '办案费'
					AND date>='$date_start'
					AND date<'$date_end'
					AND `case` IN (SELECT id FROM `case` WHERE filed=1)
			";
			
			if($range=='my'){
				$q.=" AND `case` IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role='主办律师')";
			}
			
			if($range=='contribute'){
				$q="
					SELECT SUM(contribute_amount) AS sum
					FROM
					(
						SELECT account.amount*SUM(case_people.contribute) AS contribute_amount
						FROM account 
							INNER JOIN case_people ON case_people.case=account.case AND case_people.type='律师'
							INNER JOIN `case` ON case.id=account.case
							INNER JOIN case_label ON case_label.case=case.id AND case_label.label_name='案卷已归档'
						WHERE account.name <> '办案费'
							AND date>='$date_start'
							AND date<'$date_end''
							AND case_people.people={$this->user->id}
						GROUP BY account.id
					)account_contribute";
			}
		}elseif($type=='estimated'){
			//预计-全所
			 $q="
				SELECT SUM(
					IF(account.amount IS NULL,
						IF(case_fee.reviewed=1,0,case_fee.fee),
						account.amount
					)
				) AS sum
				FROM case_fee LEFT JOIN account ON case_fee.id=account.case_fee
					LEFT JOIN `case` ON case_fee.case=case.id
				WHERE case_fee.pay_date>='$date_start' AND case_fee.pay_date<'$date_end'
					AND (
							(account.date>='$date_start' AND account.date<'$date_end')
							OR account.id IS NULL
						)
			";
			
			//预计-我主办的
			if($range=='my'){
				$q.=" AND case_fee.case IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role='主办律师')";
			}
			
			//预计-我的贡献
			if($range=='contribute'){
				$q="
					SELECT SUM(estimated.fee*contribute.sum) AS sum
					FROM
					(
						SELECT IF(account.amount IS NULL,
								IF(case_fee.reviewed=1,0,case_fee.fee),
								account.amount
						) AS fee,case_fee.case
						FROM case_fee LEFT JOIN account ON case_fee.id=account.case_fee
							LEFT JOIN `case` ON case_fee.case=case.id
						WHERE case_fee.pay_date>='$date_start' AND case_fee.pay_date<'$date_end'
							AND (
									(account.date>='$date_start' AND account.date<'$date_end')
									OR account.id IS NULL
								)
					)estimated
					INNER JOIN 
					(
						SELECT `case`,SUM(contribute) AS sum 
						FROM case_people
						WHERE type='律师'
							AND people={$this->user->id}
						GROUP BY `case` HAVING sum>0
					)contribute USING (`case`)
				";
			}
		}
		
		$sum=$this->db->query($q)->row()->sum;
		
		if($ten_thousand_unit){
			$sum=$sum/1E4;
		}
		
		return round($sum,2);
	}
	
	/**
	 * 获得应收账款总数
	 * @param $type 应收账款类型：expired：过期未收，recent：近期催收
	 * @return 返回一个数组，包含num(总数)和sum(总额)两个键
	 */
	function receivableSum($type=NULL,$date_from=NULL,$date_to=NULL){

		$q="
			SELECT COUNT(case_fee.id) AS num,SUM(case_fee.fee) AS sum
			FROM case_fee
				LEFT JOIN (
					SELECT `case_fee`,SUM(amount) AS amount_sum
					FROM account
					GROUP BY `case_fee`
				)account_grouped -- 根据case_fee分组求和的account
				ON case_fee.id=account_grouped.case_fee
			WHERE case_fee.type<>'办案费'
				AND case_fee.reviewed=0
				AND (account_grouped.amount_sum IS NULL OR case_fee.fee-account_grouped.amount_sum>0) -- 款未到/未到齐
				AND case_fee.case NOT IN (
					SELECT `case` FROM case_label WHERE label_name='案卷已归档'
				)
		";
		
		if($type=='recent'){
			$q.=" AND pay_date>='{$this->config->item('date')}'";
		
		}elseif($type=='expired'){
			$q.=" AND pay_date<'{$this->config->item('date')}'";
		}
		
		if(isset($date_from)){
			$q.=" AND pay_date>='$date_from'";
		}
		
		if(isset($date_to)){
			$q.=" AND pay_date<='$date_to'";
		}

		if(!$this->user->isLogged('finance')){
			$q.=" AND case_fee.case IN (
					SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id}
				)
			";
		}
		
		//$q=$this->search($q,array('case.name'=>'案件','lawyers.names'=>'主办律师'),false);
		
		//$q=$this->dateRange($q,'pay_date',true,false);

		$result_array=$this->db->query($q)->row_array();
		if(!isset($result_array['sum'])){
			$result_array['sum']=0;
		}
		$result_array['sum']=round($result_array['sum']/1E4,2);
	
		if(!isset($result_array['num'])){
			$result_array['num']=0;
		}
	
		return $result_array;
	}
	
	/**
	 * 个人业绩列表
	 * @param array $config:array(
	 *	from
	 *	to
	 *	contribute_type
	 *	
	 * )
	 * @return type
	 */
	function getList($config=array()){
		if(!isset($config['date_from'])){
			$config['date_from']=$this->date->year_begin;
		}
		
		if(!isset($config['date_to'])){
			$config['date_to']=$this->date->today;
		}

		$q="
			SELECT account.amount, case_people.role, IF(client.abbreviation IS NULL,client.name,client.abbreviation) AS client_name, 
				account.date AS account_time,
				ROUND(account.amount*case_people.contribute) AS contribution, ROUND(account.amount*case_people.contribute*0.15) AS bonus,
				case.name AS case_name,case.id AS `case`
			FROM account
				INNER JOIN `case` ON account.case=case.id
				INNER JOIN people client ON client.type='客户' AND client.id=account.people
				INNER JOIN case_people USING(`case`)
		";
		
		$q_rows="
			SELECT COUNT(*)
			FROM account
				INNER JOIN `case` ON account.case=case.id
				INNER JOIN people client ON client.type='客户' AND client.id=account.people
				INNER JOIN case_people USING(`case`)
		";

		$where="	
			WHERE case_people.role<>'督办人'
				AND case_people.people={$this->user->id}
		";
		
		if(!isset($config['contribute_type']) || $config['contribute_type']=='fixed'){
			$where.=" AND TO_DAYS(account.date)>=TO_DAYS('{$config['date_from']}') AND TO_DAYS(account.date)<=TO_DAYS('{$config['date_to']}')";
			$where.=" AND case_people.role<>'实际贡献'";
		}else{
			$where.=" AND TO_DAYS(case.time_end)>=TO_DAYS('{$config['date_from']}') AND TO_DAYS(case.time_end)<=TO_DAYS('{$config['date_to']}')";
			$where.=" AND case_people.role='实际贡献' AND case.id IN (
				SELECT `case` FROM case_label WHERE label_name='案卷已归档'
			)";
		}
		
		$q.=$where;
		$q_rows.=$where;
		
		if(!isset($config['orderby'])){
			$config['orderby']='account_time DESC';
		}
		
		$q.=" ORDER BY ";
		if(is_array($config['orderby'])){
			foreach($config['orderby'] as $orderby){
				$q.=$orderby;
			}
		}else{
			$q.=$config['orderby'];
		}
		
		if(!isset($config['limit'])){
			$config['limit']=$this->limit($q_rows);
		}
		
		if(!isset($config['limit'])){
			$config['limit']=$this->limit($q_rows);
		}
		
		if(is_array($config['limit']) && count($config['limit'])==2){
			$q.=" LIMIT {$config['limit'][1]}, {$config['limit'][0]}";
		}elseif(is_array($config['limit']) && count($config['limit'])==1){
			$q.=" LIMIT {$config['limit'][0]}";
		}elseif(!is_array($config['limit'])){
			$q.=" LIMIT ".$config['limit'];
		}
		
		return $this->db->query($q)->result_array();
	}
	
	/**
	 * 获得应收账款列表
	 * @param $type 应收账款类型：expired：过期未收，recent：近期催收
	 */
	function getReceivableList($type=NULL){
		$q="
		SELECT case_fee.id,case_fee.type,case_fee.fee,pay_date,
			case.name AS case_name,case.id AS `case`,
			IF(account_grouped.amount_sum IS NULL,case_fee.fee,case_fee.fee-account_grouped.amount_sum) AS uncollected,
			clients.clients,
			lawyers.lawyers
		FROM case_fee
			LEFT JOIN (
				SELECT `case_fee`,SUM(amount) AS amount_sum
				FROM account
				GROUP BY `case_fee`
				)account_grouped -- 根据case_fee分组求和的account
			ON case_fee.id=account_grouped.case_fee
				
			INNER JOIN `case` ON case.id=case_fee.case
			
			LEFT JOIN
			(
				SELECT `case`,GROUP_CONCAT(people.name) AS lawyers
				FROM case_people,people
				WHERE case_people.people=people.id AND case_people.role='主办律师'
				GROUP BY case_people.`case`
			)lawyers
			ON `case_fee`.case=lawyers.`case`
		
			LEFT JOIN (
				SELECT case_people.case,GROUP_CONCAT(DISTINCT people.abbreviation) AS clients
				FROM case_people INNER JOIN people ON case_people.people=people.id
				WHERE people.type='客户'
				GROUP BY case_people.case
			)clients
			ON clients.case=case_fee.case
			
		WHERE case_fee.type<>'办案费'
			AND case_fee.reviewed=0
			AND (account_grouped.amount_sum IS NULL OR case_fee.fee-account_grouped.amount_sum>0) -- 款未到/未到齐
			AND case.id NOT IN (
				SELECT `case` FROM case_label WHERE label_name='案卷已归档'
			)
		";
		
		if($type=='recent'){
			$q.=" AND pay_date>='{$this->config->item('date')}'";
			
		}elseif($type=='expired'){
			$q.=" AND pay_date<'{$this->config->item('date')}'";
		}
		
		if(!$this->user->isLogged('finance')){
			$q.="
				AND case_fee.case IN (
					SELECT `case` FROM case_people WHERE people={$this->user->id}
				)
			";
		}
		
		$q=$this->search($q,array('case.name'=>'案件','lawyers.lawyers'=>'主办律师'));
		
		//$q=$this->dateRange($q,'pay_date',false);
		
		$q=$this->orderBy($q,'case_fee.pay_date'); //添加排序条件
		
		$q=$this->pagination($q); //添加分页设置

		return $this->db->query($q)->result_array();
	}

	function getCaseBonusList(){
		$q_cases_to_distribute="SELECT id FROM `case` INNER JOIN case_label ON case.id=case_label.case AND case_label.label_name='职员已锁定'  WHERE TRUE";
		
		if($this->input->get('contribute_type')=='actual'){
			$contribute_type='actual';
			$q_cases_to_distribute.=" AND case.id IN (
				SELECT `case` FROM case_label WHERE label_name='案卷已归档'
			)";
			//$q_cases_to_distribute=$this->dateRange($q_cases_to_distribute,'case.time_end',false);
		}else{
		  $contribute_type='fixed';
		  //$q_cases_to_distribute=$this->dateRange($q_cases_to_distribute,'account.date');
		}
		
		if($this->user->isLogged('finance') && $this->input->post('distribute')){
		  $this->db->update('account',array('distributed_'.$contribute_type=>1),"`case` IN (".$q_cases_to_distribute.")");
		}

		$q="
			SELECT staff.name AS staff_name, ROUND(SUM(case_collect.amount*case_contribute.contribute),2) AS contribute_sum,ROUND(SUM(case_collect.amount*case_contribute.contribute*0.15),2) AS bonus_sum
			FROM (
				SELECT  `case` , SUM( amount ) amount
				FROM account
				WHERE name <> '办案费'
					AND `case` IN (".$q_cases_to_distribute.")";//律师锁定的案子才能计算奖金
		$q.="  AND distributed_{$contribute_type}=0";
		
		$q.="
			GROUP BY  `case`
		)case_collect
		INNER JOIN (
			SELECT  `case` , lawyer, SUM( contribute ) AS contribute
			FROM case_lawyer
			WHERE 1=1
		";
		
		if($this->input->get('contribute_type')=='actual'){
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
		
		$q_rows="SELECT COUNT(id) FROM staff";
		
		$q=$this->orderby($q,'staff.id','ASC',array('staff_name'));
		
		$q=$this->pagination($q,$q_rows);
		return $this->db->query($q)->result_array();
		
	}
	
	//每月的咨询数，统计图用
	function getMonthlyQueries(){
		//@TODO 需要重写
		$query="
			SELECT month,queries,filed_queries,live_queries,cases
			FROM (
				SELECT LEFT(first_contact,7) AS month, COUNT(id) AS queries, SUM(IF(filed=1,1,0)) AS filed_queries, SUM(IF(filed=0,1,0)) AS live_queries
				FROM `case` 
				WHERE company={$this->company->id} AND display=1 AND is_query=1 AND LEFT(first_contact,4)='".date('Y',$this->config->item('timestamp'))."'
				GROUP BY LEFT(first_contact,7)
			)query INNER JOIN (
				SELECT LEFT(time_contract,7) AS month, COUNT(id) AS cases
				FROM `case`
				WHERE company={$this->company->id} AND display=1 AND is_query=0 AND LEFT(time_contract,4)='".date('Y',$this->config->item('timestamp'))."'
					AND id NOT IN (SELECT `case` FROM case_label WHERE label_name='内部行政')
				GROUP BY LEFT(time_contract,7)
			)`case` USING(month)
		";
		
		return $this->db->query($query)->result_array();
	}
	
	//每人咨询数，统计图用
	function getPersonallyQueries(){
		$query="
			SELECT people.name AS staff_name, COUNT(case.id) AS queries, SUM(filed AND is_query) AS filed_queries, SUM(NOT filed AND is_query) AS live_queries, SUM(NOT is_query) AS success_case
			FROM `case` 
				INNER JOIN case_people ON case.id=case_people.case 
				INNER JOIN people ON people.id=case_people.people AND case_people.role = '接洽律师'
			WHERE case.display=1 AND LEFT(first_contact,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY people.id
			ORDER BY live_queries DESC, queries DESC
		";
		
		return $this->db->query($query)->result_array();
	}
	
	//每人咨询分类数，统计图用
	function getPersonallyTypeQueries(){
		$query="
			SELECT people.name AS staff_name, COUNT(case.id) AS queries, SUM(IF(query_type.label_name='面谈',1,0)) AS face_queries, SUM(IF(query_type.label_name='电话',1,0)) AS call_queries, SUM(IF(query_type.label_name='网络',1,0)) AS online_queries
			FROM `case` 
				INNER JOIN case_people ON case.id=case_people.case 
				INNER JOIN people ON people.id=case_people.people AND case_people.role = '接洽律师'
				INNER JOIN (
					SELECT `case`,label_name FROM case_label WHERE type='咨询方式'
				)query_type ON query_type.case=case.id
			WHERE is_query=1 AND LEFT(first_contact,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY people.id
			ORDER BY face_queries DESC, call_queries DESC, online_queries DESC
		";
		
		return $this->db->query($query)->result_array();
	}
	
	//每月创收，统计图用
	function getMonthlyAchievement(){
		$query="
			SELECT month,collect.sum AS collect,contract.sum AS contract
			FROM(
				SELECT LEFT(date,7) AS `month`,SUM(amount) AS sum
				FROM account 
				GROUP BY LEFT(date,7)
			)collect LEFT JOIN
			(
				SELECT LEFT(case.time_contract,7) AS month,SUM(case_fee.fee) AS sum
				FROM case_fee INNER JOIN `case` ON case.id=case_fee.case
				GROUP BY LEFT(case.time_contract,7)
			)contract USING (month)
			-- WHERE LEFT(month,4)='".date('Y',$this->config->item('timestamp'))."'
		";
		
		return $this->db->query($query)->result_array();
	}
	
	/**
	 * 案件分类创收数，统计图用
	 * @return array(
	 *	array(
	 *		'name'=>'刑事',
	 *		'y'=>35000
	 *	),
	 *	...
	 * )
	 */
	function getCaseTypeIncome(){
		$this_year_beginning=date('Y-1-1');
		$this_month_beginning=date('Y-m-1');

		$query="
			SELECT case_type.label_name AS name, SUM( amount ) AS y, TRUE AS sliced
			FROM account
				INNER JOIN  `case` ON case.id = account.case
				INNER JOIN (
					SELECT `case`,label,label_name FROM case_label WHERE type='领域'
				)case_type ON case_type.case=account.case
			WHERE account.name <>  '办案费'
			AND date >= '$this_year_beginning'
			AND date < '$this_month_beginning'
			GROUP BY case_type.label
			ORDER BY y DESC
		";
		
		return $this->db->query($query)->result_array();
	}
	
	/**
	 * 返回一名员工一段时间内的一项奖金总和
	 */
	function myBonus(array $type,$from,$to){
		$from_date=date('Y-m-d',$from);
		$to_date=date('Y-m-d',$to);
		
		if($type[0]=='case'){
			$q="
				SELECT ROUND(SUM(account.amount*case_people.contribute)*0.15) AS bonus
				FROM account
					INNER JOIN `case` ON account.case=case.id
					INNER JOIN case_people USING(`case`)
			";

			$q.="	
				WHERE case_people.role<>'督办人'
					AND case_people.people={$this->user->id}
			";

			$contribute_type=$type[1];

			if($contribute_type=='fixed'){
				option('in_date_range') && $q.=" AND account.date>='$from_date' AND account.date<'$to_date'";
				$q.=" AND case_people.role<>'实际贡献'";
			}else{
				option('in_date_range') && $q.=" AND case.time_end>='$from_date' AND case.time_end<'$to_date'";
				$q.=" AND case_people.role='实际贡献' AND case.id IN (
					SELECT `case` FROM case_label WHERE label_name='案卷已归档'
				)";
			}
		}
		
		return $this->db->query($q)->row()->bonus;
	}
	
}
?>