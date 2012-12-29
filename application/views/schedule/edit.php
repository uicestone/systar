<?=javascript('schedule_add')?>
<form method="post" enctype="multipart/form-data">
<div class="contentTableMenu">
	<div class="right">
		<? if($this->value('schedule/uid')==$this->user->id){?>
		<input type="submit" name="submit[schedule]" value="保存" />
		<? }?>
		<input type="submit" name="submit[cancel]" value="关闭" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title">
				<? if(!$this->value('schedule/case')){?>
				<?=radio(array(0=>'案件',1=>'所务',2=>'营销'),'type',$this->value('schedule_extra/type'),true)?>
				<? }else{?>
				<button type="button" onclick="javascript:window.opener.parent.focus();window.rootOpener.location.href='/cases/edit/<?=$this->value('schedule/case')?>'">查看</button>
				<? }?>
			</div>

			<? if(!$this->value('schedule/case')){?>
			<select name="schedule[case]">
				<?=options($case_array,$this->value('schedule/case'),true)?>
			</select>
			<? }else{?>
			<div class="field"><?=$this->value('schedule_extra/case_name')?></div>
			<? }?>

			<? if($this->value('schedule/client')){?>
			<div class="field"><?=$this->value('schedule_extra/client_name')?></div>
			<? }else{?>
			<select name="schedule[client]"<? if($this->value('schedule_extra/type')!=2)echo ' disabled style="display:none"'?>>
				<?=options($client_array,$this->value('schedule/client'),true);?>
			</select>
			<input type="text" name="schedule[client]" autocomplete="client"<? if($this->value('schedule_extra/type')!=2)echo ' disabled style="display:none"'?> />
			<? }?>
		</div>

		<div class="item">
			<div class="title"><label>标题：</label></div>
			<input name="schedule[name]" value="<?=$this->value('schedule/name'); ?>" type="text" />
		</div>

		<div class="item">
			<div class="title"><label>内容：</label></div>
			<textarea class="item" name="schedule[content]" rows="7"><?=$this->value('schedule/content'); ?></textarea>
		</div>

		<? if($this->input->get('completed')!==0){?>
			<? if($this->value('schedule/uid')==$this->user->id){?>
		<div class="item">
			<div class="title"><label>心得体会：</label></div>
			<textarea class="item" name="schedule[experience]" rows="5"><?=$this->value('schedule/experience'); ?></textarea>
		</div>

		<div class="item">
			<div class="title"><label>评语：</label></div>
			<textarea readonly="readonly" class="item" name="schedule[comment]" rows="5"><?=$this->value('schedule/comment'); ?></textarea>
		</div>
			<? }?>
		<? } ?>

		<div class="item">
			<div class="title"><label>时间：</label></div>
			<label>
				开始时间：
				<input type="text" name="schedule_extra[time_start]" value="<?=$this->value('schedule_extra/time_start')?>" style="width:40%" />
			</label>

			<label>
				时长：
				<input type="text" name="schedule[hours_own]" value="<? echo round(($this->value('schedule/time_end')-$this->value('schedule/time_start'))/3600,2); ?>" style="width:18%" />
			</label>

			<label><input name="schedule[all_day]" <? if($this->value('schedule/all_day')) echo 'checked';?> type="checkbox" value="1" />全天</label>
			<label><input name="schedule[completed]" <? if($this->value('schedule/completed')==1) echo 'checked="checked"';?> type="radio" value="1" />日志</label>
			<label><input name="schedule[completed]" <? if($this->value('schedule/completed')==0) echo 'checked="checked"';?> type="radio" value="0" />提醒</label>
			
		</div>

		<div class="item">
			<div class="title">费用和地点</div>
			<label>外出地点：<input name="schedule[place]" value="<?=$this->value('schedule/place');?>" type="text" style="width:30%" /></label>
			<label>产生费用：<input name="schedule[fee]" value="<?=$this->value('schedule/fee');?>" type="text" style="width:10%" />元，</label>
		  <label>费用名称：<input name="schedule[fee_name]" value="<?=$this->value('schedule/fee_name');?>" type="text" style="width:20%" /></label>
		</div>
		
		<? if($this->value('schedule/document') || $this->user->id==$this->value('schedule/uid')){ ?>
		<div class="item">
			<div class="title">相关文件</div>
			<? if($this->value('schedule/document')){ ?>
			<a href="/cases/document<?=$this->value('schedule/document')?>"><?=$this->value('case_document/name')?></a>
			<? }else{ ?>
			<input type="file" name="file" id="file" width="30%" />
			<? } ?>
			<select name="case_document[doctype]" style="width:15%">
			<?=options(array('_ENUM','case_document','doctype'),$this->value('case_document/doctype'));?>
			</select>
			<label>备注：</label><input type="text" name="case_document[comment]" value="<?=$this->value('case_document/comment') ?>" style="width:35%" />
		</div>
		<? } ?>
		
		<div class="submit">
			<? if( $this->value('schedule/uid')==$this->user->id){?>
			<input class="submit" type="submit" name="submit[schedule]" value="保存" />
			<? }?>
			<input class="submit" type="submit" name="submit[cancel]" value="关闭" />
		</div>
	</div>
</div>
</form>