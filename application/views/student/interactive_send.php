<form method="post">
<div class="contentTable">
    <div class="item"><div class="title"><label>发送/回复</label></div>
    	<input type="text" name="student_comment_extra[reply_to_username]" value="{post student_comment_extra/reply_to_username}" placeholder="收件人（通过点击上面的用户名，可以快捷输入）" />
        <input type="text" name="student_comment[title]" placeholder="标题" value="{post student_comment/title}" />
	    <textarea name="student_comment[content]" placeholder="内容">{post student_comment/content}</textarea>
	</div>
    
    <div class="submit">
    	<input type="submit" name="submit[student_comment]" value="发送" />
    </div>
</div>
</form>