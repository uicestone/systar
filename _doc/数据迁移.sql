-- 该脚本能将现master运行的starsys数据库导入新的syssh数据库中
-- 步骤如下
-- 下载最新的starsys生产环境数据，建立本地starsys数据库
-- 导入syssh-structure.sql建立新的syssh数据结构
-- 站在syssh库中运行本脚本即可

INSERT INTO syssh.affair 
(`id`, `name`, `add_action`, `add_target`, `is_on`, `display_in_nav`, `ui_name`, `order`)
SELECT `id`, `name`, `add_action`, `add_target`, `is_on`, `display_in_nav`, `ui_name`, `order`
FROM starsys.affair;

INSERT INTO syssh.group (`id`, `name`, `affair`, `action`, `display_in_nav`, `affair_ui_name`, `order`, `company`)
SELECT `id`, `name`, `affair`, `action`, `display_in_nav`, `affair_ui_name`, `order`, `company` FROM starsys.`group`;

INSERT INTO syssh.company
(`id`, `name`, `type`, `host`, `syscode`, `sysname`, `ucenter`, `default_controller`)
SELECT `id`, `name`, `type`, `host`, `syscode`, `sysname`,0, `default_controller` FROM starsys.`company`;

ALTER TABLE  starsys.`staff` DROP FOREIGN KEY  `staff_ibfk_7` ;

UPDATE starsys.user SET id = id + 3000 WHERE id < 3000;
UPDATE starsys.user SET id = id + 2000 WHERE id < 6000;

INSERT INTO syssh.people
(`id`, `name`, `type`, `id_card`, `position`, `company`, time_insert, time)
SELECT 
id,name,'职员',id_card,position,company,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
FROM starsys.staff;

INSERT IGNORE INTO syssh.people
(`id`, `name`,`company`)
SELECT id,username,company
FROM starsys.user;

INSERT INTO syssh.user
(`id`, `name`, `alias`, `password`, `group`, `lastip`, `lastlogin`, `company`)
SELECT
 `id`, `username`, `alias`, `password`, `group`, `lastip`, `lastlogin`, `company`
FROM starsys.user;

INSERT INTO syssh.course
(`id`, `name`, `chart_color`)
SELECT 
 `id`, `name`, `chart_color`
FROM starsys.course;

INSERT INTO syssh.staff
(`id`, `title`, `modulus`, `course`, `timing_fee_default`)
SELECT
 `id`, `title`, `modulus`, `course`, `timing_fee_default`
FROM starsys.staff;

INSERT IGNORE INTO syssh.people (id,`character`,`name`,`name_en`,`abbreviation`,`type`,`gender`,`id_card`,`work_for`,`position`,`birthday`,`staff`,`display`,`city`,`company`,`uid`,`username`)
SELECT id,`character`,`name`,`name_en`,`abbreviation`,`classification`,`gender`,`id_card`,`work_for`,`position`,`birthday`,`source_lawyer`,`display`,`city`,`company`,`uid`,`username`
FROM starsys.client;

INSERT INTO syssh.client_source
(`id`, `type`, `detail`, `people`)
SELECT
 `id`, `type`, `detail`, `staff`
FROM starsys.client_source;

UPDATE syssh.people INNER JOIN starsys.client USING(id) SET syssh.people.source=starsys.client.source;

INSERT INTO syssh.`case`
(`id`, `name`, `num`, `name_extra`, `first_contact`, `time_contract`, `time_end`, `quote`, `timing_fee`, `display`, `focus`, `summary`, `source`, `is_reviewed`, `type_lock`, `client_lock`, `staff_lock`, `fee_lock`, `apply_file`, `is_query`, `finance_review`, `info_review`, `manager_review`, `filed`, `company`, `uid`, `username`, time_insert, `time`, `comment`)
SELECT 
 `id`, `name`, `num`, `name_extra`, `first_contact`, `time_contract`, `time_end`, `quote`, `timing_fee`, `display`, `focus`, `summary`, `source`, `is_reviewed`, `type_lock`, `client_lock`, `lawyer_lock`, `fee_lock`, `apply_file`, `is_query`, `finance_review`, `info_review`, `manager_review`, `filed`, `company`, `uid`, `username`, `time`, `time`, `comment`
FROM starsys.case;

INSERT INTO syssh.`case_fee`
(`id`, `case`, `fee`, `type`, `receiver`, `condition`, `pay_date`, `reviewed`, `comment`, `company`, `uid`, `username`, `time`)
SELECT 
 `id`, `case`, `fee`, `type`, `receiver`, `condition`, FROM_UNIXTIME(`pay_time`,'%Y-%m-%d'), `reviewed`, `comment`, `company`, `uid`, `username`, `time`
FROM starsys.case_fee;

INSERT INTO syssh.account
(`id`, `name`, `amount`, `date`, `case`, `case_fee`, `people`, `comment`, `distributed_fixed`, `distributed_actual`, `display`, `company`, `uid`, `username`, `time`,time_insert)
SELECT
 `id`, `name`, `amount`, FROM_UNIXTIME(`time_occur`,'%Y-%m-%d'), `case`, `case_fee`, `client`, `comment`, `distributed_fixed`, `distributed_actual`, `display`, `company`, `uid`, `username`,`time`,`time`
FROM starsys.account;

INSERT INTO syssh.case_fee_timing
(`id`, `case`, `included_hours`, `contract_cycle`, `payment_cycle`, `bill_day`, `payment_day`, `date_start`, `company`, `uid`, `username`, `time`)
SELECT 
 `id`, `case`, `included_hours`, `contract_cycle`, `payment_cycle`, `bill_day`, `payment_day`, FROM_UNIXTIME(`time_start`,'%Y-%m-%d'), `company`, `uid`, `username`, `time`
FROM starsys.case_fee_timing;

--
-- 表的结构 `label`
--

CREATE TABLE IF NOT EXISTS `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=106 ;

--
-- 转存表中的数据 `label`
--

INSERT INTO `label` (`id`, `name`) VALUES
(74, '2008级'),
(75, '2009级'),
(96, 'cdr'),
(97, 'doc'),
(98, 'docx'),
(99, 'jpg'),
(76, 'K'),
(100, 'pdf'),
(101, 'rar'),
(102, 'rtf'),
(103, 'tif'),
(104, 'xls'),
(105, 'zip'),
(11, '一审'),
(20, '个人事务'),
(21, '事业单位'),
(12, '二审'),
(40, '仲裁委'),
(22, '企业'),
(70, '体育'),
(23, '侵权'),
(68, '信息'),
(77, '借读'),
(24, '公司'),
(41, '公安局'),
(42, '公证处'),
(9, '内部行政'),
(13, '再审'),
(26, '刑事'),
(87, '办案文书'),
(50, '办案费'),
(25, '劳动'),
(14, '劳动仲裁'),
(73, '劳技'),
(63, '化学'),
(78, '区推优'),
(39, '单位'),
(65, '历史'),
(27, '合同'),
(80, '名额分配'),
(51, '咨询费'),
(15, '商事仲裁'),
(66, '地理'),
(28, '婚姻'),
(1, '客户'),
(79, '市推优'),
(48, '律师费'),
(69, '心理'),
(5, '成交客户'),
(29, '房产'),
(43, '房管局'),
(16, '执行'),
(88, '接洽资料'),
(64, '政治'),
(60, '数学'),
(81, '新疆插班学生'),
(37, '新疆部'),
(36, '本部'),
(44, '检察院'),
(8, '法律顾问'),
(45, '法院'),
(4, '潜在客户'),
(46, '潜在联系人'),
(62, '物理'),
(67, '生物'),
(18, '电话咨询'),
(30, '留学'),
(2, '相对方'),
(31, '知产'),
(32, '移民'),
(89, '签约合同（扫描）'),
(52, '红包'),
(82, '统招'),
(33, '继承'),
(17, '网上咨询'),
(71, '美术'),
(3, '联系人'),
(90, '聘请委托文书'),
(83, '自主招生'),
(38, '自然人'),
(84, '自荐'),
(61, '英语'),
(34, '行政'),
(91, '行政文书'),
(92, '裁判文书'),
(93, '证据材料'),
(10, '诉前争议解决'),
(6, '诉讼'),
(59, '语文'),
(95, '身份材料'),
(94, '身份资料'),
(85, '重读（留级）'),
(35, '金融'),
(47, '鉴定评估机构'),
(86, '零志愿'),
(7, '非诉讼'),
(19, '面谈咨询'),
(72, '音乐'),
(49, '顾问费'),
(53, '预初'),
(54, '高一'),
(56, '高三'),
(55, '高二');

INSERT INTO syssh.case_label
(`case`,label,type,label_name)
SELECT 
 case.id,label.id,'分类',label.name
FROM starsys.case INNER JOIN syssh.label
WHERE starsys.case.classification = syssh.label.name
 AND starsys.case.classification IS NOT NULL
 AND starsys.case.classification <> '';

INSERT IGNORE INTO syssh.case_label
(`case`,label,type,label_name)
SELECT 
 case.id,label.id,'领域',label.name
FROM starsys.case INNER JOIN syssh.label
WHERE starsys.case.type = syssh.label.name
 AND starsys.case.type IS NOT NULL
 AND starsys.case.type <> '';

INSERT INTO syssh.case_label
(`case`,label,type,label_name)
SELECT 
 case.id,label.id,'阶段',label.name
FROM starsys.case INNER JOIN syssh.label
WHERE starsys.case.stage = syssh.label.name
 AND starsys.case.stage IS NOT NULL
 AND starsys.case.stage <> '';

INSERT INTO syssh.case_label
(`case`,label,type,label_name)
SELECT 
 case.id,label.id,'咨询方式',label.name
FROM starsys.case INNER JOIN syssh.label
WHERE starsys.case.query_type = syssh.label.name
 AND starsys.case.query_type IS NOT NULL
 AND starsys.case.query_type <> '';

INSERT INTO case_num
(`id`, `case`, `classification_code`, `type_code`, `year_code`, `number`, `company`, `uid`, `username`, `time`)
SELECT 
 `id`, `case`, `classification_code`, `type_code`, `year_code`, `number`, `company`, `uid`, `username`, `time`
FROM starsys.case_num;

INSERT INTO case_people
(`id`, `case`, `people`, `type`, `role`,  `company`, `uid`, `username`, `time`)
SELECT
 `id`, `case`, `client`, '客户', `role`, `company`, `uid`, `username`, `time`
FROM starsys.case_client;

INSERT INTO case_people
(`case`, `people`, `type`, `role`, `hourly_fee`, `contribute`, `company`, `uid`, `username`, `time`)
SELECT
 `case`, `lawyer`, '律师', `role`, `hourly_fee`, `contribute`, `company`, `uid`, `username`, `time`
FROM starsys.case_lawyer;

INSERT INTO position
(`id`, `name`, `ui_name`, `company`)
SELECT `id`, `name`, `ui_name`, `company`
FROM starsys.position;

INSERT INTO `evaluation_indicator` 
(`id`, `name`, `weight`, `position`, `critic`, `company`)
SELECT
 `id`, `name`, `weight`, `position`, `critic`, `company`
FROM starsys.`evaluation_indicator`;

INSERT INTO `evaluation_score`
(`id`, `staff`, `indicator`, `score`, `comment`, `quarter`, `company`, `uid`, `username`, `time`)
SELECT `id`, `staff`, `indicator`, `score`, `comment`, `quarter`, `company`, `uid`, `username`, `time`
FROM starsys.evaluation_score;

INSERT INTO `exam` (`id`, `name`, `grade`, `depart`, `is_on`, `seat_allocated`, `term`)
SELECT `id`, `name`, `grade`, `depart`, `is_on`, `seat_allocated`, `term`
FROM starsys.exam;

INSERT INTO `exam_paper`
(`id`, `is_scoring`, `teacher_group`, `exam`, `course`, `is_extra_course`, `students`, `term`, `comment`)
SELECT
 `id`, `is_scoring`, `teacher_group`, `exam`, `course`, `is_extra_course`, `students`, `term`, `comment`
FROM starsys.exam_paper;

INSERT INTO `exam_part`
(`id`, `exam_paper`, `name`, `discription`, `full_score`)
SELECT
 `id`, `exam_paper`, `name`, `discription`, `full_score`
FROM starsys.exam_part;

INSERT INTO `exam_room`
(`id`, `name`, `capacity`, `depart`, `grade`)
SELECT
 `id`, `name`, `capacity`, `depart`, `grade`
FROM starsys.exam_room;

INSERT INTO `exam_student`
(`id`, `exam`, `student`, `room`, `seat`, `depart`, `extra_course`, `time`, `rand`)
SELECT
 `id`, `exam`, `student`, `room`, `seat`, `depart`, `extra_course`, `time`, `rand`
FROM starsys.exam_student;

INSERT INTO `express`(
`id`, `sender`, `num`, `destination`, `time_send`, `fee`, `content`, `amount`, `comment`, `display`, `company`, `uid`, `username`, `time`
)
SELECT
`id`, `sender`, `num`, `destination`, FROM_UNIXTIME(`time_send`,'%Y-%m-%d %H:%i:%s'), `fee`, `content`, `amount`, `comment`, `display`, `company`, `uid`, `username`, `time`
FROM starsys.express;

INSERT INTO `document`(
`id`, `case`, `people`, `name`, `extname`, `size`, `comment`, `display`, `company`, `uid`, `username`,`time`,time_insert
)
SELECT
`id`, `case`, IF(`client`=0,NULL,client), `name`, `type`, `size`, `comment`, `display`, `company`, `uid`, `username`, `time`,`time`
FROM starsys.case_document;

INSERT INTO syssh.document_label
(document,label,type,label_name)
SELECT 
 case_document.id,label.id,'类型',label.name
FROM starsys.case_document INNER JOIN syssh.label
WHERE starsys.case_document.doctype = syssh.label.name
 AND starsys.case_document.doctype IS NOT NULL
 AND starsys.case_document.doctype <> '';

INSERT INTO `holidays`
(`id`, `date`, `is_overtime`, `staff`)
SELECT 
`id`, `date`, `is_overtime`, `staff`
FROM starsys.holidays;

INSERT INTO `idcard_region`
(`id`, `num`, `name`)
SELECT
`id`, `num`, `name`
FROM
starsys.idcard_region;

INSERT INTO news 
(`id`, `title`, `content`, `display`, `company`, `uid`, `username`, `time`,time_insert)
SELECT 
 `id`, `title`, `content`, `display`, `company`, `uid`, `username`, `time`,`time`
 FROM starsys.news;

INSERT INTO syssh.people_label
(people,label,type,label_name)
SELECT 
 client.id,label.id,'类型',label.name
FROM starsys.client INNER JOIN syssh.label
WHERE starsys.client.type = syssh.label.name
 AND starsys.client.type IS NOT NULL
 AND starsys.client.type <> '';

UPDATE syssh.`people` INNER JOIN starsys.student USING(id)
SET syssh.`people`.name=starsys.student.name,
syssh.`people`.type='学生',
syssh.`people`.gender=starsys.student.gender,
syssh.`people`.race=starsys.student.race,
syssh.`people`.id_card=starsys.student.id_card,
syssh.`people`.birthday=starsys.student.birthday,
syssh.`people`.display=starsys.student.display,
syssh.`people`.company=starsys.student.company,
syssh.`people`.uid=starsys.student.uid,
syssh.`people`.username=starsys.student.username,
syssh.`people`.time=starsys.student.time;

INSERT INTO syssh.people_label
(people,label,type,label_name)
SELECT 
 student.id,label.id,'生源',label.name
FROM starsys.student INNER JOIN syssh.label
WHERE starsys.student.type = syssh.label.name
 AND starsys.student.type IS NOT NULL
 AND starsys.student.type <> '';

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'junior_school',junior_school
FROM starsys.student
WHERE junior_school<>''
 AND junior_school IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'youth_league',youth_league
FROM starsys.student
WHERE youth_league<>''
 AND youth_league IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'resident',resident
FROM starsys.student
WHERE resident<>''
 AND resident IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'dormitory',dormitory
FROM starsys.student
WHERE dormitory<>''
 AND dormitory IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'email',email
FROM starsys.student
WHERE email<>''
 AND email IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'phone', phone
FROM starsys.student
WHERE phone <>''
 AND phone IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'mobile', mobile
FROM starsys.student
WHERE mobile <>''
 AND mobile IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'address', address
FROM starsys.student
WHERE address <>''
 AND address IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'neighborhood_committees', neighborhood_committees
FROM starsys.student
WHERE neighborhood_committees <>''
 AND neighborhood_committees IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'bank_account', bank_account
FROM starsys.student
WHERE bank_account <>''
 AND bank_account IS NOT NULL;

INSERT INTO `people_profile` 
(`people`, `name`, `content`)
SELECT id,'disease_history', disease_history
FROM starsys.student
WHERE disease_history <>''
 AND disease_history IS NOT NULL;

INSERT INTO `people_relationship`
(`people`, `relative`, `relation`,`is_default_contact`, `uid`, `username`, `time`)
SELECT client_left,client_right,role,is_default_contact,uid,username,time
FROM starsys.client_client;

UPDATE people_relationship SET `relation`=NULL WHERE relation='其他';

INSERT INTO people_relationship
(`people`,relative,relation)
SELECT staff,manager,'主管'
FROM starsys.manager_staff;

INSERT INTO schedule
(`id`, `name`, `experience`, `content`, `place`, `fee`, `fee_name`, `time_start`, `time_end`, `hours_own`, `hours_checked`, `hours_bill`, `all_day`, `completed`, `case`, `people`, `document`, `display`, `company`, `uid`, `username`,  `time`, time_insert, `comment`)
SELECT 
 `id`, `name`, `experience`, `content`, `place`, `fee`, `fee_name`, `time_start`, `time_end`, `hours_own`, `hours_checked`, `hours_bill`, `all_day`, `completed`, `case`, `client`, `document`, `display`, `company`, `uid`, `username`, `time`, `time`, `comment`
FROM starsys.schedule;

INSERT INTO score 
(`id`, `student`, `exam`, `exam_paper`, `exam_part`, `score`, `is_absent`, `rank`, `uid`, `username`, `time`, `comment`)
SELECT 
`id`, `student`, `exam`, `exam_paper`, `exam_part`, `score`, `is_absent`, `rank`, `scorer`, `scorer_username`, `time`, `comment`
FROM starsys.score;

INSERT INTO student_comment
(`id`, `title`, `content`, `student`, `reply_to`, `company`, `uid`, `username`, `time`)
SELECT * FROM starsys.student_comment;

INSERT INTO team
(`type`, `num`, `name`, `leader`, `extra_course`, `display`, `company`, `uid`, `username`,`time`,time_insert)
SELECT
'班级',`id`,`name`,`class_teacher`,`extra_course`,`display`,company,uid,username,`time`,0
FROM starsys.class;

INSERT INTO team
(`type`, `num`, `name`,time_insert)
SELECT
'年级',`id`,`name`,0
FROM starsys.grade;

INSERT INTO team (type,name,company,time_insert) values
('部门','本部',2,0),
('部门','新疆部',2,0);

INSERT INTO team 
(`type`, `name`, `leader`,extra_course,`company`,time_insert)
SELECT '备课组',name,leader,course,2,0 FROM starsys.staff_group;

INSERT INTO team 
(type,name,leader,company,time_insert)
SELECT '教研组', name,leader,2,0
FROM starsys.teacher_course_group;

INSERT INTO people_profile
(`people`, `name`, `content`, `comment`, `uid`, `username`, `time`)
SELECT
 client,type,content,comment,uid,username,time
FROM starsys.client_contact;

-- TODO 尚未导入团队关系和团队－人员关系

INSERT INTO people_profile
(`people`, `name`, `content`, `comment`, `uid`, `username`, `time`)
SELECT
 client,type,content,comment,uid,username,`time`
FROM starsys.client_contact;

INSERT INTO `label_relationship` (`id`, `label`, `relative`, `relation`) VALUES
(1, 1, 4, NULL),
(2, 1, 5, NULL),
(3, 3, 40, NULL),
(4, 3, 41, NULL),
(5, 3, 42, NULL),
(6, 3, 43, NULL),
(7, 3, 44, NULL),
(8, 3, 45, NULL),
(9, 3, 45, NULL),
(10, 3, 46, NULL),
(11, 3, 47, NULL);

UPDATE  `syssh`.`affair` SET  `add_action` = NULL WHERE  `affair`.`id` =70;
-- uice 1/21

UPDATE `people` SET display=1 WHERE type='职员';
-- uice 1/24

UPDATE  `syssh`.`affair` SET  `add_action` =  'client/add' WHERE  `affair`.`id` =80;
