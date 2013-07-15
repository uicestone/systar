<?php
class Contact_model extends People_model{
	function __construct(){
		parent::__construct();
		$this->fields['type']='contact';
	}
}
?>