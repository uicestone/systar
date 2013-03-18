delete from permission where ui_name = '其他案件';

delete from permission where controller = 'contact';

INSERT INTO  `syssh`.`permission` (
`id` ,
`group` ,
`controller` ,
`method` ,
`display_in_nav` ,
`ui_name` ,
`discription` ,
`order` ,
`company`
)
VALUES (
NULL ,  'lawyer',  'contact',  '',  '1',  '', NULL ,  '0',  '1'
);
insert into label (name) value ('已申请归档'),('等待立案审核'),('在办'),('通过财务审核'),('通过信息审核'),('案卷已归档'),('职员已锁定'),('客户已锁定'),('费用已锁定'),('已全额到账'),('类型已锁定'),('通过主管审核'),('案件'),('咨询');

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '已申请归档'
where case.apply_file=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '等待立案审核'
where case.is_reviewed=0 AND is_query=0;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '通过财务审核'
where case.finance_review=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '通过信息审核'
where case.info_review=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '案卷已归档'
where case.filed=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '职员已锁定'
where case.staff_lock=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '客户已锁定'
where case.client_lock=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '费用已锁定'
where case.fee_lock=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '类型已锁定'
where case.type_lock=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '通过主管审核'
where case.manager_review=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '咨询'
where case.is_query=1;

INSERT INTO case_label (`case`,label,label_name)
SELECT case.id,label.id,label.name FROM
`case` LEFT JOIN label ON label.name = '案件'
where case.is_query=0;

UPDATE `case` SET first_contact=NULL where first_contact = '0000-00-00';# MySQL 返回的查询结果为空 (即零行)。

UPDATE `case` SET time_contract=NULL where time_contract = '0000-00-00';# 影响了 10 行。

UPDATE `case` SET time_end=NULL where time_end = '0000-00-00';# 影响了 10 行。