<?php
class Client_model extends People_model{
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * 抓取一条客户信息
	 * @param int $id 案件id
	 * @param mixed $field 需要指定抓取的字段，留空则返回整个数组
	 * @return 一条信息的数组，或者一个字段的值，如果指定字段且字段不存在，返回false
	 */
	function fetch($id,$field=NULL){
		$query="
			SELECT * 
			FROM client 
			WHERE id='{$id}' AND company='{$this->company->id}'";
		$row=$this->db->query($query)->row_array();

		if(is_null($field)){
			return $row;
	
		}elseif(isset($row[$field])){
			return $row[$field];

		}else{
			return false;
		}
		
	}
	
	/**
	 * 检查客户名，返回错误信息或获取唯一客户的信息
	 * @param $client_name 要检查的完整或部分客户姓名
	 * @param $data_type 检查信息唯一时，返回数据内容，'array'为返回整行，其他为指定字段
	 * @param $show_error 除返回错误外，是否直接使用showMessage()显示错误
	 * @param $fuzzy 是否使用模糊匹配
	 * @return 名称唯一的时候，返回指定信息，否则返回错误码	-3:未输入名称，-2存在多个匹配，-1不存在匹配
	 */
	function check($client_name,$data_type='id',$show_error=true,$fuzzy=true){
		//$data_type:id,array
		
		if(!$client_name){
			if($show_error){
				showMessage('请输入客户名称','warning');
			}
			return -3;
		}
	
		if($fuzzy){
			$q_client="SELECT * FROM `client` WHERE display=1 AND company='".$this->company->id."' AND (`name` LIKE '%".$client_name."%' OR abbreviation LIKE '".$client_name."')";
		}else{
			$q_client="SELECT * FROM `client` WHERE display=1 AND company='".$this->company->id."' AND (`name` LIKE '".$client_name."' OR abbreviation LIKE '".$client_name."')";
		}
		$r_client=db_query($q_client);
		$num_clients=db_rows($r_client);
	
		if($num_clients==0){
			if($show_error){
				showMessage('没有这个客户：'.$client_name,'warning');
			}
			return -1;
			
		}elseif($num_clients>1){
			if($show_error){
				showMessage('此关键词存在多个符合客户','warning');
			}
			return -2;
	
		}else{
			$data=db_fetch_array($r_client);
			if($data_type=='array'){
				$return=$data;
			}else{
				$return=$data[$data_type];
			}
			
			return $return;
		}
	}
	
	function add($data){
		$field=array('name','character','classification','type','abbreviation','source','source_lawyer','comment','work_for');
		foreach($data as $key => $value){
			if(!in_array($key,$field)){
				unset($data[$key]);
			}
		}
		
		$data['abbreviation']=$data['name'];
	
		$data['display']=1;
		$data+=uidTime();
	
		return db_insert('client',$data);
	}
	
	/**
	 * 添加客户相关人
	 */
	function addRelated($data){
		return db_insert('client_client',$data);
	}
	
	/**
	 * 添加客户联系方式
	 */
	function addContact($data){
		return db_insert('client_contact',$data);
	}
	
	function addSource($data){
		return db_insert('client_source',$data);
	}
	
	function addContact_phone_email($client,$phone,$email){
		$new_client_contact=array();
		if($phone){
			$new_client_contact[]=array(
				'client'=>$client,
				'type'=>isMobileNumber($phone)?'手机':'固定电话',
				'content'=>$phone,
			);
		}
		if($email){
			$new_client_contact[]=array(
				'client'=>$client,
				'type'=>'电子邮件',
				'content'=>$email,
			);
		}
		db_multiinsert('client_contact',$new_client_contact);
	}
	
	function setDefaultRelated($client_client_id,$client){
		$this->clearDefaultRelated($client);
		
		if($this->db->update('client_client',array('is_default_contact'=>1),array('id'=>$client_client_id))){
			return true;
		}
		return false;
	}
	
	function clearDefaultRelated($client){
		if($this->db->update('client_client',array('is_default_contact'=>'_NULL_'),array('client_left'=>$client))){
			return true;
		}
		return false;
	}
	
	function delete($client_id){
		if(is_array($client_id)){
			$condition = db_implode($client_id, $glue = ' OR ','id','=',"'","'", '`','key');
	
		}elseif(is_int($client_id)){
			$condition = "id = '".$client_id."'";
	
		}else{
			return false;
		}
		return db_delete('client',$condition);
	}
	
	/**
	 * 删除相关人
	 */
	function deleteRelated($client_clients){
		$condition = db_implode($client_clients, $glue = ' OR ','id','=',"'","'", '`','key');
		db_delete('client_client',$condition);
	}
	
	/**
	 * 删除客户联系方式
	 */
	function deleteContact($client_contacts){
		$condition = db_implode(post('client_contact_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		db_delete('client_contact',$condition);
	}
	
	function checkSource($detail,$checktype){
		if($checktype=='client'){
			$q_source="SELECT id FROM client_source WHERE client='".intval($detail)."' LIMIT 1";
			
		}elseif($checktype=='staff'){
			$q_source="SELECT id FROM client_source WHERE staff='".intval($detail)."' LIMIT 1";
	
		}else{
			$q_source="SELECT id FROM client_source WHERE type='".$checktype."' AND detail='".$detail."' LIMIT 1";
		}
		
		if($client_source_id=db_fetch_field($q_source)){
			return $client_source_id;
		}else{
			return false;
		}
	}
	
	function setSource($type,$detail){
		
		if(!$type){
			showMessage('请选择客户来源','warning');
			return false;
		}
	
		if($type=='老客户介绍'){
			$client_check=$this->check($detail,'array');
			if($client_check<0){
				return false;
			}else{
				post('source/client',$client_check['id']);
				post('source/detail',$client_check['abbreviation']);
			}
			$client_source=$this->checkSource(post('source/client'),'client');
	
		}else{
			$client_source=$this->checkSource($detail,$type);
		}
		//试图获得现存"来源"的ID
		if($client_source===false){
			//插入一种新来源
			$client_source_array=array(
				'type'=>$type,
				'detail'=>$detail
			);
			if(in_array($client_source_array['type'],array('其他网络','媒体','老客户介绍','中介机构介绍','其他'))){
				$client_source_array['detail']=post('source/detail');
				if($client_source_array['type']=='老客户介绍'){
					$client_source_array['client']=post('source/client');
				}
			}else{
				post('source/detail','');
			}
			$client_source=$this->addSource($client_source_array);
		}
		
		return $client_source;
	}
	
	function fetchSource($source_id){
		return $this->db->query("SELECT type,detail FROM client_source WHERE id='{$source_id}'")->row_array();
	}
	
	function getListByCase($case_id){
		//根据相关案件获得客户列表
		$option_array=array();
		
		$q_option_array="SELECT id,abbreviation FROM client WHERE display=1 AND classification='客户'";
		if($case_id>20){
			$q_option_array.=" AND id IN (SELECT client FROM case_client WHERE `case`='".$case_id."')";
		
		}elseif($case_id==11){
			//潜在客户维护
			$q_option_array.=" AND type='潜在客户' AND id NOT IN (SELECT client FROM case_client)";
	
		}elseif($case_id==12){
			//老客户维护
			$q_option_array.=" AND type='成交客户' 
				AND id IN (
					SELECT client FROM case_client WHERE `case` IN (
						SELECT id FROM `case` WHERE filed=1
							AND id IN (
								SELECT `case` FROM case_lawyer 
								WHERE lawyer={$this->user->id} OR uid={$this->user->id}
							)
					)
			)";
	
		}elseif($case_id==13){
			//咨询跟踪
			$q_option_array.="
				AND id IN (SELECT client FROM query WHERE display=1 AND filed=0 AND (partner={$this->user->id} OR lawyer={$this->user->id} OR assistant={$this->user->id}))
			";
		}
		$q_option_array.=" ORDER BY id DESC";
		
		$option_array=db_toArray($q_option_array);
		$option_array=array_sub($option_array,'abbreviation','id');
	
		return $option_array;	
	}
	
	function match($part_of_name,$classification='client'){
		$query="SELECT id,name FROM client WHERE display=1 AND name LIKE '%".$part_of_name."%' OR abbreviation LIKE '".$part_of_name."'OR name_en LIKE '%".$part_of_name."%'";
		
		switch($classification){
			case 'client': $query.=" AND classification='客户'";break;
			case 'contact': $query.=" AND classification='联系人'";break;
			case 'opposite': $query.=" AND classification='相对方'";break;
		}
		
		$query.=" ORDER BY time DESC";
		
		$client_array=db_toArray($query);
		
		return $client_array;
	}

	function getList($method=NULL){
		$q="
			SELECT client.id,client.name,client.abbreviation,client.time,client.comment,
				phone.content AS phone,address.content AS address
			FROM `client` 
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
				)phone ON client.id=phone.client
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='地址' GROUP BY client
				)address ON client.id=address.client
			WHERE display=1 AND classification='客户'
		";
		$q_rows="
			SELECT COUNT(client.id)
			FROM `client` 
			WHERE display=1 AND classification='客户'
		";
		$condition='';

		if($method=='potential'){
			$condition.=" AND type='潜在客户'";
		
		}else{
			$condition.="
				AND type='成交客户'
				AND client.id IN (SELECT client FROM case_client WHERE `case` IN (SELECT `case` FROM case_lawyer WHERE lawyer={$this->user->id}))
			";
			
			if(!is_logged('service') && !is_logged('developer')){
				$condition.="
					AND client.id IN (
						SELECT client FROM case_client WHERE `case` IN (
							SELECT `case` FROM case_lawyer WHERE lawyer='{$_SESSION['id']}'
						)
					)
				";
			}
		}
		
		
		$condition=$this->search($condition,array('name'=>'姓名','phone.content'=>'电话','work_for'=>'单位','address'=>'地址','comment'=>'备注'));
		$condition=$this->orderBy($condition,'time','DESC',array('abbreviation','type','address','comment'));
		$q.=$condition;
		$q_rows.=$condition;
		$q=$this->pagination($q/*,$q_rows*/);
		return $this->db->query($q)->result_array();
	}
	
	function getRelatedClients($client_id){
		$query="
			SELECT 
				client_client.id AS id,client_client.role,client_client.client_right,client_client.is_default_contact,
				client.abbreviation AS client_right_name,client.classification,
				phone.content AS client_right_phone,email.content AS client_right_email
			FROM 
				client_client INNER JOIN client ON client_client.client_right=client.id
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
				)phone ON client.id=phone.client
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='电子邮件' GROUP BY client
				)email ON client.id=email.client
			WHERE `client_left`='{$client_id}'
			ORDER BY role
		";
		return $this->db->query($query)->result_array();
	}
	
	function getContacts($client_id){
		$query="
			SELECT 
				client_contact.id,client_contact.comment,client_contact.content,client_contact.type
			FROM client_contact INNER JOIN client ON client_contact.client=client.id
			WHERE client_contact.client='{$client_id}'
		";
		return $this->db->query($query)->result_array();
	}
}
?>