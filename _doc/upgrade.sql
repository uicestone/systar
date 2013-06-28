ALTER TABLE  `people` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'people';
ALTER TABLE  `people_relationship` CHANGE  `accepted`  `accepted` TINYINT( 1 ) NULL;
-- server updated

ALTER TABLE  `team` ADD  `open` BOOLEAN NOT NULL AFTER  `leader`;

ALTER TABLE `document_mod` DROP `id`;
ALTER TABLE  `document_mod` ADD  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

UPDATE  `syssh`.`people` SET  `num` =  'teacher' WHERE  `people`.`id` =13453;
UPDATE  `syssh`.`people` SET  `num` =  'finance' WHERE  `people`.`id` =13447;
UPDATE  `syssh`.`people` SET  `num` =  'hr' WHERE  `people`.`id` =13448;
UPDATE  `syssh`.`people` SET  `num` =  'service' WHERE  `people`.`id` =13450;
UPDATE  `syssh`.`people` SET  `num` =  'hr' WHERE  `people`.`id` =13532;
