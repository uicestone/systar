<?php
class Society_model extends Team_model{
	
	function __construct() {
		parent::__construct();
	}
	
	function countApplicants($team_id,$accepted=NULL){
		
		if(isset($accepted)){
			$this->db->where('accepted',true);
		}
		
		return $this->db->from('people_relationship')
			->where('people',$team_id)->where('is_on',true)
			->count_all_results();
	}
	
	function getList(array $args=array()){
		
		!isset($args['type']) && $args['type']='society';
		
		return parent::getList($args);
	}
	
}
?>