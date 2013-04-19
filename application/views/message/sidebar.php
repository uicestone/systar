<form>
	<table class="contentTable search-bar">
		<thead><tr><th width="80px">发送消息</td></tr></thead>
		<tbody>
			<tr>
				<td>
					<select name="receivers[]" class="chosen allow-new" data-placeholder="收件人" multiple="multiple">
						<?=options($this->user->getArray(array(
							'is_relative_of'=>$this->user->id,
							'has_relative_like'=>$this->user->id,
							'in_team'=>array_keys($this->user->teams),
							'in_related_team_of'=>array_keys($this->user->teams),
							'in_team_which_has_relative_like'=>array_keys($this->user->teams)
						),'name','id'), NULL, NULL, true)?>
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