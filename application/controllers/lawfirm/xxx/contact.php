<?php
class Contact extends People{
	
	function __construct(){
		parent::__construct();
		$this->load->model('contact_model','contact');
		$this->people=$this->contact;
		$this->load->view_path['edit']='client/edit';
		
		$this->form_validation_rules['people']=array(
			array(
				'field'=>'profiles[来源类型]',
				'label'=>'客户来源类型',
				'rules'=>'required'
			),
			array(
				'field'=>'people[staff]',
				'label'=>'来源律师',
				'rules'=>'required'
			)
		);
		
		if($this->user->isLogged('service')){
			$this->list_args+=array(
				'uid'=>array('heading'=>'添加人','parser'=>array('function'=>function($uid){
					return $this->user->fetch($uid,'name');
				},'args'=>array('uid'))),
				'time_insert'=>array('heading'=>'添加时间','parser'=>array('function'=>function($time_insert){
					return date('Y-m-d',$time_insert);
				},'args'=>array('time_insert')))
			);
			
			$this->search_items=array_merge($this->search_items,array('time_insert/from','time_insert/to','uid'));
		}
		
		
	}

}
?>