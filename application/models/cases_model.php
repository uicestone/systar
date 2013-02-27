<?php
class Cases_model extends SS_Model{
	
	var $id;
	
	var $fields=array(
		'name'=>'名称',
		'num'=>'编号',
		'name_extra'=>'补充名称',
		'first_contact'=>'首次接洽时间',
		'time_contract'=>'签约时间',
		'time_end'=>'（预估）完结时间',
		'quote'=>'报价',
		'timing_fee'=>'是计时收费',
		'focus'=>'焦点',
		'summary'=>'概况',
		'source'=>'来源',
		'is_reviewed'=>'立项已审核',
		'type_lock'=>'类别已锁定',
		'client_lock'=>'客户已锁定',
		'staff_lock'=>'员工已锁定',
		'fee_lock'=>'收款已锁定',
		'apply_file'=>'已申请归档',
		'is_query'=>'是咨询',
		'finance_review'=>'已通过财务审核',
		'info_review'=>'已通过信息审核',
		'manager_review'=>'已通过主管审核',
		'filed'=>'已归档',
		'comment'=>'备注',
		'display'=>'显示在列表中'
	);
	
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
		$id=intval($id);
		
		//finance和manager可以看到所有案件，其他律师只能看到自己涉及的案件
		$query="
			SELECT * 
			FROM `case` 
			WHERE id=$id AND company={$this->company->id}
				AND (
					'".($this->user->isLogged('manager') || $this->user->isLogged('finance') || $this->user->isLogged('admin'))."'='1' 
					OR uid={$this->user->id} 
					OR id IN (
						SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id}
					)
				)
		";
		$row=$this->db->query($query)->row_array();
		
		//$row+=$this->getLabels($id,true);
		
		if(is_null($field)){
			return $row;
		}elseif(isset($row[$field])){
			return $row[$field];
		}else{
			return false;
		}
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
		$data=array_intersect_key($data, $this->fields);
		
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
	    $data=array_intersect_key((array)$data,$this->fields);
		
		$data+=uidTime();
	    
		return $this->db->update('case',$data,array('id'=>$id));
	}
	
	function getLabels($id,$type=NULL){
		$id=intval($id);
		
		$query="
			SELECT label.name, case_label.type
			FROM label INNER JOIN case_label ON label.id=case_label.label
			WHERE case_label.case = $id
		";
		
		if($type===true){
			$query.=" AND case_label.type IS NOT NULL";
		}
		elseif(isset($type)){
			$query.=" AND case_label.type = '$type'";
		}
		
		$result=$this->db->query($query)->result_array();
		
		$labels=array_sub($result,'name','type');
		
		return $labels;
	}
	
	/**
	 * 对于指定案件，在case_label中写入一组label
	 * 对于不存在的label，当场在label表中添加
	 * @param int $case_id
	 * @param array $labels: array([$type=>]$name,...)
	 */
	function updateLabels($case_id,$labels){
		$case_id=intval($case_id);
		foreach((array)$labels as $type => $name){
			$label_id=$this->label->match($name);
			$set=array('label'=>$label_id,'label_name'=>$name);
			$where=array('case'=>$case_id);
			if(!is_integer($type)){
				$where['type']=$type;
			}
			$this->db->replace('case_label',$set+$where);
		}
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
	
	function addPeople($case_id,$people_id,$type,$role=NULL){
		
		$this->db->insert('case_people',array(
			'case'=>$case_id,
			'people'=>$people_id,
			'type'=>$type,
			'role'=>$role
		));
		
		return $this->db->insert_id();
	}
	
	function removePeople($case_id,array $case_people_ids){
		$condition = db_implode($case_people_ids, $glue = ' OR ','id');
		return $this->db->delete('case_people',$condition);
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
	
	function removeFee(array $case_fee_ids){
		$condition = db_implode($case_fee_ids, $glue = ' OR ','id');
		return $this->db->delete('case_fee',$condition);
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
	
	function removeDocument($case_document_ids){
		
	}
	
	function getDocumentList($case_id){
		$case_id=intval($case_id);
		
		$query="
			SELECT document.id,document.name,extname,type.name AS type,document.comment,document.time,document.username
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
	
	//大列表
	function getList($method=NULL){
		$q="
			SELECT
				case.id,case.name,case.num,case.time_contract,
				case.is_reviewed,case.apply_file,case.is_query,
				case.type_lock*case.client_lock*case.staff_lock*case.fee_lock AS locked,
				case.finance_review,case.info_review,case.manager_review,case.filed,
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
						SELECT * FROM `schedule` WHERE completed=0 AND display=1 AND time_start>{$this->config->item('timestamp')} ORDER BY time_start LIMIT 1000
					)schedule_id_asc 
					GROUP BY `case`
				)plan_grouped
				ON `case`.id = plan_grouped.`case`
				
				LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
					FROM case_people INNER JOIN people staff ON case_people.type='律师' AND case_people.people=staff.id AND case_people.role='主办律师'
					GROUP BY case_people.`case`
				)lawyers
				ON `case`.id=lawyers.`case`
				
				LEFT JOIN 
				(
					SELECT `case`,SUM(contribute) AS contribute_sum
					FROM case_people
					WHERE type='律师'
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
				
			WHERE case.company={$this->company->id} AND case.display=1 AND is_query=0 AND case.filed=0
		";
		$q_rows="
			SELECT
				COUNT(id)
			FROM 
				`case`
			WHERE case.company={$this->company->id} AND case.display=1 AND is_query=0 AND case.filed=0
		";
		
		$condition='';
		
		if($method=='host'){
			$condition.=" AND case.apply_file=0 AND case.id IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role='主办律师')";
		
		}elseif($method=='consultant'){
			$condition.=" AND case.apply_file=0 AND (case.id IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id}) OR case.uid={$this->user->id})";
			$condition.=" AND case.id IN (SELECT `case` FROM case_label WHERE label_name='法律顾问')";
		}elseif($method=='etc'){
			$condition.=" AND case.apply_file=0 AND (case.id IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role<>'主办律师') OR case.uid={$this->user->id})";
			$condition.=" AND case.id NOT IN (SELECT `case` FROM case_label WHERE label_name='法律顾问')";
			
		}elseif($method=='file'){
			$condition.=" AND case.apply_file=1 AND (case.id IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role<>'主办律师') OR case.uid={$this->user->id})";
			$condition.=" AND case.id NOT IN (SELECT `case` FROM case_label WHERE label_name='法律顾问')";
			
		}elseif(!$this->user->isLogged('developer') && !$this->user->isLogged('finance')){
			$condition.=" AND (case.id IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id} AND role IN ('接洽律师','接洽律师（次要）','主办律师','协办律师','律师助理','督办人')) OR case.uid={$this->user->id})";
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
				case.id,case.name,case.time_contract,case.time_end,case.num,
				case.is_reviewed,case.apply_file,case.is_query,
				case.type_lock*case.client_lock*case.staff_lock*case.fee_lock AS locked,
				case.finance_review,case.info_review,case.manager_review,case.filed,
				lawyers.lawyers,
				contribute_allocate.contribute_sum,
				uncollected.uncollected
			FROM 
				`case` INNER JOIN case_num ON `case`.id=case_num.`case`

				LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
					FROM case_people INNER JOIN people staff ON case_people.people=staff.id AND case_people.type='律师' AND case_people.role='主办律师'
					GROUP BY case_people.`case`
				)lawyers
				ON `case`.id=lawyers.`case`

				LEFT JOIN 
				(
					SELECT `case`,SUM(contribute) AS contribute_sum
					FROM case_people
					WHERE case_people.type='律师'
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

			WHERE case.company={$this->company->id} AND case.display=1 AND case.filed=1 AND case.is_query=0
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
				case.type_lock*case.client_lock*case.staff_lock*case.fee_lock AS locked,
				case.finance_review,case.info_review,case.manager_review,case.filed,
				contribute_allocate.contribute_sum,
				uncollected.uncollected,
				lawyers.lawyers

			FROM 
				`case` LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
					FROM case_people INNER JOIN staff ON case_people.people=staff.id AND case_people.type='律师' AND case_people.role='主办律师'
					GROUP BY case_people.`case`
				)lawyers
				ON `case`.id=lawyers.`case`

				LEFT JOIN 
				(
					SELECT `case`,SUM(contribute) AS contribute_sum
					FROM case_people
					WHERE type='律师'
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
		$case_id=intval($case_id);
		
		$query="
			SELECT is_reviewed,type_lock,client_lock,staff_lock,fee_lock,is_query,apply_file,
				finance_review,info_review,manager_review,filed
			FROM `case` 
			WHERE id = $case_id
		";
		
		$case_data=$this->db->query($query)->row_array();
		extract($case_data);
		if($type_lock && $client_lock && $staff_lock && $fee_lock){
			$locked=true;
		}else{
			$locked=false;
		}
		
		$uncollected=$this->db->query("
			SELECT IF(amount_sum IS NULL,fee_sum,fee_sum-amount_sum) AS uncollected FROM
			(
				SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' AND reviewed=0 AND `case`='{$case_id}'
			)case_fee_grouped
			LEFT JOIN
			(
				SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE `case`='{$case_id}'
			)account_grouped
			USING (`case`)
		")->row()->uncollected;
				
		$contribute_sum=$this->db->query("
			SELECT SUM(contribute) AS contribute_sum
			FROM case_people
			WHERE type='律师' AND `case`=$case_id
		")->row()->contribute_sum;
		
		return $this->getStatus($is_reviewed,$locked,$apply_file,$is_query,$finance_review,$info_review,$manager_review,$filed,$contribute_sum,$uncollected);
	}
	
	function reviewMessage($reviewWord,$lawyers){
		$message='案件[url=http://sys.lawyerstars.com/cases/edit/'.post('cases/id').']'.strip_tags(post('cases/name')).'[/url]'.$reviewWord.'，"'.post('review_message').'"';
		foreach($lawyers as $lawyer){
			$this->user->sendMessage($lawyer,$message);
		}
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

	/*
		日志添加界面，根据日志类型获得案件列表
		$schedule_type:0:案件,1:所务,2:营销
	*/
	function getListByScheduleType($schedule_type){
		
		$option_array=array();
		
		$q_option_array="SELECT id,name FROM `case` WHERE display=1";
		
		if($schedule_type==0){
			$q_option_array.=" AND ((id>=20 AND filed=0 AND (id IN (SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id}) OR uid = {$this->user->id})) OR id=10)";
		
		}elseif($schedule_type==1){
			$q_option_array.=" AND id<10 AND id>0";
		
		}elseif($schedule_type==2){
			$q_option_array.=" AND id<=20 AND id>10";
	
		}
		
		$q_option_array.=" ORDER BY time_contract DESC";
		$option_array=$this->db->query($q_option_array)->result_array();
		$option_array=array_sub($option_array,'name','id');
		
		foreach($option_array as $case_id => $case_name){
			$option_array[$case_id]=strip_tags($case_name);
		}
	
		return $option_array;	
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
	
	function getClientRole($case_id){
		//获得当前案件的客户-相对方名称
		$case_id=intval($case_id);
		
		$query="
			SELECT * FROM
			(
				SELECT case_people.people AS client,IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS client_name,role AS client_role 
				FROM case_people INNER JOIN people ON case_people.type='客户' AND case_people.people=people.id 
				WHERE `case`=$case_id
				ORDER BY case_people.id
				LIMIT 1
			)client LEFT JOIN
			(
				SELECT case_people.people AS opposite,IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS opposite_name,role AS opposite_role 
				FROM case_people INNER JOIN people ON case_people.type='相对方' AND case_people.people=people.id 
				WHERE `case`=$case_id
				LIMIT 1
			)opposite
			ON 1
		";
		return $this->db->query($query)->row_array();
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
		if(!$is_query){
			post('cases/type_lock',1);//申请正式案号之后不可以再改变案件类别
		}
		post('cases/display',1);//申请案号以后案件方可见
		$num=$case_num['classification_code'].$case_num['type_code'].$case_num['year_code'].'第'.$case_num['number'].'号';
		return $num;
	}

	/**
	 * 更新案件名称
	 */
	function getName($case_client_role,$is_query=false,$classification=NULL,$type=NULL,$name_extra=NULL){
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
			
		}
		
		if(isset($classification) && $classification=='法律顾问'){
			$case_name.='('.$classification.')';

		}elseif(isset($type)){
			$case_name.='('.$type.')';
		}
		
		if(isset($name_extra)){
			$case_name.=(' '.$name_extra);
		}
		
		return $case_name;
	}
	
	/**
	 * 更新归档状态
	 */
	function updateFileStatus($id,$status){
		
	}
	
}
?>