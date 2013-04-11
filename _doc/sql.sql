-- 根据案下案源人是否存在确定案件案源标签
delete from project_label where label_name = '所内案源' and project in (select project from project_people where role='案源人');
insert ignore into project_label (project,label,label_name)
select id,138,'个人案源' from project where id in (select project from project_people where role = '案源人');

-- 个人案源接洽律师无配比
update project_people set weight = null where role = '接洽律师' and project in (select project from project_label where label_name = '个人案源');

-- 监测是否有未设定type的领域
select * from project_label where label_name in ('公司','房产建筑','劳动人事','涉外','韩日','知识产权','婚姻家庭','诉讼','刑事行政') and type is null;

-- 监测是否有未设定领域的案件
select * from project where id not in (select project from project_label where type = '领域')
and type = '业务' and
id in (select project from project_label where label_name = '案件');

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

-- 确定个人案源的案源总和
select sum(weight) sum from project_people where role = '案源人' group by project having sum!=1

-- 个人案源接洽无比例
update project_people set weight = null where 
project in (select project from project_label where label_name='个人案源')
and role = '接洽律师';

-- 所内案源都有接洽律师
select * from project where type='业务'
and id in (select project from project_label where label_name = '案件')
and id in (select project from project_label where label_name = '所内案源')
and id not in (select project from project_people where role = '接洽律师')

delete from project_label where label_name = '个人案源' and project not in (select project from project_people where role = '案源人');
insert ignore into project_label (project,label,label_name)
select id,139,'所内案源' from project where id not in (select project from project_people where role = '案源人');

-- 校验并纠正item-label.label_name冗余
update account_label inner join label on account_label.label = label.id set account_label.label_name = label.name;
update people_label inner join label on people_label.label = label.id set people_label.label_name = label.name;
update project_label inner join label on project_label.label = label.id set project_label.label_name = label.name;
update team_label inner join label on team_label.label = label.id set team_label.label_name = label.name;
update document_label inner join label on document_label.label = label.id set document_label.label_name = label.name;
update schedule_label inner join label on schedule_label.label = label.id set schedule_label.label_name = label.name;

-- 确定个人案源的案源总和
select project,sum(weight) sum from project_people where role = '案源人'
and project in (select project from project_label where label_name='个人案源')
group by project having sum != 1

-- 确定个人案源的案源总和
select project,sum(weight) sum from project_people where role = '接洽律师'
and project in (select project from project_label where label_name='所内案源')
group by project having sum != 1

-- 监测同案同人同时为主办和协办的错误
select count(*) count from project_people where role in ('主办律师','协办律师') group by project,people having count!=1;

-- 确定办案总和
select project,sum(weight) sum from project_people where role in ('主办律师','协办律师')
group by project having sum != 1

-- 所内案源多人接洽平摊
update project_people inner join(
	select project,count(*) count,sum(weight) sum from project_people where role = '接洽律师'
	and project in (select project from project_label where label_name='所内案源')
	group by project having sum != 1
)project_peoplecount
using (project)
set project_people.weight = 1/project_peoplecount.count where project_people.role = '接洽律师'

-- 清除添加失败的project
delete from project_people where project in (select id from project where display = 0 and name is null);
delete from project where display = 0 and name is null;

delete from people_label where label_name ='';
delete from project_label where label_name ='';
delete from label where name = '';

-- 对于没有督办人的案件设置默认督办人
insert ignore into project_people (project,people,role)
select id,6356,'督办人' from project where id in (select project from project_label where label_name = '案件');

-- 将人员资料项中的电话更新到人员基本字段
update people inner join people_profile on people_profile.people=people.id and people_profile.name in ('电话','手机','固定电话')
set people.phone = people_profile.content;
update people inner join people_profile on people_profile.people=people.id and people_profile.name in ('电子邮件')
set people.email = people_profile.content;
