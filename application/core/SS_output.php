<?php
class SS_Output extends CI_Output{

	/**
	 * 响应状态
	 * success
	 * fail
	 * redirect: 此时data作为一个字符串被读取，作为新的hash导航
	 * login_required
	 * denied
	 */
	var $status;
	
	var $message;
	
	var $data=array();
	
	/**
	 * 作为ajax输出，为true时，即使输出内容不为空，也将封装入Output::data属性中
	 * 为false时则直接将待输出内容输出，不论Output::data,message等属性
	 */
	var $as_ajax=true;
	
	function __construct(){
		parent::__construct();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Prepend Output
	 *
	 * Prepends data onto the output string
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function prepend_output($output)
	{
		if ($this->final_output == '')
		{
			$this->final_output = $output;
		}
		else
		{
			$this->final_output = $output.$this->final_output;
		}

		return $this;
	}
	
	function message($message,$type='notice'){
		$this->message[$type][]=$message;
	}
	
	/**
	 * 后台传送到前台的数据中，封装有数据内容，处理方式和对应填充前台元素的jQuery选择器
	 * 如此一来，前端就可以用一个通用的方法来处理所有的后台响应，对页面上的任何元素作更新
	 * @param $type 可选uri,html
	 * @param $content 对应$type，为html内容或uri地址
	 * @param $content_name 内容的名称，如'content'(页面内容),'name'(标签选项名称)
	 */
	function setData($content,$content_name='content',$type='html',$selector=NULL,$method=NULL){
		
		if(is_array($content)){
			isset($content['content_name']) && $content_name=$content['content_name'];
			isset($content['type']) && $type=$content['type'];
			isset($content['selector']) && $selector=$content['selector'];
			isset($content['method']) && $method=$content['method'];
			isset($content['content']) && $content=$content['content'];
		}
		
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
	
	function header($name,$value_set_to=NULL){
		$headers_list=headers_list();
		$headers=array();
		foreach($headers_list as $header){
			preg_match('/^(.*?)\:/', $header,$matches);
			$header_name=$matches[1];
			preg_match('/\:\s*?(.*?)$/',$header,$matches);
			$header_value=$matches[1];
			$headers[$header_name]=$header_value;
		}
		
		if(is_null($value_set_to)){
			if(isset($headers[$name])){
				return $headers[$name];
			}else{
				return false;
			}
		}elseif(!headers_sent()){
			$this->set_header($name.': '.$value_set_to);
		}else{
			return false;
		}
	}
}
?>