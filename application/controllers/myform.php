<?php
class Myform extends SS_controller
{
	function __construct()
	{
		$this->default_method='validate';
		parent::__construct();
	}
	
	function validate()
	{
		$this -> load -> helper(array('form' , 'url'));
		$this -> load -> library('form_validation');
		
		if($this -> form_validation -> run() == false)
		{
			$this -> load -> view('form/test_form');
		}
		else
		{
			$this -> load -> view('form/form_success');
		}
	}
	
	function isDate($date)
	{
		$tempDate = explode("." , $date);
		if(count($tempDate) < 3)
		{
			$this -> form_validation ->set_message('isDate' , 'You must input a right date!');
			return false;
		}
		else if(checkdate($tempDate[1] , $tempDate[2] , $tempDate[0]))
		{
			return true;
		}
		else
		{
			$tempDate = explode("-" , $date);
			if(checkdate($tempDate[1] , $tempDate[2] , $tempDate[0]))
			{
				return true;
			}
			else
			{
				$this -> form_validation ->set_message('isDate' , 'You must input a right date!');
				return false;
			}
		}
	}
	
	function verifyIdCard($idNumber)
	{
		$this -> load -> model('user_model' , 'user');
		
		if($this -> user -> verifyIdCard($idNumber))
		{
			return true;
		}
		else
		{
			$this -> form_validation ->set_message('verifyIdCard' , 'That is not a IdCard number!');
			return false;
		}
	}
}
?>