<?php
class Team_model extends BaseItem_model{
	
	function __construct() {
		parent::__construct();
		$this->table='team';
	}
	
	/**
	 * @param array $args
	 *	people_type	只列出含有某类人员的组
	 */
	function getList($args=array()){
		
		if(isset($args['people_type'])){
			$this->db->join('team_people','team_people.team = team.id','INNER')
				->join('people',"people.id = team_people.people AND people.type = '{$args['people_type']}'",'INNER');
		}
		
		$args['orderby']=false;
		
		return parent::getList($args);
	}

}

?>
