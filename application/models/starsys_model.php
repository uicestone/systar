<?php
class Starsys_model extends SS_Model{
	function __construct() {
		parent::__construct();
		
		$this->config->set_item('客户来源类型', array('律所网站','其他网络','线下媒体','律所营销活动','合作单位介绍','陌生上门','亲友介绍','老客户介绍','其他'));
		$this->config->set_item('咨询方式', array('面谈','电话','网络'));
		$this->config->set_item('案件领域', array('公司','房产建筑','诉讼','婚姻家庭','刑事行政','知识产权','劳动人事','涉外','韩日'));
		$this->config->set_item('案件文档类型', array('接洽资料','身份资料','聘请委托文书','签约合同（扫描）','办案文书','裁判文书','行政文书','证据材料','其他'));
		$this->config->set_item('单位相关人关系', array('负责人','法务','财务','人事','行政','其他','其他代理人'));
		$this->config->set_item('个人相关人关系', array('父','母','配偶','亲属','朋友','代理人'));
	}
}
?>
