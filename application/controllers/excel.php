<?php
/**
 * this class is used to test SS_table::generateExcel(),if it's no problem, it should be removed
 */
class Excel extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}

	function index(){
		$this->load->model('schedule_model','schedule');
		$field=array(
			'name'=>array('heading'=>'标题'),
			'content'=>array('heading'=>'内容'),
			'time_start'=>array('heading'=>'时间','eval'=>true,'cell'=>"return date('m-d H:i',{time_start});"),
			'hours_own'=>array('heading'=>'自报小时'),
			'staff_name'=>array('heading'=>'律师')
		);
		$this->table->setFields($field)
			->setData($this->schedule->getList())
			->generateExcel();
		
		
		$this->load->sidebar_loaded=true;
	}
}
?>
