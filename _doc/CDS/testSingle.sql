call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call schedule_CDS(4,1);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();

call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call case_CDS(4,1);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();

call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call account_CDS(4,1);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();

call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call people_CDS(4,1);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();

call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call property_CDS(4,1);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();

call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call team_CDS(4,1);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();

call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call document_CDS(4,1);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
call finalize_CDS();
-- call dump_CDS();

call init_CDS();
insert into keywords_table values('一审');
insert into keywords_table values('诉讼');
insert into keywords_table values('法律');
call schedule_CDS(4,0);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;

call case_CDS(4,0);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;

call account_CDS(4,0);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;

call people_CDS(4,0);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;

call property_CDS(4,0);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;

call team_CDS(4,0);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;

call document_CDS(4,0);
select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;
-- call dump_CDS();
call finalize_CDS();
