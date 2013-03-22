<?php
/**
 * 案件，继承于 项目
 */
class Cases extends Project{
	
	var $section_title='案件';

	function __construct() {
		parent::__construct();
		
		$this->project=$this->cases;

		$this->list_args=array(
			'time_contract'=>array(
				'heading'=>array('data'=>'案号','width'=>'140px'),
				'cell'=>array('data'=>'{num}','title'=>'立案时间：{time_contract}')
			),
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'responsible'=>array('heading'=>array('data'=>'主办律师','width'=>'110px'),'parser'=>array('function'=>array($this->cases,'getResponsibleStaffNames'),'args'=>array('{id}'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->cases,'getCompiledLabels'),'args'=>array('{id}')))
		);
	
	}
	
	function add(){
		$this->cases->id=$this->cases->add(array('time_contract'=>$this->date->today,'time_end'=>date('Y-m-d',$this->date->now+100*86400)));
		$this->edit($this->project->id);
		redirect('#'.CONTROLLER.'/edit/'.$this->cases->id);
	}
	
	function host(){
		$this->section_title='主办案件';
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
