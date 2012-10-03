<?php
class Nav extends CI_Controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		global $_G;
		$data=compact('_G');
		$this->load->view('head_nav',$data);
		$this->load->view('nav');
		$this->load->view('foot');
	}
}
?>