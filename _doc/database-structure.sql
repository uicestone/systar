SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE DATABASE `syssh` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `syssh`;

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '数额',
  `date` date NOT NULL COMMENT '到账日期',
  `project` int(11) DEFAULT NULL,
  `project_account` int(11) DEFAULT NULL COMMENT '关联项目下预估收费',
  `people` int(11) DEFAULT NULL COMMENT '关联客户',
  `comment` text COMMENT '备注',
  `distributed_fixed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '固定贡献业务奖已发',
  `distributed_actual` tinyint(1) NOT NULL DEFAULT '0' COMMENT '实际贡献业务奖已发',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `case` (`project`),
  KEY `case_fee` (`project_account`),
  KEY `people` (`people`),
  KEY `company` (`company`),
  KEY `amount` (`amount`),
  KEY `time_insert` (`time_insert`),
  KEY `subject` (`subject`),
  KEY `summary` (`summary`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='账目（主表）' AUTO_INCREMENT=671 ;

CREATE TABLE IF NOT EXISTS `account_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` enum('item') DEFAULT NULL,
  `label_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_label` (`account`,`label`),
  UNIQUE KEY `account-type` (`account`,`type`),
  KEY `label` (`label`),
  KEY `label_name` (`label_name`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `account_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `team` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account` (`account`),
  KEY `team` (`team`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `case_fee_timing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL DEFAULT '0',
  `included_hours` int(11) NOT NULL DEFAULT '0',
  `contract_cycle` int(11) NOT NULL DEFAULT '12' COMMENT '合同周期（月）',
  `payment_cycle` int(11) NOT NULL DEFAULT '1' COMMENT '付款周期（月）',
  `bill_day` int(11) NOT NULL DEFAULT '10',
  `payment_day` int(11) NOT NULL DEFAULT '20',
  `date_start` date NOT NULL,
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `case` (`case`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目计时收费规则' AUTO_INCREMENT=26 ;

CREATE TABLE IF NOT EXISTS `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `syscode` varchar(255) NOT NULL,
  `sysname` varchar(255) NOT NULL,
  `ucenter` tinyint(1) NOT NULL,
  `default_controller` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公司列表（系统表）' AUTO_INCREMENT=4 ;

CREATE TABLE IF NOT EXISTS `company_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `chart_color` char(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='课程' AUTO_INCREMENT=16 ;

CREATE TABLE IF NOT EXISTS `dialog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_message` int(11) DEFAULT NULL,
  `company` int(11) NOT NULL,
  `users` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_message` (`last_message`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `company` (`company`),
  KEY `users` (`users`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

CREATE TABLE IF NOT EXISTS `dialog_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `message` (`message`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

CREATE TABLE IF NOT EXISTS `dialog_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `team` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `team` (`team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `dialog_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `user` (`user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

CREATE TABLE IF NOT EXISTS `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `extname` char(8) NOT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `company` (`company`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目下文件' AUTO_INCREMENT=1918 ;

CREATE TABLE IF NOT EXISTS `document_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `label_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document-label` (`document`,`label`),
  UNIQUE KEY `document-type` (`document`,`type`),
  KEY `label` (`label`),
  KEY `type` (`type`),
  KEY `label_name` (`label_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4292 ;

CREATE TABLE IF NOT EXISTS `evaluation_indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `weight` decimal(5,1) NOT NULL DEFAULT '0.0',
  `position` int(11) DEFAULT NULL COMMENT '被评价人职位',
  `critic` int(11) DEFAULT NULL COMMENT '评价人职位',
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `critic` (`critic`),
  KEY `position` (`position`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考核指标' AUTO_INCREMENT=347 ;

CREATE TABLE IF NOT EXISTS `express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) DEFAULT NULL,
  `num` varchar(255) NOT NULL DEFAULT '',
  `destination` varchar(255) NOT NULL DEFAULT '',
  `time_send` datetime NOT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `content` varchar(255) NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT '1',
  `comment` text,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `company` (`company`),
  KEY `time` (`time`),
  KEY `sender` (`sender`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='快递（主表）' AUTO_INCREMENT=868 ;

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `is_overtime` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为假日（覆盖正常工作日），1为加班（覆盖集体假期）',
  `staff` int(11) DEFAULT NULL COMMENT 'NULL为全体行为，否则为单名员工行为',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

CREATE TABLE IF NOT EXISTS `idcard_region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(6) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='身份证地域区段（资源表）' AUTO_INCREMENT=3466 ;

CREATE TABLE IF NOT EXISTS `indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `weight` decimal(5,1) NOT NULL DEFAULT '0.0',
  `candidates` int(11) DEFAULT NULL,
  `judges` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL COMMENT '被评价人职位',
  `critic` int(11) DEFAULT NULL COMMENT '评价人职位',
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `critic` (`critic`),
  KEY `position` (`position`),
  KEY `candidates` (`candidates`),
  KEY `judges` (`judges`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考核指标' AUTO_INCREMENT=640 ;

CREATE TABLE IF NOT EXISTS `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '标签组合在一起时的顺序',
  `color` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=160 ;

CREATE TABLE IF NOT EXISTS `label_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `get` text NOT NULL,
  `post` text NOT NULL,
  `client` varchar(255) NOT NULL,
  `duration` float NOT NULL,
  `ip` char(15) DEFAULT NULL,
  `company` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统请求日志（记录表）' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公告（主表）' AUTO_INCREMENT=67 ;

CREATE TABLE IF NOT EXISTS `message_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `message` (`message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `href` varchar(255) NOT NULL,
  `add_href` varchar(255) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `order` int(11) NOT NULL,
  `team` int(11) DEFAULT NULL COMMENT 'href相同的条目，team.id大的将覆盖小的和NULL',
  `company` int(11) DEFAULT NULL COMMENT 'href相同的条目，具体值将覆盖NULL',
  `company_type` varchar(255) DEFAULT NULL COMMENT 'href相同的条目，具体值将覆盖NULL',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `team` (`team`),
  KEY `company` (`company`),
  KEY `href` (`href`),
  KEY `company_type` (`company_type`),
  KEY `order` (`order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=36 ;

CREATE TABLE IF NOT EXISTS `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `character` enum('单位','个人') NOT NULL DEFAULT '个人',
  `name` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `abbreviation` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `gender` enum('男','女') DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `id_card` char(18) DEFAULT NULL,
  `work_for` varchar(255) NOT NULL DEFAULT '',
  `position` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `staff` int(11) DEFAULT NULL COMMENT '人员直接相关职员',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `city` varchar(255) NOT NULL DEFAULT '',
  `race` char(20) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `abbreviation` (`abbreviation`),
  KEY `staff` (`staff`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户（主表）' AUTO_INCREMENT=12510 ;

CREATE TABLE IF NOT EXISTS `people_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `label_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `people-label` (`people`,`label`),
  UNIQUE KEY `people-type` (`people`,`type`),
  KEY `label` (`label`),
  KEY `type` (`type`),
  KEY `label_name` (`label_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17468 ;

CREATE TABLE IF NOT EXISTS `people_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '手机',
  `content` mediumtext NOT NULL,
  `comment` text,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `name` (`name`),
  KEY `people-name` (`people`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户联系方式' AUTO_INCREMENT=44141 ;

CREATE TABLE IF NOT EXISTS `people_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) DEFAULT NULL,
  `relative` int(11) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `relation_type` varchar(255) DEFAULT NULL,
  `is_default_contact` tinyint(1) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `people` (`people`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`),
  KEY `relation_type` (`relation_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='人员关系' AUTO_INCREMENT=8104 ;

CREATE TABLE IF NOT EXISTS `people_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` enum('班级','年级','校级','区级','市级','全国') NOT NULL,
  `type` enum('奖','惩') NOT NULL,
  `date` date NOT NULL,
  `content` text,
  `company` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student` (`student`),
  KEY `date` (`date`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `ui_name` varchar(255) NOT NULL DEFAULT '',
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='员工职位' AUTO_INCREMENT=10 ;

CREATE TABLE IF NOT EXISTS `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `team` int(11) DEFAULT NULL,
  `num` char(20) DEFAULT NULL,
  `first_contact` date DEFAULT NULL,
  `time_contract` date DEFAULT NULL,
  `time_end` date DEFAULT NULL,
  `quote` varchar(255) NOT NULL DEFAULT '' COMMENT '报价',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `focus` text,
  `summary` text,
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `num` (`num`),
  KEY `first_contact` (`first_contact`),
  KEY `time_contract` (`time_contract`),
  KEY `time_end` (`time_end`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `company` (`company`),
  KEY `time_insert` (`time_insert`),
  KEY `team` (`team`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目（主表）' AUTO_INCREMENT=2087 ;

CREATE TABLE IF NOT EXISTS `project_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `type` varchar(255) NOT NULL DEFAULT '固定',
  `receiver` enum('承办律师','律所') DEFAULT NULL,
  `condition` text,
  `pay_date` date NOT NULL,
  `reviewed` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text,
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `case` (`project`),
  KEY `fee` (`fee`),
  KEY `pay_date` (`pay_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目下收费' AUTO_INCREMENT=926 ;

CREATE TABLE IF NOT EXISTS `project_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `document` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case` (`project`),
  KEY `document` (`document`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2199 ;

CREATE TABLE IF NOT EXISTS `project_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `label` int(11) NOT NULL,
  `label_name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project-label` (`project`,`label`),
  UNIQUE KEY `project-type` (`project`,`type`),
  KEY `label` (`label`),
  KEY `label_name` (`label_name`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14878 ;

CREATE TABLE IF NOT EXISTS `project_num` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `classification_code` char(3) NOT NULL DEFAULT '',
  `type_code` char(3) NOT NULL DEFAULT '',
  `year_code` char(4) NOT NULL DEFAULT '',
  `number` int(11) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `case` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目编号' AUTO_INCREMENT=1511 ;
DROP TRIGGER IF EXISTS `trig_project_num_multiautoincrease`;
DELIMITER //
CREATE TRIGGER `trig_project_num_multiautoincrease` BEFORE INSERT ON `project_num`
 FOR EACH ROW SET `new`.`number` = IF(
	(SELECT COUNT(*) FROM project_num WHERE classification_code = new.classification_code AND type_code = new.type_code AND year_code = new.year_code) = 0, 
	1,
	(SELECT MAX(number)+1 FROM project_num WHERE classification_code = new.classification_code AND type_code = new.type_code AND year_code = new.year_code)
)
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `project_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `people` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `weight` decimal(5,5) DEFAULT NULL,
  `hourly_fee` decimal(10,2) DEFAULT NULL,
  `contribute` decimal(5,5) NOT NULL DEFAULT '0.00000',
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `case` (`project`,`people`,`role`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `people` (`people`),
  KEY `role` (`role`),
  KEY `weight` (`weight`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目人员关系' AUTO_INCREMENT=46365 ;

CREATE TABLE IF NOT EXISTS `project_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `comment` text NOT NULL,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `uid` (`uid`),
  KEY `name` (`name`),
  KEY `project` (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `project_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `project_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `date` (`date`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `project` (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `experience` text,
  `content` text,
  `place` varchar(255) NOT NULL DEFAULT '',
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fee_name` varchar(255) NOT NULL DEFAULT '',
  `time_start` int(11) DEFAULT NULL,
  `time_end` int(11) DEFAULT NULL,
  `hours_own` decimal(10,2) NOT NULL DEFAULT '0.00',
  `hours_checked` decimal(10,2) DEFAULT NULL,
  `hours_bill` decimal(10,2) DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `project` int(11) DEFAULT NULL,
  `people` int(11) DEFAULT NULL,
  `document` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `time_insert` int(11) NOT NULL,
  `time` int(11) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `time_start` (`time_start`),
  KEY `time_end` (`time_end`),
  KEY `hours_own` (`hours_own`),
  KEY `hours_checked` (`hours_checked`),
  KEY `hours_bill` (`hours_bill`),
  KEY `case` (`project`),
  KEY `people` (`people`),
  KEY `document` (`document`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='日程（主表）' AUTO_INCREMENT=19137 ;

CREATE TABLE IF NOT EXISTS `schedule_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `label_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule-label` (`schedule`,`label`),
  UNIQUE KEY `schedule-type` (`schedule`,`type`),
  KEY `label` (`label`),
  KEY `type` (`type`),
  KEY `label_name` (`label_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `schedule_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `people` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule-people` (`schedule`,`people`),
  KEY `schedule` (`schedule`),
  KEY `people` (`people`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

CREATE TABLE IF NOT EXISTS `schedule_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `comment` text,
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `name` (`name`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `schedule_taskboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `sort_data` text,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;

CREATE TABLE IF NOT EXISTS `school_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `capacity` int(11) NOT NULL DEFAULT '0',
  `team` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考场' AUTO_INCREMENT=34 ;

CREATE TABLE IF NOT EXISTS `school_view_score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL,
  `extra_course` int(11) DEFAULT NULL,
  `exam` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `course_1` decimal(5,1) DEFAULT NULL,
  `course_2` decimal(5,1) DEFAULT NULL,
  `course_3` decimal(5,1) DEFAULT NULL,
  `course_4` decimal(5,1) DEFAULT NULL,
  `course_5` decimal(5,1) DEFAULT NULL,
  `course_6` decimal(5,1) DEFAULT NULL,
  `course_7` decimal(5,1) DEFAULT NULL,
  `course_8` decimal(5,1) DEFAULT NULL,
  `course_9` decimal(5,1) DEFAULT NULL,
  `course_10` decimal(5,1) DEFAULT NULL,
  `course_sum_3` int(11) DEFAULT NULL,
  `course_sum_5` int(11) DEFAULT NULL,
  `course_sum_8` int(11) DEFAULT NULL,
  `rank_1` int(11) DEFAULT NULL,
  `rank_2` int(11) DEFAULT NULL,
  `rank_3` int(11) DEFAULT NULL,
  `rank_4` int(11) DEFAULT NULL,
  `rank_5` int(11) DEFAULT NULL,
  `rank_6` int(11) DEFAULT NULL,
  `rank_7` int(11) DEFAULT NULL,
  `rank_8` int(11) DEFAULT NULL,
  `rank_9` int(11) DEFAULT NULL,
  `rank_10` int(11) DEFAULT NULL,
  `rank_sum_3` int(11) DEFAULT NULL,
  `rank_sum_5` int(11) DEFAULT NULL,
  `rank_sum_8` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `student-exam` (`student`,`exam`),
  KEY `student` (`student`),
  KEY `exam` (`exam`),
  KEY `time` (`time`),
  KEY `extra_course` (`extra_course`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='分数汇总' AUTO_INCREMENT=12007 ;

CREATE TABLE IF NOT EXISTS `score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) NOT NULL,
  `project` int(11) DEFAULT NULL,
  `indicator` int(11) DEFAULT NULL,
  `score` decimal(4,1) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `answer` varchar(255) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `student` (`people`),
  KEY `scorer` (`uid`),
  KEY `time` (`time`),
  KEY `indicator` (`indicator`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='阅卷分数' AUTO_INCREMENT=95017 ;

CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` mediumtext,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL DEFAULT '0',
  `position` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT '职称',
  `modulus` decimal(3,2) NOT NULL DEFAULT '0.00' COMMENT '团奖系数',
  `course` int(11) DEFAULT NULL,
  `timing_fee_default` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `course` (`course`),
  KEY `id` (`id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='员工（主表）';

CREATE TABLE IF NOT EXISTS `team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `num` varchar(255) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `extra_course` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `time_insert` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leader` (`leader`),
  KEY `display` (`display`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `num` (`num`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='团队（主表）' AUTO_INCREMENT=454 ;

CREATE TABLE IF NOT EXISTS `team_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `label_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team-label` (`team`,`label`),
  UNIQUE KEY `team-type` (`team`,`type`),
  KEY `label` (`label`),
  KEY `label_name` (`label_name`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `team_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(11) DEFAULT NULL,
  `people` int(11) NOT NULL,
  `relation` varchar(255) NOT NULL,
  `id_in_team` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `till` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`),
  KEY `relation` (`relation`),
  KEY `num_in_class` (`id_in_team`),
  KEY `people` (`people`),
  KEY `till` (`till`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='人与团队关系' AUTO_INCREMENT=5783 ;

CREATE TABLE IF NOT EXISTS `team_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team-relative` (`team`,`relative`),
  KEY `team` (`team`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='团队间关系' AUTO_INCREMENT=160 ;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT '',
  `lastip` varchar(255) DEFAULT NULL,
  `lastlogin` int(11) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `company` (`company`),
  KEY `password` (`password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户';

CREATE TABLE IF NOT EXISTS `user_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `account`
  ADD CONSTRAINT `account_ibfk_10` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_11` FOREIGN KEY (`project_account`) REFERENCES `project_account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_4` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_7` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_8` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_9` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `account_label`
  ADD CONSTRAINT `account_label_ibfk_1` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `account_team`
  ADD CONSTRAINT `account_team_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_team_ibfk_1` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_team_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `case_fee_timing`
  ADD CONSTRAINT `case_fee_timing_ibfk_1` FOREIGN KEY (`case`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_fee_timing_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_fee_timing_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `company_config`
  ADD CONSTRAINT `company_config_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `dialog`
  ADD CONSTRAINT `dialog_ibfk_3` FOREIGN KEY (`last_message`) REFERENCES `message` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_ibfk_5` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `dialog_message`
  ADD CONSTRAINT `dialog_message_ibfk_2` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_message_ibfk_1` FOREIGN KEY (`dialog`) REFERENCES `dialog` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `dialog_team`
  ADD CONSTRAINT `dialog_team_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_team_ibfk_1` FOREIGN KEY (`dialog`) REFERENCES `dialog` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `dialog_user`
  ADD CONSTRAINT `dialog_user_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_user_ibfk_1` FOREIGN KEY (`dialog`) REFERENCES `dialog` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `document_label`
  ADD CONSTRAINT `document_label_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `evaluation_indicator`
  ADD CONSTRAINT `evaluation_indicator_ibfk_1` FOREIGN KEY (`position`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_indicator_ibfk_2` FOREIGN KEY (`critic`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_indicator_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `express`
  ADD CONSTRAINT `express_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_2` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_4` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_5` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `indicator`
  ADD CONSTRAINT `indicator_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `indicator_ibfk_4` FOREIGN KEY (`position`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `indicator_ibfk_5` FOREIGN KEY (`critic`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `indicator_ibfk_6` FOREIGN KEY (`candidates`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `label_relationship`
  ADD CONSTRAINT `label_relationship_ibfk_1` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `label_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `message_user`
  ADD CONSTRAINT `message_user_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `message_user_ibfk_1` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `nav`
  ADD CONSTRAINT `nav_ibfk_3` FOREIGN KEY (`parent`) REFERENCES `nav` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `nav_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `nav_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_4` FOREIGN KEY (`staff`) REFERENCES `staff` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `people_label`
  ADD CONSTRAINT `people_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_label_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `people_profile`
  ADD CONSTRAINT `people_profile_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_profile_ibfk_4` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `people_relationship`
  ADD CONSTRAINT `people_relationship_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_relationship_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_relationship_ibfk_4` FOREIGN KEY (`relative`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `people_status`
  ADD CONSTRAINT `people_status_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_4` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_account`
  ADD CONSTRAINT `project_account_ibfk_4` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_account_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_account_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_document`
  ADD CONSTRAINT `project_document_ibfk_4` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_label`
  ADD CONSTRAINT `project_label_ibfk_4` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_num`
  ADD CONSTRAINT `project_num_ibfk_5` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_num_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_num_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_people`
  ADD CONSTRAINT `project_people_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_people_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_people_ibfk_5` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_profile`
  ADD CONSTRAINT `project_profile_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_profile_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_relationship`
  ADD CONSTRAINT `project_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_relationship_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_status`
  ADD CONSTRAINT `project_status_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_status_ibfk_2` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_5` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `schedule_label`
  ADD CONSTRAINT `schedule_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_label_ibfk_4` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `schedule_people`
  ADD CONSTRAINT `schedule_people_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_people_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `schedule_profile`
  ADD CONSTRAINT `schedule_profile_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_profile_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `schedule_taskboard`
  ADD CONSTRAINT `schedule_taskboard_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `school_room`
  ADD CONSTRAINT `school_room_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`course`) REFERENCES `course` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_3` FOREIGN KEY (`position`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `team`
  ADD CONSTRAINT `team_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_ibfk_1` FOREIGN KEY (`leader`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `team_label`
  ADD CONSTRAINT `team_label_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `team_people`
  ADD CONSTRAINT `team_people_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_people_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `team_relationship`
  ADD CONSTRAINT `team_relationship_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `user_config`
  ADD CONSTRAINT `user_config_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
