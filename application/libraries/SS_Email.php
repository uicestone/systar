<?php
class SS_Email extends CI_Email{
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 继承原生email类的邮件标题编码方法，换成用base64编码，来更好地适应中文邮件标题
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function subject($subject)
	{
		//$subject = $this->_prep_q_encoding($subject);
		$subject = '=?'. $this->charset .'?B?'. base64_encode($subject) .'?=';
		$this->_set_header('Subject', $subject);
		return $this;
	}

}
?>
