drop procedure if exists init_CDS;
/*
* 初始化CDS
*/
delimiter //
create procedure init_CDS()
begin
	create temporary table if not exists keywords_table(
		keyword varchar(255) primary key
	);
	truncate keywords_table;

	create temporary table if not exists CD_table(
		id int(11) primary key,
		name varchar(255),
		column_name varchar(255),
		degree float default 0,
		matches int default 0,
		key degree(degree),
		key matches(matches)
	);
	truncate CD_table;

	create temporary table if not exists matches_table(
		id int(11) primary key,
		matches int
	);
	truncate matches_table;

	create temporary table if not exists alltags_table(
		id int(11) primary key,
		alltags int
	);
	truncate alltags_table;

	create temporary table if not exists matchdegree_table(
		id int(11) primary key,
		degree float default 0,
		matches int default 0
	);
	truncate matchdegree_table;

	create temporary table if not exists freshdegree_table(
		id int(11) primary key,
		degree float default 0
	);
	truncate freshdegree_table;

	create temporary table if not exists peopledegree_table(
		id int(11) primary key,
		degree float default 0
	);
	truncate peopledegree_table;
	
	create temporary table if not exists team1_table(
		id int(11) primary key
	);
	truncate team1_table;
	
	create temporary table if not exists team1_relative_team_table(
		id int(11) primary key
	);
	truncate team1_relative_team_table;
end
//
delimiter ;

drop procedure if exists finalize_CDS;
/*
* 清理CDS所产生的临时表
*/
delimiter //
create procedure finalize_CDS()
begin
drop table if exists keywords_table;
drop table if exists CD_table;
drop table if exists matches_table;
drop table if exists alltags_table;
drop table if exists matchdegree_table;
drop table if exists freshdegree_table;
drop table if exists peopledegree_table;
drop table if exists team1_table;
drop table if exists team1_relative_team_table;
end
//
delimiter ;

drop procedure if exists dump_CDS;
/*
* 显示CDS所涉及的所有临时表，用于调试
*/
delimiter //
create procedure dump_CDS()
begin
select * from keywords_table;
select * from CD_table;
select * from matches_table;
select * from alltags_table;
select * from matchdegree_table;
select * from freshdegree_table;
select * from peopledegree_table;
select * from team1_table;
select * from team1_relative_team_table;
end
//
delimiter ;