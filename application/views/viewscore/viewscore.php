<div class="contentTableMenu"><? echo $menu['head']?></div>
<div class="contentTableBox">
<? $this->arrayExportTable($table,NULL,false,false,array(),true,true) ?>
<? exportTable($q_avg,$field_avg,NULL,false,false,array(),false,true) ?>
</div>
