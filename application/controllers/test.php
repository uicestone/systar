<?php
class Test extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		//$this->load->view('test');
		//$this->session->sess_destroy();
		//session_destroy();
		print_r($this->session->all_userdata());
		print_r($_SESSION);
	}
	
	/**
		* CDS示例代码，id为4的用户在case栏目里搜索"一审"、"诉讼"、"法律"
		*/
	function cds(){
		$keywords=array('一审','诉讼','法律');
		$hasError=false;
		$sql='call init_CDS();';
		$isOK=$this->db->simple_query($sql); //先调用init_CDS()来初始化CDS
		if($isOK){
			for($i=0,$isContinue=true;$i<count($keywords)&&$isContinue;$i++){
				$keyword=$keywords[$i];
				$sql='insert into keywords_table values(\''.$keyword.'\');'; //顺序插入搜索关键字
				$this->db->query($sql);
				$affectedRows=$this->db->affected_rows();
				if($affectedRows==0){
					$this->errorSQLMessage($sql);
					$isContinue=false;
					$hasError=true;
				}
			}
			if(!$hasError){
				$onlyThis=1;
				$userId=2;
				$sql='call case_CDS('.$userId.','.$onlyThis.');';
				$isOK=$this->db->simple_query($sql);
				if($isOK){
					//从CD_table里去取得结果
					$sql='select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;';
					$results=$this->db->query($sql);
					$rows=$results->result_array();
					foreach($rows as $row){
						$rowDisplay='';
						foreach($row as $field=>$value){
							$rowDisplay.=$field.' : '.$value.'&nbsp;&nbsp;&nbsp;';
						}
						echo $rowDisplay.'<br/>';
					}
					$results->free_result();					
				}
				else{
					$this->errorMessage($sql);
				}
			}
		}
		$sql='call finalize_CDS();'; //释放CDS资源
		$isOK=$this->db->simple_query($sql);
		if(!$isOK){
			$this->errorSQLMessage($sql);
		}
		$this->load->require_head=false;
		$this->load->main_view_loaded=true;
		$this->load->sidebar_loaded=true;
	}
}

?>
