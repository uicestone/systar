<?php
class SS_Session extends CI_Session{
	
	var $db;
	
	function __construct($params = array()) {
		parent::__construct($params);
	}
	
	function all_userdata($prefix='') {
		$user_data=parent::all_userdata();
		return array_prefix($user_data, $prefix);
	}
}

?>
