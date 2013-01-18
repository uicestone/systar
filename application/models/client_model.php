<?php
class Client_model extends People_model{
	function __construct(){
		parent::__construct();
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
		
		$client=parent::add($data);
		
		if(isset($data['profiles'])){
			foreach($data['profiles'] as $name => $value){
				$this->addProfile($client,$name,$value);
			}
		}
		
		if(isset($data['labels'])){
			foreach($data['labels'] as $type => $name){
				if(is_integer($name)){
					$this->addLabel($client, $name, $type, true);
				}else{
					$this->addLabel($client,$name,$type);
				}
			}
		}
		
		return $client;
	}
	
	/**
	 * 添加客户相关人
	 */
	function addRelated($data){
		return db_insert('people_relationship',$data);
	}
	
	/**
	 * deprecated
	 * 添加客户联系方式
	 */
	function addContact($data){
		return db_insert('people_profile',$data);
	}
	
	function addSource($data){
		return db_insert('client_source',$data);
	}
	
	function addContact_phone_email($client,$phone,$email){
		$new_people_profile=array();
		if($phone){
			$new_people_profile[]=array(
				'client'=>$client,
				'type'=>$this->isMobileNumber($phone)?'手机':'固定电话',
				'content'=>$phone,
			);
		}
		if($email){
			$new_people_profile[]=array(
				'client'=>$client,
				'type'=>'电子邮件',
				'content'=>$email,
			);
		}
		db_multiinsert('people_profile',$new_people_profile);
	}
	
	function setDefaultRelated($people_relationship_id,$client){
		$this->clearDefaultRelated($client);
		
		if($this->db->update('people_relationship',array('is_default_contact'=>1),array('id'=>$people_relationship_id))){
			return true;
		}
		return false;
	}
	
	function clearDefaultRelated($client){
		if($this->db->update('people_relationship',array('is_default_contact'=>'_NULL_'),array('client_left'=>$client))){
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
	function deleteRelated($people_relationships){
		$condition = db_implode($people_relationships, $glue = ' OR ','id','=',"'","'", '`','key');
		db_delete('people_relationship',$condition);
	}
	
	/**
	 * 删除客户联系方式
	 */
	function deleteContact($people_profiles){
		$condition = db_implode(post('people_profile_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		db_delete('people_profile',$condition);
	}
	
	function checkSource($detail,$checktype){
		if($checktype=='client'){
			$q_source="SELECT id FROM client_source WHERE client='".intval($detail)."' LIMIT 1";
			
		}elseif($checktype=='staff'){
			$q_source="SELECT id FROM client_source WHERE staff='".intval($detail)."' LIMIT 1";
	
		}else{
			$q_source="SELECT id FROM client_source WHERE type='$checktype' AND detail='$detail' LIMIT 1";
		}
		
		$row_source=$this->db->query($q_source)->row();
		
		if($row_source){
			return $row_source->id;
		}else{
			return false;
		}
	}
	
	function setSource($type,$detail){
		
		if(!$type){
			$this->output->message('请选择客户来源','warning');
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
	
	/**
	 * 根据部分客户名称返回匹配的客户id和名称列表
	 * @param $part_of_name
	 * @return array
	 */
	function match($part_of_name){
		$part_of_name=mysql_real_escape_string($part_of_name);
		
		$query="
			SELECT people.id,people.name 
			FROM people
				INNER JOIN people_label ON people_label.people=people.id AND people_label.label=(SELECT id FROM label WHERE name='客户')
			WHERE people.company={$this->company->id} AND people.display=1 
				AND (name LIKE '%$part_of_name%' OR abbreviation LIKE '$part_of_name' OR name_en LIKE '%$part_of_name%')
			ORDER BY people.id DESC
		";
		
		return $this->db->query($query)->result_array();
	}

	function getList($method=NULL){
		$q="
			SELECT people.id,people.name,people.abbreviation,people.time,people.comment,
				phone.content AS phone,address.content AS address
			FROM people
				LEFT JOIN (
					SELECT people,GROUP_CONCAT(content) AS content FROM people_profile WHERE name IN('手机','电话') GROUP BY people
				)phone ON people.id=phone.people
				LEFT JOIN (
					SELECT people,GROUP_CONCAT(content) AS content FROM people_profile WHERE name='地址' GROUP BY people
				)address ON people.id=address.people
			WHERE display=1 AND type='客户'
		";
		$q_rows="
			SELECT COUNT(*)
			FROM people 
			WHERE display=1 AND type='客户'
		";
		$condition='';

		if($method=='potential'){
			$condition.=" AND people.id IN (SELECT people_label.people FROM people_label INNER JOIN label ON label.id=people_label.label WHERE label.name='潜在客户')";
		
		}else{
			$condition.="
				AND people.id IN (SELECT people_label.people FROM people_label INNER JOIN label ON label.id=people_label.label WHERE label.name='成交客户')
			";
			
			if(!$this->user->isLogged('service') && !$this->user->isLogged('developer')){
				$condition.="
					AND people.id IN (
						SELECT people FROM case_people WHERE `case` IN (
							SELECT `case` FROM case_people WHERE people = {$this->user->id}
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
		$client_id=intval($client_id);
		
		$query="
			SELECT 
				people_relationship.id AS id,people_relationship.relation,people_relationship.relative,people_relationship.is_default_contact,
				people.abbreviation AS relative_name,
				phone.content AS relative_phone,email.content AS relative_email
			FROM 
				people_relationship INNER JOIN people ON people_relationship.relative=people.id
				LEFT JOIN (
					SELECT people,GROUP_CONCAT(content) AS content FROM people_profile WHERE name IN('手机','电话') GROUP BY people
				)phone ON people.id=phone.people
				LEFT JOIN (
					SELECT people,GROUP_CONCAT(content) AS content FROM people_profile WHERE name='电子邮件' GROUP BY people
				)email ON people.id=email.people
			WHERE people_relationship.people = $client_id
			ORDER BY relation
		";
		return $this->db->query($query)->result_array();
	}
	
	/**
	 * 返回一个人的资料项列表
	 * @param $client_id
	 * @return type
	 */
	function getProfiles($client_id){
		$client_id=intval($client_id);
		
		$query="
			SELECT 
				people_profile.id,people_profile.comment,people_profile.content,people_profile.name
			FROM people_profile INNER JOIN people ON people_profile.people=people.id
			WHERE people_profile.people = $client_id
		";
		return $this->db->query($query)->result_array();
	}
}
?>