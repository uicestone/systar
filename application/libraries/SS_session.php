<?php
class SS_Session extends CI_Session{
	
	var $db;
	
	function __construct($params = array()) {
		parent::__construct($params);
	}
	
	function all_userdata($prefix='') {
		$user_data=parent::all_userdata();
		
		if($prefix===''){
			return $user_data;
		}
		else{
			
			$prefixed_user_data=array();
			
			foreach($user_data as $key => $value){
				if(strpos($key,$prefix)===0){
					$prefix_preg=preg_quote($prefix,'/');
					$prefixed_user_data[preg_replace("/^$prefix_preg/", '', $key)]=$value;
				}
			}
			
			return $prefixed_user_data;
		}
	}
}

?>
