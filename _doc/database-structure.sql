# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.18)
# Database: syssh-sdfz
# Generation Time: 2018-04-05 05:05:46 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table account
# ------------------------------------------------------------

CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '数额',
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `reviewed` tinyint(1) NOT NULL DEFAULT '0',
  `count` tinyint(1) NOT NULL DEFAULT '1',
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
  KEY `team` (`team`),
  CONSTRAINT `account_ibfk_10` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `account_ibfk_12` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `account_ibfk_13` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `account_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `account_ibfk_4` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `account_ibfk_9` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账目（主表）';



# Dump of table account_label
# ------------------------------------------------------------

CREATE TABLE `account_label` (
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
  KEY `type` (`type`),
  CONSTRAINT `account_label_ibfk_1` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `account_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table company
# ------------------------------------------------------------

CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `syscode` varchar(255) NOT NULL,
  `sysname` varchar(255) NOT NULL,
  `ucenter` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公司列表（系统表）';



# Dump of table company_config
# ------------------------------------------------------------

CREATE TABLE `company_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `name` (`name`),
  CONSTRAINT `company_config_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table course
# ------------------------------------------------------------

CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `chart_color` char(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课程';



# Dump of table dialog
# ------------------------------------------------------------

CREATE TABLE `dialog` (
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
  KEY `users` (`users`),
  CONSTRAINT `dialog_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `dialog_ibfk_5` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `dialog_ibfk_6` FOREIGN KEY (`last_message`) REFERENCES `message` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dialog_message
# ------------------------------------------------------------

CREATE TABLE `dialog_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `message` (`message`),
  CONSTRAINT `dialog_message_ibfk_1` FOREIGN KEY (`dialog`) REFERENCES `dialog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dialog_message_ibfk_2` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dialog_user
# ------------------------------------------------------------

CREATE TABLE `dialog_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialog` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dialog` (`dialog`),
  KEY `user` (`user`),
  CONSTRAINT `dialog_user_ibfk_1` FOREIGN KEY (`dialog`) REFERENCES `dialog` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `dialog_user_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table document
# ------------------------------------------------------------

CREATE TABLE `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT 'document',
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
  KEY `time_insert` (`time_insert`),
  CONSTRAINT `document_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目下文件';



# Dump of table document_label
# ------------------------------------------------------------

CREATE TABLE `document_label` (
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
  KEY `label_name` (`label_name`),
  CONSTRAINT `document_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `document_label_ibfk_3` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table document_mod
# ------------------------------------------------------------

CREATE TABLE `document_mod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document` int(11) NOT NULL,
  `people` int(11) DEFAULT NULL,
  `mod` tinyint(4) NOT NULL COMMENT '1:read 2:write',
  PRIMARY KEY (`id`),
  UNIQUE KEY `document-people` (`document`,`people`),
  KEY `people` (`people`),
  CONSTRAINT `document_mod_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `document_mod_ibfk_3` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table evaluation_indicator
# ------------------------------------------------------------

CREATE TABLE `evaluation_indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `indicator` int(11) NOT NULL,
  `candidates` varchar(255) NOT NULL,
  `judges` varchar(255) NOT NULL,
  `weight` decimal(10,1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project` (`project`,`indicator`),
  KEY `indicator` (`indicator`),
  CONSTRAINT `evaluation_indicator_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `evaluation_indicator_ibfk_2` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table evaluation_model
# ------------------------------------------------------------

CREATE TABLE `evaluation_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `company` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  CONSTRAINT `evaluation_model_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table evaluation_model_indicator
# ------------------------------------------------------------

CREATE TABLE `evaluation_model_indicator` (
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
  KEY `judges_team` (`judges`),
  CONSTRAINT `evaluation_model_indicator_ibfk_1` FOREIGN KEY (`model`) REFERENCES `evaluation_model` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `evaluation_model_indicator_ibfk_2` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table express
# ------------------------------------------------------------

CREATE TABLE `express` (
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
  KEY `sender` (`sender`),
  CONSTRAINT `express_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `express_ibfk_2` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `express_ibfk_4` FOREIGN KEY (`sender`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递（主表）';



# Dump of table holidays
# ------------------------------------------------------------

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `is_overtime` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为假日（覆盖正常工作日），1为加班（覆盖集体假期）',
  `staff` int(11) DEFAULT NULL COMMENT 'NULL为全体行为，否则为单名员工行为',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff`),
  CONSTRAINT `holidays_ibfk_1` FOREIGN KEY (`staff`) REFERENCES `staff` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table idcard_region
# ------------------------------------------------------------

CREATE TABLE `idcard_region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(6) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='身份证地域区段（资源表）';



# Dump of table indicator
# ------------------------------------------------------------

CREATE TABLE `indicator` (
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
  KEY `judges` (`judges`),
  CONSTRAINT `indicator_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='考核指标';



# Dump of table label
# ------------------------------------------------------------

CREATE TABLE `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '标签组合在一起时的顺序',
  `color` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`order`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table label_relationship
# ------------------------------------------------------------

CREATE TABLE `label_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`),
  CONSTRAINT `label_relationship_ibfk_1` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `label_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table log
# ------------------------------------------------------------

CREATE TABLE `log` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统请求日志（记录表）';



# Dump of table message
# ------------------------------------------------------------

CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  CONSTRAINT `message_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公告（主表）';



# Dump of table message_document
# ------------------------------------------------------------

CREATE TABLE `message_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `document` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message` (`message`),
  KEY `document` (`document`),
  CONSTRAINT `message_document_ibfk_1` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `message_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table message_user
# ------------------------------------------------------------

CREATE TABLE `message_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `read` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `message` (`message`),
  CONSTRAINT `message_user_ibfk_1` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `message_user_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table migrations
# ------------------------------------------------------------

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table nav
# ------------------------------------------------------------

CREATE TABLE `nav` (
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
  KEY `order` (`order`),
  CONSTRAINT `nav_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `nav_ibfk_3` FOREIGN KEY (`parent`) REFERENCES `nav` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `nav_ibfk_4` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oauth_access_tokens
# ------------------------------------------------------------

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_auth_codes
# ------------------------------------------------------------

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `scopes` text COLLATE utf8_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_clients
# ------------------------------------------------------------

CREATE TABLE `oauth_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_personal_access_clients
# ------------------------------------------------------------

CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_personal_access_clients_client_id_index` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_refresh_tokens
# ------------------------------------------------------------

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table people
# ------------------------------------------------------------

CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `character` enum('单位','个人') NOT NULL DEFAULT '个人',
  `name` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_pinyin` varchar(255) DEFAULT NULL,
  `abbreviation` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'people',
  `num` varchar(255) DEFAULT NULL,
  `gender` enum('男','女') DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `id_card` char(18) DEFAULT NULL,
  `work_for` varchar(255) DEFAULT '',
  `position` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `staff` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `city` varchar(255) DEFAULT NULL,
  `race` char(20) DEFAULT NULL,
  `company` int(11) DEFAULT '1',
  `uid` int(11) DEFAULT NULL,
  `time_insert` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type-num-company` (`type`,`num`,`company`),
  KEY `name` (`name`),
  KEY `abbreviation` (`abbreviation`),
  KEY `staff` (`staff`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `time_insert` (`time_insert`),
  KEY `name_pinyin` (`name_pinyin`),
  KEY `num` (`num`),
  CONSTRAINT `people_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_ibfk_5` FOREIGN KEY (`staff`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户（主表）';



# Dump of table people_label
# ------------------------------------------------------------

CREATE TABLE `people_label` (
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
  KEY `label_name` (`label_name`),
  CONSTRAINT `people_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_label_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table people_profile
# ------------------------------------------------------------

CREATE TABLE `people_profile` (
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
  KEY `people-name` (`people`),
  CONSTRAINT `people_profile_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_profile_ibfk_4` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户联系方式';



# Dump of table people_relationship
# ------------------------------------------------------------

CREATE TABLE `people_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `people` int(11) DEFAULT NULL,
  `relative` int(11) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `is_on` tinyint(1) DEFAULT '1',
  `till` date DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `accepted` tinyint(1) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `people-relative-relation-is_on` (`people`,`relative`,`relation`,`is_on`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`),
  KEY `num` (`num`),
  KEY `till` (`till`),
  CONSTRAINT `people_relationship_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_relationship_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_relationship_ibfk_4` FOREIGN KEY (`relative`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='人员关系';



# Dump of table people_status
# ------------------------------------------------------------

CREATE TABLE `people_status` (
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
  KEY `team` (`team`),
  CONSTRAINT `people_status_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_status_ibfk_4` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `people_status_ibfk_5` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table project
# ------------------------------------------------------------

CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'project',
  `team` int(11) DEFAULT NULL,
  `num` char(20) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `first_contact` date DEFAULT NULL,
  `time_contract` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `quote` varchar(255) DEFAULT NULL COMMENT '报价',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `focus` text,
  `summary` text,
  `company` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
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
  KEY `team` (`team`),
  CONSTRAINT `project_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_ibfk_4` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目（主表）';



# Dump of table project_document
# ------------------------------------------------------------

CREATE TABLE `project_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) DEFAULT NULL,
  `document` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case` (`project`),
  KEY `document` (`document`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  CONSTRAINT `project_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_document_ibfk_4` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table project_label
# ------------------------------------------------------------

CREATE TABLE `project_label` (
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
  KEY `type` (`type`),
  CONSTRAINT `project_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_label_ibfk_4` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table project_people
# ------------------------------------------------------------

CREATE TABLE `project_people` (
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
  `comment` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `case` (`project`,`people`,`role`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `people` (`people`),
  KEY `role` (`role`),
  KEY `weight` (`weight`),
  CONSTRAINT `project_people_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_people_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_people_ibfk_6` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目人员关系';



# Dump of table project_profile
# ------------------------------------------------------------

CREATE TABLE `project_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `comment` text NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project-name` (`project`,`name`),
  KEY `time` (`time`),
  KEY `uid` (`uid`),
  KEY `name` (`name`),
  CONSTRAINT `project_profile_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_profile_ibfk_3` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table project_relationship
# ------------------------------------------------------------

CREATE TABLE `project_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `relative` int(11) NOT NULL,
  `relation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project-relative-relation` (`project`,`relative`,`relation`),
  KEY `relative` (`relative`),
  KEY `relation` (`relation`),
  CONSTRAINT `project_relationship_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_relationship_ibfk_2` FOREIGN KEY (`relative`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table project_status
# ------------------------------------------------------------

CREATE TABLE `project_status` (
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
  KEY `project` (`project`),
  CONSTRAINT `project_status_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `project_status_ibfk_2` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table schedule
# ------------------------------------------------------------

CREATE TABLE `schedule` (
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
  KEY `deadline` (`deadline`),
  CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `schedule_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `schedule_ibfk_5` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='日程（主表）';



# Dump of table schedule_label
# ------------------------------------------------------------

CREATE TABLE `schedule_label` (
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
  KEY `label_name` (`label_name`),
  CONSTRAINT `schedule_label_ibfk_2` FOREIGN KEY (`label`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `schedule_label_ibfk_4` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table schedule_people
# ------------------------------------------------------------

CREATE TABLE `schedule_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule` int(11) NOT NULL,
  `people` int(11) NOT NULL,
  `enrolled` tinyint(1) NOT NULL,
  `in_todo_list` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule-people` (`schedule`,`people`),
  KEY `schedule` (`schedule`),
  KEY `people` (`people`),
  CONSTRAINT `schedule_people_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `schedule_people_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table schedule_profile
# ------------------------------------------------------------

CREATE TABLE `schedule_profile` (
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
  KEY `time` (`time`),
  CONSTRAINT `schedule_profile_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `schedule_profile_ibfk_3` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table schedule_taskboard
# ------------------------------------------------------------

CREATE TABLE `schedule_taskboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `sort_data` text,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  CONSTRAINT `schedule_taskboard_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table school_room
# ------------------------------------------------------------

CREATE TABLE `school_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `capacity` int(11) NOT NULL DEFAULT '0',
  `team` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`),
  CONSTRAINT `school_room_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='考场';



# Dump of table school_view_score
# ------------------------------------------------------------

CREATE TABLE `school_view_score` (
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
  KEY `extra_course` (`extra_course`),
  CONSTRAINT `school_view_score_ibfk_1` FOREIGN KEY (`exam`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分数汇总';



# Dump of table score
# ------------------------------------------------------------

CREATE TABLE `score` (
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
  KEY `project` (`project`),
  CONSTRAINT `score_ibfk_1` FOREIGN KEY (`indicator`) REFERENCES `indicator` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `score_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `score_ibfk_3` FOREIGN KEY (`project`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `score_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='阅卷分数';



# Dump of table sessions
# ------------------------------------------------------------

CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` longtext NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table staff
# ------------------------------------------------------------

CREATE TABLE `staff` (
  `id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL COMMENT '职称',
  `modulus` decimal(3,2) NOT NULL DEFAULT '0.00' COMMENT '团奖系数',
  `course` int(11) DEFAULT NULL,
  `timing_fee_default` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `course` (`course`),
  KEY `id` (`id`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`course`) REFERENCES `course` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='员工（主表）';



# Dump of table team
# ------------------------------------------------------------

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `open` tinyint(1) NOT NULL,
  `extra_course` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `company` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `time_insert` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leader` (`leader`),
  KEY `display` (`display`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `time_insert` (`time_insert`),
  CONSTRAINT `team_ibfk_1` FOREIGN KEY (`leader`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `team_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `team_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `team_ibfk_4` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团队（主表）';



# Dump of table user
# ------------------------------------------------------------

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT '',
  `lastip` varchar(255) DEFAULT NULL,
  `lastlogin` int(11) DEFAULT NULL,
  `company` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`company`),
  KEY `company` (`company`),
  KEY `password` (`password`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户';



# Dump of table user_config
# ------------------------------------------------------------

CREATE TABLE `user_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user-name` (`user`,`name`),
  CONSTRAINT `user_config_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

INSERT INTO `company` (`id`, `name`, `type`, `host`, `syscode`, `sysname`, `ucenter`)
VALUES
	(1, 'Law Firm', 'lawfirm', 'localhost:8080', 'starsys', 'SYSTAR', 0);


INSERT INTO `company_config` (`id`, `company`, `name`, `value`)
VALUES
	(1, 1, 'default_page', 'schedule');

INSERT INTO `people` (`id`, `character`, `name`, `name_en`, `name_pinyin`, `abbreviation`, `type`, `num`, `gender`, `phone`, `email`, `id_card`, `work_for`, `position`, `birthday`, `staff`, `display`, `city`, `race`, `company`, `uid`, `time_insert`, `time`, `comment`)
VALUES
	(1, '个人', 'Root', 'root', 'root', 'root', 'people', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL);


INSERT INTO `user` (`id`, `name`, `alias`, `email`, `password`, `remember_token`, `group`, `lastip`, `lastlogin`, `company`)
VALUES
	(1, 'root', NULL, '', 'root', NULL, '', NULL, NULL, 1);

INSERT INTO `nav` (`id`, `name`, `href`, `add_href`, `parent`, `order`, `team`, `company`, `company_type`)
VALUES
	(1, '日程', '#schedule', 'javascript:$.createSchedule({selection:this});', NULL, 5, NULL, NULL, NULL),
	(2, '案件', '#cases', '#cases/add', NULL, 10, NULL, NULL, 'lawfirm'),
	(3, '咨询', '#query', '#query/add', NULL, 20, NULL, NULL, 'lawfirm'),
	(4, '客户', '#client', '#client/add', NULL, 30, NULL, NULL, 'lawfirm'),
	(6, '评价', '#evaluation', '#evaluation/add', NULL, 60, NULL, NULL, NULL),
	(7, '通讯录', '#contact', '#contact/add', NULL, 50, NULL, NULL, 'lawfirm'),
	(8, '文档', '#document', NULL, NULL, 10, NULL, 1, NULL),
	(9, '事务', '#project', '#project/add', NULL, 5, NULL, NULL, NULL),
	(10, '任务墙', '#schedule/taskboard', NULL, 1, 0, NULL, NULL, NULL),
	(11, '列表', '#schedule/lists', NULL, 1, 10, NULL, NULL, NULL);

