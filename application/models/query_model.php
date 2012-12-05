<?php
class Query_model extends Cases_model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="
			SELECT * 
			FROM `case` 
			WHERE id='{$id}' AND company='{$this->company->id}'";
		return $this->db->query($query)->row_array();
	}
	
	function getList($method=NULL){
		$q="
			SELECT 
				case.id,case.first_contact,case.num,case.query_type AS type,case.summary,case.comment,
				client.abbreviation AS client_name,case_client.client,
				staff.names AS staff_names,
				client_source.type AS source
			FROM `case`
				LEFT JOIN case_client ON case.id=case_client.case
				LEFT JOIN client_source ON case.source=client_source.id
				LEFT JOIN client ON client.id=case_client.client
				LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(DISTINCT staff.name) AS names
					FROM case_lawyer INNER JOIN staff ON case_lawyer.lawyer=staff.id AND case_lawyer.role ='接洽律师'
					WHERE TRUE
					GROUP BY case_lawyer.`case`
				)staff
				ON `case`.id=staff.`case`
			WHERE case.company='{$this->company->id}' AND case.display=1 AND case.is_query=1
		";
		
		if(!$this->user->isLogged('service')){//客服可以看到所有咨询
			$q.="
				AND case.id IN (
					SELECT `case` FROM case_lawyer WHERE lawyer='{$this->user->id}'
				)
			";
		}
		
		if($method=='filed'){
			$q.=" AND case.filed=1";
		}else{
			$q.=" AND case.filed=0";
		}

		$q=$this->search($q,array('client.name'=>'咨询人'));
		$q.=" GROUP BY case.id";
		$q=$this->orderBy($q,'first_contact','DESC');
		$q=$this->pagination($q);
		return $this->db->query($q)->result_array();
	}
}
?>