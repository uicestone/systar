<?php
class Company_model extends BaseItem_model{
	
	var $name;
	var $type;
	var $host;
	var $syscode;
	var $sysname;
	var $ucenter;
	var $default_controller;
	
	function __construct(){
		parent::__construct();
		$this->table='company';
		$this->recognize($this->input->server('SERVER_NAME'));

		//获取存在数据库中的公司配置项
		$this->db->from('company_config')
			->where('company',$this->id);
		$this->config->company=array_sub($this->db->get()->result_array(),'value','name');
		
	}

	function recognize($host_name){
		$this->db->select('id,name,type,syscode,sysname,ucenter,default_controller')
			->from('company')
			->or_where(array('host'=>$host_name,'syscode'=>$host_name));

		$row_array=$this->db->get()->row_array();
		
		if(!$row_array){
			show_error("We're sorry but no company called $host_name here.");
		}
		
		foreach($row_array as $key => $value){
			$this->$key=$value;
		}
	}
}
?>