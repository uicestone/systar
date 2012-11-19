UPDATE  `starsys`.`affair` SET  `name` =  'cases' WHERE  `affair`.`id` =75;
UPDATE  `starsys`.`affair` SET  `add_action` =  'cases?add' WHERE  `affair`.`id` =75;
UPDATE  `group` SET affair =  'cases' WHERE affair =  'case';
update `group` set action='lists' where action='list';
update affair set add_action = replace(add_action,'?add','/add');

update `group` set action = 'stafflist' where action='staff_list';

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `is_overtime` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为假日（覆盖正常工作日），1为加班（覆盖集体假期）',
  `staff` int(11) DEFAULT NULL COMMENT 'NULL为全体行为，否则为单名员工行为',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

INSERT INTO `holidays` (`id`, `date`, `is_overtime`, `staff`) VALUES
(1, '2012-09-30', 0, NULL),
(2, '2012-10-01', 0, NULL),
(3, '2012-10-02', 0, NULL),
(4, '2012-10-03', 0, NULL),
(5, '2012-10-04', 0, NULL),
(6, '2012-10-05', 0, NULL),
(7, '2012-10-06', 0, NULL),
(8, '2012-10-07', 0, NULL),
(9, '2012-09-29', 1, NULL);

-- uice 2012/11/12 废弃“资金－案下资金”权限
delete from `group` where affair='account' and action='case';
-- end


-- uice 2012/11/15
update affair set name = 'classes' where name = 'class';
update `group` set affair = 'classes' where affair = 'class';
update affair set is_on = 0 where name = 'teach';
update affair set name = 'viewscore' where name ='view_score';
update `group` set affair = 'viewscore' where affair ='view_score';
-- end

-- uice 2012/11/15
delete from `group` where action='classdiv';
-- end

-- uice 2012/11/17
CREATE TABLE IF NOT EXISTS `schedule_taskboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `sort_data` text,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `schedule_taskboard`
  ADD CONSTRAINT `schedule_taskboard_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

INSERT INTO  `starsys`.`group` (
`id` ,
`name` ,
`affair` ,
`action` ,
`display_in_nav` ,
`affair_ui_name` ,
`order` ,
`company`
)
VALUES (
NULL ,  'developer',  'schedule',  'taskboard',  '1',  '任务',  '0',  '1'
);
-- end

-- uice 2012/11/19
-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2012 年 11 月 19 日 20:18
-- 服务器版本: 5.5.27-log
-- PHP 版本: 5.3.15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `starsys`
--

-- --------------------------------------------------------

--
-- 表的结构 `account_label`
--

CREATE TABLE IF NOT EXISTS `account_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account` (`account`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `case_label`
--

CREATE TABLE IF NOT EXISTS `case_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case` (`case`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `class_label`
--

CREATE TABLE IF NOT EXISTS `class_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `class` (`class`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `client_label`
--

CREATE TABLE IF NOT EXISTS `client_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `document_label`
--

CREATE TABLE IF NOT EXISTS `document_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `document` (`document`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `label`
--

CREATE TABLE IF NOT EXISTS `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `property_label`
--

CREATE TABLE IF NOT EXISTS `property_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `property` (`property`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `schedule_label`
--

CREATE TABLE IF NOT EXISTS `schedule_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `staff_label`
--

CREATE TABLE IF NOT EXISTS `staff_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff` int(11) NOT NULL,
  `label` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `staff` (`staff`),
  KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 限制导出的表
--

--
-- 限制表 `account_label`
--
ALTER TABLE `account_label`
  ADD CONSTRAINT `account_label_ibfk_1` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `case_label`
--
ALTER TABLE `case_label`
  ADD CONSTRAINT `case_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_label_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `class_label`
--
ALTER TABLE `class_label`
  ADD CONSTRAINT `class_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `class_label_ibfk_1` FOREIGN KEY (`class`) REFERENCES `class` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `client_label`
--
ALTER TABLE `client_label`
  ADD CONSTRAINT `client_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `client_label_ibfk_1` FOREIGN KEY (`client`) REFERENCES `client` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `document_label`
--
ALTER TABLE `document_label`
  ADD CONSTRAINT `document_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_label_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `property_label`
--
ALTER TABLE `property_label`
  ADD CONSTRAINT `property_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `property_label_ibfk_1` FOREIGN KEY (`property`) REFERENCES `property` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `schedule_label`
--
ALTER TABLE `schedule_label`
  ADD CONSTRAINT `schedule_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_label_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- 限制表 `staff_label`
--
ALTER TABLE `staff_label`
  ADD CONSTRAINT `staff_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_label_ibfk_1` FOREIGN KEY (`staff`) REFERENCES `staff` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
--end
-- PangPang 2012-11-19 upgrade
