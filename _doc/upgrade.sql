ALTER TABLE  `people` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'people';
ALTER TABLE  `people_relationship` CHANGE  `accepted`  `accepted` TINYINT( 1 ) NULL;
-- server updated

ALTER TABLE  `team` ADD  `open` BOOLEAN NOT NULL AFTER  `leader`;

