<?php
class Cases_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	/**
	 * 抓取一条案件信息
	 * @param int $id 案件id
	 * @param mixed $field 需要指定抓取的字段，留空则返回整个数组
	 * @return 一条信息的数组，或者一个字段的值，如果指定字段且字段不存在，返回false
	 */
	function fetch($id,$field=NULL){
		//finance和manager可以看到所有案件，其他律师只能看到自己涉及的案件
		$query="
			SELECT * 
			FROM `case` 
			WHERE id='".$id."' 
				AND ( '".(is_logged('manager') || is_logged('finance') || is_logged('admin'))."'=1 OR uid='".$_SESSION['id']."' OR id IN (
					SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'
				))
		";
		$row=$this->db->query($query)->row_array();

		if(is_null($field)){
			return $row;
		}elseif(isset($row[$field])){
			return $row['field'];
		}else{
			return false;
		}
	}
	
	function add($data){
		$field=db_list_fields('case');
	    $data=array_keyfilter($data,$field);
	    $data['display']=1;
	    $data+=uidTime();
	
	    return db_insert('case',$data);
	}
	
	function update($case_id,$data){
		$field=db_list_fields('case');
	    $data=array_keyfilter($data,$field);
		$data+=uidTime();
	    
		return $this->db_update('case',$data,"id='".$case_id."'");
	}
	
	function addDocument($case,$data){
		$field=array('name','type','doctype','doctype_other','size','comment');
		$data=array_keyfilter($data,$field);
		$data['case']=$case;
		$data+=uidTime();
		
		return db_insert('case_document',$data);
	}
	
	function addFee($case,$data){
	    $field=array('fee','type','receiver','condition','pay_time','comment');
		$data=array_keyfilter($data,$field);
		$data['case']=$case;
		$data+=uidTime();
		return db_insert('case_fee',$data);
	}
	
	function addFeeTiming($case,$data){
		//TODO case_addFeeTiming
	}
	
	function addLawyer($case,$data){
		if(!isset($data['lawyer'])){
			return false;
		}
		
		$field=array('lawyer','role','hourly_fee','contribute');
		foreach($data as $key => $value){
			if(!in_array($key,$field)){
				unset($data[$key]);
			}
		}
		
		$data['case']=$case;
		
		$data+=uidTime();
		
		return db_insert('case_lawyer',$data);
	}
	function getStatus($is_reviewed,$locked,$apply_file,$is_query,$finance_review,$info_review,$manager_review,$filed,$contribute_sum,$uncollected){
		$status_expression='';
	
		$file_review=array(
			'finance'=>$finance_review,
			'info'=>$info_review,
			'manager'=>$manager_review,
			'filed'=>$filed
		);
		
		$file_review_name=array(
			'finance'=>'财务审核',
			'info'=>'信息审核',
			'manager'=>'主管审核',
			'filed'=>'实体归档'
		);
		
		$status_color=array(
			-1=>'800',//红：异常
			0=>'000',//黑：正常
			1=>'080',//绿：完成
			2=>'F80',//黄：警告，提示
			3=>'08F',//蓝：超目标完成
			4=>'888'//灰：忽略
		);
		
		if($is_query){
			return '咨询';
	
		}elseif($apply_file){
			$review_status=0;//归档审核状态
			if($finance_review==1 && $info_review==1 && $manager_review==1){
				$review_status=1;
			}elseif($finance_review==-1 || $info_review==-1 || $manager_review==-1){
				$review_status=-1;
			}elseif($finance_review==1 || $info_review==1 || $manager_review==1){
				$review_status=2;
			}
			
			$review_status_string='';
			
			foreach($file_review as $name => $value){
				switch($value){
					case 1:$review_status_string.=$file_review_name[$name].'：通过';break;
					case -1:$review_status_string.=$file_review_name[$name].'：驳回';break;
					case 0:$review_status_string.=$file_review_name[$name].'：等待';
				}
				$review_status_string.="\n";
			}
			
			$status_expression.='<span title="'.$review_status_string.'" style="color:#'.$status_color[$review_status].'">归</span>';
	
		}else{
			$review_status_string='';
			switch($is_reviewed){
				case 1:$review_status_string.='立案审核：通过';break;
				case -1:$review_status_string.='立案审核：驳回';break;
				case 0:$review_status_string.='立案审核：等待';
			}
			$status_expression.='<span title="'.$review_status_string.'" style="color:#'.$status_color[$is_reviewed].'">立</span>';
		}
		
		if($locked){
			$status_expression.='<span title="已锁定" style="color:#080">锁</span>';
		}else{
			$status_expression.='<span title="部分未锁定" style="color:#800">锁</span>';
		}
		
		if($contribute_sum<0.7){
			$status_expression.='<span title="贡献已分配'.($contribute_sum*100).'%" style="color:#800">配</span>';
		}elseif($contribute_sum<1){
			$status_expression.='<span title="贡献已分配'.($contribute_sum*100).'%" style="color:#F80">配</span>';
		}else{
			$status_expression.='<span title="贡献已分配'.($contribute_sum*100).'%" style="color:#080">配</span>';
		}
		
		if($uncollected>0){
			$status_expression.='<span title="未收款：'.$uncollected.'"元 style="color:#800">款</span>';
		}elseif($uncollected<0){
			$status_expression.='<span title="费用已到账（超预估收款：'.-$uncollected.'元）" style="color:#08F">款</span>';
		}elseif($uncollected==='0.00'){
			$status_expression.='<span title="费用已到账" style="color:#080">款</span>';
		}else{
			$status_expression.='<span title="未预估收费" style="color:#888">款</span>';
		}
			
		return $status_expression;
	}
	
	function getStatusById($case_id){
		$case_data=db_fetch_first("SELECT is_reviewed,type_lock,client_lock,lawyer_lock,fee_lock,is_query,apply_file,finance_review,info_review,manager_review,filed FROM `case` WHERE id = '".$case_id."'");
		extract($case_data);
		if($type_lock && $client_lock && $lawyer_lock && $fee_lock){
			$locked=true;
		}else{
			$locked=false;
		}
		
		$uncollected=db_fetch_field("
			SELECT IF(amount_sum IS NULL,fee_sum,fee_sum-amount_sum) AS uncollected FROM
			(
				SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' AND reviewed=0 AND `case`='".$case_id."'
			)case_fee_grouped
			INNER JOIN
			(
				SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE `case`='".$case_id."'
			)account_grouped
			USING (`case`)
		");
		
		$contribute_sum=db_fetch_field("
			SELECT SUM(contribute) AS contribute_sum
			FROM case_lawyer
			WHERE `case`='".$case_id."'
		");
		
		return $this->cases->getStatus($is_reviewed,$locked,$apply_file,$is_query,$finance_review,$info_review,$manager_review,$filed,$contribute_sum,$uncollected);
	}
	
	function reviewMessage($reviewWord,$lawyers){
		$message='案件[url=http://sys.lawyerstars.com/cases/edit/'.post('cases/id').']'.strip_tags(post('cases/name')).'[/url]'.$reviewWord.'，"'.post('review_message').'"';
		foreach($lawyers as $lawyer){
			sendMessage($lawyer,$message);
		}
	}
	
	function getIdByCaseFee($case_fee){
		return db_fetch_field("SELECT `case` FROM case_fee WHERE id='".intval($case_fee)."'",'case');
	}
	
	/**
	 * 获得与一个客户相关的所有案件
	 * @param type $client_id
	 * @return 一个案件列表，包含案件名称，案号和主办律师
	 */
	function getListByClient($client_id){
		$query="
			SELECT case.id,case.name AS case_name,case.num,	
				GROUP_CONCAT(DISTINCT staff.name) AS lawyers
			FROM `case`
				LEFT JOIN case_lawyer ON (case.id=case_lawyer.case AND case_lawyer.role='主办律师')
				LEFT JOIN staff ON staff.id=case_lawyer.lawyer
			WHERE case.id IN (
				SELECT `case` FROM case_client WHERE client='{$client_id}'
			)
			GROUP BY case.id
			HAVING id IS NOT NULL
		";
		return $this->db->query($query)->result_array();

	}

	/*
		日志添加界面，根据日志类型获得案件列表
		$schedule_type:0:案件,1:所务,2:营销
	*/
	function getListByScheduleType($schedule_type){
		
		$option_array=array();
		
		$q_option_array="SELECT id,name FROM `case` WHERE display=1";
		
		if($schedule_type==0){
			$q_option_array.=" AND ((id>=20 AND filed=0 AND (id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."') OR uid = '".$_SESSION['id']."')) OR id=10)";
		
		}elseif($schedule_type==1){
			$q_option_array.=" AND id<10 AND id>0";
		
		}elseif($schedule_type==2){
			$q_option_array.=" AND id<=20 AND id>10";
	
		}
		
		$q_option_array.=" ORDER BY time_contract DESC";
		
		$option_array=db_toArray($q_option_array);
		$option_array=array_sub($option_array,'name','id');
		
		foreach($option_array as $case_id => $case_name){
			$option_array[$case_id]=strip_tags($case_name);
		}
	
		return $option_array;	
	}
	
	//根据客户id获得其参与案件的收费
	function getFeeListByClient($client_id){
		$option_array=array();
		
		$q_option_array="
			SELECT case_fee.id,case_fee.type,case_fee.fee,case_fee.pay_time,case_fee.receiver,case.name
			FROM case_fee INNER JOIN `case` ON case_fee.case=case.id
			WHERE case.id IN (SELECT `case` FROM case_client WHERE client='".$client_id."')";
		
		$r_option_array=db_query($q_option_array);
		
		while($a_option_array=mysql_fetch_array($r_option_array)){
			$option_array[$a_option_array['id']]=strip_tags($a_option_array['name']).'案 '.$a_option_array['type'].' ￥'.$a_option_array['fee'].' '.date('Y-m-d',$a_option_array['pay_time']).($a_option_array['type']=='办案费'?' '.$a_option_array['receiver'].'收':'');
		}
	
		return $option_array;	
	}
	
	//根据案件ID获得收费array
	function getFeeOptions($case_id){
		$option_array=array();
		
		$q_option_array="
			SELECT case_fee.id,case_fee.type,case_fee.fee,case_fee.pay_time,case_fee.receiver,case.name
			FROM case_fee INNER JOIN `case` ON case_fee.case=case.id
			WHERE case.id='".$case_id."'";
		
		$r_option_array=db_query($q_option_array);
		
		while($a_option_array=db_fetch_array($r_option_array)){
			$option_array[$a_option_array['id']]=strip_tags($a_option_array['name']).'案 '.$a_option_array['type'].' ￥'.$a_option_array['fee'].' '.date('Y-m-d',$a_option_array['pay_time']).($a_option_array['type']=='办案费'?' '.$a_option_array['receiver'].'收':'');
		}
	
		return $option_array;	
	}
	
	function feeConditionPrepend($case_fee_id,$new_condition){
		global $_G;
		
		$this->db_update('case_fee',array('condition'=>"_CONCAT('".$new_condition."\\n',`condition`)_",'uid'=>$_SESSION['id'],'username'=>$_SESSION['username'],'time'=>$this->config->item('timestamp')),"id='".$case_fee_id."'");
		
		return db_fetch_field("SELECT `condition` FROM case_fee WHERE id = '".$case_fee_id."'");
	}
	
	function addClient($case_id,$client_id,$role){
		return db_insert('case_client',array('case'=>$case_id,'client'=>$client_id,'role'=>$role));
	}
	
	//增减案下律师的时候自动计算贡献
	function calcContribute($case_id){
		$case_lawyer_array=db_toArray("SELECT id,lawyer,role FROM case_lawyer WHERE `case`='".$case_id."'");
		
		$case_lawyer_array=array_sub($case_lawyer_array,'role','id');
	
		//各角色计数器
		$role_count=array('接洽律师'=>0,'接洽律师（次要）'=>0,'主办律师'=>0,'协办律师'=>0,'律师助理'=>0);
	
		foreach($case_lawyer_array as $id => $role){
			if(!isset($role_count[$role])){
				$role_count[$role]=0;
			}
			$role_count[$role]++;
		}
		
		$contribute=array('接洽'=>0.15,'办案'=>0.35);
		if(isset($role_count['信息提供（10%）']) && $role_count['信息提供（10%）']==1 && !isset($role_count['信息提供（20%）'])){
			$contribute['接洽']=0.25;
		}
		
		foreach($case_lawyer_array as $id=>$role){
			if($role=='接洽律师（次要）' && isset($role_count['接洽律师']) && $role_count['接洽律师']==1){
				$this->db_update('case_lawyer',array('contribute'=>$contribute['接洽']*0.3),"id='".$id."'");
	
			}elseif($role=='接洽律师'){
				if(isset($role_count['接洽律师（次要）']) && $role_count['接洽律师（次要）']==1){
					$this->db_update('case_lawyer',array('contribute'=>$contribute['接洽']*0.7),"id='".$id."'");
				}else{
					$this->db_update('case_lawyer',array('contribute'=>$contribute['接洽']/$role_count[$role]),"id='".$id."'");
				}
	
			}elseif($role=='主办律师'){
				if(isset($role_count['协办律师']) && $role_count['协办律师']){
					$this->db_update('case_lawyer',array('contribute'=>($contribute['办案']-0.05)/$role_count[$role]),"id='".$id."'");
				}else{
					$this->db_update('case_lawyer',array('contribute'=>$contribute['办案']/$role_count[$role]),"id='".$id."'");
				}
	
			}elseif($role=='协办律师'){
				$this->db_update('case_lawyer',array('contribute'=>0.05/$role_count[$role]),"id='".$id."'");
			}
		}
	}
	
	function lawyerRoleCheck($case_id,$new_role,$actual_contribute=NULL){
		if(strpos($new_role,'信息提供')!==false && db_fetch_field("SELECT SUM(contribute) FROM case_lawyer WHERE role LIKE '信息提供%' AND `case`='".$case_id."'")+substr($new_role,15,2)/100>0.2){
			//信息贡献已达到20%
			showMessage('信息提供贡献已满额','warning');
			return false;
			
		}elseif(strpos($new_role,'接洽律师')!==false && db_fetch_field("SELECT COUNT(id) FROM case_lawyer WHERE role LIKE '接洽律师%' AND `case`='".$case_id."'")>=2){
			//接洽律师已达到2名
			showMessage('接洽律师不能超过2位','warning');
			return false;
		}
		
		if($new_role=='信息提供（20%）'){
			return 0.2;
	
		}elseif($new_role=='信息提供（10%）'){
			return 0.1;
	
		}elseif($new_role=='实际贡献'){
			$actual_contribute=$actual_contribute/100;
			
			if(!$actual_contribute){
				$actual_contribute_left=
					0.3-db_fetch_field("SELECT SUM(contribute) FROM case_lawyer WHERE `case`='".$case_id."' AND role='实际贡献'");
				if($actual_contribute_left>0){
					return $actual_contribute_left;
				}else{
					showMessage('实际贡献额已分配完','warning');
					return false;
				}
				
			}elseif(db_fetch_field("SELECT SUM(contribute) FROM case_lawyer WHERE `case`='".$case_id."' AND role='实际贡献'")+($actual_contribute/100)>0.3){
				showMessage('实际贡献总数不能超过30%','warning');
				return false;
	
			}else{
				return $actual_contribute;
			}
		}else{
			return 0;
		}
	}
	
	function getRoles($case_id){
		if($case_role=db_toArray("SELECT lawyer,role FROM case_lawyer WHERE `case`='".$case_id."'")){
			return $case_role;
		}else{
			return false;
		}
	}
	
	function getPartner($case_role){
		if(empty($case_role)){
			return false;
		}
		foreach($case_role as $lawyer_role){
			if($lawyer_role['role']=='督办合伙人'){
				return $lawyer_role['lawyer'];
			}
		}
		return false;
	}
	
	function getlawyers($case_role){
		if(empty($case_role)){
			return false;
		}
		$lawyers=array();
		foreach($case_role as $lawyer_role){
			if(!in_array($lawyer_role['lawyer'],$lawyers) && $lawyer_role['role']!='督办合伙人'){
				$lawyers[]=$lawyer_role['lawyer'];
			}
		}
		return $lawyers;
	}
	
	function getMyRoles($case_role){
		if(empty($case_role)){
			return false;
		}
		$my_role=array();
		foreach($case_role as $lawyer_role){
			if($lawyer_role['lawyer']==$_SESSION['id']){
				$my_role[]=$lawyer_role['role'];
			}
		}
		return $my_role;
	}
	
	function getClientList($case_id){
	//案件相关人信息
		$query="
			SELECT case_client.id,case_client.client,case_client.role,
				client.abbreviation AS client_name,client.classification,
				default_contact.contact,default_contact.name AS contact_name,default_contact.classification AS contact_classification,
				if(LENGTH(default_contact.phone),default_contact.phone,phone.content) AS phone,
				if(LENGTH(default_contact.email),default_contact.email,email.content) AS email
			FROM 
				case_client INNER JOIN client ON (case_client.client=client.id)
		
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
				)phone ON client.id=phone.client
		
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='电子邮件' GROUP BY client
				)email ON client.id=email.client
		
				LEFT JOIN(
					SELECT client_client.client_left AS client,client_client.client_right AS contact,client.abbreviation AS name,client.classification,phone.content AS phone,email.content AS email
					FROM client_client
						INNER JOIN client ON client_client.client_right=client.id AND client_client.is_default_contact=1
						LEFT JOIN (
								SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
						)phone ON client.id=phone.client
						LEFT JOIN (
								SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='电子邮件' GROUP BY client
						)email ON client.id=email.client
				)default_contact
				ON client.id=default_contact.client
		
			WHERE case_client.`case`='".$case_id."'
			ORDER BY client.classification
		";
		
		return $this->db->query($query)->result_array();
	}
	
	function getStaffList($case_id){
	//案件律师信息
		$query="
			SELECT
				case_lawyer.id,case_lawyer.role,case_lawyer.hourly_fee,CONCAT(TRUNCATE(case_lawyer.contribute*100,1),'%') AS contribute,
				staff.name AS lawyer_name,
				TRUNCATE(SUM(account.amount)*contribute,2) AS contribute_amount,
				lawyer_hour.hours_sum
			FROM 
				case_lawyer	INNER JOIN staff ON staff.id=case_lawyer.lawyer
				LEFT JOIN account ON case_lawyer.case=account.case AND account.name <> '办案费'
				LEFT JOIN (
					SELECT uid,SUM(IF(hours_checked IS NULL,hours_own,hours_checked)) AS hours_sum FROM schedule WHERE schedule.`case`='".$case_id."' AND display=1 AND completed=1 GROUP BY uid
				)lawyer_hour
				ON lawyer_hour.uid=case_lawyer.lawyer
			WHERE case_lawyer.case='".$case_id."'
				
			GROUP BY case_lawyer.id
			ORDER BY case_lawyer.role";
		
		return $this->db->query($query)->result_array();
	}
	
	function getFeeList($case_id){
		$query="
			SELECT case_fee.id,case_fee.type,case_fee.receiver,case_fee.condition,case_fee.pay_time,case_fee.fee,case_fee.reviewed,
				if(SUM(account.amount) IS NULL,'',SUM(account.amount)) AS fee_received,
				FROM_UNIXTIME(MAX(account.time_occur),'%Y-%m-%d') AS fee_received_time
			FROM 
				case_fee LEFT JOIN account ON case_fee.id=account.case_fee
			WHERE case_fee.case='".$case_id."' AND case_fee.type<>'办案费'
			GROUP BY case_fee.id";
		
		return $this->db->query($query)->result_array();
	}
	
	function getTimingFeeString($case_id){
		$query="SELECT CONCAT('包含',included_hours,'小时，','账单日：',bill_day,'，付款日：',payment_day,'，付款周期：',payment_cycle,'个月，合同周期：',contract_cycle,'个月，','合同起始日：',FROM_UNIXTIME(time_start,'%Y-%m-%d')) AS case_fee_timing_string FROM case_fee_timing WHERE `case`='".$case_id."'";
		$row=$this->db->query($query)->row_array();
		return $row['case_fee_timing_string'];
	}
	
	function getFeeMiscList($case_list){
		$query="
			SELECT case_fee.id,case_fee.type,case_fee.receiver,case_fee.comment,case_fee.pay_time,case_fee.fee,
				if(SUM(account.amount) IS NULL,'',SUM(account.amount)) AS fee_received
			FROM 
				case_fee LEFT JOIN account ON case_fee.id=account.case_fee
			WHERE case_fee.case='".post('cases/id')."' AND case_fee.type='办案费'
			GROUP BY case_fee.id";
		
		return $this->db->query($query)->result_array();
	}
	
	function getDocumentList($case_id){
		$query="
			SELECT id,name,type,IF(doctype='其他',doctype_other,doctype) AS doctype,comment,time,username
			FROM 
				case_document
			WHERE display=1 AND `case`='".$case_id."'
			ORDER BY time DESC";

		return $this->db->query($query)->result_array();
	}
	
	function getDocumentCatalog($case_id,$choosen_documents){
		$query="
			SELECT * FROM(
				SELECT DISTINCT doctype FROM case_document WHERE `case`='$case_id' AND (".db_implode($choosen_documents,' OR ','id','=',"'","'",'`','key').") AND doctype<>'其他' ORDER BY doctype
			)doctype
			UNION
			SELECT DISTINCT doctype_other FROM case_document WHERE `case`='$case_id' AND doctype='其他'
		";
		$array=db_toArray($query);
		$doctypes=array_sub($array,'doctype');
		return $doctypes;
	}
	
	function getScheduleList($case_id){
		$query="SELECT *
			FROM 
				schedule
			WHERE display=1 AND completed=1 AND `case`='".$case_id."'
			ORDER BY time_start DESC
			LIMIT 10";
		
		return $this->db->query($query)->result_array();
	}
	
	function getPlanList($case_id){
		$query="SELECT *
			FROM 
				schedule
			WHERE display=1 AND completed=0 AND `case`='".$case_id."'
			ORDER BY time_start
			LIMIT 10";
		
		return $this->db->query($query)->result_array();
	}
	
	function getClientRole($case_id){
		//获得当前案件的客户-相对方名称
		$query="
			SELECT * FROM
			(
				SELECT case_client.client,client.abbreviation AS client_name,role AS client_role 
				FROM case_client INNER JOIN client ON case_client.client=client.id 
				WHERE client.classification='客户' AND `case`='".$case_id."'
				ORDER BY case_client.id
				LIMIT 1
			)client LEFT JOIN
			(
				SELECT client AS opposite,client.abbreviation AS opposite_name,role AS opposite_role 
				FROM case_client LEFT JOIN client ON case_client.client=client.id 
				WHERE client.classification='相对方' AND `case`='".$case_id."'
				LIMIT 1
			)opposite
			ON 1=1";	
		return db_fetch_first($query);
	}
	
	/*
	 * 根据案件信息，获得案号
	 * $case参数为array，需要包含is_query,filed,classification,type,type_lock,first_contact/time_contract键
	 */
	function getNum($case,$case_client_role=NULL){
		$case_num=array();
		
		if(is_null($case_client_role)){
			$case_client_role=$this->getClientRole($case['id']);
		}
		
		if($case['is_query']){
			$case_num['classification_code']='询';
			$case_num['type_code']='';
		}else{
			switch($case['classification']){
				case '诉讼':$case_num['classification_code']='诉';break;
				case '非诉讼':$case_num['classification_code']='非';break;
				case '法律顾问':$case_num['classification_code']='顾';break;
				case '内部行政':$case_num['classification_code']='内';break;
				default:'';
			}
			switch($case['type']){
				case '房产':$case_num['type_code']='（房）';break;
				case '公司':$case_num['type_code']='（公）';break;
				case '婚姻':$case_num['type_code']='（婚）';break;
				case '劳动':$case_num['type_code']='（劳）';break;
				case '金融':$case_num['type_code']='（金）';break;
				case '继承':$case_num['type_code']='（继）';break;
				case '知产':$case_num['type_code']='（知）';break;
				case '合同':$case_num['type_code']='（合）';break;
				case '刑事':$case_num['type_code']='（刑）';break;
				case '行政':$case_num['type_code']='（行）';break;
				case '其他':$case_num['type_code']='（他）';break;
				case '公民个人':$case_num['type_code']='（个）';break;
				case '侵权':$case_num['type_code']='（侵）';break;
				case '移民':$case_num['type_code']='（移）';break;
				case '留学':$case_num['type_code']='（留）';break;
				case '企业':$case_num['type_code']='（企）';break;
				case '事业单位':$case_num['type_code']='（事）';break;
				case '个人事务':$case_num['type_code']='（个）';break;
				default:$case_num['type_code']='';
			}
		}
		$case_num['case']=$case['id'];
		$case_num+=uidTime();
		$case_num['year_code']=substr($case['is_query']?$case['first_contact']:$case['time_contract'],0,4);
		db_insert('case_num',$case_num,true,true);
		$case_num['number']=db_fetch_field("SELECT number FROM case_num WHERE `case`='".$case['id']."'");
		if(!$case['is_query']){
			post('cases/type_lock',1);//申请正式案号之后不可以再改变案件类别
		}
		post('cases/display',1);//申请案号以后案件方可见
		$num='沪星'.$case_num['classification_code'].$case_num['type_code'].$case_num['year_code'].'第'.$case_num['number'].'号';
		$this->db_update('case',array('num'=>$num),"id='".$case['id']."'");
		return $num;
	}

	/**
	 * 更新案件名称
	 */
	function getName($case_client_role,$classification,$type,$is_query=false){
		$case_name=$case_client_role['client_name'];
		
		if($is_query){
			$case_name.=' 咨询';
			return $case_name;

		}
		
		if(isset($case_client_role['opposite_name'])){
			
			if($classification=='诉讼' && ($case_client_role['client_role']=='原告' || $case_client_role['client_role']=='申请人') && ($case_client_role['opposite_role']=='被告' || $case_client_role['opposite_role']=='被申请人')){
					$case_name.=' 诉 '.$case_client_role['opposite_name'].'('.$type.')';

			}elseif($classification=='诉讼' && ($case_client_role['client_role']=='被告' || $case_client_role['client_role']=='被申请人') && ($case_client_role['opposite_role']=='原告' || $case_client_role['opposite_role']=='申请人')){
					$case_name.=' 应诉 '.$case_client_role['opposite_name'].'('.$type.')';

			}elseif($classification=='诉讼' && $case_client_role['client_role']=='上诉人'){
				$case_name.=' 上诉 '.$case_client_role['opposite_name'].'('.$type.')';

			}elseif($classification=='诉讼' && $case_client_role['client_role']=='被上诉人'){
				$case_name.=' 应 '.$case_client_role['opposite_name'].' 上诉('.$type.')';

			}elseif($classification=='诉讼' && ($case_client_role['client_role']=='第三人' || $case_client_role['opposite_role']=='第三人')){
				$case_name.=' 与 '.$case_client_role['opposite_role'].' '.$case_client_role['opposite_name'].'('.$type.')';
			}
			
			return $case_name;

		}
		
		if($classification=='法律顾问'){
			$case_name.='('.$classification.')';

		}else{
			$case_name.='('.$type.')';
		}
		
		return $case_name;
	}
	
	function getList($method=NULL){
		$q="
			SELECT
				case.id,case.name,case.num,case.stage,case.time_contract,
				case.is_reviewed,case.apply_file,case.is_query,
				case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
				case.finance_review,case.info_review,case.manager_review,case.filed,
				contribute_allocate.contribute_sum,
				uncollected.uncollected,
				schedule_grouped.id AS schedule,schedule_grouped.name AS schedule_name,schedule_grouped.time_start,schedule_grouped.username AS schedule_username,
				plan_grouped.id AS plan,plan_grouped.name AS plan_name,FROM_UNIXTIME(plan_grouped.time_start,'%m-%d') AS plan_time,plan_grouped.username AS plan_username,
				lawyers.lawyers
			FROM 
				`case`
			
				LEFT JOIN
				(
					SELECT * FROM(
						SELECT * FROM `schedule` WHERE completed=1 AND display=1 ORDER BY time_start DESC LIMIT 1000
					)schedule_id_desc 
					GROUP BY `case`
				)schedule_grouped
				ON `case`.id = schedule_grouped.`case`
				
				LEFT JOIN
				(
					SELECT * FROM(
						SELECT * FROM `schedule` WHERE completed=0 AND display=1 AND time_start>'{$this->config->item('timestamp')}' ORDER BY time_start LIMIT 1000
					)schedule_id_asc 
					GROUP BY `case`
				)plan_grouped
				ON `case`.id = plan_grouped.`case`
				
				LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
					FROM case_lawyer INNER JOIN staff ON case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
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
					INNER JOIN
					(
						SELECT `case`, SUM(amount) AS amount_sum FROM account GROUP BY `case`
					)account_grouped
					USING (`case`)
				)uncollected
				ON case.id=uncollected.case
				
			WHERE case.company='{$this->config->item('company')}' AND case.display=1 AND is_query=0 AND case.filed=0 AND case.id>=20
		";
		$q_rows="
			SELECT
				COUNT(id)
			FROM 
				`case`
			WHERE case.company='{$this->config->item('company')}' AND case.display=1 AND is_query=0 AND case.filed=0 AND case.id>=20
		";
		
		$condition='';
		
		if($method=='host'){
			$condition.="AND case.apply_file=0 AND case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";
		
		}elseif($method=='consultant'){
			$condition.="AND case.apply_file=0 AND classification='法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."') OR case.uid='".$_SESSION['id']."')";
		
		}elseif($method=='etc'){
			$condition.="AND case.apply_file=0 AND classification<>'法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role<>'主办律师') OR case.uid='".$_SESSION['id']."')";
			
		}elseif($method=='file'){
			$condition.="AND case.apply_file=1 AND classification<>'法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role<>'主办律师') OR case.uid='".$_SESSION['id']."')";
			
		}elseif(!is_logged('developer')){
			$condition.="AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role IN ('接洽律师','接洽律师（次要）','主办律师','协办律师','律师助理','督办合伙人')) OR case.uid='".$_SESSION['id']."')";
		}
		$condition=$this->search($condition, array('case.num'=>'案号','case.type'=>'类别','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));
		$condition=$this->orderBy($condition,'time_contract','DESC',array('case.name','lawyers'));
		$q.=$condition;
		$q_rows.=$condition;
		$q=$this->pagination($q,$q_rows);
		return $this->db->query($q)->result_array();
	}
	
	/**
	 * 已归档案件列表
	 */
	function getFiledList(){
		$query="
			SELECT
				case.id,case.name AS case_name,case.stage,case.time_contract,case.time_end,case.num,
				case.is_reviewed,case.apply_file,case.is_query,
				case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
				case.finance_review,case.info_review,case.manager_review,case.filed,
				lawyers.lawyers,
				file_status_grouped.status,file_status_grouped.staff AS staff,FROM_UNIXTIME(file_status_grouped.time,'%Y-%m-%d %H:%i:%s') AS status_time,
				contribute_allocate.contribute_sum,
				uncollected.uncollected,
				staff.name AS staff_name
			FROM 
				`case` INNER JOIN case_num ON `case`.id=case_num.`case`

				LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
					FROM case_lawyer,staff 
					WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
					GROUP BY case_lawyer.`case`
				)lawyers
				ON `case`.id=lawyers.`case`

				LEFT JOIN (
					SELECT * FROM (
						SELECT `case`,status,staff,time FROM file_status ORDER BY time DESC
					)file_status_ordered
					GROUP BY `case`
				)file_status_grouped 
				ON case.id=file_status_grouped.case

				LEFT JOIN staff ON file_status_grouped.staff=staff.id

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
						SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' GROUP BY `case`
					)case_fee_grouped
					LEFT JOIN
					(
						SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE 1 GROUP BY `case`
					)account_grouped
					USING (`case`)
				)uncollected
				ON case.id=uncollected.case

			WHERE case.display=1 AND case.id>=20 AND case.filed=1
		";
		
		$query=$this->search($query,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));
		
		$query=$this->orderby($query,'time_contract','DESC',array('case.name','lawyers'));
		
		$query=$this->pagination($query);
		
		return $this->db->query($query)->result_array();
	}
	
	function getTobeFiledList(){
		$query="
			SELECT
				case.id,case.name,case.num,case.stage,case.time_contract,case.time_end,
				case.is_reviewed,case.apply_file,case.is_query,
				case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
				case.finance_review,case.info_review,case.manager_review,case.filed,
				contribute_allocate.contribute_sum,
				uncollected.uncollected,
				lawyers.lawyers

			FROM 
				`case` LEFT JOIN
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
						SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' GROUP BY `case`
					)case_fee_grouped
					LEFT JOIN
					(
						SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE 1 GROUP BY `case`
					)account_grouped
					USING (`case`)
				)uncollected
				ON case.id=uncollected.case

			WHERE case.display=1 AND case.id>=20 AND case.apply_file=1 AND filed=0
		";
		
		$query=$this->search($query,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));
		
		$query=$this->orderby($query,'case.time_contract','ASC',array('case.name','lawyers'));
		
		$query=$this->pagination($query);
		
		return $this->db->query($query)->result_array();
	}
}
?>