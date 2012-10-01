<?php
class Frame extends CI_Controller{
	function index(){
		$this->load->view('head_frame');
		$this->load->view('frame');
	}
}
?>