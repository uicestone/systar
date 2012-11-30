drop procedure if exists CDS;
/*
* 针对特定关键字下的全局CDS（相关度排序）
* param query_user_id 查询者id
* e.g. 
* call init_CDS();
* insert into keywords_table values('一审');
* insert into keywords_table values('诉讼');
* insert into keywords_table values('法律');
* call CDS(4);
* select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
* call finalize_CDS();
*/
delimiter //
create procedure CDS(in query_user_id int(11))
begin
	call schedule_CDS(query_user_id,0);
	call case_CDS(query_user_id,0);
	call people_CDS(query_user_id,0);
	call team_CDS(query_user_id,0);
	call property_CDS(query_user_id,0);
	call account_CDS(query_user_id,0);
	call document_CDS(query_user_id,0);
end
//
delimiter ;