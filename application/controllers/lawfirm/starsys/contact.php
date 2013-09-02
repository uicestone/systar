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
		
	}

}
?>