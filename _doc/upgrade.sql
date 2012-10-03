UPDATE  `starsys`.`affair` SET  `name` =  'cases' WHERE  `affair`.`id` =75;
UPDATE  `starsys`.`affair` SET  `add_action` =  'cases?add' WHERE  `affair`.`id` =75;
UPDATE  `group` SET affair =  'cases' WHERE affair =  'case';
update `group` set action='lists' where action='list';
