-- 监测是否有未设定type的领域
select * from project_label where label_name in ('公司','房产建筑','劳动人事','涉外','韩日','知识产权','婚姻家庭','诉讼','刑事行政') and type is null;

-- 监测是否有未设定领域的案件
select * from project where type='cases' and id not in (select project from project_label where type = '领域');

-- 根据业务领域确定案件小组
update 
project 
inner join project_label on project_label.project=project.id and project_label.type = '领域' 
inner join team on team.name = project_label.label_name
set project.team=team.id;

-- 根据业务领域确定帐目小组
update 
account 
inner join project on project.id=account.project
set account.team=project.team;

-- 检测没有案源类型的案件
select * from project where type = 'cases' 
	and id not in (select project from project_label where label_name in ('所内案源','个人案源'));

-- 所内案源都有接洽律师
select * from project where type='cases'
and id in (select project from project_label where label_name = '所内案源')
and id not in (select project from project_people where role = '接洽律师')

-- 校验并纠正item-label.label_name冗余
update account_label inner join label on account_label.label = label.id set account_label.label_name = label.name;
update people_label inner join label on people_label.label = label.id set people_label.label_name = label.name;
update project_label inner join label on project_label.label = label.id set project_label.label_name = label.name;
update document_label inner join label on document_label.label = label.id set document_label.label_name = label.name;
update schedule_label inner join label on schedule_label.label = label.id set schedule_label.label_name = label.name;

-- 确定个人案源的案源总和
select project,sum(weight) sum from project_people where role = '案源人'
and project in (select project from project_label where label_name='个人案源')
and project in (select project from account where received=1 and count=1 and date between '2013-09-01' and '2013-09-30')
group by project having sum > 1

-- 确定所内案源接洽总和
select project,sum(weight) sum from project_people where role = '接洽律师'
and project in (select project from project_label where label_name='所内案源')
and project in (select project from account where received=1 and count=1 and date between '2013-09-01' and '2013-09-30')
group by project having sum != 1

-- 确认已申请归档案件的实际贡献总额
select project.id,project.name,sum(weight) sum from
project left join project_people on project.id = project_people.project and project_people.role = '实际贡献'
where project.active=0 
and end between '2013-01-01' and '2013-06-30'
and project.id in (select project from account where received = 1)
group by project.id having round(sum,3) != 1 or sum is null;

-- 协办律师没有比例
select * from project_people where weight is not null and role = '协办律师';

-- 确定办案总和
select project,sum(weight) sum from project_people where role = '主办律师'
group by project having sum != 1

-- 清除添加失败的project
delete from project_document where project in (select id from project where display = 0 and name is null); 
delete from project_people where project in (select id from project where display = 0 and name is null);
delete from project where display = 0 and name is null;

-- 删除错误的标签
delete from people_label where label in (select id from label where name = '');
delete from project_label where label in (select id from label where name = '');
delete from document_label where label in (select id from label where name = '');
delete from label where name = '';

delete from people_label where label_name ='null';
delete from document_label where label_name ='null';
delete from label where name = 'null';

-- 统计所内案源创收
select amount,project.name,group_concat(people.name)
from account 
inner join project on project.id = account.project 
inner join project_label on project_label.project=account.project and project_label.label_name = '所内案源'
inner join project_people on project_people.project = account.project
inner join people on people.id = project_people.people
where account.date between '2013-01-01' and '2013-06-30' and received = 1
group by account.id
order by amount desc;

-- 将人员资料项中的电话更新到人员基本字段
update people inner join people_profile on people_profile.people=people.id and people_profile.name in ('电话','手机','固定电话')
set people.phone = people_profile.content
where people.phone is null;
update people inner join people_profile on people_profile.people=people.id and people_profile.name in ('电子邮件')
set people.email = people_profile.content
where people.email is null;

-- 人员信息导出
select
people.name `姓名`,
school.content `初中`,
phone `电话`,
address.content `地址`,
score1.content `语文`,
score2.content `数学`,
score3.content `英语`,
score4.content `理化`,
locale.content `户籍`
from
people 
left join people_profile school on school.name = '就读初中' and school.people=people.id
left join people_profile address on address.name = '联系地址' and address.people=people.id
left join people_profile score1 on score1.name = '区质管考语文成绩' and score1.people=people.id
left join people_profile score2 on score2.name = '区质管考数学成绩' and score2.people=people.id
left join people_profile score3 on score3.name = '区质管考英语成绩' and score3.people=people.id
left join people_profile score4 on score4.name = '区质管考理化成绩' and score4.people=people.id
left join people_profile locale on locale.name = '户籍情况' and locale.people=people.id
inner join people_label on people.id = people_label.people and people_label.label_name='报名考生';

-- 含有职员的组也是职员
insert ignore into staff (id)
select people from people_relationship
where people in (select id from team)
and relative in (select id from staff);

-- 含有用户的组也是用户
insert ignore into user (id,company)
select id,company from people where id in(
	select people from people_relationship
	where people in (select id from team)
	and relative in (select id from user)
);

-- 用户组下的人员都是用户
insert ignore into user (id,name,company)
select id,name,company from people where id in(
	select relative from people_relationship where people in (select id from user) and people in (select id from team) and relative not in (select id from user)
);

-- 根据人员名更新空的组名和用户名
update user inner join people using (id) set user.name = people.name where user.name is null;
update team inner join people using (id) set team.name = people.name where team.name is null;

-- 给项目成员以项目文件的读权限
insert ignore into document_mod (document,people,`mod`)
select document,people,1
from project_document inner join project_people using (project)
where project_people.people in (select id from staff);

-- 更新学生学号
update people
inner join people_relationship class_student ON class_student.relative = people.id AND (class_student.till>=CURDATE() OR class_student.till IS NULL)
inner join team ON team.id = class_student.people
inner join people people_team ON people_team.id = class_student.people
SET people.num = RIGHT((1000000 + CONCAT(people_team.num, RIGHT((100 + class_student.num),2))),6)
where people.type = 'student';

-- 到账贡献明细
create temporary table account_detail
select project.name project_name,account.date account_date,people.id people, people.name people_name,
account.amount,project_people.role,project_people.weight
from account inner join project on project.id=account.project
inner join project_people on project_people.project=account.project and role = '主办律师'
inner join people on people.id = project_people.people
where account.date between '2013-01-01' and '2013-06-30'
and account.received=1;

create temporary table account_detail_grouped
select people,people_name,role,
SUM(amount * weight) contribute
from account_detail
group by people;

select *,
ROUND(IF(contribute-1000000>0,contribute-1000000,0)*0.4
+IF(IF(contribute>1000000,1000000,contribute)-500000>0,IF(contribute>1000000,1000000,contribute)-500000,0)*0.35
+IF(IF(contribute>500000,500000,contribute)-300000>0,IF(contribute>500000,500000,contribute)-300000,0)*0.25
+IF(IF(contribute>300000,500000,contribute)-100000>0,IF(contribute>300000,300000,contribute)-100000,0)*0.15
,2) bonus
from account_detail_grouped;

-- 删除孤立消息
create temporary table t
select id from message_user m
where message not in (select message from dialog_message where dialog in (select dialog from dialog_user where user = m.user))
and m.read = 0;

delete from message_user where id  in (select id from t);

-- 给星瀚每个用户加一个系统对话
insert into dialog(company,users,uid,time)
select 1,1,id,unix_timestamp() from user where company = 1;

insert into dialog_user(dialog,user,title)
select id,uid,'系统' from dialog where company = 1 and users = 1;

-- 根据符合条件的案件创建一组消息
insert into message (content,time)
select concat('您主办的案件 <a href="#cases/',project.id,'">',project.name,'</a> 已申请归档，但实际贡献尚未输入，请核实，否则将影响结案奖金发放，谢谢配合'),unix_timestamp() -- ,project_people.people
from
project
where
project.id in (select project from project_label where label_name = '已申请归档')
and (select sum(weight) from project_people where project = project.id and role = '实际贡献') != 1

-- 向每个主办律师推送消息
create temporary table t
select 
message.id message
,project_people.people user
from
project
inner join project_people on project_people.project = project.id and role='主办律师'
inner join message on content = concat('您主办的案件 <a href="#cases/',project.id,'">',project.name,'</a> 已申请归档，但实际贡献尚未输入，请核实，否则将影响结案奖金发放，谢谢配合')
where
project.id in (select project from project_label where label_name = '已申请归档')
and (select sum(weight) from project_people where project = project.id and role = '实际贡献') != 1;

insert into message_user (message,user)
select message,user from t;

insert into dialog_message (dialog,message)
select dialog.id,t.message
from dialog inner join t on dialog.uid = t.user and dialog.users=1;

update dialog inner join t on dialog.uid = t.user and dialog.users=1 set last_message = t.message;

update project set active = 0 , end = '2013-06-30' where 
id in (select project from project_label where label_name = '通过财务审核')
and id in (select project from project_label where label_name = '通过信息审核')
and id in (select project from project_label where label_name = '通过主管审核')
and (
	id in (select project from project_label where label_name = '案卷已归档')
	OR id in (select project from project_label where label_name = '确认无实体归档')
)
and active = 1;

-- 删除生成的办案和结案奖金
delete from account_label where account in (select id from account where type in ('结案奖金','办案奖金','结案奖金储备'));

delete from account where type in ('结案奖金','办案奖金','结案奖金储备');

delete from account_label where  label_name = '奖金已生成';

delete from project_label where label_name = '结案奖金已生成';

-- 将已全额到账的账目预估日期调整为最后到账日期
create temporary table balanced
select account
from account
group by account
having sum(if(received=1,amount,0)) = sum(if(received=0,amount,0));

create temporary table last_pay
select account,date from (select * from account where received = 1 order by date desc)t group by account;

update account 
inner join last_pay using (account) 
set account.date = last_pay.date
where account.received = 0
and account.account in (select account from balanced)
and account.date > last_pay.date;

-- 计算去年到账案件的结案奖金
use starsys;
select staff.name `职员`,project.name `案件`,account.amount `创收`,case_lawyer.contribute `实际贡献`,FROM_UNIXTIME(account.time_occur,'%Y-%m-%d') `到账日期`,project.end `结案日期`
from account
inner join case_lawyer on case_lawyer.case = account.case and case_lawyer.role = '实际贡献'
inner join staff on staff.id = case_lawyer.lawyer
inner join syssh.project on project.active = 0 and project.id = account.case
where account.distributed_actual = 0
and account.time_occur <= UNIX_TIMESTAMP('2012-12-31')
order by case_lawyer.case,case_lawyer.lawyer;

-- 去年每人创收明细
select project.name project,project.active,people.name people,from_unixtime(account.time_occur,'%Y-%m-%d'),account.amount,case_lawyer.role, case_lawyer.contribute
from starsys.account inner join starsys.case_lawyer on account.case = case_lawyer.case
inner join syssh.people on people.id = case_lawyer.lawyer
inner join syssh.project on project.id = case_lawyer.case
where account.time_occur >= unix_timestamp('2012-01-01') and account.time_occur < unix_timestamp('2013-01-01');

-- 去年有到帐，目前已结案案件，未分配全
select `case`,sum(contribute) sum from starsys.case_lawyer
where `case` in (
	select `case` from starsys.account
	where account.time_occur >= unix_timestamp('2012-01-01') and account.time_occur < unix_timestamp('2013-01-01')
)
-- and `case` in (select id from syssh.project where active = 0)
group by `case`
having round(sum,3) < 0.7;

-- 今年以前有到账但尚未结案
select * from syssh.project
where active = 1
and id in (
	select `case`
	from starsys.account
	where time_occur < unix_timestamp('2013-01-01')
);

-- 将类team类人员添加为组
insert ignore into team (id,name,display,company,uid,time_insert,time)
select id,name,display,company,uid,time_insert,time
from people where type in ('team','teacher_group','course_group','classes');

-- 应收账款催收列表
SELECT project.name, MAX(account.type) AS type,
SUM(IF(account.received,account.amount,0)) AS received_amount,						SUM(IF(account.received,0,account.amount)) AS total_amount,
SUM(IF(account.received,0,account.amount)) - SUM(IF(account.received,account.amount,0)) AS receivable_amount,
MAX(IF(account.received,account.date,NULL)) AS received_date,
MAX(IF(account.received,NULL,account.date)) AS receivable_date,
lawyers.lawyers,
GROUP_CONCAT(account.comment) AS comment
from account
inner join project on project.id = account.project -- and project.active = 0
inner join (
	select project_people.project, group_concat(distinct people.name) lawyers from
        project_people inner join people on project_people.people = people.id
        inner join staff on staff.id = people.id
        group by project_people.project
)lawyers on lawyers.project = account.project
where account.date >= '2013-01-01'
group by account.account
having sum(if(received=1,amount,0)) < sum(if(received=0,amount,0));

-- 奖金总表
SELECT `people`.`name` AS people_name, `people`.`id` AS people, ROUND( SUM( account.amount * weight * content ),2 )  `bonus` 
FROM `account`
INNER JOIN  `project` ON  `project`.`id` =  `account`.`project` 
INNER JOIN  `project_label` `t_0` ON  `account`.`project` =  `t_0`.`project` 
AND t_0.label_name =  '费用已锁定'
INNER JOIN  `project_people` ON  `project_people`.`project` =  `account`.`project` 
INNER JOIN  `people` ON  `people`.`id` =  `project_people`.`people` 
INNER JOIN `project_profile` ON project_profile.project = account.project AND project_profile.name='案源系数'
WHERE  `account`.`received` =1 and `account`.count=1
AND TO_DAYS( account.date ) >= TO_DAYS(  '2013-01-01' ) 
AND TO_DAYS( account.date ) <= TO_DAYS(  '2013-06-30' ) 
AND  `account`.`count` =1
AND  `project_people`.`role` =  '案源人'
AND  `account`.`company` =1
AND  `account`.`display` =1
GROUP BY  `project_people`.`people`;

-- 奖金详单
SELECT project.name project_name, project.id project, `people`.`name` AS people_name, `people`.`id` AS people, project_people.role ,account.amount, project_people.weight, project_profile.content, account.amount * project_people.weight * project_profile.content bonus 
FROM `account`
INNER JOIN  `project` ON  `project`.`id` =  `account`.`project` 
INNER JOIN  `project_label` `t_0` ON  `account`.`project` =  `t_0`.`project` 
AND t_0.label_name =  '费用已锁定'
INNER JOIN  `project_people` ON  `project_people`.`project` =  `account`.`project` 
INNER JOIN  `people` ON  `people`.`id` =  `project_people`.`people` 
INNER JOIN `project_profile` ON project_profile.project = account.project AND project_profile.name='案源系数'
WHERE  `account`.`received` =1 and `account`.count=1
AND TO_DAYS( account.date ) >= TO_DAYS(  '2013-01-01' ) 
AND TO_DAYS( account.date ) <= TO_DAYS(  '2013-06-30' ) 
AND  `account`.`count` =1
AND  `project_people`.`role` =  '案源人'
AND  `account`.`company` =1
AND  `account`.`display` =1;