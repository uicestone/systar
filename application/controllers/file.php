<?php
class File extends SS_Controller{
	function __construct() {
		parent::__construct();
		$this->load->model('cases_model','cases');
	}
	
	function index(){
		$field=array(
			'time_contract'=>array('title'=>'案号','td_title'=>'width="140px"','td'=>'title="立案时间：{time_contract}" hash="cases/edit/{id}"','content'=>'{num}'),
			'name'=>array('title'=>'案名','content'=>'{name}'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
			'is_reviewed'=>array('title'=>'状态','td_title'=>'width="75px"','eval'=>true,'content'=>"
				return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			")
		);
		
		$table=$this->table->setFields($field)
			->setData($this->cases->getFiledList())
			->generate();
		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}
	
}
?>
