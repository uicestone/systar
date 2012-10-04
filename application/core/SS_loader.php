<?php
class SS_Loader extends CI_Loader{
	function __construct(){
		parent::__construct();
	}

	/*
		将$array输出成一个表格
		$array:数据数组
		结构：
		Array
		(
			[_field] => Array
				(
					[字段名1] => array(
						'html'=>字段标题
						'attrib'=>字段html标签属性
					),
					[字段名2] => array(
						'html'=>字段标题
						'attrib'=>字段html标签属性
					)
				)

			[第一行行号] => Array
				(
					[字段名1] =>  array(
						'html'=>字段值
						'attrib'=>字段html标签属性
					),
					[字段名2] =>  array(
						'html'=>字段值
						'attrib'=>字段html标签属性
					)
				)
			...
		)
	*/
	function arrayExportTable(array $array,$menu=NULL,$surroundForm=false,$surroundBox=true,array $attributes=array(),$show_line_id=false,$trim_columns=false){
		//print_r($array);

		if($trim_columns){
			$table_head['_field']=$array['_field'];
			$table_body=array_slice($array,1);

			$column_is_empty=array();

			foreach($table_head['_field'] as $field_name => $field_title){
				$column_is_empty[$field_name]=true;
			}

			foreach($table_body as $line_id => $line){
				foreach($line as $field_name => $field){
					if((is_array($field) && (strip_tags($field['html'])!='')) || (!is_array($field) && strip_tags($field)!='')){
						$column_is_empty[$field_name]=false;
					}
				}
			}

			foreach($array as $line_id => $line){
				foreach($line as $field_name => $field){
					if($column_is_empty[$field_name]){
						unset($array[$line_id][$field_name]);
					}
				}
			}
		}

		if($surroundForm){
			echo '<form method="post">'."\n";
		}

		if(isset($menu['head'])){
			echo '<div class="contentTableMenu"';

			foreach($attributes as $attribute_name => $attribute_value){
				echo ' '.$attribute_name.'="'.$attribute_value.'"';
			}

			echo '>'."\n".$menu['head'].'</div>'."\n";
		}

		if($surroundBox){
			echo '<div class="contentTableBox">'."\n";
		}

		echo '<table class="contentTable" cellpadding="0" cellspacing="0"';

		foreach($attributes as $attribute_name => $attribute_value){
			echo ' '.$attribute_name.'="'.$attribute_value.'"';
		}

		echo '>'."\n".
		'	<thead><tr>'."\n";

		if($show_line_id){
			echo '<td width="40px">&nbsp;</td>';
		}

		$fields=$array['_field'];unset($array['_field']);

		foreach($fields as $field_name=>$value){
			echo '<td field="'.$field_name.'"'.(is_array($value) && isset($value['attrib'])?' '.$value['attrib']:'').'>'.(is_array($value)?$value['html']:$value).'</td>';
		}

		echo "	</tr></thead>"."\n";

		echo "	<tbody>"."\n";

		$line_id=1;
		foreach($array as $linearray){
			if($line_id%2==0){
				$tr='class="oddLine"';
			}else{
				$tr='';
			}
			echo "<tr ".$tr.">";

			if($show_line_id){
				echo '<td style="text-align:center">'.($line_id+option('list/start')).'</td>';
			}

			foreach($fields as $field_name=>$value){
				$html=is_array($linearray[$field_name])?$linearray[$field_name]['html']:$linearray[$field_name];
				if(empty($html)){
					$html='&nbsp;';
				}

				echo '<td field="'.$field_name.'"'.(is_array($linearray[$field_name]) && isset($linearray[$field_name]['attrib'])?' '.$linearray[$field_name]['attrib']:'').'>'.$html.'</td>';
			}

			echo "</tr>";
			$line_id++;
		}
		echo "	</tbody>"."\n";
		echo "</table>"."\n";

		if($surroundBox){
			if(isset($menu['foot'])){
				echo
				'<div class="contentTableFoot"';

				foreach($attributes as $attribute_name => $attribute_value){
					echo ' '.$attribute_name.'="'.$attribute_value.'"';
				}

				echo '>'."\n".
					$menu['foot'].
				'</div>'."\n";
			}

			echo "</div>"."\n";
		}

		if($surroundForm){
			echo '</form>'."\n";
		}
	}
}
?>