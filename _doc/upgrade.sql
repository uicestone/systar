-- uice 2012/11/24 -3
ALTER TABLE  `team` ADD  `num` VARCHAR( 255 ) NULL AFTER  `type` ,
ADD INDEX (  `num` );
UPDATE  `syssh`.`team` SET  `num` =  '1201' WHERE  `team`.`id` =1;
ALTER TABLE  `team_people` CHANGE  `num_in_class`  `id_in_team` INT( 11 ) NULL DEFAULT NULL;
UPDATE  `syssh`.`user` SET  `group` =  'teacher,jiaowu' WHERE  `user`.`id` =1;
UPDATE  `syssh`.`team` SET  `num` =  '12' WHERE  `team`.`id` =2;
ALTER TABLE  `people_relationship` ADD  `relation_type` VARCHAR( 255 ) NOT NULL AFTER  `relation`;
ALTER TABLE  `people_relationship` ADD INDEX (  `relation_type` );
ALTER TABLE  `people_relationship` DROP company;