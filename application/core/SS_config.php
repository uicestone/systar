<?php
class SS_config extends CI_Config{
	function __construct(){
		parent::__construct();
		require 'config/config.php';
	}
}
?>