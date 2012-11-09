<form id="schedule">
    <? if(!$this->input->get('edit')){?>
    <input type="text" name="name" placeholder="标题" style="width:98%" />
    <? }?>
    <textarea name="content" placeholder="内容" rows="7" style="width:98%"></textarea>
    <textarea name="experience" placeholder="心得" rows="4" style="width:98%"></textarea>
    <? if(!$this->input->get('edit')){?>
    <label>项目：</label>
    <span>
        <label><input name="type" type="radio" value="0" />案件</label>
        <label><input name="type" type="radio" value="1" />所务</label>
        <label><input name="type" type="radio" value="2" />营销</label>
    </span>
    <? }?>
    <span class="right">
        <label><input name="completed" type="radio" value="1" />日志</label>
        <label><input name="completed" type="radio" value="0" />提醒</label>
    </span>
    <? if(!$this->input->get('edit')){?>
    <div id="caseSelectBox" class="ui-widget"><label>案件：</label><select id="combobox" name="case" style="width:97%"></select></div>
    <div id="clientSelectBox" class="ui-widget" style="display:none"><label>客户：</label><select id="combobox" name="client" disabled="disabled"></select></div>
    <? }?>
    <div style="clear:right"><label>外出：</label><input type="text" name="place" placeholder="外出地点" /><input type="text" name="fee" size="5" placeholder="费用" />元：<input type="text" name="fee_name" placeholder="费用用途" /></div>
</form>
