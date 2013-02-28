<?php
/**
 * 接待台
 * 接受系统外部提交的数据至本公司日程
 */
class Reception extends SS_Controller{
	function __construct() {
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	function index(){
		try{
			$this->output->as_ajax=false;
			$this->load->model('schedule_model','schedule');
			$this->load->model('staff_model','staff');
			$receptionist=$this->staff->check($this->input->post('to'));

			$content='';
			foreach($this->input->post() as $name=>$value){
				$content.=$name.': '.$value."\n";
			}
			$insert_id=$this->schedule->add(array(
				'name'=>$this->input->post('title'),
				'people'=>$receptionist,
				'content'=>$content,
				'time_start'=>$this->config->item('timestamp'),
				'time_end'=>$this->config->item('timestamp')+3600,
				'completed'=>false
			));

			if($insert_id){
				echo '您提交的信息已经收到！';
			}
		}catch(Exception $e){
			foreach($this->output->message as $messages){
				foreach($messages as $message){
					echo $message."\n";
				}
			}
		}
		
	}
}
?>
