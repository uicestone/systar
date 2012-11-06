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
							AND time_contract>='".$date_start."' 
							AND time_contract<'".$date_end."'
					)
			";
			
			if($range=='my'){
				//我主办的签约
				$q.=" 
					AND `case` IN (
						SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师'
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
							AND `case` IN (SELECT id FROM `case` WHERE is_reviewed=1 AND time_contract>='".$date_start."' AND time_contract<'".$date_end."')
							AND case_lawyer.lawyer='".$_SESSION['id']."'
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
					AND pay_time>=".$time_start.' 
					AND pay_time<'.$time_end;
			
			if($range=='my'){
				$q.=" AND `case` IN (
					SELECT `case` 
					FROM case_lawyer 
					WHERE lawyer='".$_SESSION['id']."' AND role='主办律师'
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
							AND case_fee.pay_time>=".$time_start.' AND case_fee.pay_time<'.$time_end."
							AND case_lawyer.lawyer='".$_SESSION['id']."'
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
					AND time_occur>='".$time_start."'
					AND time_occur<'".$time_end."'";
			
			if($range=='my'){
				$q.=" AND `case` IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";
			}
			
			if($range=='contribute'){
				$q="
					SELECT SUM(contribute_amount)
					FROM
					(
						SELECT account.amount*SUM(case_lawyer.contribute) AS contribute_amount
						FROM account INNER JOIN case_lawyer USING (`case`)
						WHERE account.name <> '办案费'
							AND time_occur>=".$time_start."
							AND time_occur<".$time_end."
							AND case_lawyer.lawyer='".$_SESSION['id']."'
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
					AND time_occur>=".$time_start."
					AND time_occur<".$time_end."
					AND `case` IN (SELECT id FROM `case` WHERE filed=1)";
			
			if($range=='my'){
				$q.=" AND `case` IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";
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
							AND time_occur>=".$time_start."
							AND time_occur<".$time_end."
							AND case_lawyer.lawyer='".$_SESSION['id']."'
							AND case.filed=1
						GROUP BY account.id
					)account_contribute";
			}
		}elseif($type=='estimated'){
			$q="
				SELECT SUM(IF(account.amount IS NULL,case_fee.fee,account.amount)) AS sum
				FROM case_fee LEFT JOIN account ON case_fee.id=account.case_fee
				WHERE case_fee.pay_time>='$time_start' AND case_fee.pay_time<'$time_end'
					AND (
							(account.time_occur>='$time_start' AND account.time_occur<'$time_end')
							OR account.id IS NULL
						)
			";
			
			if($range=='my'){
				$q.=" AND case_fee.case IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";
			}
			
			if($range=='contribute'){
				$q="
				SELECT SUM(estimated.fee*contribute.sum)
				FROM
				(
					SELECT IF(account.amount IS NULL,case_fee.fee,account.amount) AS fee,case_fee.case
					FROM case_fee LEFT JOIN account ON case_fee.id=account.case_fee
						INNER JOIN case_lawyer ON case_lawyer.id=case_fee.case
					WHERE case_fee.pay_time>='$time_start' AND case_fee.pay_time<'$time_end'
						AND (
								(account.time_occur>='$time_start' AND account.time_occur<'$time_end')
								OR account.id IS NULL
							)
						AND case_fee.case IN (SELECT id FROM `case` WHERE is_reviewed=1)
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
		
		global $_G;
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
					AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')>='".$_G['date']."'
					AND pay_time<'".($_G['timestamp']+86400*30)."'
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
					AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')<'".$_G['date']."'
			";
		}
	
		$result_array=db_fetch_first($q);
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
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
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
}
?>