<?php
class News_model extends SS_Model{
	
	var $id;
	
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="
			SELECT * 
			FROM news 
			WHERE id = '{$id}' AND company='{$this->company->id}'";
		return $this->db->query($query)->row_array();
	}
	
	function getList($rows=NULL){
		$q="SELECT * FROM news WHERE display=1 AND company={$this->company->id}";
		
		if(is_null($rows)){
			$q=$this->search($q,array('title'=>'标题'));		    
		}
		$q=$this->orderby($q,'time','DESC');
		if(is_null($rows)){
		    $q=$this->pagination($q);
		}else{
		    $q.=" LIMIT {$rows}";
		}
		
		return $this->db->query($q)->result_array();
	}
}
?>