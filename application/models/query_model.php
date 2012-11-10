<?php
class Query_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM `case` WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
	
	function getList($para=NULL){
		$q="
			SELECT 
				case.id,case.first_contact,case.num,case.query_type AS type,case.summary,case.comment,
				client.abbreviation AS client_name,case_client.client,
				GROUP_CONCAT(staff.name) AS staff_name,
				client_source.type AS source
			FROM `case`
				LEFT JOIN case_client ON case.id=case_client.case
				LEFT JOIN client ON client.id=case_client.client
				LEFT JOIN case_lawyer ON case.id=case_lawyer.case
				LEFT JOIN staff ON staff.id=case_lawyer.lawyer
				LEFT JOIN client_source ON case.source=client_source.id 
			WHERE case.company='{$this->config->item('company')}' AND case.display=1 AND case.is_query=1
		";
		$q.=" AND case_lawyer.lawyer='".$_SESSION['id']."'";
		if($para=='filed'){
			$q.=" AND case.filed=1";
		}else{
			$q.=" AND case.filed=0";
		}
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
		$q=$this->search($q,array('client.name'=>'咨询人'));
		$q.=" GROUP BY case.id";
		$q=$this->orderBy($q,'first_contact','DESC');
		$q=$this->pagination($q);
		return $this->db->query($q)->result_array();
	}
}
?>