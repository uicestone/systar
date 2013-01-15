<?php
class Misc extends SS_controller{
	function __construct(){
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	function editable(){
		if(count($_POST)>2){
			exit('post data error');
		}
		
		$value=$table=$field='';
		$id=0;
		
		foreach($_POST as $k => $v){
			if(preg_match('/-id$/',$k)){
				//id项
				$table=substr($k,0,-3);
				$id=$v;
			}else{
				$field=$k;
				$value=$v;
			}
		}
		
		$data=array($field=>$value);
		
		$this->db->update($table,$data,"id = '{$id}'");
		
		echo db_fetch_field("SELECT `".$field."` FROM `".$table."` WHERE id='".$id."'");
	}
	
	function getHtml(){
		$name=implode('/',func_get_args());
		if(is_file(APPPATH.'views/'.$name.'.php')){
			require APPPATH.'views/'.$name.'.php';
		}
	}
	
	function getSelectOption(){
		$select_type=intval($this->input->post('select_type'));
		
		$this->load->model($this->input->post('affair').'_model',$this->input->post('affair'));
		
		if($select_type){
			$call_controller=$this->input->post('affair');
			if(is_callable(array($this->$call_controller,$this->input->post('method')))){
				$options=call_user_func(array($this->$call_controller,$this->input->post('method')),$this->input->post('active_value'));
				displayOption($options,NULL,true);
			}
		
		}else{
			$q_get_options="SELECT type FROM type WHERE affair='".$this->input->post('affair')."' AND classification='".$this->input->post('active_value')."'";
			$options_array=db_toArray($q_get_options);
			$options=array_sub($options_array,'type');
			
			displayOption($options);
		}
	}
	
	function getSession($var){
		if($var=='minimized'){
			$this->output->data=(bool)$this->session->userdata('minimized');
		}
		
		if($var=='default_controller'){
			$this->output->data=$this->config->item('default_controller');
		}
	}
	
	function setSession($var){
		if($var=='minimized'){
			if($this->session->userdata('minimized')===false || $this->session->userdata('minimized')=='maximized'){
				$this->session->set_userdata('minimized','minimized');
			}else{
				$this->session->set_userdata('minimized','maximized');
			}
			$this->output->status='success';

		}
	}
}
?>