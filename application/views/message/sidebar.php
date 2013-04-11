<form>
	<table class="contentTable search-bar">
		<thead><tr><th width="80px">发送消息</td></tr></thead>
		<tbody>
			<tr>
				<td>
					<select name="receivers[]" class="chosen allow-new" data-placeholder="收件人" multiple="multiple">
						<?=options($this->user->getArray(array(),'name','id'), NULL, NULL, true)?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<textarea name="content" placeholder="内容"></textarea>
				</td>
			</tr>
			<tr>
				<td class="submit">
					<button type="submit" name="send">发送</button>
				</td>
			</tr>
		</tbody>
	</table>
</form>