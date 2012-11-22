<?php
class Achievement_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function sum($type,$range=NULL,$time_start=NULL,$time_end=NULL,$ten_thousand_unit=true){
		/*
		计算各项业绩总值
		$type:contracted,estimated,collected
		$range:total,my,contribute
		$time_start,$time_end采用timestamp格式
		$ten_thousand_unit:万元为单位
		*/
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
						SELECT id FROM `case` 
						WHERE is_reviewed=1
							AND time_contract>='$date_start' 
							AND time_contract<'$date_end'
					)
			";
			
			if($range=='my'){
				//我主办的签约
				$q.=" 
					AND `case` IN (
						SELECT `case` FROM case_lawyer WHERE lawyer='{$_SESSION['id']}' AND role='主办律师'
					)
				";
			}
			
			if($range=='contribute'){
				//我贡献的签约
				$q="
					SELECT SUM(contribute_fee)
					FROM
					(
						SELECT case_fee.fee*SUM(case_lawyer.contribute) AS contribute_fee
						FROM case_fee INNER JOIN case_lawyer USING (`case`)
						WHERE case_fee.type<>'办案费' 
							AND `case` IN (SELECT id FROM `case` WHERE is_reviewed=1 AND time_contract>='$date_start' AND time_contract<'$date_end')
							AND case_lawyer.lawyer='{$_SESSION['id']}'
						GROUP BY case_fee.id
					)case_fee_contribute";
				}
		}elseif($type=='estimated_inprocess'){
			//在办案件的预估收费
			$q="
				SELECT SUM(fee) AS sum 
				FROM case_fee 
				WHERE type<>'办案费' AND `case` IN (SELECT id FROM `case` WHERE filed=0 AND fee_lock=1)
					AND reviewed=0
					AND pay_time>='$time_start'
					AND pay_time<'$time_end'
			";
			
			if($range=='my'){
				$q.=" AND `case` IN (
					SELECT `case` 
					FROM case_lawyer 
					WHERE lawyer='{$_SESSION['id']}' AND role='主办律师'
				)";
			}
			
			if($range=='contribute'){
				$q="
					SELECT SUM(contribute_fee)
					FROM
					(
						SELECT case_fee.fee*SUM(case_lawyer.contribute) AS contribute_fee
						FROM case_fee INNER JOIN case_lawyer USING (`case`)
						WHERE case_fee.type<>'办案费' 
							AND case_fee.pay_time>='$time_start' AND case_fee.pay_time<'$time_end'
							AND case_lawyer.lawyer='{$_SESSION['id']}'
							AND `case` IN (SELECT id FROM `case` WHERE filed=0 AND fee_lock=1
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
					AND time_occur>='$time_start'
					AND time_occur<'$time_end'
			";
			
			if($range=='my'){
				$q.=" AND `case` IN (SELECT `case` FROM case_lawyer WHERE lawyer='{$_SESSION['id']}' AND role='主办律师')";
			}
			
			if($range=='contribute'){
				$q="
					SELECT SUM(contribute_amount)
					FROM
					(
						SELECT account.amount*SUM(case_lawyer.contribute) AS contribute_amount
						FROM account INNER JOIN case_lawyer USING (`case`)
						WHERE account.name <> '办案费'
							AND time_occur>='$time_start'
							AND time_occur<'$time_end'
							AND case_lawyer.lawyer='{$_SESSION['id']}'
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
					AND time_occur>='$time_start'
					AND time_occur<'$time_end'
					AND `case` IN (SELECT id FROM `case` WHERE filed=1)
			";
			
			if($range=='my'){
				$q.=" AND `case` IN (SELECT `case` FROM case_lawyer WHERE lawyer='{$_SESSION['id']}' AND role='主办律师')";
			}
			
			if($range=='contribute'){
				$q="
					SELECT SUM(contribute_amount)
					FROM
					(
						SELECT account.amount*SUM(case_lawyer.contribute) AS contribute_amount
						FROM account INNER JOIN case_lawyer USING (`case`)
							INNER JOIN `case` ON case.id=account.case
						WHERE account.name <> '办案费'
							AND time_occur>='$time_start'
							AND time_occur<'$time_end''
							AND case_lawyer.lawyer='{$_SESSION['id']}'
							AND case.filed=1
						GROUP BY account.id
					)account_contribute";
			}
		}elseif($type=='estimated'){
			//预计-全所
			 $q="
				SELECT SUM(
					IF(account.amount IS NULL,
						IF(case_fee.reviewed=1 OR case.fee_lock=0,0,case_fee.fee),
						account.amount
					)
				) AS sum
				FROM case_fee LEFT JOIN account ON case_fee.id=account.case_fee
					LEFT JOIN `case` ON case_fee.case=case.id
				WHERE case_fee.pay_time>='$time_start' AND case_fee.pay_time<'$time_end'
					AND (
							(account.time_occur>='$time_start' AND account.time_occur<'$time_end')
							OR account.id IS NULL
						)
			";
			
			//预计-我主办的
			if($range=='my'){
				$q.=" AND case_fee.case IN (SELECT `case` FROM case_lawyer WHERE lawyer='{$_SESSION['id']}' AND role='主办律师')";
			}
			
			//预计-我的贡献
			if($range=='contribute'){
				$q="
					SELECT SUM(estimated.fee*contribute.sum)
					FROM
					(
						SELECT IF(account.amount IS NULL,
								IF(case_fee.reviewed=1 OR case.fee_lock=0,0,case_fee.fee),
								account.amount
						) AS fee,case_fee.case
						FROM case_fee LEFT JOIN account ON case_fee.id=account.case_fee
							LEFT JOIN `case` ON case_fee.case=case.id
						WHERE case_fee.pay_time>='$time_start' AND case_fee.pay_time<'$time_end'
							AND (
									(account.time_occur>='$time_start' AND account.time_occur<'$time_end')
									OR account.id IS NULL
								)
					)estimated
					INNER JOIN 
					(
						SELECT `case`,SUM(contribute) AS sum 
						FROM case_lawyer 
						WHERE lawyer='{$_SESSION['id']}' 
						GROUP BY `case` HAVING sum>0
					)contribute USING (`case`)
				";
			}
		}
		
		$sum=db_fetch_field($q);
		
		if($ten_thousand_unit){
			$sum=$sum/1E4;
		}
		
		return round($sum,2);
	}
	
	function todo($type){
		/*未实现的业绩
		$type:recent(近期催收)，expired(过期未收)
		返回一个数组，包含num(总数)和sum(总额)两个键
		*/
		
		if($type=='recent'){
			$q="
				SELECT COUNT(case_fee.id) AS num,SUM(case_fee.fee) AS sum
				FROM case_fee
					LEFT JOIN (
						SELECT `case_fee`,SUM(amount) AS amount_sum
						FROM account
						GROUP BY `case_fee`
					)account_grouped#根据case_fee分组求和的account
					ON case_fee.id=account_grouped.case_fee
				WHERE case_fee.type<>'办案费'
					AND case_fee.reviewed=0
					AND (account_grouped.amount_sum IS NULL OR case_fee.fee-account_grouped.amount_sum>0)#款未到/未到齐
					AND case_fee.case IN (
						SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'
					)
					AND `case` NOT IN (
						SELECT id FROM `case` WHERE filed=1
					)
					AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')>='".$this->config->item('date')."'
					AND pay_time<'".($this->config->item('timestamp')+86400*30)."'
			";
		}elseif($type=='expired'){
			$q="
				SELECT COUNT(case_fee.id) AS num,SUM(case_fee.fee) AS sum
				FROM case_fee
					LEFT JOIN (
						SELECT `case_fee`,SUM(amount) AS amount_sum
						FROM account
						GROUP BY `case_fee`
					)account_grouped#根据case_fee分组求和的account
					ON case_fee.id=account_grouped.case_fee
				WHERE case_fee.type<>'办案费'
					AND case_fee.reviewed=0
					AND (account_grouped.amount_sum IS NULL OR case_fee.fee-account_grouped.amount_sum>0)#款未到/未到齐
					AND case_fee.case IN (
						SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'
					)
					AND `case` NOT IN (
						SELECT id FROM `case` WHERE filed=1
					)
					AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')<'".$this->config->item('date')."'
			";
		}
	
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
	
	function getList(){
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
		$q=$this->dateRange($q,'account.time_occur');
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
		$q=$this->orderBy($q,'case_fee_collected.pay_time','DESC');
		$q=$this->pagination($q);
		return $this->db->query($q)->result_array();
	}
	
	function getRecentList(){
		$q="
		SELECT case_fee.id,case_fee.type,case_fee.fee,FROM_UNIXTIME(case_fee.pay_time,'%Y-%m-%d') AS pay_time,
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
			AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')>='".$this->config->item('date')."'
		";
		
		$q=$this->orderBy($q,'case_fee.pay_time'); //添加排序条件
		
		$q=$this->pagination($q); //添加分页设置
		
		return $this->db->query($q)->result_array();
		
	}
	
	function getExpiredList(){
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
			AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')<'".$this->config->item('date')."'
			AND case.filed<>'已归档'
		";
		
		$q=$this->orderBy($q,'case_fee.pay_time'); //添加排序条件
		
		$q=$this->pagination($q); //添加分页设置
		
		return $this->db->query($q)->result_array();
	}

	function getCaseBonusList(){
		$q_cases_to_distribute="SELECT id FROM `case` WHERE lawyer_lock=1";
		
		if(got('contribute_type','actual')){
		  $contribute_type='actual';
		  $q_cases_to_distribute.=" AND case.filed=1";
		  $q_cases_to_distribute=$this->dateRange($q_cases_to_distribute,'case.time_end',false);
		}else{
		  $contribute_type='fixed';
		  $q_cases_to_distribute=$this->dateRange($q_cases_to_distribute,'account.time_occur');
		}
		
		if(is_logged('finance') && $this->input->post('distribute')){
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
	
	function getTeambonusList(){
		
		$q="
		SELECT
			staff.name AS staff_name,
			ROUND((account_sum.sum-600000)*0.04*staff.modulus/(SELECT SUM(modulus) FROM `staff` WHERE company=1 AND modulus>0),2) AS bonus_sum
		FROM staff CROSS JOIN
		(
			SELECT SUM(amount) AS sum
			FROM account
			WHERE name <> '办案费'
		";
		
		$date_range_bar=$this->dateRange($q,'time_occur');
		
		$q.="
		)account_sum
		WHERE (account_sum.sum-600000)*0.04*staff.modulus>0
		";
		
		$q_rows="SELECT COUNT(id) FROM staff WHERE modulus>0";
		
		$q=$this->orderby($q,'staff.id','ASC',array('staff_name'));
		
		$q=$this->pagination($q,$q_rows);
		return $this->db->query($q)->result_array();
		
	}
	
	//每月的咨询数，统计图用
	function getMonthlyQueries(){
		$query="
			SELECT month,queries,filed_queries,live_queries,cases
			FROM (
				SELECT LEFT(first_contact,7) AS month, COUNT(id) AS queries, SUM(IF(filed=1,1,0)) AS filed_queries, SUM(IF(filed=0,1,0)) AS live_queries
				FROM `case` 
				WHERE is_query=1 AND LEFT(first_contact,4)='".date('Y',$this->config->item('timestamp'))."'
				GROUP BY LEFT(first_contact,7)
			)query INNER JOIN (
				SELECT LEFT(time_contract,7) AS month, COUNT(id) AS cases
				FROM `case`
				WHERE is_query=0 AND LEFT(time_contract,4)='".date('Y',$this->config->item('timestamp'))."'
				GROUP BY LEFT(time_contract,7)
			)`case` USING(month)
		";
		
		return $this->db->query($query)->result_array();
	}
	
	//每人咨询数，统计图用
	function getPersonallyQueries(){
		$query="
			SELECT staff.name AS staff_name, COUNT(case.id) AS queries, SUM(filed AND is_query) AS filed_queries, SUM(NOT filed AND is_query) AS live_queries, SUM(NOT is_query) AS success_case			FROM `case` 
				INNER JOIN case_lawyer ON case.id=case_lawyer.case 
				INNER JOIN staff ON staff.id=case_lawyer.lawyer AND case_lawyer.role = '接洽律师'
			WHERE display=1 AND LEFT(first_contact,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY staff.id
			ORDER BY live_queries DESC, queries DESC
		";
		
		return $this->db->query($query)->result_array();
	}
	
	//每人咨询分类数，统计图用
	function getPersonallyTypeQueries(){
		$query="
			SELECT staff.name AS staff_name, COUNT(case.id) AS queries, SUM(IF(query_type='面谈咨询',1,0)) AS face_queries, SUM(IF(query_type='电话咨询',1,0)) AS call_queries, SUM(IF(query_type='网上咨询',1,0)) AS online_queries
			FROM `case` 
				INNER JOIN case_lawyer ON case.id=case_lawyer.case 
				INNER JOIN staff ON staff.id=case_lawyer.lawyer AND case_lawyer.role = '接洽律师'
			WHERE is_query=1 AND LEFT(first_contact,4)='".date('Y',$this->config->item('timestamp'))."'
			GROUP BY staff.id
			ORDER BY face_queries DESC, call_queries DESC, online_queries DESC
		";
		
		return $this->db->query($query)->result_array();
	}
	
	//每月创收，统计图用
	function getMonthlyAchievement(){
		$query="
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
		
		return $this->db->query($query)->result_array();
	}
	
	//案件分类创收数，统计图用
	function getCaseTypeIncome(){
		$this_year_beginning=strtotime(date('Y-1-1'));
		$this_month_beginning=strtotime(date('Y-m-1'));

		$query="
			SELECT case.type, SUM( amount ) AS sum
			FROM account
			INNER JOIN  `case` ON case.id = account.case
			WHERE account.name <>  '办案费'
			AND time_occur >= $this_year_beginning
			AND time_occur < $this_month_beginning
			GROUP BY case.type
			ORDER BY sum
		";
		
		return $this->db->query($query)->result_array();
	}
}
?>