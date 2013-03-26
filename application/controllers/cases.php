<?php
/**
 * 案件，继承于 项目
 */
class Cases extends Project{
	
	var $section_title='案件';

	function __construct() {
		parent::__construct();
		
		$this->project=$this->cases;

		$this->list_args=array(
			'time_contract'=>array(
				'heading'=>array('data'=>'案号','width'=>'140px'),
				'cell'=>array('data'=>'{num}','title'=>'立案时间：{time_contract}')
			),
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'responsible'=>array('heading'=>array('data'=>'主办律师','width'=>'110px'),'parser'=>array('function'=>array($this->cases,'getResponsibleStaffNames'),'args'=>array('{id}'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->cases,'getCompiledLabels'),'args'=>array('{id}')))
		);
		
		$this->staff_list_args=array(
			'staff_name'=>array('heading'=>'名称','cell'=>'{staff_name}<button type="submit" name="submit[remove_staff]" id="{id}" class="hover">删除</button>'),
			'role'=>array('heading'=>'本案职位'),
			'contribute'=>array('heading'=>'贡献','eval'=>true,'cell'=>"
				\$hours_sum_string='';
				if('{hours_sum}'){
					\$hours_sum_string='<span class=\"right\">{hours_sum}小时</span>';
				}

				return \$hours_sum_string.'<span>{contribute}'.('{contribute_amount}'?' ({contribute_amount})':'').'</span>';
			")
		);
	
	}
	
	function add(){
		$this->cases->id=$this->cases->add(array('time_contract'=>$this->date->today,'time_end'=>date('Y-m-d',$this->date->now+100*86400)));
		$this->edit($this->project->id);
		redirect('#'.CONTROLLER.'/edit/'.$this->cases->id);
	}
	
	function edit($id){
		$this->cases->id=$id;
		
		try{
			$this->cases=array_merge($this->cases->fetch($id),$this->input->sessionPost('project'));

			$this->cases->labels=array_merge($this->cases->getLabels($this->cases->id),$this->input->sessionPost('labels'));

			if(!$this->cases['name']){
				$this->section_title='未命名'.$this->section_title;
			}else{
				$this->section_title=$this->cases['name'];
			}

			$project_role=$this->cases->getRoles($this->cases->id);

			$responsible_partner=$this->cases->getPartner($project_role);
			//获得本案督办人的id

			$lawyers=$this->cases->getLawyers($project_role);
			//获得本案办案人员的id

			$my_roles=$this->cases->getMyRoles($project_role);
			//本人的本案职位

			$this->load->addViewArrayData(compact('project','labels','case_role','responsible_partner','lawyers','my_roles'));

			//计算本案有效日志总时间
			$this->load->view_data['schedule_time']=$this->schedule->calculateTime($this->cases->id);

			$this->load->view_data['case_type_array']=array('诉前','一审','二审','再审','执行','劳动仲裁','商事仲裁');

			if(in_array('咨询',$this->cases->labels)){
				$this->load->view_data['staff_role_array']=array('督办人','接洽律师','律师助理');
			}else{
				$this->load->view_data['staff_role_array']=array('案源人','督办人','接洽律师','主办律师','协办律师','律师助理');
			}

			$this->load->addViewData('staff_list', $this->staffList());
			
			$this->load->addViewData('fee_list', $this->feeList());
			
			$this->load->addViewData('schedule_list', $this->scheduleList());
			
			$this->load->addViewData('plan_list', $this->planList());
			
			$this->load->addViewData('document_list', $this->documentList());

			$this->load->view('case/edit');
			
			$this->load->view('project/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
	}
	
	function submit($submit,$id,$button_id=NULL){
		
		parent::submit($submit, $id, $button_id);
		
		if($submit=='project'){
			if(!$this->project->data['num']){
				$this->output->message('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
				throw new Exception();
			}
			if(isset($this->cases->labels['分类']) && in_array($this->cases->labels['分类'],array('诉讼','非诉讼')) && !in_array('咨询', $this->cases->labels) && !$this->project->data['focus']){
				$this->output->message('请填写案件争议焦点','warning');
				throw new Exception;
			}
		}
	}

	function host(){
		$this->section_title='主办案件';
		option('search/role','主办律师');
		$this->index();
	}
	
	function consultant(){
		
		if(is_null(option('search/labels'))){
			option('search/labels',array('分类'=>'法律顾问'));
		}
		
		$this->index();
	}
	
	function file(){
		if(is_null(option('search/labels'))){
			option('search/labels',array('已申请归档','案卷已归档'));
		}
		
		$this->index();
	}
	
	function index(){
		if(is_null(option('search/labels'))){
			option('search/labels',array('案件'));
		}

		parent::index();
	}
}
?>
