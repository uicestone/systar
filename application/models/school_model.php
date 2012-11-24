<?php
class School_model extends SS_Model{
	
	var $current_term;
	var $highest_grade;
	
	function __construct() {
		parent::__construct();
		
		$this->current_term=$this->session->userdata('current_term');
		$this->highest_grade=$this->session->userdata('highest_grade');

		$this->load->library('Lunar');
		$year=date('y',$this->config->item('timestamp'));//两位数年份
		$term_begin_this_year_timestamp=strtotime($year.'-8-1');//今年8/1
		$lunar_this_new_year_timestamp=$this->lunar->L2S($year.'-1-1');//今年农历新年的公历
		if($this->config->item('timestamp')>=$term_begin_this_year_timestamp){
			$term=1;$term_year=$year;
		}elseif($this->config->item('timestamp')<$lunar_this_new_year_timestamp){
			$term=1;$term_year=$year-1;
		}else{
			$term=2;$term_year=$year-1;
		}
		$this->session->set_userdata('current_term',$term_year.'-'.$term);//计算当前学期
		$this->session->set_userdata('highest_grade',$term_year-2);//计算当前在校最高年级
	}
}

?>
