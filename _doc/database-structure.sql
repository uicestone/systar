SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE `syssh` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `syssh`;

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '数额',
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `reviewed` tinyint(1) NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL COMMENT '到账日期',
  `project` int(11) DEFAULT NULL,
  `team` int(11) DEFAULT NULL,
  `account` int(11) DEFAULT NULL,
  `people` int(11) DEFAULT NULL COMMENT '关联客户',
  `comment` text COMMENT '备注',
  `distributed_fixed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '固定贡献业务奖已发',
  `distributed_actual` tinyint(1) NOT NULL DEFAULT '0' COMMENT '实际贡献业务奖已发',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `case` (`project`),
  KEY `case_fee` (`account`),
  KEY `people` (`people`),
  KEY `company` (`company`),
  KEY `amount` (`amount`),
  KEY `time_insert` (`time_insert`),
  KEY `subject` (`subject`),
  KEY `type` (`type`),
  KEY `team` (`team`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='账目（主表）' AUTO_INCREMENT=1917 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=295 ;

CREATE TABLE IF NOT EXISTS `dialog_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `message` (`message`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=440 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=555 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目下文件' AUTO_INCREMENT=2347 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4620 ;

CREATE TABLE IF NOT EXISTS `document_mod` (
  `id` int(11) DEFAULT NULL,
  `document` int(11) NOT NULL,
  `people` int(11) DEFAULT NULL,
  `mod` tinyint(4) NOT NULL COMMENT '1:read 2:write',
  KEY `document` (`document`),
  KEY `people` (`people`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

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
  `question` text,
  `type` enum('score','text') NOT NULL,
  `weight` decimal(5,1) DEFAULT NULL,
  `candidates` varchar(255) DEFAULT NULL,
  `judges` varchar(255) DEFAULT NULL,
  `position` int(11) DEFAULT NULL COMMENT '被评价人职位',
  `critic` int(11) DEFAULT NULL COMMENT '评价人职位',
  `company` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `critic` (`critic`),
  KEY `position` (`position`),
  KEY `candidates` (`candidates`),
  KEY `judges` (`judges`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='考核指标' AUTO_INCREMENT=648 ;

CREATE TABLE IF NOT EXISTS `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '标签组合在一起时的顺序',
  `color` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`order`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=215 ;

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
  `username` varchar(255) DEFAULT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='系统请求日志（记录表）' AUTO_INCREMENT=198801 ;

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='公告（主表）' AUTO_INCREMENT=474 ;

CREATE TABLE IF NOT EXISTS `message_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `document` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message` (`message`),
  KEY `document` (`document`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

CREATE TABLE IF NOT EXISTS `message_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `message` (`message`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=903 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=66 ;

CREATE TABLE IF NOT EXISTS `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `character` enum('单位','个人') NOT NULL DEFAULT '个人',
  `name` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `name_pinyin` varchar(255) DEFAULT NULL,
  `abbreviation` varchar(255) DEFAULT NULL,
  `type` enum('people','team','staff','contact','client','student','classes','teacher_group','course_group') NOT NULL DEFAULT 'people',
  `num` varchar(255) DEFAULT NULL,
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
  `time_insert` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `abbreviation` (`abbreviation`),
  KEY `staff` (`staff`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `time_insert` (`time_insert`),
  KEY `name_pinyin` (`name_pinyin`),
  KEY `num` (`num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户（主表）' AUTO_INCREMENT=13544 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20172 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='客户联系方式' AUTO_INCREMENT=45200 ;

CREATE TABLE IF NOT EXISTS `people_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) DEFAULT NULL,
  `relative` int(11) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `relation_type` varchar(255) DEFAULT NULL,
  `till` date DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `is_default_contact` tinyint(1) DEFAULT NULL,
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
  KEY `people` (`people`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='人员关系' AUTO_INCREMENT=17566 ;

CREATE TABLE IF NOT EXISTS `people_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `content` text,
  `comment` text,
  `uid` int(11) NOT NULL,
  `team` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student` (`people`),
  KEY `date` (`date`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `team` (`team`)
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
  `type` enum('project','evaluation','cases','query') NOT NULL DEFAULT 'project',
  `team` int(11) DEFAULT NULL,
  `num` char(20) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `first_contact` date DEFAULT NULL,
  `time_contract` date DEFAULT NULL,
  `end` date DEFAULT NULL,
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
  KEY `time_end` (`end`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `company` (`company`),
  KEY `time_insert` (`time_insert`),
  KEY `team` (`team`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目（主表）' AUTO_INCREMENT=2672 ;

CREATE TABLE IF NOT EXISTS `project_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `document` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case` (`project`),
  KEY `document` (`document`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2492 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19315 ;

CREATE TABLE IF NOT EXISTS `project_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `people` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `weight` decimal(6,5) DEFAULT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='项目人员关系' AUTO_INCREMENT=47964 ;

CREATE TABLE IF NOT EXISTS `project_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `comment` text NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `uid` (`uid`),
  KEY `name` (`name`),
  KEY `project` (`project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=175 ;

CREATE TABLE IF NOT EXISTS `project_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project-relative-relation` (`project`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

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
  `content` text,
  `start` int(10) DEFAULT NULL,
  `end` int(10) DEFAULT NULL,
  `deadline` int(10) DEFAULT NULL,
  `hours_own` decimal(10,2) DEFAULT NULL,
  `hours_checked` decimal(10,2) DEFAULT NULL,
  `hours_bill` decimal(10,2) DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `in_todo_list` tinyint(1) NOT NULL DEFAULT '0',
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
  KEY `time_start` (`start`),
  KEY `time_end` (`end`),
  KEY `hours_own` (`hours_own`),
  KEY `hours_checked` (`hours_checked`),
  KEY `hours_bill` (`hours_bill`),
  KEY `case` (`project`),
  KEY `people` (`people`),
  KEY `document` (`document`),
  KEY `time_insert` (`time_insert`),
  KEY `deadline` (`deadline`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='日程（主表）' AUTO_INCREMENT=21205 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=265 ;

CREATE TABLE IF NOT EXISTS `schedule_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `comment` text,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule` (`schedule`),
  KEY `name` (`name`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1485 ;

CREATE TABLE IF NOT EXISTS `schedule_taskboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `sort_data` text,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=382 ;

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
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `extra_course` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `time_insert` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leader` (`leader`),
  KEY `display` (`display`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团队（主表）';

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
  ADD CONSTRAINT `account_ibfk_12` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_13` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_4` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_ibfk_9` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `account_label`
  ADD CONSTRAINT `account_label_ibfk_1` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

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
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `document_label`
  ADD CONSTRAINT `document_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `document_label_ibfk_3` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `document_mod`
  ADD CONSTRAINT `document_mod_ibfk_3` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_mod_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `evaluation_indicator`
  ADD CONSTRAINT `evaluation_indicator_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_indicator_ibfk_2` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `evaluation_model`
  ADD CONSTRAINT `evaluation_model_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `evaluation_model_indicator`
  ADD CONSTRAINT `evaluation_model_indicator_ibfk_1` FOREIGN KEY (`model`) REFERENCES `evaluation_model` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `evaluation_model_indicator_ibfk_2` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `express`
  ADD CONSTRAINT `express_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_2` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_4` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `express_ibfk_5` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `indicator`
  ADD CONSTRAINT `indicator_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `indicator_ibfk_5` FOREIGN KEY (`critic`) REFERENCES `position` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `label_relationship`
  ADD CONSTRAINT `label_relationship_ibfk_1` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `label_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

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

ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_ibfk_5` FOREIGN KEY (`staff`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

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
  ADD CONSTRAINT `people_status_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_status_ibfk_4` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `people_status_ibfk_5` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_4` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_document`
  ADD CONSTRAINT `project_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_document_ibfk_4` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_label`
  ADD CONSTRAINT `project_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_label_ibfk_4` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `project_people`
  ADD CONSTRAINT `project_people_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_people_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_people_ibfk_5` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_profile`
  ADD CONSTRAINT `project_profile_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_profile_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `project_relationship`
  ADD CONSTRAINT `project_relationship_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

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
