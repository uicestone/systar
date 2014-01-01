<?php
class Client_model extends People_model{
	function __construct(){
		parent::__construct();
		$this->fields['type']='client';
	}
	
	function getList($args = array()) {
		!isset($args['type']) && $args['type']='client';
		
		if(array_key_exists('staff', $args)){
			$this->db->where('people.staff', $args['staff']);
		}
		
		return parent::getList($args);
	}
	
	/**
	 * 获得系统中所有客户的email
	 */
	function getAllEmails(){
		$query="
			SELECT content 
			FROM people_profile 
				INNER JOIN people ON people.id=people_profile.people
			WHERE
				people.type='client' 
				AND people_profile.name='电子邮件'
		";
		
		$result=$this->db->query($query);
		
		return array_sub($result->result_array(),'content');
	}
}
?>