ALTER TABLE  `people` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'people';
ALTER TABLE  `people_relationship` CHANGE  `accepted`  `accepted` TINYINT( 1 ) NULL;

ALTER TABLE  `team` ADD  `open` BOOLEAN NOT NULL AFTER  `leader`;

ALTER TABLE `document_mod` DROP `id`;
ALTER TABLE  `document_mod` ADD  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

UPDATE  `syssh`.`people` SET  `num` =  'teacher' WHERE  `people`.`id` =13453;
UPDATE  `syssh`.`people` SET  `num` =  'finance' WHERE  `people`.`id` =13447;
UPDATE  `syssh`.`people` SET  `num` =  'hr' WHERE  `people`.`id` =13448;
UPDATE  `syssh`.`people` SET  `num` =  'service' WHERE  `people`.`id` =13450;
UPDATE  `syssh`.`people` SET  `num` =  'hr' WHERE  `people`.`id` =13532;

ALTER TABLE  `_` ADD  `capacity` INT NOT NULL AFTER  `people`;
update _ set capacity = intro;
UPDATE  `syssh`.`_` SET  `capacity` =  '16' WHERE  `_`.`id` =23;
ALTER TABLE  `_` ADD  `team_id` INT NOT NULL;
update _ inner join people on people.name = _.name and people.type = 'society'
set _.team_id=people.id;
update team inner join _ on team.id=_.team_id set team.leader = _.people where _.people!=0;
INSERT INTO people( name, 
TYPE ,  `character` , display, company, uid, time_insert, TIME ) 
VALUES (
'社团开课教师',  'team',  '单位', 1, 2, 8000, UNIX_TIMESTAMP( ) , UNIX_TIMESTAMP( )
);
insert into team (id,name,display,company,uid,time_insert,time)
values
(13621,'社团开课教师',1,2,8000,unix_timestamp(),unix_timestamp());
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
NULL ,  '社团',  '#society', NULL , NULL ,  '0',  '13621',  '2', NULL
);
insert into people_relationship (people,relative)
select 13621,leader from team where id in (select id from people where type = 'society');

insert into people_profile (people,name,content)
select team_id , '名额', capacity from _ where capacity > 0;

insert into people_profile (people,name,content)
select team_id , '简介', intro from _;

insert into people_profile (people,name,content)
select team_id , '地点', place from _;

insert into label (name)
values('创新社团'),('学生自主社团'),('艺体类社团');

insert into people_label (people,label,label_name)
select team_id,label.id,_.label from _ inner join label on label.name = _.label;

update team set open = 1 where id in (select id from people where type = 'society');

insert into people_profile (people,name,content)
select team_id,'状态','内部招生' from _;

ALTER TABLE  `project` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'project';

ALTER TABLE `project` DROP `username`;

ALTER TABLE  `score` ADD FOREIGN KEY (  `indicator` ) REFERENCES  `syssh`.`indicator` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

update starsys.exam set id = id + 1536 order by id desc;
update school_view_score set exam = exam + 212 where exam in (1567,1568,1569);
update starsys.exam_paper set id=id+1566 order by id desc;

insert into project (id,name,type,active,display,uid,time_insert,time,comment,company)
select exam.id,name,'exam',is_on,1,8000,unix_timestamp(),unix_timestamp(),id,2 from starsys.exam;

insert into project(id,name,type,active,display,uid,time_insert,time,comment,company)
select exam_paper.id,course.name,'exam_paper',is_scoring,1,8000,unix_timestamp(),unix_timestamp(),exam_paper.id,2 from starsys.exam_paper inner join starsys.course on course.id=exam_paper.course;

INSERT INTO  `syssh`.`project` (
`id` ,
`name` ,
`type` ,
`team` ,
`num` ,
`active` ,
`first_contact` ,
`time_contract` ,
`end` ,
`quote` ,
`display` ,
`focus` ,
`summary` ,
`company` ,
`uid` ,
`time_insert` ,
`time` ,
`comment`
)
VALUES (
'1777',  '2012第二季度互评',  'evaluation', NULL , NULL ,  '1', NULL , NULL , NULL ,  '',  '1', NULL , NULL ,  '1',  '6356',  '0',  '0', NULL
), (
'1778',  '2013第二季度互评',  'evaluation', NULL , NULL ,  '1', NULL , NULL , NULL ,  '',  '1', NULL , NULL ,  '1',  '6356',  '0',  '0', NULL
), (
'1779',  '期中',  'exam', NULL , NULL ,  '1', NULL , NULL , NULL ,  '',  '1', NULL , NULL ,  '2',  '8000',  '0',  '0', NULL
), (
'1780',  '期中',  'exam', NULL , NULL ,  '1', NULL , NULL , NULL ,  '',  '1', NULL , NULL ,  '2',  '8000',  '0',  '0', NULL
), (
'1781',  '期中',  'exam', NULL , NULL ,  '1', NULL , NULL , NULL ,  '',  '1', NULL , NULL ,  '2',  '8000',  '0',  '0', NULL
);

ALTER TABLE  `score` ADD FOREIGN KEY (  `people` ) REFERENCES  `syssh`.`people` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `score` ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `score` ADD FOREIGN KEY (  `uid` ) REFERENCES  `syssh`.`user` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `syssh`.`project_relationship` DROP INDEX  `project-relative-relation` ,
ADD UNIQUE  `project-relative-relation` (  `project` ,  `relative` ,  `relation` );

insert into project_relationship (project,relative)
select exam,id from starsys.exam_paper;

insert into project_profile (project,name,content)
select id,'学期',term from starsys.exam;

insert into project_profile (project,name,content)
select id,'学期',term from starsys.exam_paper;

update starsys.course inner join label on label.name = course.name set course.id=label.id;

insert into project_label (project,type,label,label_name)
select exam_paper.id,'学科',label.id,label.name from starsys.exam_paper inner join label on exam_paper.course=label.id;

insert into project_profile (project,name,content)
select id,'人数',students from starsys.exam_paper;

update project set first_contact=null where first_contact = '0000-00-00';
update project set time_contract=null where time_contract = '0000-00-00';
update project set end=null where end = '0000-00-00';

ALTER TABLE  `school_view_score` ADD FOREIGN KEY (  `exam` ) REFERENCES  `syssh`.`project` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `schedule_people` ADD  `enrolled` BOOLEAN NOT NULL;
ALTER TABLE  `schedule_people` ADD  `deleted` BOOLEAN NOT NULL;

insert ignore into schedule_people (schedule,people,enrolled)
select id,uid,1 from schedule where uid is not null;

update schedule_people inner join schedule on schedule.uid = schedule_people.people and schedule.id=schedule_people.schedule
set schedule_people.enrolled = 1;

ALTER TABLE  `schedule_people` ADD  `in_todo_list` BOOLEAN NOT NULL AFTER  `enrolled`;

update schedule_people inner join schedule on schedule.id = schedule_people.schedule
set schedule_people.in_todo_list = schedule.in_todo_list;

ALTER TABLE  `project_profile` DROP FOREIGN KEY  `project_profile_ibfk_1` ,
ADD FOREIGN KEY (  `project` ) REFERENCES  `syssh`.`project` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE  `project` CHANGE  `quote`  `quote` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT  '报价';
ALTER TABLE  `people` CHANGE  `name_en`  `name_en` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `people` CHANGE  `city`  `city` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- server updated

ALTER TABLE  `account` DROP FOREIGN KEY  `account_ibfk_12` ;
update account set id = id + (SELECT MAX(id)+10 FROM people) order by id desc;
update account set account = account + (SELECT MAX(id)+10 FROM people);
ALTER TABLE  `account` ADD FOREIGN KEY (  `account` ) REFERENCES  `syssh`.`account` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;
update document set id = id + (SELECT MAX(id)+10 FROM account) order by id desc;
update project set id = id + (SELECT MAX(id)+10 FROM document) order by id desc;
update schedule set id = id + (SELECT MAX(id)+10 FROM project) order by id desc;

CREATE TABLE IF NOT EXISTS `object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `team` int(11) DEFAULT NULL,
  `display` tinyint(1) NOT NULL,
  `company` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `time_insert` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`),
  KEY `company` (`company`),
  KEY `uid` (`uid`),
  KEY `time_insert` (`time_insert`),
  KEY `time` (`time`),
  KEY `name` (`name`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `object`
  ADD CONSTRAINT `object_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `object_ibfk_2` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

RENAME TABLE  `syssh`.`people_label` TO  `syssh`.`object_label` ;
RENAME TABLE  `syssh`.`people_profile` TO  `syssh`.`object_meta` ;
RENAME TABLE  `syssh`.`people_relationship` TO  `syssh`.`object_relationship` ;
RENAME TABLE  `syssh`.`people_status` TO  `syssh`.`object_status` ;
ALTER TABLE  `object_label` DROP FOREIGN KEY  `object_label_ibfk_3` ;
ALTER TABLE  `object_meta` DROP FOREIGN KEY  `object_meta_ibfk_4` ;
ALTER TABLE  `object_relationship` DROP FOREIGN KEY  `object_relationship_ibfk_1` ;
ALTER TABLE  `object_status` DROP FOREIGN KEY  `object_status_ibfk_4` ;

CREATE TABLE  `syssh`.`object_mod` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
 `object` INT( 11 ) NOT NULL ,
 `user` INT( 11 ) DEFAULT NULL ,
 `mod` TINYINT( 4 ) NOT NULL COMMENT  '1:read 2:write',
PRIMARY KEY (  `id` ) ,
UNIQUE KEY  `object-user` (  `object` ,  `user` ) ,
KEY  `user` (  `user` )
) ENGINE = INNODB DEFAULT CHARSET = utf8;

ALTER TABLE  `object_status` CHANGE  `people`  `object` INT( 11 ) NOT NULL;
ALTER TABLE  `object_relationship` CHANGE  `people`  `object` INT( 11 ) NOT NULL;
ALTER TABLE  `object_meta` CHANGE  `people`  `object` INT( 11 ) NOT NULL;
ALTER TABLE  `object_label` CHANGE  `people`  `object` INT( 11 ) NOT NULL;

insert into object (id,type,team,name,display,company,uid,time_insert,time)
select id,'account',team,name,display,company,uid,time_insert,time from account;

insert into object (id,type,name,display,company,uid,time_insert,time)
select id,'document',name,display,company,uid,time_insert,time from document;

insert into object (id,type,name,display,company,uid,time_insert,time)
select id,type,name,display,company,uid,time_insert,time from people;

insert into object (id,type,team,name,display,company,uid,time_insert,time)
select id,type,team,name,display,company,uid,time_insert,time from project;

update schedule set company = 1 where company is null;

insert into object (id,type,name,display,company,uid,time_insert,time)
select id,'schedule',name,display,company,uid,time_insert,time from schedule;

ALTER TABLE  `object_label` ADD FOREIGN KEY (  `object` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_meta` ADD FOREIGN KEY (  `object` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_relationship` ADD FOREIGN KEY (  `object` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_relationship` DROP FOREIGN KEY  `object_relationship_ibfk_4` ,
ADD FOREIGN KEY (  `relative` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_meta` ADD FOREIGN KEY (  `object` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_mod` ADD FOREIGN KEY (  `object` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_mod` ADD FOREIGN KEY (  `user` ) REFERENCES  `syssh`.`user` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `account` CHANGE  `id`  `id` INT( 11 ) NOT NULL;
ALTER TABLE  `document` CHANGE  `id`  `id` INT( 11 ) NOT NULL;
ALTER TABLE  `project` CHANGE  `id`  `id` INT( 11 ) NOT NULL;
ALTER TABLE  `people` CHANGE  `id`  `id` INT( 11 ) NOT NULL;
ALTER TABLE  `schedule` CHANGE  `id`  `id` INT( 11 ) NOT NULL;

ALTER TABLE  `account` ADD FOREIGN KEY (  `id` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `document` ADD FOREIGN KEY (  `id` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `people` ADD FOREIGN KEY (  `id` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `project` ADD FOREIGN KEY (  `id` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `schedule` ADD FOREIGN KEY (  `id` ) REFERENCES  `syssh`.`object` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_label` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
update object_label set type = null where type = '';
insert into object_label (object, label, label_name,type)
select account,label,label_name,type from account_label;
insert into object_label (object, label, label_name,type)
select document,label,label_name,type from document_label;
insert into object_label (object, label, label_name,type)
select project,label,label_name,type from project_label;
insert into object_label (object, label, label_name,type)
select schedule,label,label_name,type from schedule_label;

insert into object_meta (object,name,content,comment,uid,time)
select project,name,content,comment,uid,time from project_profile;
insert into object_meta (object,name,content,comment,uid,time)
select schedule,name,content,comment,uid,time from schedule_profile;

insert into object_relationship (object,relative,relation)
select project,relative,relation from project_relationship;

ALTER TABLE  `object_relationship` ADD  `mod` INT NOT NULL DEFAULT  '0' AFTER  `relation`;
update object_relationship set `mod` = accepted;

insert into object_relationship (object,relative,uid,time)
select project,document,uid,time from project_document;

update `project_people` set id = id + (SELECT MAX(id)+10 FROM object_relationship) order by id desc;

insert into object_relationship (id,object,relative,relation,uid,time)
select id,project,people,role,uid,time from project_people;

update `schedule_people` set id = id + (SELECT MAX(id)+10 FROM object_relationship) order by id desc;

insert into object_relationship (id,object,relative)
select id,schedule,people from schedule_people;

update object_relationship inner join schedule_people using (id)
set object_relationship.mod = deleted*1|enrolled*2|in_todo_list*4;

ALTER TABLE  `object_relationship` ADD  `weight` DOUBLE NULL AFTER  `mod` ,
ADD INDEX (  `weight` );

update object_relationship inner join project_people using (id) set object_relationship.weight=project_people.weight;

DROP TABLE `document_label`, `document_mod`,`account_label`,`project_document`, `project_label`, `project_people`, `project_profile`, `project_relationship`, `project_status`, `schedule_label`, `schedule_people`, `schedule_profile`;

insert into object_meta (object,name,content)
select id,'报价',quote
from project where quote is not null and quote != '';

insert into object_meta (object,name,content)
select id,'争议焦点',focus
from project where focus is not null and focus != '';

insert into object_meta (object,name,content)
select id,'案情简介',summary
from project where summary is not null and summary != '';

ALTER TABLE  `object_status` CHANGE  `uid`  `uid` INT( 11 ) NULL;

insert into object_status (object,name,date)
select id,'首次接洽',first_contact
from project where first_contact is not null and first_contact != '';

insert into object_status (object,name,date)
select id,'立案日期',time_contract
from project where time_contract is not null and time_contract != '';

insert into object_status (object,name,date)
select id,'结案日期',end
from project where end is not null and end != '';

ALTER TABLE  `syssh`.`object_meta` DROP INDEX  `people-name` ,
ADD INDEX  `object-name` (  `object` );

ALTER TABLE  `syssh`.`object_label` DROP INDEX  `people-label` ,
ADD UNIQUE  `object-label` (  `object` ,  `label` );

ALTER TABLE  `syssh`.`object_label` DROP INDEX  `people-type` ,
ADD UNIQUE  `object-type` (  `object` ,  `type` );

ALTER TABLE  `object_label` CHANGE  `type`  `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `project`
  DROP `first_contact`,
  DROP `time_contract`,
  DROP `end`,
  DROP `quote`,
  DROP `focus`,
  DROP `summary`;

update
school_view_score inner join course on extra_course=course.id inner join label on label.name = course.name
set extra_course=label.id;
ALTER TABLE  `staff` DROP FOREIGN KEY  `staff_ibfk_2` ;
update
staff inner join course on course=course.id inner join label on label.name = course.name
set course=label.id;

ALTER TABLE  `staff` ADD FOREIGN KEY (  `course` ) REFERENCES  `syssh`.`label` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `sessions` CHANGE  `user_data`  `user_data` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  `object_status` CHANGE  `date`  `date` DATETIME NOT NULL;

update express set id = id + (SELECT MAX(id)+10 FROM object) order by id desc;

ALTER TABLE  `object` ADD  `num` VARCHAR( 255 ) NULL AFTER  `type` ,
ADD INDEX (  `num` );

update express set company = 1;

insert into object (id,type,name,num,display,company,uid,time)
select id,'express',content,num,display,company,uid,time from express;
insert into object_meta (object,name,content,uid,time)
select id,'收件地址',destination,uid,time from express;
insert into object_meta (object,name,content,uid,time)
select id,'数量',amount,uid,time from express;
insert into object_meta (object,name,content,uid,time)
select id,'快递费',fee,uid,time from express where fee >0;
insert into object_relationship (object,relative,relation,uid,time)
select id,sender,'寄送人',uid,time from express;

drop table `express`;
drop table `course`;

RENAME TABLE  `syssh`.`label` TO  `syssh`.`tag` ;
RENAME TABLE  `syssh`.`label_relationship` TO  `syssh`.`tag_relationship` ;
RENAME TABLE  `syssh`.`object_label` TO  `syssh`.`object_tag` ;
ALTER TABLE  `object_tag` DROP FOREIGN KEY  `object_tag_ibfk_2` ;
ALTER TABLE  `object_tag` CHANGE  `label`  `tag` INT( 11 ) NOT NULL;
ALTER TABLE  `object_tag` ADD FOREIGN KEY (  `tag` ) REFERENCES  `syssh`.`tag` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;
ALTER TABLE  `object_tag` CHANGE  `label_name`  `tag_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  `account` DROP FOREIGN KEY  `account_ibfk_13` ;

ALTER TABLE  `account` DROP FOREIGN KEY  `account_ibfk_4` ;

ALTER TABLE  `account` DROP FOREIGN KEY  `account_ibfk_9` ;

ALTER TABLE  `document` DROP FOREIGN KEY  `document_ibfk_2` ;

ALTER TABLE  `document` DROP FOREIGN KEY  `document_ibfk_3` ;

ALTER TABLE  `people` DROP FOREIGN KEY  `people_ibfk_2` ;

ALTER TABLE  `people` DROP FOREIGN KEY  `people_ibfk_3` ;

ALTER TABLE  `project` DROP FOREIGN KEY  `project_ibfk_2` ;

ALTER TABLE  `project` DROP FOREIGN KEY  `project_ibfk_3` ;

ALTER TABLE  `project` DROP FOREIGN KEY  `project_ibfk_4` ;

ALTER TABLE  `schedule` DROP FOREIGN KEY  `schedule_ibfk_3` ;

ALTER TABLE  `schedule` DROP FOREIGN KEY  `schedule_ibfk_4` ;

ALTER TABLE `account`
  DROP `name`,
  DROP `type`,
  DROP `team`,
  DROP `display`,
  DROP `company`,
  DROP `uid`,
  DROP `time_insert`,
  DROP `time`;

ALTER TABLE `document`
  DROP `name`,
  DROP `type`,
  DROP `display`,
  DROP `company`,
  DROP `uid`,
  DROP `username`,
  DROP `time_insert`,
  DROP `time`;

ALTER TABLE `people`
  DROP `name`,
  DROP `type`,
  DROP `num`,
  DROP `display`,
  DROP `company`,
  DROP `uid`,
  DROP `time_insert`,
  DROP `time`;

ALTER TABLE `project`
  DROP `name`,
  DROP `type`,
  DROP `team`,
  DROP `num`,
  DROP `display`,
  DROP `company`,
  DROP `uid`,
  DROP `time_insert`,
  DROP `time`;

ALTER TABLE `schedule`
  DROP `name`,
  DROP `in_todo_list`,
  DROP `display`,
  DROP `company`,
  DROP `uid`,
  DROP `time_insert`,
  DROP `time`;

ALTER TABLE  `team` DROP FOREIGN KEY  `team_ibfk_3` ;

ALTER TABLE `team`
  DROP `display`,
  DROP `uid`,
  DROP `time_insert`,
  DROP `time`;

RENAME TABLE  `syssh`.`team` TO  `syssh`.`group` ;
ALTER TABLE  `object` DROP FOREIGN KEY  `object_ibfk_1` ;
ALTER TABLE  `object` CHANGE  `team`  `group` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE  `object` ADD FOREIGN KEY (  `group` ) REFERENCES  `syssh`.`group` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE `object_meta` DROP `username`;

ALTER TABLE  `object_mod` ADD  `uid` INT NOT NULL ,
ADD  `time` INT NOT NULL;

ALTER TABLE  `object_mod` ADD INDEX (  `uid` );
ALTER TABLE  `object_mod` ADD INDEX (  `time` );

ALTER TABLE  `object_mod` ADD FOREIGN KEY (  `uid` ) REFERENCES  `syssh`.`user` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_status` DROP FOREIGN KEY  `object_status_ibfk_5` ;

ALTER TABLE  `object_status` CHANGE  `team`  `group` INT( 11 ) NULL DEFAULT NULL;

ALTER TABLE  `object_status` ADD FOREIGN KEY (  `group` ) REFERENCES  `syssh`.`group` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `object_tag` ADD  `uid` INT NULL ,
ADD  `time` INT NOT NULL;

ALTER TABLE  `object_tag` ADD INDEX (  `uid` );

ALTER TABLE  `object_tag` ADD INDEX (  `time` );

ALTER TABLE  `object_tag` ADD FOREIGN KEY (  `uid` ) REFERENCES  `syssh`.`user` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

ALTER TABLE  `syssh`.`object_tag` DROP INDEX  `object-label` ,
ADD UNIQUE  `object-tag` (  `object` ,  `tag` );

ALTER TABLE  `syssh`.`object_tag` DROP INDEX  `label_name` ,
ADD INDEX  `tag_name` (  `tag_name` );

ALTER TABLE  `syssh`.`object_meta` DROP INDEX  `object-name` ,
ADD INDEX  `object` (  `object` );

-- `object_relationship` DROP `till`;

ALTER TABLE  `syssh`.`object_relationship` DROP INDEX  `people` ,
ADD UNIQUE  `object-relative-relation-till` (  `object` ,  `relative` ,  `relation`, `till` );

ALTER TABLE  `object_status` CHANGE  `date`  `datetime` DATETIME NOT NULL;