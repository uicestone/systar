<? javascript('schedule_add')?>
<form method="post" enctype="multipart/form-data">
<div class="contentTableMenu">
	<div class="right">
		<? if(post('schedule/uid')==$_SESSION['id']){?>
		<input type="submit" name="submit[schedule]" value="保存" />
		<? }?>
		<input type="submit" name="submit[cancel]" value="关闭" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title">
				<? if(!post('schedule/case')){?>
				<? displayRadio(array(0=>'案件',1=>'所务',2=>'营销'),'type',post('schedule_extra/type'),true)?>
				<? }else{?>
				<button type="button" onclick="javascript:window.opener.parent.focus();window.rootOpener.location.href='case?edit=<? displayPost('schedule/case')?>'">查看</button>
				<? }?>
			</div>

			<? if(!post('schedule/case')){?>
			<select name="schedule[case]">
				<? displayOption($case_array,post('schedule/case'),true)?>
			</select>
			<? }else{?>
			<div class="field"><? displayPost('schedule_extra/case_name')?></div>
			<? }?>

			<? if(post('schedule/client')){?>
			<div class="field"><? displayPost('schedule_extra/client_name')?></div>
			<? }else{?>
			<select name="schedule[client]"<? if(post('schedule_extra/type')!=2)echo ' disabled="disabled" style="display:none"'?>>
				<? displayOption($client_array,post('schedule/client'),true);?>
			</select>
			<input type="text" name="schedule[client]" autocomplete="client"<? if(post('schedule_extra/type')!=2)echo ' disabled="disabled" style="display:none"'?> />
			<? }?>
		</div>

		<div class="item">
			<div class="title"><label>标题：</label></div>
			<input name="schedule[name]" value="<? displayPost('schedule/name'); ?>" type="text" />
		</div>

		<div class="item">
			<div class="title"><label>内容：</label></div>
			<textarea class="item" name="schedule[content]" rows="7"><? displayPost('schedule/content'); ?></textarea>
		</div>

		<? if(!got('completed',0)){?>
			<? if(post('schedule/uid')==$_SESSION['id']){?>
		<div class="item">
			<div class="title"><label>心得体会：</label></div>
			<textarea class="item" name="schedule[experience]" rows="5"><? displayPost('schedule/experience'); ?></textarea>
		</div>

		<div class="item">
			<div class="title"><label>评语：</label></div>
			<textarea readonly="readonly" class="item" name="schedule[comment]" rows="5"><? displayPost('schedule/comment'); ?></textarea>
		</div>
			<? }?>
		<? } ?>

		<div class="item">
			<div class="title"><label>时间：</label></div>
			<label>
				开始时间：
				<input type="text" name="schedule[time_start]" value="<? displayPost('schedule/time_start',true,'Y-m-d H:i:s')?>" style="width:40%" />
			</label>

			<label>
				时长：
				<input type="text" name="schedule[hours_own]" value="<? echo round((post('schedule/time_end')-post('schedule/time_start'))/3600,2); ?>" style="width:18%" />
			</label>

			<label><input name="schedule[all_day]" <? if(post('schedule/all_day')) echo 'checked';?> type="checkbox" value="1" />全天</label>
			<label><input name="schedule[completed]" <? if(post('schedule/completed')==1) echo 'checked="checked"';?> type="radio" value="1" />日志</label>
			<label><input name="schedule[completed]" <? if(post('schedule/completed')==0) echo 'checked="checked"';?> type="radio" value="0" />提醒</label>
			
		</div>

		<div class="item">
			<div class="title">费用和地点</div>
			<label>外出地点：<input name="schedule[place]" value="<? displayPost('schedule/place');?>" type="text" style="width:30%" /></label>
			<label>产生费用：<input name="schedule[fee]" value="<? displayPost('schedule/fee');?>" type="text" style="width:10%" />元，</label>
		  <label>费用名称：<input name="schedule[fee_name]" value="<? displayPost('schedule/fee_name');?>" type="text" style="width:20%" /></label>
		</div>
		
		<!--<? if(post('schedule/document') || $_SESSION['id']==post('schedule/uid')){ ?>-->
		<div class="item">
			<div class="title">相关文件</div>
			<!--<? if(post('schedule/document')){ ?>-->
			<a href="case?document=<? echo post('schedule/document') ?>"><? echo post('case_document/name') ?></a>
			<!--<? }else{ ?>-->
			<input type="file" name="file" id="file" width="30%" />
			<!--<? } ?>-->
			<select name="case_document[doctype]" style="width:15%">
			<? displayOption(array('_ENUM','case_document','doctype'),post('case_document/doctype'));?>
			</select>
			<label>备注：</label><input type="text" name="case_document[comment]" value="<? displayPost('case_document/comment') ?>" style="width:35%" />
		</div>
		<!--<? } ?>-->
		
		<div class="submit">
			<? if( post('schedule/uid')==$_SESSION['id']){?>
			<input class="submit" type="submit" name="submit[schedule]" value="保存" />
			<? }?>
			<input class="submit" type="submit" name="submit[cancel]" value="关闭" />
		</div>
	</div>
</div>
</form>