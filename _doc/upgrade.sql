UPDATE case_people SET type='客户' WHERE type = 'client';
UPDATE case_people SET type='律师' WHERE type = 'lawyer';

ALTER TABLE  `syssh`.`people_profile` DROP INDEX  `people-name` ,
ADD INDEX  `people-name` (  `people` );

INSERT INTO people_profile
(`people`, `name`, `content`, `comment`, `uid`, `username`, `time`)
SELECT
 client,type,content,comment,uid,username,time
FROM starsys.client_contact;
-- uice 1/19

ALTER TABLE  `document_label` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
UPDATE `document_label` SET `type` = '类型';

INSERT INTO `label_relationship` (`id`, `label`, `relative`, `relation`) VALUES
(1, 1, 4, NULL),
(2, 1, 5, NULL),
(3, 3, 40, NULL),
(4, 3, 41, NULL),
(5, 3, 42, NULL),
(6, 3, 43, NULL),
(7, 3, 44, NULL),
(8, 3, 45, NULL),
(9, 3, 45, NULL),
(10, 3, 46, NULL),
(11, 3, 47, NULL);

UPDATE  `syssh`.`affair` SET  `add_action` = NULL WHERE  `affair`.`id` =70;
-- uice 1/21

UPDATE `people` SET display=1 WHERE type='职员';
-- uice 1/24

ALTER TABLE  `case_fee_timing` CHANGE  `time_start`  `date_start` DATE NOT NULL;
-- uice 1/25

ALTER TABLE  `case_fee` CHANGE  `receiver`  `receiver` ENUM(  '承办律师',  '律所' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- uice 1/27

ALTER TABLE  `case_label` ADD UNIQUE `case-type` (
`case` ,
`type`
);
-- uice 2/2

ALTER TABLE  `case_label` ADD  `label_name` VARCHAR( 255 ) NOT NULL AFTER  `label`;
UPDATE case_label INNER JOIN label ON label.id=case_label.label
SET case_label.label_name = label.name;
UPDATE  `syssh`.`affair` SET  `add_action` =  'client/add' WHERE  `affair`.`id` =80;
-- uice 2/2-2

ALTER TABLE  `people_label` ADD  `label_name` VARCHAR( 255 ) NOT NULL AFTER  `label`;

UPDATE people_label INNER JOIN label ON label.id=people_label.label
SET people_label.label_name = label.name;
-- uice 2/3

-- structure exported 2/3

ALTER TABLE  `document_label` ADD  `label_name` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `news` ADD  `time_insert` INT NOT NULL AFTER  `username` ,
ADD INDEX (  `time_insert` );
ALTER TABLE  `people_relationship` CHANGE  `relation_type`  `relation_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
-- uice 2/6

RENAME TABLE  `syssh`.`affair` TO  `syssh`.`controller` ;
ALTER TABLE  `controller` DROP  `add_target`;
ALTER TABLE  `controller` ADD  `discription` VARCHAR( 255 ) NULL AFTER  `ui_name`;
ALTER TABLE  `group` CHANGE  `affair`  `controller` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
ALTER TABLE  `group` CHANGE  `action`  `method` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
ALTER TABLE  `group` CHANGE  `affair_ui_name`  `ui_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';
ALTER TABLE  `group` ADD  `discription` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `ui_name`;
RENAME TABLE  `syssh`.`group` TO  `syssh`.`permission` ;
ALTER TABLE  `permission` CHANGE  `name`  `group` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';

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

ALTER TABLE `schedule_profile`
  ADD CONSTRAINT `schedule_profile_ibfk_1` FOREIGN KEY (`schedule`) REFERENCES `schedule` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_profile_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE  `syssh`.`case_people` DROP INDEX  `case` ,
ADD UNIQUE  `case` (  `case` ,  `people` ,  `role` );
-- uice 2/13

ALTER TABLE  `case_people` CHANGE  `role`  `role` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `case_people` CHANGE `hourly_fee` `hourly_fee` DECIMAL(10,2) NULL DEFAULT NULL;
-- uice 2/14

ALTER TABLE  `syssh`.`people_label` DROP INDEX  `people` ,
ADD UNIQUE  `people` (  `people` ,  `type` );
ALTER TABLE  `account` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE  `case` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE  `document` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `people` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `schedule` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `team` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- uice 2/15

--
-- 表的结构 `case_document`
--

CREATE TABLE IF NOT EXISTS `case_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case` int(11) NOT NULL,
  `document` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case` (`case`),
  KEY `document` (`document`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 限制导出的表
--

--
-- 限制表 `case_document`
--
ALTER TABLE `case_document`
  ADD CONSTRAINT `case_document_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_document_ibfk_1` FOREIGN KEY (`case`) REFERENCES `case` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `case_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE  `document` DROP FOREIGN KEY  `document_ibfk_1` ;

ALTER TABLE  `document` DROP FOREIGN KEY  `document_ibfk_4` ;

ALTER TABLE  `document` DROP  `case` ,
DROP  `people` ;

ALTER TABLE  `case_label` DROP FOREIGN KEY  `case_label_ibfk_1` ,
ADD FOREIGN KEY (  `case` ) REFERENCES  `syssh`.`case` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;
-- uice 2/19

ALTER TABLE  `case` CHANGE  `num`  `num` CHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- uice 2/21

CREATE TABLE IF NOT EXISTS  `sessions` (
  session_id varchar(40) DEFAULT '0' NOT NULL,
  ip_address varchar(16) DEFAULT '0' NOT NULL,
  user_agent varchar(120) NOT NULL,
  last_activity int(10) unsigned DEFAULT 0 NOT NULL,
  user_data text NULL,
  PRIMARY KEY (session_id),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;
-- uice 2/22

ALTER TABLE  `staff` ADD  `position` INT NULL AFTER  `id` ,
ADD INDEX (  `position` );
ALTER TABLE  `staff` ADD FOREIGN KEY (  `position` ) REFERENCES  `syssh`.`position` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;
-- uice 2/25

ALTER TABLE  `schedule` CHANGE  `username`  `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- uice 2/28

ALTER TABLE  `people` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
-- uice 3/7

ALTER TABLE  `people` CHANGE  `username`  `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `people_profile` CHANGE  `content`  `content` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
-- uice 3/10

ALTER TABLE  `people` ADD  `phone` VARCHAR( 255 ) NULL AFTER  `gender` ,
ADD  `email` VARCHAR( 255 ) NULL AFTER  `phone`
-- uice 3/13

ALTER TABLE  `schedule_profile` CHANGE  `comment`  `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
-- uice 3/16

ALTER TABLE  `case` ADD  `type` VARCHAR( 255 ) NOT NULL AFTER  `name`;
-- server upgraded

UPDATE `case` SET type='业务';
UPDATE `case` SET type='行政事务' WHERE id IN (SELECT `case` FROM case_label WHERE label_name = '内部行政');

ALTER TABLE  `case` DROP  `name_extra` ,
DROP  `is_reviewed` ,
DROP  `type_lock` ,
DROP  `client_lock` ,
DROP  `staff_lock` ,
DROP  `fee_lock` ,
DROP  `apply_file` ,
DROP  `is_query` ,
DROP  `finance_review` ,
DROP  `info_review` ,
DROP  `manager_review` ,
DROP  `filed` ;
-- uice 3/17

ALTER TABLE  `label` ADD  `order` INT NOT NULL DEFAULT  '0' COMMENT  '标签组合在一起时的顺序',
ADD INDEX (  `order` );
-- uice 3/18

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


ALTER TABLE `account_team`
  ADD CONSTRAINT `account_team_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_team_ibfk_1` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `account_team_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE  `label` ADD  `color` VARCHAR( 255 ) NOT NULL DEFAULT  'not specified';
-- uice 3/20

ALTER TABLE  `document` ADD  `filename` VARCHAR( 255 ) NOT NULL AFTER  `name`;
update document set filename = name;
ALTER TABLE  `document_label` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
-- uice 3/22

RENAME TABLE  `syssh`.`case` TO  `syssh`.`project` ;
RENAME TABLE  `syssh`.`case` TO  `syssh`.`project` ;
RENAME TABLE  `syssh`.`case_document` TO  `syssh`.`project_document` ;
RENAME TABLE  `syssh`.`case_fee` TO  `syssh`.`project_account` ;
RENAME TABLE  `syssh`.`case_label` TO  `syssh`.`project_label` ;
RENAME TABLE  `syssh`.`case_num` TO  `syssh`.`project_num` ;
RENAME TABLE  `syssh`.`case_people` TO  `syssh`.`project_people` ;

ALTER TABLE  `project_account` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '固定';

ALTER TABLE  `project_account` DROP FOREIGN KEY  `project_account_ibfk_1` ;
ALTER TABLE  `project_document` DROP FOREIGN KEY  `project_document_ibfk_1` ;
ALTER TABLE  `project_label` DROP FOREIGN KEY  `project_label_ibfk_3` ;
ALTER TABLE  `project_num` DROP FOREIGN KEY  `project_num_ibfk_4` ;
ALTER TABLE  `project_people` DROP FOREIGN KEY  `project_people_ibfk_1` ;
ALTER TABLE  `schedule` DROP FOREIGN KEY  `schedule_ibfk_1` ;
ALTER TABLE  `account` DROP FOREIGN KEY  `account_ibfk_10` ;

ALTER TABLE  `project_account` CHANGE  `case`  `project` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `project_document` CHANGE  `case`  `project` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `project_label` CHANGE  `case`  `project` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `project_num` CHANGE  `case`  `project` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `project_people` CHANGE  `case`  `project` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `schedule` CHANGE  `case`  `project` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `account` CHANGE  `case`  `project` INT( 11 ) NULL DEFAULT NULL;

ALTER TABLE  `project_account` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `project_document` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `project_label` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `project_num` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `project_people` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `schedule` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `account` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`) ON DELETE NO ACTION ON UPDATE CASCADE ;

DROP TABLE ftp;DROP TABLE ftp_fav;

ALTER TABLE  `people` DROP FOREIGN KEY  `people_ibfk_1` ;
ALTER TABLE `people` DROP `source`;
ALTER TABLE `project` DROP `source`;

DROP TABLE `client_source`;
DROP TABLE `controller`;
DROP TABLE `permission`;

ALTER TABLE  `project` ADD  `team` INT NULL AFTER  `type` ,
ADD INDEX (  `team` );

ALTER TABLE `project` DROP timing_fee;

ALTER TABLE  `project_people` DROP FOREIGN KEY  `project_people_ibfk_3` ;
ALTER TABLE `project_people` DROP `username`;
ALTER TABLE `project_people` DROP `company`;

-- uice 3/25