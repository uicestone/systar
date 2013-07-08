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
-- server updated
