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
-- uice tonghepc updated 

-- uice 2012/11/25
ALTER TABLE  `account` ADD  `time_insert` INT NOT NULL AFTER  `username` ,
ADD INDEX (  `time_insert` );
ALTER TABLE  `case` ADD  `time_insert` INT NOT NULL AFTER  `username` ,
ADD INDEX (  `time_insert` );
ALTER TABLE  `document` ADD  `time_insert` INT NOT NULL AFTER  `username` ,
ADD INDEX (  `time_insert` );
ALTER TABLE  `people` ADD  `time_insert` INT NOT NULL AFTER  `username` ,
ADD INDEX (  `time_insert` );
ALTER TABLE  `property` ADD  `time_insert` INT NOT NULL AFTER  `company` ,
ADD INDEX (  `time_insert` );
ALTER TABLE  `schedule` ADD  `time_insert` INT NOT NULL AFTER  `username` ,
ADD INDEX (  `time_insert` );
-- end uice tonghepc updated

-- uice 2012/11/26
ALTER TABLE  `property` ADD  `uid` INT NOT NULL AFTER  `company` ,
ADD  `username` VARCHAR( 255 ) NOT NULL AFTER  `uid` ,
ADD INDEX (  `uid` );
ALTER TABLE  `property` ADD  `time` INT NOT NULL;
ALTER TABLE  `property` ADD INDEX (  `time` );
-- end
-- uice air updated 2012/11/26
-- iori updated 2012/11/27


-- uice 2012/11/27
ALTER TABLE  `account` ADD  `name` VARCHAR( 255 ) NOT NULL AFTER  `id`;
ALTER TABLE  `team` ADD  `time_insert` INT NOT NULL AFTER  `username` ,
ADD INDEX (  `time_insert` );
-- end uice air updated
-- iori 2012/11/27 updated


-- uice 2012/11/30
ALTER TABLE  `people_profile` ADD INDEX (  `name` );
-- end uice air updated