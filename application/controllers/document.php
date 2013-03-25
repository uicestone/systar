<?php
class Document extends SS_controller{
	
	var $list_args;
	
	var $section_title='文件';
	
	function __construct(){
		parent::__construct();
		
		$controller=CONTROLLER;
		
		$this->list_args=array(
			'name'=>array('heading'=>'文件名','cell'=>'<a href="/document/download/{id}">{name}</a>'),
			'time_insert'=>array('heading'=>'上传时间','parser'=>array('function'=>function($time_insert){return date('Y-m-d H:i:s',$time_insert);},'args'=>array('{time_insert}'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->$controller,'getCompiledLabels'),'args'=>array('{id}')))
		);
	}
	
	function index(){
		
		//监测有效的名称选项
		if($this->input->post('name')!==false && $this->input->post('name')!==''){
			option('search/name',$this->input->post('name'));
		}
		
		if(is_array($this->input->post('labels'))){
			
			if(is_null(option('search/labels'))){
				option('search/labels',array());
			}
			
			option('search/labels',$this->input->post('labels'));
		}
		
		//点击了取消搜索按钮，则清空session中的搜索项
		if($this->input->post('submit')==='search_cancel'){
			option('search/labels',array());
			option('search/name',NULL);
		}
		
		//提交了搜索项，但搜索项中没有labels项，我们将session中搜索项的labels项清空
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			option('search/labels',array());
		}
		
		$table=$this->table->setFields($this->list_args)
			->setData($this->document->getList(option('search')))
			->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
		
		if(file_exists(APPPATH.'/views/'.CONTROLLER.'/list_sidebar'.EXT)){
			$this->load->view(CONTROLLER.'/list_sidebar',true,'sidebar');
		}else{
			$this->load->view('document/list_sidebar',true,'sidebar');
		}

	}

	function download($id){
		$this->output->as_ajax=false;

		$document=$this->document->fetch($id);
		
		$this->document->exportHead($document['name']);
		
		$filename=$this->config->item('document_path').$document['id'];
		
		$filename=iconv("utf-8","gbk",$filename);//Windows服务器的文件名采用gbk编码保存
		readfile($filename);
	}
	
	function submit(){
		
		$config=array(
			'upload_path'=>'../uploads/',
			'allowed_types'=>'*',
			'encrypt_name'=>true
		);
		
		$this->load->library('upload', $config);
		
		try{
			if (!$this->upload->do_upload('document')) {
				$this->output->message($this->upload->display_errors(), 'warning');
				throw new Exception;
			}
			
			$file_info = $this->upload->data();
			
			$document_id=$this->document->add(array(
				'name'=>$file_info['client_name'],
				'filename'=>$file_info['client_name'],
				'extname'=>$file_info['file_ext'],
				'size'=>$file_info['file_size']
			));
			
			$this->document->updateLabels($document_id,array_dir('_SESSION/document/index/search/labels'));
			
			rename('../uploads/'.$file_info['file_name'],'../uploads/'.$document_id);
			
			$data=array(
				'id'=>$document_id,
				'name'=>$file_info['client_name']
			);
			
			$this->load->addViewData('file', $data);

			//$info->name = $file_info['file_name'];
			//$info->size = $file_info['file_size'];
			//$info->type = $file_info['file_type'];
			//$info->url = $upload_path_url . $data['file_name'];
			//$info->thumbnail_url = $upload_path_url . $data['file_name']; //I set this to original file since I did not create thumbs.  change to thumbnail directory if you do = $upload_path_url .'/thumbs' .$data['file_name']
			//$info->delete_url = base_url() . 'upload/deleteImage/' . $data['file_name'];
			//$info->delete_type = 'DELETE';

			$this->output->data=$this->load->view('document/upload_list_item',true);

		}catch(Exception $e){
			$this->output->status='fail';
		}

	}
	
	function update($id){
		if($this->input->post('document')){
			$this->document->update($id,$this->input->post('document'));
		}
		
		if($this->input->post('labels')){
			$this->document->updateLabels($id, $this->input->post('labels'));
		}
	}
}
?>