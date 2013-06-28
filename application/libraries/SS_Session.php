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
	
	function unset_userdata($newdata = array(), $path=false, $regex=false) {
		if(is_string($newdata)){
			if($path){
				foreach(array_keys($this->userdata) as $key){
					if(strpos($key,$newdata.'/')===0){
						unset($this->userdata[$key]);
					}
				}
			}
			
			if($regex){
				foreach(array_keys($this->userdata) as $key){
					if(preg_match($newdata,$key)){
						unset($this->userdata[$key]);
					}
				}
			}
			
		}
		
		parent::unset_userdata($newdata);
	}
}

?>
