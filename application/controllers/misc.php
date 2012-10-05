<?php
class Misc extends SS_controller{
	function __construct(){
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
		
		db_update($table,$data,"id='".$id."'");
		
		echo db_fetch_field("SELECT `".$field."` FROM `".$table."` WHERE id='".$id."'");
	}
	
	function getHtml(){
		$name=implode('/',func_get_args());
		if(is_file(APPPATH.'views/'.$name.'.php')){
			require APPPATH.'views/'.$name.'.php';
		}
	}
	
	function getSelectOption(){
		$select_type=intval($_POST['select_type']);
		
		$this->load->model($_POST['affair'].'_model',$_POST['affair']);
		
		if($select_type){
			if(is_callable(array($this->$_POST['affair'],$_POST['method']))){
				$options=call_user_func(array($this->$_POST['affair'],$_POST['method']),$_POST['active_value']);
			}
			
			displayOption($options,NULL,true);
		
		}else{
			$q_get_options="SELECT type FROM type WHERE affair='".$_POST['affair']."' AND classification='".$_POST['active_value']."'";
			$options_array=db_toArray($q_get_options);
			$options=array_sub($options_array,'type');
			
			displayOption($options);
		}
	}
	
	function getSession($var){
		if($var=='minimized'){
			echo (bool)array_dir('_SESSION/minimized');
		}
		if($var=='scroll'){
			echo array_dir('_SESSION/'.$_POST['controller'].'/'.$_POST['action'].'/scroll_top');
		}
		if($var=='default_controller'){
			echo $this->config->item('default_controller');
		}
	}
	
	function setSession(){
		if(is_posted('minimized')){
			array_dir('_SESSION/minimized',(bool)$_POST['minimized']);
			echo 'success';
		}
		
		if(got('scroll')){
			array_dir('_SESSION/'.$_POST['controller'].'/'.$_POST['action'].'/scroll_top',$_POST['scrollTop']);
		}
	}
}
?>