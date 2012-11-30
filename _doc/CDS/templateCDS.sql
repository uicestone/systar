drop procedure if exists {column}_CDS;
/*
* 针对特定关键字下的{column}栏目的CDS（相关度排序）
* param query_user_id 查询者id
* param onlythis 是否只在该栏目下匹配和排序，为0则会清除掉所使用的临时表，只保留CD_table，以便汇总其他栏目的结果
* e.g. 
* call init_CDS();
* insert into keywords_table values('一审');
* insert into keywords_table values('诉讼');
* insert into keywords_table values('法律');
* call {column}_CDS(4,1);
* select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
* call finalize_CDS();
*/
delimiter //
create procedure {column}_CDS(in query_user_id int(11),in onlythis int)
begin
	declare done int default 0; -- 循环标志
	declare item_id,creator_id int(11); -- 条目id，条目创建者id
	declare count_item int;
	-- 游标：条目id及该条目的创建者id
	declare cur cursor for select mt.id,c.uid from matches_table mt,`{column}` c where mt.id=c.id; 
	-- 处理程序：在游标中的记录获取完毕时设done=1来终止循环
	declare continue handler for sqlstate '02000' set done=1;

	-- 获得并存储匹配数
	insert into matches_table select cl.{column_label.column},count(cl.{column_label.column}) from {column_label} cl,label l,keywords_table kt where cl.label=l.id and l.name=kt.keyword group by cl.{column_label.column};
	-- 获得并存储总标签数
	insert into alltags_table select cl.{column_label.column},count(cl.{column_label.column}) from {column_label} cl,label l where cl.label=l.id group by cl.{column_label.column};
	-- 计算并存储匹配度
	insert into matchdegree_table select mt.id,mt.matches/at.alltags,mt.matches from matches_table mt,alltags_table at where mt.id=at.id;
	-- 计算并存储新鲜度
	insert into freshdegree_table select mt.id,(now()-c.time)/(now()-c.time_insert) from matches_table mt,`{column}` c where mt.id=c.id;
	-- 找出查询用户所在的组和其相关组
	insert into team1_table select tp.team from team_people tp where tp.people=query_user_id;
	insert ignore into team1_relative_team_table select tr.team from team_relationship tr,team1_table tt where tr.relative=tt.id;
	insert ignore into team1_relative_team_table select tr.relative from team_relationship tr,team1_table tt where tr.team=tt.id;
	-- 打开游标，并获取游标中的每一个记录（条目id，条目创建者id），并利用查询者id计算本人相关度
	open cur;
	fetch cur into item_id,creator_id;
	while not done do
		if query_user_id=creator_id then
			--  是本人，则本人相关度为0.4
			insert into peopledegree_table values(item_id,0.4);
		else
			-- 判断是否是相关人
			select count(*) into count_item from people_relationship pl where (pl.people=query_user_id and pl.relative=creator_id) or (pl.people=creator_id and pl.relative=query_user_id);
			if count_item>0 then
				-- 是相关人，则本人相关度为0.3
				insert into peopledegree_table values(item_id,0.3);
			else
				-- 判断是否是同组
				select count(*) into count_item from team1_table tt,team_people tp where tt.id=tp.team and tp.people=creator_id;
				if count_item>0 then
					-- 同组，则本人相关度为0.2
					insert into peopledegree_table values(item_id,0.2);
				else
					-- 判断是否是相关组
					select count(*) into count_item from team1_relative_team_table trtt,team_people tp where trtt.id=tp.team and tp.people=creator_id;
					if count_item>0 then
						-- 是相关组，则本人相关度为0.1
						insert into peopledegree_table values(item_id,0.1);
					else 
						-- 毫无关系，则本人相关度为0
						insert into peopledegree_table values(item_id,0);
					end if;
				end if;
			end if;
		end if;
		fetch cur into item_id,creator_id;
	end while;
	-- 计算相关度并，将其与其他相关信息一并存储
	insert into CD_table select mdt.id,c.name,'{column.columnName}',mdt.degree*0.7+ft.degree*0.1+pt.degree*0.2,mdt.matches from matchdegree_table mdt,freshdegree_table ft,peopledegree_table pt,`{column}` c where mdt.id=ft.id and mdt.id=pt.id and mdt.id=c.id;
	if onlythis=0 then
		-- 清空所用的临时表，以便进行全局排序
		truncate matches_table;
		truncate alltags_table;
		truncate matchdegree_table;
		truncate freshdegree_table;
		truncate peopledegree_table;
		truncate team1_table;
		truncate team1_relative_team_table;
	end if;
end
//
delimiter ;
