<?php
class SS_Model extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	function pagination($db_active_record, $is_group_query=false, $field_for_distinct_count=NULL){
		
		if($is_group_query){
			$db_active_record->_ar_select=array();
			$db_active_record->select("COUNT(DISTINCT $field_for_distinct_count) AS num_rows",FALSE);
			$rows=$db_active_record->get()->num_rows;
		}else{
			$rows=$db_active_record->count_all_results();
		}
		
		if($this->config->user_item('pagination/start')>$rows || $rows==0){
			//已越界或空列表时，列表起点归零
			$this->config->set_user_item('pagination/start',0);

		}elseif($this->config->user_item('pagination/start')+$this->config->user_item('pagination/items')>=$rows && $rows>$this->config->user_item('pagination/items')){
			//末页且非唯一页时，列表起点定位末页起点
			$this->config->set_user_item('pagination/start',$rows - ($rows % $this->config->user_item('pagination/items')));
		}

		if($this->config->user_item('pagination/start')!==false && $this->config->user_item('pagination/items')!==false){
			if($this->input->post('start')!==false){
				$this->config->set_user_item('pagination/start',$this->input->post('start'));
			}
			if($this->input->post('items')!==false){
				$this->config->set_user_item('paginantion/items',$this->input->post('items'));
			}
		}else{
			$this->config->set_user_item('pagination/start',0);
			$this->config->set_user_item('pagination/items',25);
		}
		
		$this->config->set_user_item('pagination/rows',$rows);
		
		$this->config->set_user_item('pagination/pages', ceil($this->config->user_item('pagination/rows') / $this->config->user_item('pagination/items')) );
		
		$this->config->set_user_item('pagination/pagenum',$this->config->user_item('pagination/start') / $this->config->user_item('pagination/items') + 1);

		return array($this->config->user_item('pagination/items'),$this->config->user_item('pagination/start'));
	}
	
}
?>