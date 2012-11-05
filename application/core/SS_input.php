<?php
class SS_input extends CI_Input{
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * 继承post方法，处理post数组
	 * 现可如下访问：
	 * $this->input->post('submit/newcase')
	 */
	function post($index = NULL, $xss_clean = FALSE){
		if(is_null($index)){
			return parent::post($index, $xss_clean);
		
		}else{
			$index_array=explode('/',$index);
			
			$post=parent::post($index_array[0], $xss_clean);
			
			for($i=1;$i<count($index_array);$i++){
				if(isset($post[$index_array[$i]])){
					$post=$post[$index_array[$i]];
				}else{
					return false;
				}
				
			}
			
			return $post;
		}
	}
}
?>