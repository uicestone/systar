<?php
class Society_model extends Team_model{
	
	function __construct() {
		parent::__construct();
	}
	
	function countApplicants($team_id){
		return $this->db->from('people_relationship')
			->where('people',$team_id)
			->where('accepted',true)
			->count_all_results();
	}
	
	function getList(array $args=array()){
		
		!isset($args['type']) && $args['type']='society';
		
		return parent::getList($args);
	}
	
}
?>