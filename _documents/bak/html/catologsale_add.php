<form method="post">
<div class="inputTable">
	<div class="item">
        <div class="title"><label>名称：</label></div>
        <input class="item" name="name" value="<? displayPost('name'); ?>" type="text" maxlength="255" size="20">
	</div> 

	<div class="item">
        <div class="title"><label>电话：</label></div>
        <input class="item" name="phone" value="<? displayPost('phone'); ?>" type="text" maxlength="255" size="20">
	</div> 

	<div class="item">
        <div class="title"><label>电邮：</label></div>
        <input class="item" name="email" value="<? displayPost('email'); ?>" type="text" maxlength="255" size="20">
	</div> 

	<div class="item">
        <div class="title"><label>网站：</label></div>
        <input class="item" name="website" value="<? displayPost('website'); ?>" type="text" maxlength="255" size="20">
	</div> 

	<div class="item">
        <div class="title"><label>地址：</label></div>
        <input class="item" name="address" value="<? displayPost('address'); ?>" type="text" maxlength="255" size="20">
	</div> 

	<div class="item">
        <div class="title"><label>邮编：</label></div>
        <input class="item" name="zipcode" value="<? displayPost('zipcode'); ?>" type="text" maxlength="255" size="20">
	</div> 

	<div class="item">
        <div class="title"><label>直邮状态：</label></div>
        <select class="item" id="status" name="status">
            <option value="1">未处理</option>
            <option value="2">已打印</option>
            <option value="3">已寄出</option>
            <option value="4">已退回</option>
            <option value="5">未查明</option>
            <option value="6">无价值</option>
            <option value="7">已过期</option>
         </select>
        <script type="text/javascript">
        document.getElementById('status').value='<? displayPost('status'); ?>'
        </script>
	</div> 

	<div class="item">
        <div class="title"><label>备注：</label></div>
        <textarea class="item" name="comment"><? displayPost('comment'); ?></textarea>
        <input class="item" name="case" value="<? displayPost('case'); ?>" type="text" maxlength="255" size="20" style="display:none;">
    </div>
    <div class="submit">
        <input class="submit" type="submit" name="catologsaleSubmit" value="保存">
        <input class="submit" type="submit" name="submit[cancel]" value="取消">
    </div>
</div>
</form>