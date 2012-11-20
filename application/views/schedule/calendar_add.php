<form id="schedule">
<?if(METHOD!='ajaxedit'){?>
	<input type="text" name="name" placeholder="标题" value="<?=post('schedule/name')?>" style="width:98%" />
<?}?>
	<textarea name="content" placeholder="内容" rows="7" style="width:98%"><?=post('schedule/content')?></textarea>
	<textarea name="experience" placeholder="心得" rows="4" style="width:98%"><?=post('schedule/experience')?></textarea>
<?if(METHOD!='ajaxedit'){?>
	<label>项目：</label>
	<span>
		<?displayRadio(array(0=>'案件',1=>'所务',2=>'营销'), 'type', intval(post('schedule_extra/type')),true)?>
	</span>
<?}?>
	<span class="right">
		<?displayRadio(array(1=>'日志',0=>'提醒'), 'completed', post('schedule/completed'),true)?>
	</span>
<?if(METHOD!='ajaxedit'){?>
	<div id="caseSelectBox" class="ui-widget"><label>案件：</label><select id="combobox" name="case" style="width:97%"></select></div>
	<div id="clientSelectBox" class="ui-widget" style="display:none"><label>客户：</label><select id="combobox" name="client" disabled="disabled"></select></div>
<?}?>
	<div style="clear:right">
		<label>外出：</label><input type="text" name="place" placeholder="外出地点" />
		<input type="text" name="fee" size="5" placeholder="费用" />元：
		<input type="text" name="fee_name" placeholder="费用用途" />
	</div>
</form>
