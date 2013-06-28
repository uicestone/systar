ALTER TABLE  `people` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'people';
ALTER TABLE  `people_relationship` CHANGE  `accepted`  `accepted` TINYINT( 1 ) NULL;
-- server updated

ALTER TABLE  `team` ADD  `open` BOOLEAN NOT NULL AFTER  `leader`;
`document_mod` DROP `id`;
ALTER TABLE  `document_mod` ADD  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
