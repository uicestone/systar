<?php
require_once APPPATH.'/controllers/project.php';
class Cases extends Project{
	function __construct() {
		parent::__construct();
		$this->project=$this->cases;
	}
	
	function host(){
		option('search/role','主办律师');
		$this->index();
	}
	
	function consultant(){
		
		if(is_null(option('search/labels'))){
			option('search/labels',array('分类'=>'法律顾问'));
		}
		
		$this->index();
	}
	
	function file(){
		if(is_null(option('search/labels'))){
			option('search/labels',array('已申请归档','案卷已归档'));
		}
		
		$this->index();
	}
	
	function index(){
		if(is_null(option('search/labels'))){
			option('search/labels',array('案件'));
		}

		parent::index();
	}
}
?>
