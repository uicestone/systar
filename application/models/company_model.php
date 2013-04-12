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
	
	/**
	 * set or get a  company config value
	 * json_decode/encode automatically
	 * @param string $name
	 * @param mixed $value
	 * @return
	 *	get: the config value, false if not found
	 *	set: the insert or update query
	 */
	function config($name,$value=NULL){
		
		$row=$this->db->select('id,value')->from('company_config')->where('company',$this->id)->where('name',$name)
			->get()->row();
		
		if(is_null($value)){
			if($row){
				$json_value=json_decode($row->value);
				if(is_null($json_value)){
					return $row->value;
				}else{
					return $json_value;
				}
			}else{
				return false;
			}
		}
		else{
			
			if(is_array($value)){
				$value=json_encode($value);
			}
			
			if($row){
				return $this->db->update('company_config',array('value'=>$value),array('id'=>$row->id));
			}else{
				return $this->db->insert('company_config',array('company'=>$this->id,'name'=>$name,'value'=>$value));
			}
		}
	}
}
?>