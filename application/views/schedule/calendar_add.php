<textarea type="text" name="content" placeholder="日程概要" class="text" <?if($mode=='view'){?>disabled="disabled"<?}?>><?=$this->value('schedule/content')?></textarea>
<input name="case" value="<?=$this->value('schedule/case')?>" placeholder="相关项目" class="text" autocomplete-model="cases" <?if($mode=='view'){?>disabled="disabled"<?}?>/>
<input name="people" value="<?=$this->value('schedule/people')?>" placeholder="相关人员" class="text" autocomplete-model="people" <?if($mode=='view'){?>disabled="disabled"<?}?>/>
<div class="item" style="display: none">
    <div>&nbsp;外出地点：<input type="text" class="text" name="schedule[place]" value="<?=$this->value('schedule/place')?>" <?if($mode=='view'){?>disabled="disabled"<?}?> style="width:60%"/></div>
    <div>&nbsp;费用名称：<input class="text" name="schedule[fee_name]" value="<?=$this->value('schedule/fee_name')?>" <?if($mode=='view'){?>disabled="disabled"<?}?> style="width:60%"/></div>
    <div>&nbsp;产生费用：<input class="text" name="schedule[fee]" value="<?=$this->value('schedule/fee')?>" <?if($mode=='view'){?>disabled="disabled"<?}?> style="width:60%"/>元</div>
    <div>&nbsp;开始时间：<input class="text" name="schedule_extra[time_start]" value="<?=$this->value('schedule/time_start')?>"  <?if($mode=='view'){?>disabled="disabled"<?}?> style="width:60%"/></div>
    <div>&nbsp;时&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;长：<input class="text" name="schedule[hours_own]" value="<? echo round(($this->value('schedule/time_end')-$this->value('schedule/time_start'))/3600,2); ?>" <?if($mode=='view'){?>disabled="disabled"<?}?> style="width:60%"/></div>
</div>