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
-- structure exported
-- server updated

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
-- server updated
