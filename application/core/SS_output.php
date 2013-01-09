<?php
class SS_Output extends CI_Output{

	var $status;
	
	var $message;
	
	var $data=array();
	
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
	 * @param $selector 要更新的选择器，默认为#page
	 * @param $method 元素替换的方式,innerHTML或replace
	 */
	function setBlock($type,$content=NULL,$selector='#page',$method='innerHTML'){
		$this->data[]=array(
			'type'=>$type,
			'content'=>$content,
			'selector'=>$selector,
			'method'=>$method
		);
	}
}
?>