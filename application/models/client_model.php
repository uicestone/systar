<?php
class Client_model extends People_model{
	function __construct(){
		parent::__construct();
	}
	
	function add($data=array('type'=>'client')){
		if(!isset($data['type'])){
			$data['type']='client';
		}
		
		return parent::add($data);
	}
	
	function getList($args = array()) {
		!isset($args['type']) && $args['type']='client';
		
		$this->db->where('people.id NOT IN (SELECT id FROM staff)');
		
		return parent::getList($args);
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
	
	/**
	 * 获得系统中所有客户的email
	 */
	function getAllEmails(){
		$query="
			SELECT content 
			FROM people_profile 
				INNER JOIN people ON people.id=people_profile.people
			WHERE
				people.type='client' 
				AND people_profile.name='电子邮件'
		";
		
		$result=$this->db->query($query);
		
		return array_sub($result->result_array(),'content');
	}
}
?>