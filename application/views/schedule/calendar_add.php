<textarea type="text" name="content" placeholder="日程概要" class="text" <?if($mode=='view'){?>disabled="disabled"<?}?>><?=$this->value('schedule/content')?></textarea>
<input name="case" value="<?=$this->value('schedule/case')?>" placeholder="相关项目" class="text" autocomplete-model="cases" <?if($mode=='view'){?>disabled="disabled"<?}?>/>
<input name="people" value="<?=$this->value('schedule/people')?>" placeholder="相关人员" class="text" autocomplete-model="people" <?if($mode=='view'){?>disabled="disabled"<?}?>/>
