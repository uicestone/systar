<?php
class Starsys_model extends SS_Model{
	function __construct() {
		parent::__construct();
		
		$this->config->set_item('client_source_types', array('律所网站','其他网络','线下媒体','律所营销活动','合作单位介绍','陌生上门','亲友介绍','老客户介绍','其他'));
	}
}
?>
