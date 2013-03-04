<?php
class File extends SS_Controller{
	function __construct() {
		parent::__construct();
		$this->load->model('cases_model','cases');
	}
	
	function index(){
		$field=array(
			'time_contract'=>array('heading'=>array('data'=>'案号','width'=>'140px'),'cell'=>array('data'=>'{num}','title'=>'title="立案时间：{time_contract}')),
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'lawyers'=>array('heading'=>array('data'=>'主办律师','width'=>'100px')),
			'is_reviewed'=>array('heading'=>array('data'=>'状态','width'=>'75px'),'eval'=>true,'cell'=>"
				return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			")
		);
		
		$table=$this->table->setFields($field)
			->setRowAttributes(array('hash'=>'cases/edit/{id}'))
			->setData($this->cases->getFiledList())
			->generate();
		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}
	
}
?>
