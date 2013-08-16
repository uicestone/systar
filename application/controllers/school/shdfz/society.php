<?php
class Society extends Team{
	function __construct() {
		parent::__construct();
		
		$this->load->model('society_model','society');
		$this->people=$this->society;
		$this->group=$this->society;

		$this->list_args=array(
			'name'=>array('heading'=>'名称'),
			
			'intro'=>array('heading'=>'简介','cell'=>array('class'=>'ellipsis','title'=>'{intro}')),
			
			'capacity'=>array('heading'=>'名额/已报','parser'=>array('function'=>function($id,$capacity){
				return $capacity.'/'.$this->society->countApplicants($id);
			},'args'=>array('id','capacity')))
		);
		
		if(!$this->user->inTeam('teacher')){
			$this->list_args['apply']=array('heading'=>'报名','cell'=>array('data'=>'<button type="submit" name="apply" id="{id}">报名</button>'));
		}
		
		$this->load->view_path['edit']='society/edit';
		
	}
	
	function index(){
		
		if($this->user->inTeam('teacher')){
			$this->config->set_user_item('search/leaded_by', $this->user->id);
		}
		else{
			$this->config->set_user_item('search/has_meta',array('状态'=>array('不限额开放报名','限额开放报名')));
		}
		
		$this->config->set_user_item('search/get_meta', array('intro'=>'简介','capacity'=>'名额'));
		
		parent::index();
	}
	
	function submit($submit, $id, $button_id=NULL){
		
		parent::submit($submit, $id, $button_id);
		
		if($submit==='apply'){
			$this->society->addRelative($this->society->id, $this->user->id, '报名', NULL);
		}
		
		elseif($submit==='accept'){
			$this->society->updateRelative($this->society->id, $button_id, array('accepted'=>true));
			redirect('society/'.$this->society->id);
		}
		
		elseif($submit==='refuse'){
			$this->society->updateRelative($this->society->id, $button_id, array('accepted'=>false));
			redirect('society/'.$this->society->id);
		}
		
		elseif($submit==='cancelacception'){
			$this->society->updateRelative($this->society->id, $button_id, array('accepted'=>NULL));
			redirect('society/'.$this->society->id);
		}
	}
	
	function relativeList() {
		
		$this->load->model('student_model','student');
		
		$list_args=array(
			'name'=>array('heading'=>'姓名'),
			'num'=>array('heading'=>'学号'),
			'time'=>array('heading'=>'加入时间','parser'=>array('function'=>function($time){
				return date('Y-m-d H:i',$time);
			},'args'=>array('relationship_time'))),
			'accepted'=>array('heading'=>'状态','parser'=>array('function'=>function($id,$accepted){
				if(is_null($accepted)){
					$out='待批准加入<button type="submit" name="accept" id="'.$id.'">批准</button><button type="submit" name="refuse" id="'.$id.'">拒绝</button>';
				}elseif($accepted){
					$out='已批准加入<button type="submit" name="cancelacception" id="'.$id.'">取消</button>';
				}else{
					$out='已拒绝加入<button type="submit" name="cancelacception" id="'.$id.'">取消</button>';
				}
				
				if(count($this->society->getList(array('open'=>true,'has_relative_like'=>$id)))>1){
					$out.=' 与其他社团冲突';
				}
				return $out;
			},'args'=>array('id','accepted')))
		);
		
		$list=$this->table->setFields($list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setData($this->student->getList(array('is_relative_of'=>$this->society->id)))
			->generate();
		
		return $list;
	}
	
}
?>
