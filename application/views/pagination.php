<div class="pagination">
	<?=option('pagination/start')+1?>-<?=option('pagination/rows')<option('pagination/items')?option('pagination/rows'):option('pagination/start')+option('pagination/items')?>/<?=option('pagination/rows')?>
	<button type="button" <?if(option('pagination/start')==0){?>disabled="disabled"<?}else{?>target-page-start="0"<?}?>>&lt;&lt;</button>
	<button type="button" <?if(option('pagination/start')==0){?>disabled="disabled"<?}else{?>target-page-start="<?=option('pagination/start')-option('pagination/items')?>"<?}?>>&nbsp;&lt;&nbsp;</button>
	<button type="button" <?if(option('pagination/start')+option('pagination/items')>=option('pagination/rows')){?>disabled="disabled"<?}else{?>target-page-start="<?=option('pagination/start')+option('pagination/items')?>"<?}?>>&nbsp;&gt;&nbsp;</button>
	<button type="button" <?if(option('pagination/start')+option('pagination/items')>=option('pagination/rows')){?>disabled="disabled"<?}else{?>target-page-start="<?=(ceil(option('pagination/rows')/option('pagination/items'))-1)*option('pagination/items')?>"<?}?>>&gt;&gt;</button>
</div>