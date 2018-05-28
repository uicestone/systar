<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
#allstar_journal{font-size:13px; line-height:1.7;}
#allstar_journal p{text-indent:2em;}
</style>
<table id="allstar_journal" cellpadding="0" cellspacing="0" width="763px" border-spacing="0">
	<thead style="background-color:#FFF;">
		<tr><td style="line-height:0"><img src="http://sys.lawyerstars.com/images/mail/express/allstar.jpg" alt="上海星瀚律师事务所" /></td></tr>
		<tr><td><img src="http://sys.lawyerstars.com/images/mail/express/<?=$header_img?>" alt="<?=$title?>" width="763px"></td></tr>
	</thead>
	<tbody>
<?php $line_id=0; ?>
<?php foreach($articles as $article){ ?>
<?php $line_id++; ?>
<?php if($line_id>0){ ?>
		<tr>
			<td style="padding:0;border:0;">
				<img src="http://sys.lawyerstars.com/images/mail/express/delimiter.png" alt="_______________________________________________________" />
			</td>
		</tr>
<?php } ?>
		<tr>
			<td style="border:0;padding:40px;<?php if($line_id % 2 == 0){ ?>float:right;<?php } ?>">
				<h1 style="color:#0D0080;font:25px/1.5 Simhei;"><a href="http://www.lawyerstars.com/article-<?=$article['aid']?>-1.html"><?=$article['title']?></a></h1>
				<p style="width:500px;<?php if($line_id % 2 == 0){ ?>float:right;<?php } ?>"><?=$article['summary']?>
					<span><a href="http://www.lawyerstars.com/article-<?=$article['aid']?>-1.html">查看全文</a></span>
				</p>
			</td>
		</tr>
<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td><img src="http://sys.lawyerstars.com/images/mail/express/footer.jpg" alt="上海星瀚律师事务所" /></td>
		</tr>
	</tfoot>
</table>