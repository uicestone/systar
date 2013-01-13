<?php
class Frame extends SS_Controller{
	function __construct(){
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	/**
	 * 覆盖SS_controller的_output方法，因为框架不需要改变输出方式
	 * @param type $output
	 */
	function _output($output) {
		echo $output;
	}
	
	function index(){
		$this->load->view('head');
		$this->load->view('nav');
		$this->load->view('menu');
		$this->load->view('frame');
		$this->load->view('innerjs');
		$this->load->view('foot');
	}
}
?>