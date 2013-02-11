<nav>
	<div id="navMenu">
		<ul class="l0">
<?php
foreach($this->user->permission as $controller_name => $controller){
	if(isset($controller['_display']) && $controller['_display']){
		if(in_subarray(1,$controller,'_display')!==false){
			$has_sub_menu=true;
		}else{
			$has_sub_menu=false;
		}
		echo '<li id="nav-'.$controller_name.'">'.
			($has_sub_menu?'<span class="arrow"><img src="images/arrow_r.png" /></span>':'').
			'<a href="#'.$controller_name.'" class="controller'.($has_sub_menu?'':' dink').'" hidefocus="true">'.$controller['_controller_name'].'</a>';

		if(isset($controller['_add_action']) && $this->user->isPermitted($controller_name,'add')){
			echo '<a href="#'.$controller['_add_action'].'" hidefocus="true"> <span style="font-size:12px;color:#CEDDEC">+</span></a>';
		}
		if($has_sub_menu){
			echo '<ul class="l1">';
			foreach($controller as $action_name => $action){
				if(is_array($action)){
					if($action['_display']){
						echo '<li id="nav-'.$controller_name.'-'.$action_name.'"><a href="#'.$controller_name.'/'.
						$action_name.'" hidefocus="true">'.
						$action['_controller_name'].'</a></li>';
					}
				}
			}
			echo '</ul>';
		}
		echo '</li>';
	}
}
?>
		</ul>
	</div>
<?if(false && $this->config->item('debug_mode')){?>
	<div style="color:#091F35;font-size:10px;position:fixed;bottom:0;"><? echo codeLines()?></div>
<?}?>
</nav>