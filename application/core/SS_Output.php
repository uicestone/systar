<?php
class SS_Output extends CI_Output{

	/**
	 * 响应状态
	 * success
	 * fail
	 * refresh
	 * redirect: 此时data作为一个字符串被读取，作为新的hash导航
	 * redirecturl
	 * login_required
	 * denied
	 */
	var $status;
	
	var $title;
	
	var $message;
	
	var $data=array();
	
	/**
	 * 作为ajax输出，为true时，即使输出内容不为空，也将封装入Output::data属性中
	 * 为false时则直接将待输出内容输出，不论Output::data,message等属性
	 */
	var $as_ajax;
	
	function __construct(){
		parent::__construct();
	}
	
	function message($message,$type='notice'){
		$this->message[$type][]=$message;
	}
	
	/**
	 * 后台传送到前台的数据中，封装有数据内容，处理方式和对应填充前台元素的jQuery选择器
	 * 如此一来，前端就可以用一个通用的方法来处理所有的后台响应，对页面上的任何元素作更新
	 * @param $content 对应$type，为html内容或uri地址
	 * @param $content_name 内容的名称，如'content'(页面内容),'name'(标签选项名称)
	 * @param $type 可选uri,html,sidebar,content-table
	 * @param $selector 输出内容填入前端DOM文档的jQuery选择器
	 * @param $method 输出内容填入前端DOM文档的方式 可选replace,innerhtml
	 */
	function setData($content,$content_name='content',$type='html',$selector=NULL,$method=NULL){
		
		$data=array(
			'content'=>$content,
			'type'=>$type
		);
		
		if(isset($selector)){
			$data['selector']=$selector;
		}
		
		if(isset($method)){
			$data['method']=$method;
		}
		
		$this->data[$content_name]=$data;
	}
}
?>