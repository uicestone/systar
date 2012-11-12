UPDATE  `starsys`.`affair` SET  `name` =  'cases' WHERE  `affair`.`id` =75;
UPDATE  `starsys`.`affair` SET  `add_action` =  'cases?add' WHERE  `affair`.`id` =75;
UPDATE  `group` SET affair =  'cases' WHERE affair =  'case';
update `group` set action='lists' where action='list';
update affair set add_action = replace(add_action,'?add','/add');

update `group` set action = 'stafflist' where action='staff_list';

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `is_overtime` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为假日（覆盖正常工作日），1为加班（覆盖集体假期）',
  `staff` int(11) DEFAULT NULL COMMENT 'NULL为全体行为，否则为单名员工行为',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

INSERT INTO `holidays` (`id`, `date`, `is_overtime`, `staff`) VALUES
(1, '2012-09-30', 0, NULL),
(2, '2012-10-01', 0, NULL),
(3, '2012-10-02', 0, NULL),
(4, '2012-10-03', 0, NULL),
(5, '2012-10-04', 0, NULL),
(6, '2012-10-05', 0, NULL),
(7, '2012-10-06', 0, NULL),
(8, '2012-10-07', 0, NULL),
(9, '2012-09-29', 1, NULL);
