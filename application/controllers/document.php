<?php
class Document extends SS_controller{
	
	var $list_args;
	
	var $search_items=array();
	
	function __construct(){
		parent::__construct();
		$this->load->model('document_model','document');
		
		$this->list_args=array(
			'name'=>array('heading'=>'文件名','parser'=>array('function'=>function($id,$name,$filename){
				if($name==''){
					$name=$filename;
				}
				return '<a href="/document/download/'.$id.'">'.$name.'</a>';
			},'args'=>array('id','name','filename'))),
			'time_insert'=>array('heading'=>'上传时间','parser'=>array('function'=>function($time_insert){return date('Y-m-d H:i',$time_insert);},'args'=>array('time_insert'))),
			'tags'=>array('heading'=>'标签','parser'=>array('function'=>array($this->document,'getCompiledTags'),'args'=>array('id')))
		);
		
		$this->search_items=array('name','tags');

		$this->load->view_path['list_aside']='document/list_sidebar';
	}
	
	function index(){
		
		$this->config->set_user_item('search/order_by', 'document.id desc', false);
		$this->config->set_user_item('search/limit', 'pagination', false);
		
		$this->_search();
		
		$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>'document/{id}'))
			->setData($this->document->getList($this->config->user_item('search')));
		
		$this->load->addViewData('search_items', $this->search_items);
		
		$this->load->view('list');
		$this->load->view('list_aside',true,'sidebar');

	}

	function download($id){
		
		$this->output->as_ajax=false;
		
		try{
		
			$document=$this->document->fetch($id);

			$this->document->exportHead($document['filename']);

			$filename='../uploads/'.$document['id'];

			$filename=iconv("utf-8","gbk",$filename);//Windows服务器的文件名采用gbk编码保存
			readfile($filename);
			
		}catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
	}
	
	function edit($id){
		$this->document->id=$id;
		
		try{
			$this->document->data=array_merge($this->document->fetch($id),$this->input->sessionPost('document'));

			$this->document->tags=array_merge($this->document->getTags($this->document->id),$this->input->sessionPost('tags'));

			if(!$this->document->data['name']){
				$this->output->title='未命名'.lang(CONTROLLER);
			}else{
				$this->output->title=$this->document->data['name'];
			}
			
			if(file_exists(APPPATH.'../web/images/file_type/'.substr($this->document->data['extname'],1).'.png')){
				$this->document->data['icon']=substr($this->document->data['extname'],1).'.png';
			}
			else{
				$this->document->data['icon']='unknown.png';
			}
			
			$this->document->data['uploader_name']=$this->people->fetch($this->document->data['uid'],'name');

			$this->load->addViewData('mod', $this->document->getPeopleMod($this->document->id,array_merge(array_keys($this->user->groups),array($this->user->id))));
			
			$this->load->addViewData('read_mod_people', $this->document->getModPeople($this->document->id, 1));
			
			$this->load->addViewData('write_mod_people', $this->document->getModPeople($this->document->id, 2));
			
			$this->load->addViewData('document', $this->document->data);
			
			$this->load->addViewData('tags', $this->document->tags);

			$this->load->view('document/edit');
			
			$this->load->view('document/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}

	}
	
	function submit($submit,$id=NULL){
		
		if(isset($id)){
			$this->document->id=$id;

			$this->document->data=array_merge($this->document->fetch($id),$this->input->sessionPost('document'));
			$this->document->tags=array_merge($this->document->getTags($this->document->id),$this->input->sessionPost('tags'));
		}
		
		$this->load->library('form_validation');

		try{
			
			if($submit=='upload'){
				$config=array(
					'upload_path'=>'../uploads/',
					'allowed_types'=>'*',
					'encrypt_name'=>true
				);

				$this->load->library('upload', $config);

				if (!$this->upload->do_upload('document')) {
					$this->output->message($this->upload->display_errors(), 'warning');
					throw new Exception;
				}

				$file_info = $this->upload->data();

				$file_info['mail_name']=substr($file_info['client_name'], 0, -strlen($file_info['file_ext']));

				$document_id=$this->document->add(array(
					'name'=>$file_info['mail_name'],
					'filename'=>$file_info['client_name'],
					'extname'=>$file_info['file_ext'],
					'size'=>$file_info['file_size']
				));
				
				$tags=$this->config->user_item('search/tags','index');
				if($tags){
					$this->document->addTags($document_id, $tags);
				}

				rename('../uploads/'.$file_info['file_name'],'../uploads/'.$document_id);

				$data=array(
					'id'=>$document_id,
					'name'=>$file_info['mail_name']
				);
				$this->output->data=$data;
			}
			
			elseif($submit=='document'){
				$this->document->update($this->document->id,$this->document->data);
				
				unsetPost();
				$this->output->message($this->output->title.' 已保存');
			}
			
			elseif($submit=='delete'){
				
				if($this->document->delete($id)){
					$this->output->status='close';
				}
				else{
					throw new Exception('无法删除此文档，可能已与其他数据关联');
				}
			}
			
		}catch(Exception $e){
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
			$this->output->status='fail';
		}
		
		$this->output->as_ajax=true;

	}
	
	function update($id){
		if($this->input->post('document')){
			$this->document->update($id,$this->input->post('document'));
		}
	}
	
	function addMod($id,$people,$mod){
		
		$this->document->data=$this->document->fetch($id);
		
		if($this->user->id!=$this->document->data['uid']){
			$this->output->status='denied';
			return;
		}
		
		$this->document->addMod($mod, $people,$id);
	}
	
	function removeMod($id,$people,$mod){
		$this->document->data=$this->document->fetch($id);
		
		if($this->user->id!=$this->document->data['uid']){
			$this->output->status='denied';
			return;
		}
		
		$this->document->removeMod($mod, $people,$id);
	}
}
?>