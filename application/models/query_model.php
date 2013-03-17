<?php
class Query_model extends Cases_model{
	function __construct(){
		parent::__construct();
	}

	function getList($method=NULL){
		$q="
			SELECT case.id,case.first_contact,case.num,case.summary,case.comment,
				client.id AS client,IF(client.abbreviation IS NULL,client.name,client.abbreviation) AS client_name,query_type.label_name AS type,
				source.content AS source,
				GROUP_CONCAT( DISTINCT staff.name ) AS staff_names
			FROM `case`
				INNER JOIN case_people case_client ON case_client.type='客户' AND case_client.case=case.id
				INNER JOIN people client ON client.id=case_client.people
				INNER JOIN case_people case_staff ON case_staff.type='律师' AND case_staff.case=case.id
				INNER JOIN people staff ON staff.id=case_staff.people
				INNER JOIN case_label query_type ON query_type.type='咨询方式' AND query_type.case=case.id
				INNER JOIN (
					SELECT people,content FROM people_profile
					WHERE name='来源类型'
				)source ON source.people=client.id
			WHERE case.company={$this->company->id} AND case.display=1
				AND case.is_query=1
		";
		
		if(!$this->user->isLogged('service')){//客服可以看到所有咨询
			$q.="
				AND case.id IN (
					SELECT `case` FROM case_people WHERE type='律师' AND people={$this->user->id}
				)
			";
		}
		
		$q=$this->search($q,array('client.name'=>'咨询人'));
		$q.=" GROUP BY case.id";
		$q=$this->orderBy($q,'first_contact','DESC');
		$q=$this->pagination($q);
		return $this->db->query($q)->result_array();
	}
}
?>