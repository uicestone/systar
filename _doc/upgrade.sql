CREATE TABLE IF NOT EXISTS `company_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `user_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `company_config`
  ADD CONSTRAINT `company_config_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `user_config`
  ADD CONSTRAINT `user_config_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

alter table account drop summary;
drop table account_team;

ALTER TABLE  `project_profile` CHANGE  `uid`  `uid` INT( 11 ) NULL;

insert into project_profile (project,name,content)
select `case`,'包含小时',included_hours from case_fee_timing;
insert into project_profile (project,name,content)
select `case`,'合同周期',contract_cycle from case_fee_timing;
insert into project_profile (project,name,content)
select `case`,'账单周期',payment_cycle from case_fee_timing;
insert into project_profile (project,name,content)
select `case`,'账单日',bill_day from case_fee_timing;
insert into project_profile (project,name,content)
select `case`,'付款日',payment_day from case_fee_timing;
insert into project_profile (project,name,content)
select `case`,'起算日期',date_start from case_fee_timing;

drop table `case_fee_timing`;

alter table account modify name varchar(255) after id;
alter table account modify type varchar(255) after name;

ALTER TABLE  `account` ADD  `received` BOOLEAN NOT NULL AFTER  `amount`;

update project_account set receiver = null where type != '办案费';

ALTER TABLE  `project_account` CHANGE  `receiver`  `receiver` ENUM(  '承办律师',  '律所',  '' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `project_account` CHANGE  `condition`  `condition` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT  '';

update project_account set receiver = '' where receiver is null;
update project_account set `condition` = '' where `condition` is null;
update project_account set comment = '' where comment is null;

update project_account set comment = concat(receiver,`condition`,comment);

update account set received = 1;

insert into account (amount,date,received,project,project_account,comment,type,company)
select fee,pay_date,0,project,id,comment,type,1 from project_account;

ALTER TABLE  `account` DROP FOREIGN KEY  `account_ibfk_11` ;
alter table account drop username;
ALTER TABLE  `account` CHANGE  `project_account`  `account` INT( 11 ) NULL DEFAULT NULL;

create temporary table t
select * from account where received = 0;# 影响了 698 行。

update account inner join t on account.account=t.account set account.account = t.id where account.received =1 ;# 影响了 359 行。
update account set account = id where received = 0;
ALTER TABLE  `account` ADD FOREIGN KEY (  `account` ) REFERENCES  `syssh`.`account` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

drop table project_account;
update account set display = 1 , company =1;

ALTER TABLE  `account` DROP FOREIGN KEY  `account_ibfk_11` ,
ADD FOREIGN KEY (  `account` ) REFERENCES  `syssh`.`account` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE  `project_relationship` CHANGE  `relation`  `relation` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE  `project_people` CHANGE  `weight`  `weight` DECIMAL( 6, 5 ) NULL DEFAULT NULL;
update project_people set weight=1 where role = '案源人';
update project_people set weight = contribute where role ='主办律师';

ALTER TABLE  `syssh`.`project_relationship` DROP INDEX  `project` ,
ADD UNIQUE  `project-relative-relation` (  `project` );

ALTER TABLE  `account` ADD  `team` INT NULL AFTER  `project` ,
ADD INDEX (  `team` );

ALTER TABLE  `account` ADD FOREIGN KEY (  `team` ) REFERENCES  `syssh`.`team` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

update account inner join project on project.id = account.project set account.team = project.team;

update project_people set weight = 1 where weight > 0.99 and weight<1;

delete from account where amount = 0;

update account inner join account a
on account.account=a.account set account.name = a.name where account.name is null;
update account set name = '律师费' where received = 0 and name is null;

update account inner join project on project.id=account.project set account.name = concat(account.name,' ',project.name);

update account set name = '律师费' where received = 0 and name is null;

ALTER TABLE  `account` CHANGE  `received`  `received` TINYINT( 1 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `account` CHANGE  `date`  `date` DATE NULL COMMENT  '到账日期';

INSERT INTO `syssh`.`nav` (`id`, `name`, `href`, `add_href`, `parent`, `order`, `team`, `company`, `company_type`) VALUES (NULL, '小组', '#achievement/teams', NULL, '5', '', NULL, '1', NULL), (NULL, '个人', '#achievement/staff', NULL, '5', '', NULL, '1', NULL), (NULL, '我的', '#achievement/mine', NULL, '5', '', NULL, '1', NULL);

ALTER TABLE  `account` ADD  `reviewed` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `received`;

DROP TRIGGER IF EXISTS  `trig_project_num_multiautoincrease`;

insert ignore into team_people (team,people)
select id,leader from team
where leader is not null;

drop table project_num;

delete from project_label where label_name = '所内案源' and project in (select id from project where type = '行政事务');

ALTER TABLE  `schedule` ADD  `deadline` INT( 10 ) NOT NULL AFTER  `time_end` ,
ADD INDEX (  `deadline` );

INSERT INTO  `syssh`.`company_config` (
`id` ,
`company` ,
`name` ,
`value`
)
VALUES (
NULL ,  '1',  'default_page',  'achievement'
);

ALTER TABLE  `company_config` ADD INDEX (  `name` );
ALTER TABLE  `syssh`.`user_config` DROP INDEX  `user` ,
ADD INDEX  `user-name` (  `user` ,  `name` );

INSERT INTO  `syssh`.`company_config` (
`id` ,
`company` ,
`name` ,
`value`
)
VALUES (
NULL ,  '2',  'default_page',  'schedule'
);

ALTER TABLE `company` DROP `default_controller`;

insert ignore into people_label (people,label,label_name)
select people,134,'报名考生'
from team_people where team = (select id from team where name = '报名考生');

INSERT INTO  `syssh`.`nav` (

`id` ,
`name` ,
`href` ,
`add_href` ,
`parent` ,
`order` ,
`team` ,
`company` ,
`company_type`
)
VALUES (
NULL ,  '潜在客户',  '#client/potential', NULL ,  '4',  0, NULL ,  '1', NULL
);

INSERT INTO `syssh`.`company_config` (`id`, `company`, `name`, `value`) VALUES (NULL, '1', 'contact/index/search/labels', '["联系人"]'), (NULL, '2', 'contact/index/search/type', '职员');

ALTER TABLE  `project` ADD  `active` BOOLEAN NOT NULL DEFAULT FALSE AFTER  `num`;

update project set active = 1;

update project set active =0 where id in (select project from project_label where label_name = '案卷已归档');

insert into schedule_profile (schedule,name,content,uid,time)
select id,'心得',experience,uid,time from schedule where experience is not null and experience != '';
insert into schedule_profile (schedule,name,content,uid,time)
select id,'外出地点',place,uid,time from schedule where place is not null and place != '';
insert into schedule_profile (schedule,name,content,uid,time)
select id,'费用',fee,uid,time from schedule where fee is not null and fee != '';
insert into schedule_profile (schedule,name,content,uid,time)
select id,'费用用途',fee_name,uid,time from schedule where fee_name is not null and fee_name != '';

ALTER TABLE `schedule`
  DROP `experience`,
  DROP `place`,
  DROP `fee`,
  DROP `fee_name`;

ALTER TABLE  `schedule` CHANGE  `time_start`  `time_start` INT( 10 ) NULL DEFAULT NULL ,
CHANGE  `time_end`  `time_end` INT( 10 ) NULL DEFAULT NULL ,
CHANGE  `deadline`  `deadline` INT( 10 ) NULL DEFAULT NULL;

ALTER TABLE  `user` ADD UNIQUE (
`name`
);

update people set type = 'student' where type = '学生';

update people set type = 'contact' where type = '联系人';

update people set type = 'staff' where type = '职员';

update people set type = 'client' where type = '客户';

update people set type = 'contact' where type = '相对方';

ALTER TABLE  `people` ADD  `name_pinyin` VARCHAR( 255 ) NOT NULL AFTER  `name_en`;

ALTER TABLE  `people` ADD INDEX (  `name_pinyin` );

ALTER TABLE  `schedule` ADD  `in_todo_list` BOOLEAN NOT NULL DEFAULT TRUE AFTER  `completed`;

update people set type = 'student' where type = '学生';

update people set type = 'contact' where type = '联系人';

update people set type = 'staff' where type = '职员';

update people set type = 'client' where type = '客户';

update people set type = 'contact' where type = '相对方';

ALTER TABLE  `people` ADD  `name_pinyin` VARCHAR( 255 ) NOT NULL AFTER  `name_en`;

ALTER TABLE  `people` ADD INDEX (  `name_pinyin` );

ALTER TABLE  `schedule` ADD  `in_todo_list` BOOLEAN NOT NULL DEFAULT TRUE AFTER  `completed`;

ALTER TABLE  `project_document` CHANGE  `username`  `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

update people set type = 'student' where type = '学生';

update people set type = 'contact' where type = '联系人';

update people set type = 'staff' where type = '职员';

update people set type = 'client' where type = '客户';

update people set type = 'contact' where type = '相对方';

ALTER TABLE  `people` ADD  `name_pinyin` VARCHAR( 255 ) NOT NULL AFTER  `name_en`;

ALTER TABLE  `people` ADD INDEX (  `name_pinyin` );

ALTER TABLE `document` DROP `username`;
ALTER TABLE `people` DROP `username`;
ALTER TABLE `schedule` DROP `username`;
ALTER TABLE `project` DROP `username`;
ALTER TABLE `team` DROP `username`;
ALTER TABLE `people_profile` DROP `username`;
ALTER TABLE `people_relationship` DROP `username`;
ALTER TABLE `people_status` DROP `username`;
ALTER TABLE `schedule_profile` DROP `username`;
DROP TABLE log;

ALTER TABLE  `schedule_people` ADD  `deleted` BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE  `schedule_profile` DROP FOREIGN KEY  `schedule_profile_ibfk_1` ,
ADD FOREIGN KEY (  `schedule` ) REFERENCES  `syssh`.`schedule` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE  `label` ADD  `type` VARCHAR( 255 ) NULL AFTER  `name` ,
ADD INDEX (  `type` );
update label set type = 'course' where name in (select name from course);

ALTER TABLE  `school_view_score` CHANGE  `course_1`  `语文` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_2`  `数学` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_3`  `英语` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_4`  `物理` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_5`  `化学` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_6`  `生物` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_7`  `地理` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_8`  `历史` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_9`  `政治` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_10`  `信息` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_sum_3`  `3总` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_sum_5`  `5总` DECIMAL( 5, 1 ) NULL DEFAULT NULL ,
CHANGE  `course_sum_8`  `8总` DECIMAL( 5, 1 ) NULL DEFAULT NULL;

ALTER TABLE  `school_view_score` CHANGE  `rank_1`  `rank_语文` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_2`  `rank_数学` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_3`  `rank_英语` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_4`  `rank_物理` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_5`  `rank_化学` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_6`  `rank_生物` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_7`  `rank_地理` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_8`  `rank_历史` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_9`  `rank_政治` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_10`  `rank_信息` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_sum_3`  `rank_3总` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_sum_5`  `rank_5总` INT( 11 ) NULL DEFAULT NULL ,
CHANGE  `rank_sum_8`  `rank_8总` INT( 11 ) NULL DEFAULT NULL;

ALTER TABLE  `schedule` CHANGE  `time_start`  `start` INT( 10 ) NULL DEFAULT NULL ,
CHANGE  `time_end`  `end` INT( 10 ) NULL DEFAULT NULL;

ALTER TABLE  `project` CHANGE  `time_end`  `end` DATE NULL DEFAULT NULL;

ALTER TABLE  `people_status` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `people_status` DROP `level`;
ALTER TABLE `people_status` DROP `company`;
ALTER TABLE `people_status` DROP `username`;
ALTER TABLE  `people_status` CHANGE  `student`  `people` INT( 11 ) NOT NULL;
ALTER TABLE  `people_status` ADD FOREIGN KEY (  `people` ) REFERENCES  `syssh`.`people` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE;
ALTER TABLE  `people_status` ADD  `comment` TEXT NULL AFTER  `content`;
ALTER TABLE  `people_status` ADD  `team` INT NULL AFTER  `uid`;
ALTER TABLE  `people_status` ADD INDEX (  `team` );
ALTER TABLE  `people_status` ADD FOREIGN KEY (  `team` ) REFERENCES  `syssh`.`team` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

CREATE TABLE IF NOT EXISTS `message_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `document` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message` (`message`),
  KEY `document` (`document`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `message_document`
  ADD CONSTRAINT `message_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `message_document_ibfk_1` FOREIGN KEY (`message`) REFERENCES `message` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE  `schedule` CHANGE  `hours_own`  `hours_own` DECIMAL( 10, 2 ) NULL DEFAULT NULL;

update schedule set start = null where start = 0;
update schedule set end = null where end = 0;
update schedule set deadline = null where deadline = 0;

ALTER TABLE `schedule_profile` DROP `username`;

ALTER TABLE  `dialog_user` ADD  `read` BOOLEAN NOT NULL;

ALTER TABLE  `project` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'project';

ALTER TABLE  `people` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'people';
-- server updated
-- structure exported

ALTER TABLE  `log` CHANGE  `username`  `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
