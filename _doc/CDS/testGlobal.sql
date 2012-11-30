call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call CDS(4);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();