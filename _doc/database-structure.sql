SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE `syssh` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `syssh`;

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '数额',
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `reviewed` tinyint(1) NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL COMMENT '到账日期',
  `project` int(11) DEFAULT NULL,
  `account` int(11) DEFAULT NULL,
  `people` int(11) DEFAULT NULL COMMENT '关联客户',
  `comment` text COMMENT '备注',
  `distributed_fixed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '固定贡献业务奖已发',
  `distributed_actual` tinyint(1) NOT NULL DEFAULT '0' COMMENT '实际贡献业务奖已发',
  PRIMARY KEY (`id`),
  KEY `case` (`project`),
  KEY `case_fee` (`account`),
  KEY `people` (`people`),
  KEY `amount` (`amount`),
  KEY `subject` (`subject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `syscode` varchar(255) NOT NULL,
  `sysname` varchar(255) NOT NULL,
  `ucenter` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公司列表（系统表）' AUTO_INCREMENT=4 ;

CREATE TABLE IF NOT EXISTS `company_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=394 ;

CREATE TABLE IF NOT EXISTS `dialog_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `message` (`message`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=874 ;

CREATE TABLE IF NOT EXISTS `dialog_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `user` (`user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=915 ;

CREATE TABLE IF NOT EXISTS `document` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extname` char(8) NOT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目下文件';

CREATE TABLE IF NOT EXISTS `evaluation_indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `indicator` int(11) NOT NULL,
  `candidates` varchar(255) NOT NULL,
  `judges` varchar(255) NOT NULL,
  `weight` decimal(10,1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project` (`project`,`indicator`),
  KEY `indicator` (`indicator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `evaluation_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `company` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `evaluation_model_indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model` int(11) NOT NULL,
  `indicator` int(11) NOT NULL,
  `candidates` varchar(255) DEFAULT NULL,
  `judges` varchar(255) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `company` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `model` (`model`),
  KEY `indicator` (`indicator`),
  KEY `cadidates_team` (`candidates`),
  KEY `judges_team` (`judges`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `is_overtime` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为假日（覆盖正常工作日），1为加班（覆盖集体假期）',
  `staff` int(11) DEFAULT NULL COMMENT 'NULL为全体行为，否则为单名员工行为',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff`)
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
  `question` text,
  `type` enum('score','text') NOT NULL,
  `weight` decimal(5,1) DEFAULT NULL,
  `candidates` varchar(255) DEFAULT NULL,
  `judges` varchar(255) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `candidates` (`candidates`),
  KEY `judges` (`judges`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考核指标' AUTO_INCREMENT=647 ;

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
  `username` varchar(255) DEFAULT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='系统请求日志（记录表）' AUTO_INCREMENT=327799 ;

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=804 ;

CREATE TABLE IF NOT EXISTS `message_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `document` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message` (`message`),
  KEY `document` (`document`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

CREATE TABLE IF NOT EXISTS `message_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `message` (`message`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2768 ;

CREATE TABLE IF NOT EXISTS `nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `href` varchar(255) NOT NULL,
  `add_href` varchar(255) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=75 ;

CREATE TABLE IF NOT EXISTS `object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `num` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `team` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL,
  `company` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time_insert` (`time_insert`),
  KEY `time` (`time`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `num` (`num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45068 ;

CREATE TABLE IF NOT EXISTS `object_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` int(11) NOT NULL,
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
  KEY `object-name` (`object`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户联系方式' AUTO_INCREMENT=50940 ;

CREATE TABLE IF NOT EXISTS `object_mod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `mod` tinyint(4) NOT NULL COMMENT '1:read 2:write',
  PRIMARY KEY (`id`),
  UNIQUE KEY `object-user` (`object`,`user`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `object_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` int(11) NOT NULL,
  `relative` int(11) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `mod` int(11) NOT NULL DEFAULT '0',
  `weight` double DEFAULT NULL,
  `relation_type` varchar(255) DEFAULT NULL,
  `till` date DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `accepted` tinyint(1) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`),
  KEY `relation_type` (`relation_type`),
  KEY `num` (`num`),
  KEY `till` (`till`),
  KEY `people` (`object`),
  KEY `weight` (`weight`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='人员关系' AUTO_INCREMENT=87135 ;

CREATE TABLE IF NOT EXISTS `object_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL,
  `content` text,
  `comment` text,
  `uid` int(11) DEFAULT NULL,
  `team` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student` (`object`),
  KEY `date` (`date`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `team` (`team`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3070 ;

CREATE TABLE IF NOT EXISTS `object_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` int(11) NOT NULL,
  `tag` int(11) NOT NULL,
  `tag_name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object-label` (`object`,`tag`),
  UNIQUE KEY `object-type` (`object`,`type`),
  KEY `label` (`tag`),
  KEY `type` (`type`),
  KEY `label_name` (`tag_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30583 ;

CREATE TABLE IF NOT EXISTS `people` (
  `id` int(11) NOT NULL,
  `character` enum('单位','个人') NOT NULL DEFAULT '个人',
  `name_en` varchar(255) DEFAULT NULL,
  `name_pinyin` varchar(255) DEFAULT NULL,
  `abbreviation` varchar(255) DEFAULT NULL,
  `gender` enum('男','女') DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `id_card` char(18) DEFAULT NULL,
  `work_for` varchar(255) DEFAULT '',
  `position` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `staff` int(11) DEFAULT NULL COMMENT '人员直接相关职员',
  `city` varchar(255) DEFAULT NULL,
  `race` char(20) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `abbreviation` (`abbreviation`),
  KEY `staff` (`staff`),
  KEY `name_pinyin` (`name_pinyin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `project` (
  `id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(11) NOT NULL,
  `content` text,
  `start` int(10) DEFAULT NULL,
  `end` int(10) DEFAULT NULL,
  `deadline` int(10) DEFAULT NULL,
  `hours_own` decimal(10,2) DEFAULT NULL,
  `hours_checked` decimal(10,2) DEFAULT NULL,
  `hours_bill` decimal(10,2) DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `project` int(11) DEFAULT NULL,
  `people` int(11) DEFAULT NULL,
  `document` int(11) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `time_start` (`start`),
  KEY `time_end` (`end`),
  KEY `hours_own` (`hours_own`),
  KEY `hours_checked` (`hours_checked`),
  KEY `hours_bill` (`hours_bill`),
  KEY `case` (`project`),
  KEY `people` (`people`),
  KEY `document` (`document`),
  KEY `deadline` (`deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schedule_taskboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `sort_data` text,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=420 ;

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
  `语文` decimal(5,1) DEFAULT NULL,
  `数学` decimal(5,1) DEFAULT NULL,
  `英语` decimal(5,1) DEFAULT NULL,
  `物理` decimal(5,1) DEFAULT NULL,
  `化学` decimal(5,1) DEFAULT NULL,
  `生物` decimal(5,1) DEFAULT NULL,
  `地理` decimal(5,1) DEFAULT NULL,
  `历史` decimal(5,1) DEFAULT NULL,
  `政治` decimal(5,1) DEFAULT NULL,
  `信息` decimal(5,1) DEFAULT NULL,
  `3总` decimal(5,1) DEFAULT NULL,
  `5总` decimal(5,1) DEFAULT NULL,
  `8总` decimal(5,1) DEFAULT NULL,
  `rank_语文` int(11) DEFAULT NULL,
  `rank_数学` int(11) DEFAULT NULL,
  `rank_英语` int(11) DEFAULT NULL,
  `rank_物理` int(11) DEFAULT NULL,
  `rank_化学` int(11) DEFAULT NULL,
  `rank_生物` int(11) DEFAULT NULL,
  `rank_地理` int(11) DEFAULT NULL,
  `rank_历史` int(11) DEFAULT NULL,
  `rank_政治` int(11) DEFAULT NULL,
  `rank_信息` int(11) DEFAULT NULL,
  `rank_3总` int(11) DEFAULT NULL,
  `rank_5总` int(11) DEFAULT NULL,
  `rank_8总` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `student-exam` (`student`,`exam`),
  KEY `student` (`student`),
  KEY `exam` (`exam`),
  KEY `time` (`time`),
  KEY `extra_course` (`extra_course`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='分数汇总' AUTO_INCREMENT=13231 ;

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
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` mediumtext NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL COMMENT '职称',
  `modulus` decimal(3,2) NOT NULL DEFAULT '0.00' COMMENT '团奖系数',
  `course` int(11) DEFAULT NULL,
  `timing_fee_default` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `course` (`course`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '标签组合在一起时的顺序',
  `color` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`order`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=287 ;

CREATE TABLE IF NOT EXISTS `tag_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

CREATE TABLE IF NOT EXISTS `team` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `open` tinyint(1) NOT NULL,
  `extra_course` int(11) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leader` (`leader`),
  KEY `company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  UNIQUE KEY `name` (`name`,`company`),
  KEY `company` (`company`),
  KEY `password` (`password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户';

CREATE TABLE IF NOT EXISTS `user_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user-name` (`user`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;


ALTER TABLE `account`
  ADD CONSTRAINT `account_ibfk_10` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_14` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_15` FOREIGN KEY (`id`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `company_config`
  ADD CONSTRAINT `company_config_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `dialog`
  ADD CONSTRAINT `dialog_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_ibfk_5` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `dialog_message`
  ADD CONSTRAINT `dialog_message_ibfk_1` FOREIGN KEY (`dialog`) REFERENCES `dialog` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_message_ibfk_2` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dialog_user`
  ADD CONSTRAINT `dialog_user_ibfk_1` FOREIGN KEY (`dialog`) REFERENCES `dialog` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dialog_user_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_4` FOREIGN KEY (`id`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `evaluation_indicator`
  ADD CONSTRAINT `evaluation_indicator_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_indicator_ibfk_2` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `evaluation_model`
  ADD CONSTRAINT `evaluation_model_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `evaluation_model_indicator`
  ADD CONSTRAINT `evaluation_model_indicator_ibfk_1` FOREIGN KEY (`model`) REFERENCES `evaluation_model` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `evaluation_model_indicator_ibfk_2` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `holidays`
  ADD CONSTRAINT `holidays_ibfk_1` FOREIGN KEY (`staff`) REFERENCES `staff` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `indicator`
  ADD CONSTRAINT `indicator_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `message_document`
  ADD CONSTRAINT `message_document_ibfk_1` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `message_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `message_user`
  ADD CONSTRAINT `message_user_ibfk_1` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `message_user_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `nav`
  ADD CONSTRAINT `nav_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `nav_ibfk_3` FOREIGN KEY (`parent`) REFERENCES `nav` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `nav_ibfk_4` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `object`
  ADD CONSTRAINT `object_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `object_meta`
  ADD CONSTRAINT `object_meta_ibfk_5` FOREIGN KEY (`object`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_meta_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_meta_ibfk_4` FOREIGN KEY (`object`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `object_mod`
  ADD CONSTRAINT `object_mod_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_mod_ibfk_1` FOREIGN KEY (`object`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `object_relationship`
  ADD CONSTRAINT `object_relationship_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_relationship_ibfk_5` FOREIGN KEY (`object`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_relationship_ibfk_6` FOREIGN KEY (`relative`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `object_status`
  ADD CONSTRAINT `object_status_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_status_ibfk_5` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `object_tag`
  ADD CONSTRAINT `object_tag_ibfk_3` FOREIGN KEY (`object`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_tag_ibfk_4` FOREIGN KEY (`tag`) REFERENCES `tag` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_5` FOREIGN KEY (`staff`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_6` FOREIGN KEY (`id`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_5` FOREIGN KEY (`id`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_5` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_6` FOREIGN KEY (`id`) REFERENCES `object` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `schedule_taskboard`
  ADD CONSTRAINT `schedule_taskboard_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `school_room`
  ADD CONSTRAINT `school_room_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `school_view_score`
  ADD CONSTRAINT `school_view_score_ibfk_1` FOREIGN KEY (`exam`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `score`
  ADD CONSTRAINT `score_ibfk_1` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `score_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `score_ibfk_3` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `score_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`course`) REFERENCES `tag` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `tag_relationship`
  ADD CONSTRAINT `tag_relationship_ibfk_1` FOREIGN KEY (`label`) REFERENCES `tag` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `tag_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `tag` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `team`
  ADD CONSTRAINT `team_ibfk_1` FOREIGN KEY (`leader`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `team_ibfk_4` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `user_config`
  ADD CONSTRAINT `user_config_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
