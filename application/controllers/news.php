<?php
class News extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		$field=array(
			'time'=>array('heading'=>'日期','td_title'=>'width="80px"','eval'=>true,'cell'=>"
				return date('m-d',{time});
			"),
			'title'=>array('heading'=>'标题','cell'=>'<a href="javascript:showWindow(\'news/edit/{id}\')">{title}</a>'),
			'username'=>array('heading'=>'发布人')
		);
		
		$list=$this->table->setField($field)
			->setData($this->news->getList())
			->generate();
		
		$this->load->addViewData($list);

		
	}
	
	function add(){
	}
	
	function edit($id=NULL){
	}
}
?>