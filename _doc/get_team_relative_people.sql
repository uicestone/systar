/*
*得到某个组及其子组的人员
*用法：
*CREATE TEMPORARY TABLE IF NOT EXISTS team_relative_people(
*	id INT AUTO_INCREMENT PRIMARY KEY,
*	team INT(11),
*	people INT(11)
*);
*CALL get_team_relative_people(2);
*SELECT * FROM team_relative_people;
*DROP TEMPORARY TABLE IF EXISTS team_relative_people;
*上述代码得到父组id为2的组及其子组的人员
*/
DELIMITER //
CREATE PROCEDURE get_team_relative_people(parent_id INT(11))
BEGIN
	DECLARE team_id INT(11);
	DECLARE sub_team_count INT DEFAULT 0;
	DECLARE min_id INT;

	-- 创建临时表存储子组id
	CREATE TEMPORARY TABLE IF NOT EXISTS sub_teams(
		id INT AUTO_INCREMENT PRIMARY KEY,
		sub_team INT(11)
	);
	TRUNCATE TABLE sub_teams;

	-- 初始时，子组表中只有父组id
	INSERT INTO sub_teams(sub_team) VALUES(parent_id);
	SELECT COUNT(*) INTO sub_team_count FROM sub_teams;
	-- SELECT * FROM sub_teams;

	-- 重复到子组表中没有记录为止，子组表在这里相当于一个队列
	WHILE sub_team_count>0 DO
		-- 取子组表中第一条记录对应的组id
		SELECT MIN(id) INTO min_id FROM sub_teams;
		-- SELECT min_id;
		SELECT a.sub_team INTO team_id FROM sub_teams AS a WHERE a.id=min_id;
		-- SELECT team_id;
		-- 取出组id对应的组的成员，插入到组相关人员表
		INSERT INTO team_relative_people(team,people) SELECT tp.team,tp.people FROM team_people AS tp WHERE tp.team=team_id;
		-- SELECT * FROM team_relative_people;
		-- 取出组id的子组id插入到子组表的末尾
		INSERT INTO sub_teams(sub_team) SELECT tr.team FROM team_relationship AS tr WHERE tr.relative=team_id AND tr.relation='隶属';
		-- 在子组表中删除组id对应的记录
		DELETE FROM sub_teams WHERE sub_team=team_id;
		-- SELECT * FROM sub_teams;
		-- 重新计算子组表的记录数
		SELECT COUNT(*) INTO sub_team_count FROM sub_teams;
		-- SELECT sub_team_count;
	END WHILE;
	-- 销毁子组表
	DROP TEMPORARY TABLE IF EXISTS sub_teams;
END
//
DELIMITER ;