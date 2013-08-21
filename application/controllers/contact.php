<?php
class Contact extends People{
	
	function __construct(){
		parent::__construct();
		$this->load->model('contact_model','contact');
		$this->people=$this->contact;
		$this->load->view_path['edit']='client/edit';
	}

}
?>