<?php
class Gate extends SS_Controller{
	function __construct() {
		$this->require_login=false;
		parent::__construct();
	}
	
	function combined($type){
		$this->load->driver('minify');
		
		if(!in_array($type,array('js','css'))){
			show_error('error type for request combined file');
		}
		
		switch($type){
			case 'js':
				$files=array(
					'../web/js/underscore-min.js',
					ENVIRONMENT==='development'?'../web/js/jQuery/jquery-1.9.1.min.js':'../web/js/jQuery/jquery-1.9.1.js',
					'../web/js/jQuery/jquery-migrate-1.1.1.js',
					'../web/js/backbone-min.js',
					'../web/js/jQuery/jquery-ui-1.10.3.custom.min.js',
					
					'../web/js/jQuery/jquery.placeholder.js',
					'../web/js/jQuery/jQueryRotate.2.2.js',
					'../web/js/jQuery/jquery-ui.etc.js',
					'../web/js/jQuery/fullcalendar/fullcalendar.js',
					'../web/js/jQuery/highcharts/highcharts.js',
					'../web/js/jQuery/select2/select2.js',
					'../web/js/jQuery/select2/select2_locale_zh-CN.js',
					'../web/js/jQuery/jquery.iframe-transport.js',
					'../web/js/jQuery/jquery.fileupload.js',
					'../web/js/jQuery/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.js',
					
					'../web/js/router.js',
					
					'../web/js/functions.js',
					'../web/js/events.js',
					
					'../web/js/schedule.js',
					'../web/js/schedule_widget.js',
					'../web/js/schedule_calendar.js'

				);
				$export='../web/js/combined.js';
				$this->output->set_content_type('js');
				break;
		
			case 'css':
				$files=array(
					'../web/style/redmond/jquery-ui-1.10.3.custom.css',
					'../web/style/jquery-ui-bootstrap/jquery-ui-1.10.0.custom.css',
					'../web/style/icomoon/style.css',
					'../web/js/jQuery/fullcalendar/fullcalendar.css',
					'../web/js/jQuery/select2/select2.css',
					'../web/js/jQuery/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.css',
					'../web/style/common.css'
				);
				$export='../web/style/combined.css';
				$this->output->set_content_type('css');
				break;
		}
		$combined = $this->minify->combine_files($files, $type, false);
		
		if(ENVIRONMENT==='production'){
			$this->minify->save_file($combined, $export);
		}
		
		$this->output->set_output($combined);
	}
	
	function index(){
		$this->load->addViewData('css', $this->config->user_item('css'));
		
		$this->load->view('head');
		$this->load->view('menu');
		$this->load->view('nav');
		$this->load->view('frame');
		$this->load->view('foot');
	}
	
	/**
	 * ie6跳转提示页面
	 */
	function browser(){
		$this->section_title='请更新您的浏览器';
		$this->load->view('head');
		$this->load->view('browser');
		$this->load->view('foot');
	}
	
	/**
	 * 接待台
	 * 接受系统外部提交的数据至本公司日程
	 */
	function reception(){
		try{
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
				'start'=>$this->date->now,
				'end'=>$this->date->now+3600,
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
