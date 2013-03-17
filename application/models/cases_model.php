<?php
class Cases_model extends SS_Model{
	
	var $id;
	
	var $table='case';
	
	static $fields=array(
		'name'=>'名称',
		'num'=>'编号',
		'first_contact'=>'首次接洽时间',
		'time_contract'=>'签约时间',
		'time_end'=>'（预估）完结时间',
		'quote'=>'报价',
		'timing_fee'=>'是计时收费',
		'focus'=>'焦点',
		'summary'=>'概况',
		'comment'=>'备注',
		'display'=>'显示在列表中'
	);
	
	function __construct(){
		parent::__construct();
	}
	
	function match($part_of_name){
		$query="
			SELECT case.id,case.num,case.name
						FROM `case`
			WHERE case.company={$this->company->id} AND case.display=1 
				AND (name LIKE '%$part_of_name%' OR num LIKE '%$part_of_name%' OR name_extra LIKE '%$part_of_name%')
			ORDER BY case.id DESC
		";

		return $this->db->query($query)->result_array();
	}

	function add($data=array()){
		$data=array_intersect_key($data, self::$fields);
		
		if(isset($data['is_query']) && $data['is_query']){
			$data['first_contact']=$this->config->item('date');
		}else{
			$data['time_contract']=$this->config->item('date');
			$data['time_end']=date('Y-m-d',$this->config->item('timestamp')+100*86400);
		}
		
	    $data+=uidTime(true,true);
	
	    $this->db->insert('case',$data);
		return $this->db->insert_id();
	}
	
	function update($id,$data){
		$id=intval($id);
	    $data=array_intersect_key((array)$data,self::$fields);
		
		$data+=uidTime();
	    
		return $this->db->update('case',$data,array('id'=>$id));
	}
	
	//子表列表、增删
	
	function getClientList($case_id,$relation='客户'){
		$case_id=intval($case_id);
		
		$query="
			SELECT case_people.id,case_people.people,case_people.type,case_people.role,IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS name,phone.content AS phone,email.content AS email
			FROM case_people
				INNER JOIN people ON people.id=case_people.people
				LEFT JOIN (
					SELECT people, GROUP_CONCAT(content) AS content
					FROM people_profile 
					WHERE name IN ('固定电话','电话','手机')
					GROUP BY people
				)phone ON phone.people=case_people.people
				LEFT JOIN(
					SELECT people, GROUP_CONCAT(content) AS content
					FROM people_profile
					WHERE name IN ('电子邮件')
					GROUP BY people
				)email ON email.people=case_people.people
			WHERE case_people.case=$case_id
		";
		
		if(isset($relation)){
			$query.=" AND case_people.type='$relation'";
		}
		
		return $this->db->query($query)->result_array();
	}
	
	function getPeoplesByRole($case_id,$role=NULL){
		$case_id=intval($case_id);
		$query="
			SELECT
				people.id,people.name,people.abbreviation,people.type
				case_people.role
			FROM
				people INNER JOIN case_people ON case_people.people=people.id
			WHERE people.display=1
		";
		$result_array=$this->db->query($query)->result_array();
		$peoples=array();
		foreach($result_array as $row){
			$peoples[$row['role']][$row['id']]=$row;
		}
		
		if(is_null($role)){
			return $peoples;
		}elseif(isset($peoples[$role])){
			return $peoples[$role];
		}
	}
	
	function getPeoplesByType($case_id,$type=NULL){
		$case_id=intval($case_id);
		$query="
			SELECT
				people.id,people.name,people.abbreviation,people.type
				case_people.role
			FROM
				people INNER JOIN case_people ON case_people.people=people.id
			WHERE people.display=1
		";
		$result_array=$this->db->query($query)->result_array();
		$peoples=array();
		foreach($result_array as $row){
			$peoples[$row['type']][$row['id']]=$row;
		}
		
		if(is_null($type)){
			return $peoples;
		}elseif(isset($peoples[$type])){
			return $peoples[$type];
		}
	}
	
	function addPeople($case_id,$people_id,$type,$role=NULL){
		
		$this->db->insert('case_people',array(
			'case'=>$case_id,
			'people'=>$people_id,
			'type'=>$type,
			'role'=>$role
		));
		
		return $this->db->insert_id();
	}
	
	function removePeople($case_id,$case_people_id){
		$case_people_id=intval($case_people_id);
		return $this->db->delete('case_people',array('id'=>$case_people_id));
	}
	
	function getStaffList($case_id){
		$case_id=intval($case_id);

		$query="
			SELECT
				case_people.id,GROUP_CONCAT(case_people.role) AS role,case_people.hourly_fee,CONCAT(TRUNCATE(SUM(case_people.contribute)*100,1),'%') AS contribute,
				staff.name AS staff_name,
				TRUNCATE(account.amount_sum*SUM(case_people.contribute),2) AS contribute_amount,
				lawyer_hour.hours_sum
			FROM 
				case_people INNER JOIN people staff ON staff.id=case_people.people AND case_people.type='律师'
				CROSS JOIN (
					SELECT SUM(amount) AS amount_sum FROM account WHERE `case` = $case_id AND name <> '办案费'
				)account
				LEFT JOIN (
					SELECT uid,SUM(IF(hours_checked IS NULL,hours_own,hours_checked)) AS hours_sum 
					FROM schedule 
					WHERE schedule.`case` = $case_id AND display=1 AND completed=1 GROUP BY uid
				)lawyer_hour
				ON lawyer_hour.uid=case_people.people
			WHERE case_people.case=$case_id
			GROUP BY case_people.people
		";
		
		return $this->db->query($query)->result_array();
	}
	
	function addStaff($case,$people,$role,$hourly_fee=NULL){
		$case=intval($case);
		$people=intval($people);
		
		if(isset($hourly_fee)){
			$hourly_fee=intval($hourly_fee);
		}
		
		$data=array(
			'case'=>$case,
			'people'=>$people,
			'role'=>$role,
			'hourly_fee'=>$hourly_fee,
			'type'=>'律师'
		);
		
		$data+=uidTime();
		
		$this->db->insert('case_people',$data);
		
		return $this->db->insert_id();
	}

	function getFeeList($case_id){
		$query="
			SELECT case_fee.id,case_fee.type,case_fee.receiver,case_fee.condition,case_fee.pay_date,case_fee.fee,case_fee.reviewed,
				if(SUM(account.amount) IS NULL,'',SUM(account.amount)) AS fee_received,
				MAX(account.date) AS fee_received_time
			FROM 
				case_fee LEFT JOIN account ON case_fee.id=account.case_fee
			WHERE case_fee.case='".$case_id."' AND case_fee.type<>'办案费'
			GROUP BY case_fee.id";
		
		return $this->db->query($query)->result_array();
	}
	
	function getFeeMiscList($case_id){
		$case_id=intval($case_id);
		
		$query="
			SELECT case_fee.id,case_fee.type,case_fee.receiver,case_fee.comment,case_fee.pay_date,case_fee.fee,
				if(SUM(account.amount) IS NULL,'',SUM(account.amount)) AS fee_received
			FROM 
				case_fee LEFT JOIN account ON case_fee.id=account.case_fee
			WHERE case_fee.case = $case_id AND case_fee.type='办案费'
			GROUP BY case_fee.id";
		
		return $this->db->query($query)->result_array();
	}
	
	function getTimingFeeString($case_id){
		$case_id=intval($case_id);
		
		$query="SELECT CONCAT('包含',included_hours,'小时，','账单日：',bill_day,'，付款日：',payment_day,'，付款周期：',payment_cycle,'个月，合同周期：',contract_cycle,'个月，','合同起始日：',date_start) AS case_fee_timing_string FROM case_fee_timing WHERE `case` = $case_id";
		$row=$this->db->query($query)->row_array();
		return $row['case_fee_timing_string'];
	}
	
	function setTimingFee($case_id,$date_start,$bill_day,$payment_day,$included_hours=0,$contract_cycle=12,$payment_cycle=1){
		$case=intval($case_id);
		
		$this->db->update('case',array('timing_fee'=>1),array('id'=>$case));
		
		$data=compact('case','date_start','included_hours','contract_cycle','payment_cycle','bill_day','payment_day');
		return $this->db->insert('case_fee_timing',$data);
	}
	
	function removeTimingFee($case_id){
		$case_id=intval($case_id);
		return $this->db->delete('case_timing_fee',array('case',$case_id));
	}
	
	function addFee($case,$fee,$pay_date,$type,$condition=NULL,$receiver=NULL,$comment=NULL){
		$case=intval($case);
		
		$data=compact('case','fee','type','receiver','condition','pay_date','comment');
		
		$this->db->insert('case_fee',$data);
		return $this->db->insert_id();
	}
	
	function removeFee($case_id,$case_fee_id){
		$case_id=intval($case_id);
		$case_fee_id=intval($case_fee_id);
		return $this->db->delete('case_fee',array('id'=>$case_fee_id,'case'=>$case_id));
	}
	
	function addDocument($case_id,$document_id){
		$case_id=intval($case_id);
		$document_id=intval($document_id);
		
		$data=array(
			'case'=>$case_id,
			'document'=>$document_id
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('case_document',$data);
		
		return $this->db->insert_id();
	}
	
	function removeDocument($case_id,$case_document_id){
		$case_id=intval($case_id);
		$case_document_id=intval($case_document_id);
		return $this->db->delete('case_document',array('id'=>$case_document_id,'case'=>$case_id));
	}
	
	function getDocumentList($case_id){
		$case_id=intval($case_id);
		
		$query="
			SELECT case_document.id,document.id AS document,document.name,extname,type.name AS type,document.comment,document.time,document.username
			FROM 
				document
				INNER JOIN case_document ON document.id=case_document.document
				LEFT JOIN (
					SELECT label.name,document_label.document
					FROM document_label 
						INNER JOIN label ON document_label.label=label.id
					WHERE document_label.type='类型'
				)type ON document.id=type.document
			WHERE display=1 AND case_document.case = $case_id
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
		$array=$this->db->query($query)->result_array();
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
	
	function getList($config=array()){
		$q="
			SELECT
				case.id,case.name,case.num,case.time_contract,
				staffs.staffs
			FROM 
				`case`
				
				LEFT JOIN
				(
					SELECT case_people.case, GROUP_CONCAT(DISTINCT people.name) AS staffs
					FROM staff INNER JOIN case_people ON case_people.people=staff.id
						INNER JOIN people ON people.id=staff.id
					GROUP BY case_people.case
				)staffs
				ON `case`.id=staffs.`case`
		";
		
		$q_rows="
			SELECT COUNT(*) FROM `case`
		";
		
		$inner_join='';
		
		//使用INNER JOIN的方式来筛选标签，聪明又机灵
		if(isset($config['labels']) && is_array($config['labels'])){
			
			foreach($config['labels'] as $id => $label_name){
				
				//针对空表单的提交
				if($label_name===''){
					continue;
				}
				
				//每次连接people_label表需要定一个唯一的名字
				$inner_join.="
					INNER JOIN case_label `t_$id` ON case.id=`t_$id`.case AND `t_$id`.label_name = '$label_name'
				";
				
			}
			
		}
		
		$where="
			WHERE case.company={$this->company->id} AND case.display=1
		";
		
		if(isset($config['type'])){
			$where.=" AND case.type='{$config['type']}'";
		}
		
		if(isset($config['role'])){
			$where.=" AND case.id IN (SELECT `case` FROM case_people WHERE people={$this->user->id} AND role='{$config['role']}')";
		}
		
		if(isset($config['num'])){
			$where.=" AND case.num='{$config['num']}'";
		}
		
		$q.=$inner_join.$where;
		$q_rows.=$inner_join.$where;
		
		if(!isset($config['orderby'])){
			$config['orderby']='case.id DESC';
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
		
		if(is_array($config['limit']) && count($config['limit'])==2){
			$q.=" LIMIT {$config['limit'][1]}, {$config['limit'][0]}";
		}elseif(is_array($config['limit']) && count($config['limit'])==1){
			$q.=" LIMIT {$config['limit'][0]}";
		}elseif(!is_array($config['limit'])){
			$q.=" LIMIT ".$config['limit'];
		}
		
		//echo $this->db->_prep_query($q);
		
		return $this->db->query($q)->result_array();
	}
	
	function getIdByCaseFee($case_fee_id){
		$case_fee_id=intval($case_fee_id);
		
		$query="SELECT `case` FROM case_fee WHERE id = $case_fee_id";
		
		$result = $this->db->get_where('case_fee',array('id'=>$case_fee_id))->row();
		
		if(!$result){
			return false;
		}
		
		return $result->case;
	}
	
	/**
	 * 获得与一个客户相关的所有案件
	 * @param type $client_id
	 * @return 一个案件列表，包含案件名称，案号和主办律师
	 */
	function getListByPeople($people_id){
		$people_id=intval($people_id);
		
		$query="
			SELECT case.id,case.name AS case_name,case.num,	
				GROUP_CONCAT(DISTINCT staff.name) AS lawyers
			FROM `case`
				LEFT JOIN case_people ON case.id=case_people.case AND case_people.type='律师' AND case_people.role='主办律师'
				LEFT JOIN people staff ON staff.id=case_people.people
			WHERE case.id IN (
				SELECT `case` FROM case_people WHERE people = $people_id
			)
			GROUP BY case.id
		";
		
		return $this->db->query($query)->result_array();

	}

	//根据客户id获得其参与案件的收费
	function getFeeListByClient($client_id){
		$client_id=intval($client_id);
		
		$option_array=array();
		
		$q_option_array="
			SELECT case_fee.id,case_fee.type,case_fee.fee,case_fee.pay_date,case_fee.receiver,case.name
			FROM case_fee INNER JOIN `case` ON case_fee.case=case.id
			WHERE case.id IN (SELECT `case` FROM case_people WHERE people=$client_id)";
		
		$r_option_array=$this->db->query($q_option_array);
		
		foreach($r_option_array->result_array() as $a_option_array){
			$option_array[$a_option_array['id']]=strip_tags($a_option_array['name']).'案 '.$a_option_array['type'].' ￥'.$a_option_array['fee'].' '.$a_option_array['pay_date'].($a_option_array['type']=='办案费'?' '.$a_option_array['receiver'].'收':'');
		}
	
		return $option_array;	
	}
	
	//根据案件ID获得收费array
	function getFeeOptions($case_id){
		$case_id=intval($case_id);
		
		$option_array=array();
		
		$q_option_array="
			SELECT case_fee.id,case_fee.type,case_fee.fee,case_fee.pay_date,case_fee.receiver,case.name
			FROM case_fee INNER JOIN `case` ON case_fee.case=case.id
			WHERE case.id=$case_id";
		
		$result=$this->db->query($q_option_array)->result_array();
		
		foreach($result as $a_option_array){
			$option_array[$a_option_array['id']]=strip_tags($a_option_array['name']).'案 '.$a_option_array['type'].' ￥'.$a_option_array['fee'].' '.$a_option_array['pay_date'].($a_option_array['type']=='办案费'?' '.$a_option_array['receiver'].'收':'');
		}
	
		return $option_array;	
	}
	
	//增减案下律师的时候自动计算贡献
	function calcContribute($case_id){
		$case_id=intval($case_id);
		
		$query="SELECT id,people lawyer,role FROM case_people WHERE type='律师' AND `case` = $case_id";
		
		$case_lawyer_array=$this->db->query($query)->result_array();
		
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
				$this->db->update('case_people',array('contribute'=>$contribute['接洽']*0.3),array('id'=>$id));
	
			}elseif($role=='接洽律师'){
				if(isset($role_count['接洽律师（次要）']) && $role_count['接洽律师（次要）']==1){
					$this->db->update('case_people',array('contribute'=>$contribute['接洽']*0.7),array('id'=>$id));
				}else{
					$this->db->update('case_people',array('contribute'=>$contribute['接洽']/$role_count[$role]),array('id'=>$id));
				}
	
			}elseif($role=='主办律师'){
				if(isset($role_count['协办律师']) && $role_count['协办律师']){
					$this->db->update('case_people',array('contribute'=>($contribute['办案']-0.05)/$role_count[$role]),array('id'=>$id));
				}else{
					$this->db->update('case_people',array('contribute'=>$contribute['办案']/$role_count[$role]),array('id'=>$id));
				}
	
			}elseif($role=='协办律师'){
				$this->db->update('case_people',array('contribute'=>0.05/$role_count[$role]),array('id'=>$id));
			}
		}
	}
	
	function lawyerRoleCheck($case_id,$new_role,$actual_contribute=NULL){
		$case_id=intval($case_id);
		
		if(strpos($new_role,'信息提供')!==false && $this->db->query("SELECT SUM(contribute) sum FROM case_people WHERE type='律师' AND role LIKE '信息提供%' AND `case`=$case_id")->row()->sum+substr($new_role,15,2)/100>0.2){
			//信息贡献已达到20%
			showMessage('信息提供贡献已满额','warning');
			return false;
			
		}elseif(strpos($new_role,'接洽律师')!==false && $this->db->query("SELECT COUNT(id) num FROM case_people WHERE type='律师' AND role LIKE '接洽律师%' AND `case`=$case_id")->row()->num>=2){
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
					0.3-$this->db->query("SELECT SUM(contribute) sum FROM case_people WHERE type='律师' AND `case`=$case_id AND role='实际贡献'")->row()->sum;
				if($actual_contribute_left>0){
					return $actual_contribute_left;
				}else{
					showMessage('实际贡献额已分配完','warning');
					return false;
				}
				
			}elseif($this->db->query("SELECT SUM(contribute) sum FROM case_people WHERE type='律师' AND `case`=$case_id AND role='实际贡献'")->row()->sum+($actual_contribute/100)>0.3){
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
		$case_id=intval($case_id);
		
		$case_role=$this->db->query("SELECT people lawyer,role FROM case_people WHERE type='律师' AND `case`=$case_id")->result_array();
		
		if($case_role){
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
			if($lawyer_role['role']=='督办人'){
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
			if(!in_array($lawyer_role['lawyer'],$lawyers) && $lawyer_role['role']!='督办人'){
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
			if($lawyer_role['lawyer']==$this->user->id){
				$my_role[]=$lawyer_role['role'];
			}
		}
		return $my_role;
	}
	
	/*
	 * 根据案件信息，获得案号
	 * $case参数为array，需要包含is_query,filed,classification,type,type_lock,first_contact/time_contract键
	 */
	function getNum($case_id,$classification,$type,$is_query=false,$first_contact=NULL,$time_contract=NULL){
		$case_num=array();
		
		if($is_query){
			$case_num['classification_code']='询';
			$case_num['type_code']='';
		}else{
			switch($classification){
				case '诉讼':$case_num['classification_code']='诉';break;
				case '非诉讼':$case_num['classification_code']='非';break;
				case '法律顾问':$case_num['classification_code']='顾';break;
				case '内部行政':$case_num['classification_code']='内';break;
				default:'';
			}
			switch($type){
				case '公司':$case_num['type_code']='（公）';break;
				case '房产建筑':$case_num['type_code']='（房）';break;
				case '婚姻家庭':$case_num['type_code']='（家）';break;
				case '劳动人事':$case_num['type_code']='（劳）';break;
				case '知识产权':$case_num['type_code']='（知）';break;
				case '诉讼':$case_num['type_code']='（诉）';break;
				case '刑事行政':$case_num['type_code']='（刑）';break;
				case '涉外':$case_num['type_code']='（外）';break;
				case '韩日':$case_num['type_code']='（韩）';break;
				default:$case_num['type_code']='';
			}
		}
		$case_num['case']=$case_id;
		$case_num+=uidTime();
		$case_num['year_code']=substr($is_query?$first_contact:$time_contract,0,4);
		$this->db->insert('case_num',$case_num);
		$case_num['number']=$this->db->query("SELECT number FROM case_num WHERE `case` = $case_id")->row()->number;

		$num=$case_num['classification_code'].$case_num['type_code'].$case_num['year_code'].'第'.$case_num['number'].'号';
		return $num;
	}

	/**
	 * 更新归档状态
	 */
	function updateFileStatus($id,$status){
		
	}
	
}
?>