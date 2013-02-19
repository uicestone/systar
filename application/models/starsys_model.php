<?php
class Starsys_model extends SS_Model{
	function __construct() {
		parent::__construct();
		
		$this->config->set_item('客户来源类型', array('律所网站','其他网络','线下媒体','律所营销活动','合作单位介绍','陌生上门','亲友介绍','老客户介绍','其他'));
		$this->config->set_item('咨询方式', array('面谈','电话','网络'));
		$this->config->set_item('案件领域', array('公司','房产建筑','诉讼','婚姻家庭','刑事行政','知识产权','劳动人事','涉外','韩日'));
	}
}
?>
