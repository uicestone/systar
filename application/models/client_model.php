<?php
class Client_model extends People_model{
	function __construct(){
		parent::__construct();
	}
	
	function add($data=array('type'=>'客户')){
		if(!isset($data['type'])){
			$data['type']='客户';
		}
		
		return parent::add($data);
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
		//$this->db->query($q_client);
		//$r_client=$this->db->query($q_client);
		//$num_clients=db_rows($r_client);
	
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
			//$data=db_fetch_array($r_client);
			if($data_type=='array'){
				$return=$data;
			}else{
				$return=$data[$data_type];
			}
			
			return $return;
		}
	}
	
	function addSource($data){
		$this->db->insert('client_source',$data);
		return $this->db->insert_id();
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
	
	function setSource($type,$detail=NULL){
		
		if(!$type){
			$this->output->message('请选择客户来源','warning');
			throw new Exception;
		}
	
		if($type=='老客户介绍'){
			$client_check=$this->check($detail,'array');
			if($client_check<0){
				throw new Exception;
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
		
		if(!$client_source){
			$this->output->message('客户来源识别错误','warning');
			throw new Exception;
		}
		
		return $client_source;
	}
	
	function fetchSource($source_id){
		return $this->db->query("SELECT type,detail FROM client_source WHERE id='{$source_id}'")->row_array();
	}
	
	/**
	 * 返回一个案件的客户列表
	 * @param type $case_id
	 * @return type
	 */
	function getListByCase($case_id){
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
		
		$option_array=$this->db->query($q_option_array)->result_array();
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
			WHERE people.company={$this->company->id} AND people.display=1 
				AND type='客户'
				AND (name LIKE '%$part_of_name%' OR abbreviation LIKE '$part_of_name' OR name_en LIKE '%$part_of_name%')
			ORDER BY people.id DESC
		";
		
		return $this->db->query($query)->result_array();
	}

	function getList($method=NULL){
		$q="
			SELECT people.id,people.name,IF(people.abbreviation IS NULL,people.name,people.abbreviation) AS abbreviation,people.time,people.comment,
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
			$condition.=" AND people.id IN (SELECT people FROM people_label WHERE label_name='潜在客户')";
		
		}else{
			$condition.="
				AND people.id IN (SELECT people FROM people_label WHERE label_name='成交客户')
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
	
	/**
	 * 获得系统中所有客户的email
	 */
	function getAllEmails(){
		$query="
			SELECT content 
			FROM people_profile 
				INNER JOIN people ON people.id=people_profile.people
			WHERE
				people.type='客户' 
				AND people_profile.name='电子邮件'
		";
		
		$result=$this->db->query($query);
		
		return array_sub($result->result_array(),'content');
	}
}
?>