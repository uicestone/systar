<?php
class Test extends SS_controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		$this->load->model('schedule_model','schedule');
		$this->schedule->setTaskBoardSort(array(3=>array(1100),2=>array(1100)),$this->user->id);
		//print_r($this->session->all_userdata());
		//print_r($this->user);
	}
	
	function select2(){
		$this->load->view('head');
		$this->load->view('test/select2');
	}
	
	function ar(){
		$this->db->from('test')
			->where(array('a'=>1,'b'=>2))
			->or_where('a',1)
			->get();
		echo $this->db->last_query();
	}
	
	function team(){
		print_r($this->team->traceByPeople(8001));
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
		
		
		$this->load->sidebar_loaded=true;
	}
	
	function pscws(){
		$pscws_path=APPPATH.'third_party/pscws4/';
		require_once($pscws_path.'pscws4.class.php');
		$pscws=new PSCWS4('utf8');
		$pscws->set_dict($pscws_path.'dict.utf8.xdb');
		$pscws->set_rule($pscws_path.'etc/rules.utf8.ini');
		$pscws->set_ignore(true);
		$text='我是华东政法大学的学生';
		echo $text;
		$pscws->send_text($text);
		$words=array();
		while($some=$pscws->get_result()){
			foreach($some as $one){
				array_push($words,$one['word']);
			}
		}
		var_dump($words);
		$display_text=implode(' ',$words);
		echo $display_text;
		$pscws->close();
		
		
		$this->load->sidebar_loaded=true;
	}
	
	function labelSearch(){
		$label_string="一审婚姻法律";
		$this->load->model('Label_model','label_model');
		$sorted_results=$this->label_model->search($label_string);
		var_dump($sorted_results);
	}
}

?>
