<?php
class SS_Model extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	function limit($db_active_record){
		
		$rows=$db_active_record->count_all_results();
		
		if(option('pagination/start')>$rows || $rows==0){
			//已越界或空列表时，列表起点归零
			option('pagination/start',0);

		}elseif(option('pagination/start')+option('pagination/item')>=$rows && $rows>option('pagination/items')){
			//末页且非唯一页时，列表起点定位末页起点
			option('pagination/start',$rows - ($rows % option('pagination/items')));
		}

		if(!is_null(option('pagination/start')) && option('pagination/items')){
			if($this->input->post('start')!==false){
				option('pagination/start',$this->input->post('start'));
			}
			if($this->input->post('items')!==false){
				option('paginantion/items',$this->input->post('items'));
			}
		}else{
			option('pagination/start',0);
			option('pagination/items',25);
		}
		
		option('pagination/rows',$rows);
		
		option('pagination/pages', ceil(option('pagination/rows') / option('pagination/items')) );
		
		option('pagination/pagenum',option('pagination/start') / option('pagination/items') + 1);

		return array(option('pagination/items'),option('pagination/start'));
	}
	
}
?>