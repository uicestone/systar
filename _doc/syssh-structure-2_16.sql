-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2013 年 02 月 16 日 16:17
-- 服务器版本: 5.5.27-log
-- PHP 版本: 5.3.15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `syssh`
--
CREATE DATABASE `syssh` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `syssh`;

-- --------------------------------------------------------

--
-- 表的结构 `account`
--

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '数额',
  `date` date NOT NULL COMMENT '到账日期',
  `case` int(11) DEFAULT NULL COMMENT '关联项目',
  `case_fee` int(11) DEFAULT NULL COMMENT '关联项目下预估收费',
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
  KEY `case` (`case`),
  KEY `case_fee` (`case_fee`),
  KEY `people` (`people`),
  KEY `company` (`company`),
  KEY `amount` (`amount`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='账目（主表）' AUTO_INCREMENT=600 ;

-- --------------------------------------------------------

--
-- 表的结构 `account_label`
--

CREATE TABLE IF NOT EXISTS `account_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` enum('item') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_label` (`account`,`label`),
  KEY `account` (`account`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `case`
--

CREATE TABLE IF NOT EXISTS `case` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `num` char(20) NOT NULL DEFAULT '',
  `name_extra` varchar(255) NOT NULL DEFAULT '',
  `first_contact` date DEFAULT NULL,
  `time_contract` date DEFAULT NULL,
  `time_end` date DEFAULT NULL,
  `quote` varchar(255) NOT NULL DEFAULT '' COMMENT '报价',
  `timing_fee` tinyint(1) NOT NULL DEFAULT '0',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `focus` text,
  `summary` text,
  `source` int(11) DEFAULT NULL,
  `is_reviewed` tinyint(1) NOT NULL DEFAULT '0',
  `type_lock` tinyint(1) NOT NULL DEFAULT '0',
  `client_lock` tinyint(1) NOT NULL DEFAULT '0',
  `staff_lock` tinyint(1) NOT NULL DEFAULT '0',
  `fee_lock` tinyint(1) NOT NULL DEFAULT '0',
  `apply_file` tinyint(1) NOT NULL DEFAULT '0' COMMENT '已申请归档',
  `is_query` tinyint(1) NOT NULL DEFAULT '0',
  `finance_review` tinyint(1) NOT NULL DEFAULT '0',
  `info_review` tinyint(1) NOT NULL DEFAULT '0',
  `manager_review` tinyint(1) NOT NULL DEFAULT '0',
  `filed` tinyint(1) NOT NULL DEFAULT '0',
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
  KEY `source` (`source`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `company` (`company`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目（主表）' AUTO_INCREMENT=1340 ;

-- --------------------------------------------------------

--
-- 表的结构 `case_fee`
--

CREATE TABLE IF NOT EXISTS `case_fee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL DEFAULT '0',
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `type` enum('固定','风险','办案费','计时预付','计时收费','咨询费') NOT NULL DEFAULT '固定',
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
  KEY `case` (`case`),
  KEY `fee` (`fee`),
  KEY `pay_date` (`pay_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目下收费' AUTO_INCREMENT=823 ;

-- --------------------------------------------------------

--
-- 表的结构 `case_fee_timing`
--

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

-- --------------------------------------------------------

--
-- 表的结构 `case_label`
--

CREATE TABLE IF NOT EXISTS `case_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `label_name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `case-label` (`case`,`label`),
  UNIQUE KEY `case-type` (`case`,`type`),
  KEY `case` (`case`),
  KEY `label` (`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5494 ;

-- --------------------------------------------------------

--
-- 表的结构 `case_num`
--

CREATE TABLE IF NOT EXISTS `case_num` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL DEFAULT '0',
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
  KEY `case` (`case`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目编号' AUTO_INCREMENT=1360 ;

--
-- 触发器 `case_num`
--
DROP TRIGGER IF EXISTS `trig_case_num_multiautoincrease`;
DELIMITER //
CREATE TRIGGER `trig_case_num_multiautoincrease` BEFORE INSERT ON `case_num`
 FOR EACH ROW SET `new`.`number` = IF(
	(SELECT COUNT(*) FROM case_num WHERE classification_code = new.classification_code AND type_code = new.type_code AND year_code = new.year_code) = 0, 
	1,
	(SELECT MAX(number)+1 FROM case_num WHERE classification_code = new.classification_code AND type_code = new.type_code AND year_code = new.year_code)
)
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `case_people`
--

CREATE TABLE IF NOT EXISTS `case_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL DEFAULT '0',
  `people` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `hourly_fee` decimal(10,2) DEFAULT NULL,
  `contribute` decimal(5,5) NOT NULL DEFAULT '0.00000',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `case` (`case`,`people`,`role`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `people` (`people`),
  KEY `role` (`role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目人员关系' AUTO_INCREMENT=30273 ;

-- --------------------------------------------------------

--
-- 表的结构 `client_source`
--

CREATE TABLE IF NOT EXISTS `client_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('律所网站','其他网络','线下媒体','律所营销活动','合作单位介绍','陌生上门','亲友介绍','老客户介绍','其他') NOT NULL,
  `detail` varchar(255) NOT NULL DEFAULT '',
  `people` int(11) DEFAULT NULL COMMENT '介绍人',
  PRIMARY KEY (`id`),
  KEY `people` (`people`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户来源种类' AUTO_INCREMENT=117 ;

-- --------------------------------------------------------

--
-- 表的结构 `company`
--

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

-- --------------------------------------------------------

--
-- 表的结构 `controller`
--

CREATE TABLE IF NOT EXISTS `controller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `add_action` varchar(255) DEFAULT NULL COMMENT '添加动作url',
  `is_on` tinyint(1) NOT NULL DEFAULT '0',
  `display_in_nav` tinyint(1) NOT NULL DEFAULT '0',
  `ui_name` varchar(255) DEFAULT NULL COMMENT '默认显示名称（group中若有，此值会被覆盖）',
  `discription` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='控制器（系统表）' AUTO_INCREMENT=212 ;

-- --------------------------------------------------------

--
-- 表的结构 `course`
--

CREATE TABLE IF NOT EXISTS `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `chart_color` char(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='课程' AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- 表的结构 `document`
--

CREATE TABLE IF NOT EXISTS `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL DEFAULT '0',
  `people` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
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
  KEY `case` (`case`),
  KEY `client` (`people`),
  KEY `company` (`company`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目下文件' AUTO_INCREMENT=1744 ;

-- --------------------------------------------------------

--
-- 表的结构 `document_label`
--

CREATE TABLE IF NOT EXISTS `document_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `label_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_label` (`document`,`label`),
  KEY `document` (`document`),
  KEY `label` (`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2780 ;

-- --------------------------------------------------------

--
-- 表的结构 `evaluation_indicator`
--

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

-- --------------------------------------------------------

--
-- 表的结构 `evaluation_score`
--

CREATE TABLE IF NOT EXISTS `evaluation_score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff` int(11) DEFAULT NULL,
  `indicator` int(11) DEFAULT NULL,
  `score` decimal(4,1) NOT NULL DEFAULT '0.0',
  `comment` text,
  `quarter` tinyint(3) unsigned NOT NULL,
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `staff` (`staff`),
  KEY `indicator` (`indicator`),
  KEY `score` (`score`),
  KEY `quarter` (`quarter`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考核分数' AUTO_INCREMENT=3492 ;

-- --------------------------------------------------------

--
-- 表的结构 `exam`
--

CREATE TABLE IF NOT EXISTS `exam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `grade` int(11) NOT NULL,
  `depart` enum('本部','新疆') NOT NULL DEFAULT '本部',
  `is_on` tinyint(1) NOT NULL DEFAULT '0',
  `seat_allocated` tinyint(1) NOT NULL DEFAULT '0',
  `term` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `grade` (`grade`),
  KEY `term` (`term`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考试 一个年级的一次（主表）' AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- 表的结构 `exam_paper`
--

CREATE TABLE IF NOT EXISTS `exam_paper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_scoring` tinyint(4) NOT NULL DEFAULT '0',
  `teacher_group` int(11) DEFAULT NULL COMMENT '授权备课组',
  `exam` int(11) NOT NULL,
  `course` int(11) NOT NULL,
  `is_extra_course` tinyint(1) NOT NULL DEFAULT '0' COMMENT '属于同年级分科考试',
  `students` int(11) NOT NULL DEFAULT '0',
  `term` char(4) NOT NULL DEFAULT '',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `exam` (`exam`),
  KEY `teacher_group` (`teacher_group`),
  KEY `course` (`course`),
  KEY `term` (`term`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='试卷 一学科一张（主表）' AUTO_INCREMENT=211 ;

-- --------------------------------------------------------

--
-- 表的结构 `exam_part`
--

CREATE TABLE IF NOT EXISTS `exam_part` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_paper` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `discription` text,
  `full_score` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `exam_paper` (`exam_paper`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='大题（主表）' AUTO_INCREMENT=294 ;

-- --------------------------------------------------------

--
-- 表的结构 `exam_room`
--

CREATE TABLE IF NOT EXISTS `exam_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `capacity` int(11) NOT NULL DEFAULT '0',
  `depart` enum('本部','新疆') NOT NULL DEFAULT '本部',
  `grade` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grade` (`grade`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考场' AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- 表的结构 `exam_student`
--

CREATE TABLE IF NOT EXISTS `exam_student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam` int(11) NOT NULL,
  `student` int(11) NOT NULL,
  `room` varchar(255) DEFAULT NULL,
  `seat` int(11) DEFAULT NULL,
  `depart` enum('本部','新疆') NOT NULL DEFAULT '本部',
  `extra_course` int(11) DEFAULT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `rand` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `exam` (`exam`),
  KEY `student` (`student`),
  KEY `extra_course` (`extra_course`),
  KEY `time` (`time`),
  KEY `room` (`room`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='参加考试的学生' AUTO_INCREMENT=18481 ;

-- --------------------------------------------------------

--
-- 表的结构 `express`
--

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

-- --------------------------------------------------------

--
-- 表的结构 `ftp`
--

CREATE TABLE IF NOT EXISTS `ftp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `parent` int(11) DEFAULT NULL,
  `path` text,
  `type` char(15) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `parent` (`parent`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `ftp_fav`
--

CREATE TABLE IF NOT EXISTS `ftp_fav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` int(11) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `file` (`file`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件收藏' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `holidays`
--

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `is_overtime` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为假日（覆盖正常工作日），1为加班（覆盖集体假期）',
  `staff` int(11) DEFAULT NULL COMMENT 'NULL为全体行为，否则为单名员工行为',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- 表的结构 `idcard_region`
--

CREATE TABLE IF NOT EXISTS `idcard_region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(6) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='身份证地域区段（资源表）' AUTO_INCREMENT=3466 ;

-- --------------------------------------------------------

--
-- 表的结构 `label`
--

CREATE TABLE IF NOT EXISTS `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=106 ;

-- --------------------------------------------------------

--
-- 表的结构 `label_relationship`
--

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

-- --------------------------------------------------------

--
-- 表的结构 `log`
--

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

-- --------------------------------------------------------

--
-- 表的结构 `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `display` tinyint(1) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公告（主表）' AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- 表的结构 `people`
--

CREATE TABLE IF NOT EXISTS `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `character` enum('自然人','单位') NOT NULL DEFAULT '自然人',
  `name` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `abbreviation` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `gender` enum('男','女') DEFAULT NULL,
  `id_card` char(18) DEFAULT NULL,
  `work_for` varchar(255) NOT NULL DEFAULT '',
  `position` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `source` int(11) DEFAULT NULL,
  `staff` int(11) DEFAULT NULL COMMENT '人员直接相关职员',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `city` varchar(255) NOT NULL DEFAULT '',
  `race` char(20) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `abbreviation` (`abbreviation`),
  KEY `source` (`source`),
  KEY `staff` (`staff`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户（主表）' AUTO_INCREMENT=11515 ;

-- --------------------------------------------------------

--
-- 表的结构 `people_label`
--

CREATE TABLE IF NOT EXISTS `people_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `label_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `people-label` (`people`,`label`),
  UNIQUE KEY `people` (`people`,`type`),
  KEY `label` (`label`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11470 ;

-- --------------------------------------------------------

--
-- 表的结构 `people_profile`
--

CREATE TABLE IF NOT EXISTS `people_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '手机',
  `content` varchar(255) NOT NULL DEFAULT '',
  `comment` text,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `name` (`name`),
  KEY `people-name` (`people`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户联系方式' AUTO_INCREMENT=22855 ;

-- --------------------------------------------------------

--
-- 表的结构 `people_relationship`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='人员关系' AUTO_INCREMENT=453 ;

-- --------------------------------------------------------

--
-- 表的结构 `permission`
--

CREATE TABLE IF NOT EXISTS `permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL DEFAULT '',
  `controller` varchar(255) NOT NULL DEFAULT '',
  `method` varchar(255) NOT NULL DEFAULT '',
  `display_in_nav` tinyint(1) NOT NULL DEFAULT '0',
  `ui_name` varchar(255) NOT NULL DEFAULT '',
  `discription` varchar(255) DEFAULT NULL,
  `order` smallint(6) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `order` (`order`),
  KEY `affair` (`controller`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户组和权限（系统表）' AUTO_INCREMENT=112 ;

-- --------------------------------------------------------

--
-- 表的结构 `position`
--

CREATE TABLE IF NOT EXISTS `position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `ui_name` varchar(255) NOT NULL DEFAULT '',
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='员工职位' AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- 表的结构 `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `experience` text,
  `content` text,
  `place` varchar(255) NOT NULL DEFAULT '',
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fee_name` varchar(255) NOT NULL DEFAULT '',
  `time_start` int(11) NOT NULL DEFAULT '0',
  `time_end` int(11) NOT NULL DEFAULT '0',
  `hours_own` decimal(10,2) NOT NULL DEFAULT '0.00',
  `hours_checked` decimal(10,2) DEFAULT NULL,
  `hours_bill` decimal(10,2) DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `case` int(11) DEFAULT NULL,
  `people` int(11) DEFAULT NULL,
  `document` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
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
  KEY `case` (`case`),
  KEY `people` (`people`),
  KEY `document` (`document`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='日程（主表）' AUTO_INCREMENT=17675 ;

-- --------------------------------------------------------

--
-- 表的结构 `schedule_label`
--

CREATE TABLE IF NOT EXISTS `schedule_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule-label` (`schedule`,`label`),
  KEY `schedule` (`schedule`),
  KEY `label` (`label`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `schedule_people`
--

CREATE TABLE IF NOT EXISTS `schedule_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `people` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `people` (`people`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `schedule_profile`
--

CREATE TABLE IF NOT EXISTS `schedule_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `name` (`name`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `schedule_taskboard`
--

CREATE TABLE IF NOT EXISTS `schedule_taskboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `sort_data` text CHARACTER SET utf8,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `score`
--

CREATE TABLE IF NOT EXISTS `score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL,
  `exam` int(11) NOT NULL,
  `exam_paper` int(11) NOT NULL,
  `exam_part` int(11) NOT NULL,
  `score` decimal(10,1) DEFAULT NULL,
  `is_absent` tinyint(1) NOT NULL DEFAULT '0',
  `rank` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `time` int(10) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `student` (`student`),
  KEY `exam` (`exam`),
  KEY `exam_paper` (`exam_paper`),
  KEY `exam_part` (`exam_part`),
  KEY `scorer` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='阅卷分数' AUTO_INCREMENT=91272 ;

-- --------------------------------------------------------

--
-- 表的结构 `staff`
--

CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL COMMENT '职称',
  `modulus` decimal(3,2) NOT NULL DEFAULT '0.00' COMMENT '团奖系数',
  `course` int(11) DEFAULT NULL,
  `timing_fee_default` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `course` (`course`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='员工（主表）';

-- --------------------------------------------------------

--
-- 表的结构 `student_behaviour`
--

CREATE TABLE IF NOT EXISTS `student_behaviour` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生奖惩' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `student_comment`
--

CREATE TABLE IF NOT EXISTS `student_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `student` int(11) NOT NULL,
  `reply_to` int(11) DEFAULT NULL,
  `company` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student` (`student`),
  KEY `reply_to` (`reply_to`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='学生评价' AUTO_INCREMENT=626 ;

-- --------------------------------------------------------

--
-- 表的结构 `team`
--

CREATE TABLE IF NOT EXISTS `team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `num` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `extra_course` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `time_insert` int(11) NOT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leader` (`leader`),
  KEY `display` (`display`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `num` (`num`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='团队（主表）' AUTO_INCREMENT=291 ;

-- --------------------------------------------------------

--
-- 表的结构 `team_label`
--

CREATE TABLE IF NOT EXISTS `team_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  `type` enum('depart','grade','extra_course') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team-label` (`team`,`label`),
  KEY `team` (`team`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `team_people`
--

CREATE TABLE IF NOT EXISTS `team_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(11) DEFAULT NULL,
  `people` int(11) NOT NULL,
  `relation` varchar(255) NOT NULL,
  `id_in_team` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `term` char(4) NOT NULL DEFAULT '',
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `team` (`team`),
  KEY `relation` (`relation`),
  KEY `num_in_class` (`id_in_team`),
  KEY `time` (`time`),
  KEY `term` (`term`),
  KEY `people` (`people`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='人与团队关系' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `team_relationship`
--

CREATE TABLE IF NOT EXISTS `team_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团队间关系' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

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

-- --------------------------------------------------------

--
-- 表的结构 `view_score`
--

CREATE TABLE IF NOT EXISTS `view_score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student` int(11) NOT NULL,
  `extra_course` int(11) DEFAULT NULL,
  `exam` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `course_1` decimal(10,1) DEFAULT NULL,
  `course_2` decimal(10,1) DEFAULT NULL,
  `course_3` decimal(10,1) DEFAULT NULL,
  `course_4` decimal(10,1) DEFAULT NULL,
  `course_5` decimal(10,1) DEFAULT NULL,
  `course_6` decimal(10,1) DEFAULT NULL,
  `course_7` decimal(10,1) DEFAULT NULL,
  `course_8` decimal(10,1) DEFAULT NULL,
  `course_9` decimal(10,1) DEFAULT NULL,
  `course_10` decimal(10,1) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分数汇总' AUTO_INCREMENT=1 ;

--
-- 限制导出的表
--

--
-- 限制表 `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `account_ibfk_10` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_2` FOREIGN KEY (`case_fee`) REFERENCES `case_fee` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_4` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_6` FOREIGN KEY (`case_fee`) REFERENCES `case_fee` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_7` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_8` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_9` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `account_label`
--
ALTER TABLE `account_label`
  ADD CONSTRAINT `account_label_ibfk_1` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `case`
--
ALTER TABLE `case`
  ADD CONSTRAINT `case_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `case_fee`
--
ALTER TABLE `case_fee`
  ADD CONSTRAINT `case_fee_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_fee_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_fee_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `case_fee_timing`
--
ALTER TABLE `case_fee_timing`
  ADD CONSTRAINT `case_fee_timing_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_fee_timing_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_fee_timing_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `case_label`
--
ALTER TABLE `case_label`
  ADD CONSTRAINT `case_label_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `case_num`
--
ALTER TABLE `case_num`
  ADD CONSTRAINT `case_num_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_num_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_num_ibfk_4` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `case_people`
--
ALTER TABLE `case_people`
  ADD CONSTRAINT `case_people_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_people_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_people_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_people_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `client_source`
--
ALTER TABLE `client_source`
  ADD CONSTRAINT `client_source_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_4` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `document_label`
--
ALTER TABLE `document_label`
  ADD CONSTRAINT `document_label_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `evaluation_indicator`
--
ALTER TABLE `evaluation_indicator`
  ADD CONSTRAINT `evaluation_indicator_ibfk_1` FOREIGN KEY (`position`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_indicator_ibfk_2` FOREIGN KEY (`critic`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_indicator_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `evaluation_score`
--
ALTER TABLE `evaluation_score`
  ADD CONSTRAINT `evaluation_score_ibfk_1` FOREIGN KEY (`indicator`) REFERENCES `evaluation_indicator` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_score_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `exam_student`
--
ALTER TABLE `exam_student`
  ADD CONSTRAINT `exam_student_ibfk_1` FOREIGN KEY (`exam`) REFERENCES `exam` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `exam_student_ibfk_2` FOREIGN KEY (`student`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `exam_student_ibfk_3` FOREIGN KEY (`extra_course`) REFERENCES `course` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `express`
--
ALTER TABLE `express`
  ADD CONSTRAINT `express_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_2` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_4` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_5` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `label_relationship`
--
ALTER TABLE `label_relationship`
  ADD CONSTRAINT `label_relationship_ibfk_1` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `label_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `news_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `people`
--
ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_1` FOREIGN KEY (`source`) REFERENCES `client_source` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_4` FOREIGN KEY (`staff`) REFERENCES `staff` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `people_label`
--
ALTER TABLE `people_label`
  ADD CONSTRAINT `people_label_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `people_profile`
--
ALTER TABLE `people_profile`
  ADD CONSTRAINT `people_profile_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_profile_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `people_relationship`
--
ALTER TABLE `people_relationship`
  ADD CONSTRAINT `people_relationship_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_relationship_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_relationship_ibfk_4` FOREIGN KEY (`relative`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `schedule_label`
--
ALTER TABLE `schedule_label`
  ADD CONSTRAINT `schedule_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_label_ibfk_4` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `schedule_people`
--
ALTER TABLE `schedule_people`
  ADD CONSTRAINT `schedule_people_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_people_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `schedule_profile`
--
ALTER TABLE `schedule_profile`
  ADD CONSTRAINT `schedule_profile_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_profile_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `schedule_taskboard`
--
ALTER TABLE `schedule_taskboard`
  ADD CONSTRAINT `schedule_taskboard_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `score`
--
ALTER TABLE `score`
  ADD CONSTRAINT `score_ibfk_1` FOREIGN KEY (`exam`) REFERENCES `exam` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `score_ibfk_2` FOREIGN KEY (`exam_paper`) REFERENCES `exam_paper` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `score_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `score_ibfk_5` FOREIGN KEY (`exam_part`) REFERENCES `exam_part` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`course`) REFERENCES `course` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `student_behaviour`
--
ALTER TABLE `student_behaviour`
  ADD CONSTRAINT `student_behaviour_ibfk_1` FOREIGN KEY (`student`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `student_behaviour_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `student_behaviour_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `student_comment`
--
ALTER TABLE `student_comment`
  ADD CONSTRAINT `student_comment_ibfk_1` FOREIGN KEY (`student`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `student_comment_ibfk_2` FOREIGN KEY (`reply_to`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `student_comment_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `student_comment_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `team`
--
ALTER TABLE `team`
  ADD CONSTRAINT `team_ibfk_1` FOREIGN KEY (`leader`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `team_label`
--
ALTER TABLE `team_label`
  ADD CONSTRAINT `team_label_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `team_people`
--
ALTER TABLE `team_people`
  ADD CONSTRAINT `team_people_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_people_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `team_relationship`
--
ALTER TABLE `team_relationship`
  ADD CONSTRAINT `team_relationship_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
