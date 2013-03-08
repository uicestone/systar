<?php
/*	生成一组<option>
 *	$options为给出的选项数组
 *  $options[0]=='_ENUM'时，根据$options[1]表，$options[2]字段的enum选项来定制选项
 *	$options是一个值时,尝试从type(可以指定)表中获得classification(可以指定)为$options的type(可以指定)
 *	指定$affair值时，优先根据$affair获得第一个classfication
 */
function options($options,$checked=NULL,$label=NULL,$array_key_as_option_value=false,$etc_option=false){
	
	$options_html='';
	
	if(isset($label)){
		$options_html.="<option value=\"\" disabled=\"disabled\"".(is_null($checked)?'selected="selected"':'').">$label</option>";
	}
	
	foreach($options as $option_key=>$option){
		
		if(is_array($option)){
			$options_html.='<optgroup label="'.$option_key.'">';
			foreach($option as $option_key => $option){
				$value=$array_key_as_option_value?$option_key:$option;
				$options_html.='<option value="'.$value.'"'.($value==$checked?' selected="selected"':'').'>'.$option.'</option>';
			}
			$options_html.='</optgroup>';
		}else{
			$value=$array_key_as_option_value?$option_key:$option;
			$options_html.='<option value="'.$value.'"'.($value==$checked?' selected="selected"':'').'>'.$option.'</option>';
		}
	}
	
	if($etc_option){
		$options_html.='<option value="">其他</option>';
	}

	return $options_html;
}

/**
 * 生成一组个单选框
 */
function radio($options,$name,$checked,$array_key_as_option_value=false){
	$radio='';
	
	foreach($options as $option_key=>$option){
		$radio.='<label><input name="'.$name.'" value="'.($array_key_as_option_value?$option_key:$option).'" type="radio"'.($checked==($array_key_as_option_value?$option_key:$option)?' checked="checked"':'').' />'.$option.'</label>';
	}
	
	return $radio;
}

/**
 * 生成一个多选框
 */
function checkbox($html,$name,$check_value,$value=NULL,$attribute=''){
	if(is_null($value)){
		$value=$html;
	}
	return "<label $attribute><input name=\"$name\" type=\"checkbox\" value=\"$value\" ".($check_value==$value?'checked="checked"':'')." />$html</label>";
}?>
