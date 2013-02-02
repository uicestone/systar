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

UPDATE `people` SET display=1 WHERE type='职员'；
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