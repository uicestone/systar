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
ADD UNIQUE  `case` (  `case` ,  `people` ,  `role` )
-- uice 2/13
