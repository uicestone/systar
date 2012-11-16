<div class="contentTableMenu">
	<div class="left"><button type="button" onclick="post('updateScore',true)">更新</button></div>
	<div class="right"><?$this->load->view('pagination')?></div>
</div>
<div class="contentTableBox">
<?=$list?>
<?=$avg?>
</div>
