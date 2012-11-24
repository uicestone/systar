-- uice 2012/11/24
ALTER TABLE  `user` CHANGE  `username`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- end


-- uice 2012/11/24 -2
ALTER TABLE  `team` DROP  `grade` ,
DROP  `depart` ,
DROP  `course_group` ,
DROP  `course` ;

-- 调换team_people表中team和people顺序
ALTER TABLE  `team_people` ADD  `people1` INT NOT NULL AFTER  `team`;
ALTER TABLE  `team_people` DROP FOREIGN KEY  `team_people_ibfk_1` ;
ALTER TABLE  `team_people` CHANGE  `people1`  `people` INT( 11 ) NOT NULL;
ALTER TABLE  `team_people` ADD INDEX (  `people` );
ALTER TABLE  `team_people` ADD FOREIGN KEY (  `people` ) REFERENCES  `syssh`.`people` (
`id`
) ON DELETE NO ACTION ON UPDATE CASCADE ;

-- 一些测试数据供理解
--
-- 转存表中的数据 `case`
--

INSERT INTO `case` (`id`, `name`, `num`, `name_extra`, `first_contact`, `time_contract`, `time_end`, `quote`, `timing_fee`, `display`, `focus`, `summary`, `source`, `is_reviewed`, `type_lock`, `client_lock`, `lawyer_lock`, `fee_lock`, `apply_file`, `is_query`, `finance_review`, `info_review`, `manager_review`, `filed`, `company`, `uid`, `username`, `time`, `comment`) VALUES
(1, '第一个客户的项目（案件）', '系统0001号', '', '2012-11-01', '2012-11-01', NULL, '', 0, 1, '谈生意谈的不开心，要打官司', NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 2, '律所用户', 0, NULL);

--
-- 转存表中的数据 `people`
--

INSERT INTO `people` (`id`, `character`, `name`, `name_en`, `abbreviation`, `gender`, `id_card`, `work_for`, `position`, `birthday`, `source`, `staff`, `display`, `city`, `race`, `company`, `uid`, `username`, `time`, `comment`) VALUES
(1, '自然人', '学校用户', 'teacher', '学校用户', '男', NULL, '', NULL, '1990-09-24', NULL, NULL, 1, '', NULL, 2, NULL, '', 0, NULL),
(2, '自然人', '律所用户', 'lawyer', '律所用户', '男', NULL, '', NULL, '1991-09-22', NULL, NULL, 2, '', NULL, 1, NULL, '', 0, NULL),
(4, '自然人', '第一个客户', 'first client', '首位客户', '男', NULL, '', NULL, NULL, NULL, 2, 1, '', NULL, 1, 2, '律所用户', 0, NULL),
(5, '自然人', '第一个学生', 'first student', '首位学生', '男', NULL, '', NULL, NULL, NULL, 1, 1, '', NULL, 2, 1, '学校用户', 0, NULL);

--
-- 转存表中的数据 `people_profile`
--

INSERT INTO `people_profile` (`id`, `people`, `name`, `content`, `comment`, `uid`, `username`, `time`) VALUES
(1, 4, '手机', '1381235678', '一个不真实的手机', 2, '律所用户', 0);

--
-- 转存表中的数据 `people_relationship`
--

INSERT INTO `people_relationship` (`id`, `people`, `relative`, `relation`, `is_default_contact`, `company`, `uid`, `username`, `time`) VALUES
(2, 2, 4, '朋友', NULL, 0, 2, '律所用户', 0);

--
-- 转存表中的数据 `staff`
--

INSERT INTO `staff` (`id`, `title`, `modulus`, `course`, `timing_fee_default`) VALUES
(1, '中高', '0.00', 1, '0.00'),
(2, NULL, '1.00', NULL, '1200.00');

--
-- 转存表中的数据 `team`
--

INSERT INTO `team` (`id`, `type`, `name`, `leader`, `extra_course`, `display`, `company`, `uid`, `username`, `time`) VALUES
(1, 'class', '高一（1）班', 1, NULL, 1, 2, 1, '学校用户', NULL),
(2, 'grade', '高一年级组', 1, NULL, 1, 2, 1, '学校用户', NULL),
(3, 'depart', '本部', 1, NULL, 1, 2, 1, '学校用户', NULL);

--
-- 转存表中的数据 `team_people`
--

INSERT INTO `team_people` (`id`, `team`, `people`, `relation`, `num_in_class`, `position`, `term`, `time`) VALUES
(1, 1, 5, '就读', 1, '班长', '12-1', 0);

--
-- 转存表中的数据 `team_relationship`
--

INSERT INTO `team_relationship` (`id`, `team`, `relative`, `relation`) VALUES
(1, 2, 1, '隶属'),
(2, 3, 1, '隶属');

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `name`, `alias`, `password`, `group`, `lastip`, `lastlogin`, `company`) VALUES
(1, '学校用户', 'teacher', '123', 'teacher', NULL, NULL, 2),
(2, '律所用户', 'lawyer', '123', 'lawyer', '127.0.0.1', 1353726264, 1);
-- end