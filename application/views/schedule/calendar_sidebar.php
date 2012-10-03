<? exportTable($q_news,$field_news,NULL,false,false)?>
<? foreach($sidebar_table as $sidebar_table_single){?>
<div>
<? arrayExportTable($sidebar_table_single,NULL,false,false)?>
</div>
<? }?>