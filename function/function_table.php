<?php
function fetchTableArray($query,$field){
	/*
	输出一个数组，包含表格中的所有单元格数据
	$q_data:数据库查询语句,必须包含WHERE条件,留空为WHERE 1=1
	$field:输出表的列定义
		array(
			'查询结果的列名'=>'显示的列名',//此为简写
			'查询结果的列名'=>array(
					'title'=>'列的显示标题'
					'surround_title'=>array(
							'mark'=>'标签名，如 a',
							'标签的属性名如href'=>'标签的值如http://www.google.com',
						)标题单元格文字需要嵌套的HTML标签
					'surround'
					'eval'=>false，'是否'将content作为源代码运行
					'content'=>'显示的内容，可以用如{client}来显示变量，{client}是数据库查询结果的字段名'
				)
		)
	*/
	//if($_SESSION['username']=='uicestone')showMessage($query,'notice');
	
	global $_G;
	
	$result=db_query($query);
	
	if($result===false){
		return false;
	}
	
	$table=array('_field'=>array());
	
	foreach($field as $k=>$v){
		if(!is_array($v))
			$table['_field'][$k]=$v;
		else{
			$str='';
			if(isset($v['title'])){
				$str=$v['title'];
			}
			if(isset($v['surround_title'])){
				$str=surround($str,$v['surround_title']);
			}elseif(!isset($v['orderby']) || $v['orderby']){
				$str=surround($str,array('mark'=>'a','href'=>"javascript:postOrderby('".$k."')"));
			}
			$table['_field'][$k]['html']=$str;
			if(isset($v['td_title'])){
				$table['_field'][$k]['attrib']=$v['td_title'];
			}
		}
	}

	while($data=db_fetch_array($result)){
		$line_data=array();
		foreach($field as $k => $v){
			if(!is_array($v))
				$line_data[$k]=variableReplace(isset($data[$k])?$data[$k]:NULL,$data);
			else{
				$str=isset($v['content']) ? $v['content'] : (isset($data[$k])?$data[$k]:NULL);
				$str=variableReplace($str,$data);
				if(isset($v['eval']) && $v['eval']){
					$str=eval($str);
				}
				if(isset($v['surround'])){
					array_walk($v['surround'],'variableReplaceSelf',$data);
					$str=surround($str,$v['surround']);
				}
				$line_data[$k]['html']=$str;
				if(isset($v['td'])){
					$line_data[$k]['attrib']=variableReplace($v['td'],$data);
				}
			}
		}
		$table[]=$line_data;
	}
	
	return $table;
}

function arrayExportTable(array $array,$menu=NULL,$surroundForm=false,$surroundBox=true,array $attributes=array(),$show_line_id=false,$trim_columns=false){
	/*
	根据_head中规定的列，将$array输出成一个表格
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

function arrayExportExcel(array $array){
	require 'plugin/PHPExcel/PHPExcel.php';
	require 'plugin/PHPExcel/PHPExcel/Writer/Excel5.php';
	
	$objExcel = new PHPExcel();
	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
	$objProps = $objExcel->getProperties();
	$objProps->setCreator($_SESSION['username']);
	$objProps->setLastModifiedBy($_SESSION['username']);
    /*$objProps->setTitle($file_title);
    $objProps->setSubject("Office XLS Test Document, Demo"); 
    $objProps->setDescription("Test document, generated by PHPExcel.");
    $objProps->setKeywords("office excel PHPExcel");
    $objProps->setCategory("Test");*/

	$objExcel->setActiveSheetIndex(0); 
    $objActSheet = $objExcel->getActiveSheet();  
 
	//设置当前活动sheet的名称  
    $objActSheet->setTitle('sheet1');  

	//设置单元格内容  由PHPExcel根据传入内容自动判断单元格内容类型  
    $objActSheet->setCellValue('A1', '字符串内容');  // 字符串内容  
    $objActSheet->setCellValue('A2', 26);            // 数值  
    $objActSheet->setCellValue('A3', true);          // 布尔值  
    $objActSheet->setCellValue('A4', '=SUM(A2:A2)'); // 公式   
	
	$fields=$array['_field'];unset($array['_field']);

	foreach($fields as $field_name=>$value){
		echo '<td field="'.$field_name.'"'.(is_array($value) && isset($value['attrib'])?' '.$value['attrib']:'').'>'.(is_array($value)?$value['html']:$value).'</td>';
	}

	foreach($array as $linearray){
		foreach($fields as $field_name=>$value){
			$html=is_array($linearray[$field_name])?$linearray[$field_name]['html']:$linearray[$field_name];
			echo '<td field="'.$field_name.'"'.(is_array($linearray[$field_name]) && isset($linearray[$field_name]['attrib'])?' '.$linearray[$field_name]['attrib']:'').'>'.$html.'</td>';
		}

	}
	$objWriter->save('php://output');
}

function exportTable($q_data,$field,$menu=NULL,$surroundForm=false,$surroundBox=true,array $attributes=array(),$show_line_id=false,$trim_columns=false){
	$array=fetchTableArray($q_data,$field);
	arrayExportTable($array,$menu,$surroundForm,$surroundBox,$attributes,$show_line_id,$trim_columns);
}

function variableReplace($content,$data){
	while(preg_match('/{(\S*?)}/',$content,$match)){
		if(!isset($data[$match[1]])){
			$data[$match[1]]=NULL;
		}
		$content=str_replace($match[0],$data[$match[1]],$content);
	}
	return $content;
}

function variableReplaceSelf(&$content,$key,$data){
	$content=variableReplace($content,$data);
}

function surround($str,$surround){
	if($str=='')
		return '';
	
	$mark=$surround['mark'];
	unset($surround['mark']);
	$property=db_implode($surround,' ',NULL,'=','"','"','','value',false);
	return '<'.$mark.' '.$property.'>'.$str.'</'.$mark.'>';
	
}

function processSearch(&$q,$fields){
	global $_G;
	if(is_posted('search_cancel')){
		unset($_SESSION[IN_UICE][$_G['action']]['in_search_mod']);
		unset($_SESSION[IN_UICE][$_G['action']]['keyword']);
	}
	
	if(is_posted('search')){
		option('keyword',array_trim($_POST['keyword']));
		option('in_search_mod',true);
	}
	
	if(option('in_search_mod')){
		
		$condition_search='';
		
		foreach(option('keyword') as $field => $keywords){
			
			$condition='';

			$condition=preg_split('/[\s]+|,/',option('keyword/'.$field));
			
			$condition=' AND ('.db_implode($condition,' AND ',db_field_name($field),' LIKE ',"'%","%'",'').')';
			
			$condition_search.=$condition;
			
		}
		$q.=$condition_search;
	}
		
	$search_bar='<form method="post" name="search">'.
		'<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">'.
			'<thead><tr><td width="80px">搜索</td><td>&nbsp;</td></tr></thead>'.
			'<tbody>';
	foreach($fields as $field_table_name => $field_ui_name){
		$search_bar.='<tr><td>'.
			'<label>'.$field_ui_name.'：'.'</label></td>'.
			'<td>'.
			'<input type="text" name="keyword['.$field_table_name.']" value="'.option('keyword/'.$field_table_name).'" /><br />'.
			'</td></tr>';
	}
	
	$search_bar.='<tr><td colspan="2"><input type="submit" name="search" value="搜索" tabindex="0" />';
	if(option('in_search_mod')){
		$search_bar.='<input type="submit" name="search_cancel" value="取消" tabindex="1" />';
	}
	$search_bar.='</td></tr></tbody>'.
			'</table>'.
		'</form>';
	
	return $search_bar;
}

function processOrderby(&$q,$defaultOrder,$defaultMethod=NULL,$field_need_convert=array(),$only_table_of_the_page=true){
	global $_G;
	if (is_null(option('orderby'))){
		option('orderby',$defaultOrder);
	}
	if (is_null(option('method'))){
		option('method',is_null($defaultMethod)?'ASC':$defaultMethod);
	}

	if($only_table_of_the_page && is_posted('orderby') && !is_null(option('orderby')) && $_POST['orderby']==$_SESSION[IN_UICE][$_G['action']]['orderby']){
		if(option('method')=='ASC'){
			option('method','DESC');
		}else{
			option('method','ASC');
		}
	}
	
	if(is_posted('orderby')){
		option('orderby',$_POST['orderby']);
	}
	if(is_posted('method')){
		option('method',$_POST['method']);
	}
	
	$needConvert=in_array(option('orderby'),$field_need_convert);
	
	$q.= ' ORDER BY '.
		($needConvert?'convert(':'').
		db_field_name(option('orderby')).
		($needConvert?' USING GBK) ':' ').
		option('method');
}

function dateRange(&$q,$date_field,$timestamp=true){
	global $_G;
	if(is_posted('date_range_cancel')){
		unset($_SESSION[IN_UICE][$_G['action']]['in_date_range']);
		unset($_SESSION[IN_UICE][$_G['action']]['date_range']);
	}
	
	if(is_posted('date_range')){
		if(!strtotime($_POST['date_from']) || !strtotime($_POST['date_to'])){
			showMessage('日期格式错误','warning');

		}else{
			option('date_range/from_timestamp',strtotime($_POST['date_from']));
			option('date_range/to_timestamp',strtotime($_POST['date_to'])+86400);
			
			option('date_range/from',date('Y-m-d',option('date_range/from_timestamp')));
			option('date_range/to',date('Y-m-d',option('date_range/to_timestamp')-86400));
			
			option('in_date_range',true);
		}
	}
	
	if(option('in_date_range')){
		
		if($timestamp){
		$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from_timestamp')."' AND ".db_field_name($date_field)."<'".option('date_range/to_timestamp')."')";
		}else{
			$condition_date_range=" AND (".db_field_name($date_field).">='".option('date_range/from')."' AND ".db_field_name($date_field)."<='".option('date_range/to')."')";
		}

		$q.=$condition_date_range;
	}
		
	$date_range_bar=
	'<form method="post" name="date_range">'.
		'<table class="contentTable search-bar" cellpadding="0" cellspacing="0" align="center">'.
		'<thead><tr><td width="60px">日期</td><td>&nbsp;</td></tr></thead>'.
		'<tbody>'.
    	'<tr><td>开始：</td><td><input type="text" name="date_from" value="'.option('date_range/from').'" class="date" /></td></tr>'.
        '<tr><td>结束：</td><td><input type="text" name="date_to" value="'.option('date_range/to').'" class="date" /></td></tr>'.
        '<input style="display:none;" name="date_field" value="'.$date_field.'" />';
	
	$date_range_bar.='<tr><td colspan="2"><input type="submit" name="date_range" value="提交" />';
	if(option('in_date_range')){
		$date_range_bar.='<input type="submit" name="date_range_cancel" value="取消" tabindex="1" />';
	}
	$date_range_bar.='</td></tr></tbody></table></form>';
	
	return $date_range_bar;
}

function addCondition(&$q,$condition_array,$unset=array()){
	global $_G;
	
	foreach($unset as $changed_variable => $unset_variable){
		if(is_posted($changed_variable)){
			unset($_SESSION[IN_UICE][$_G['action']][$unset_variable]);
		}
	}

	foreach($condition_array as $variable=>$field){
		if(is_posted($variable)){
			option($variable,$_POST[$variable]);
		}
		
		if(!is_null(option($variable)) && option($variable)!=''){
			$q.=' AND '.db_field_name($field)."='".option($variable)."'";
		}
	}
	return $q;
}

function processMultiPage(&$q,$q_rows=NULL){
	global $_G;
	if(is_null($q_rows)){
		$q_rows=$q;
		if(preg_match('/GROUP BY[^()]*?[ORDER BY].*?$/',$q_rows)){
			$q_rows="SELECT COUNT(*) AS number FROM (".$q_rows.")query";
		}else{
			$q_rows=preg_replace('/^[\s\S]*?FROM /','SELECT COUNT(1) AS number FROM ',$q_rows);
			$q_rows=preg_replace('/GROUP BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
			$q_rows=preg_replace('/ORDER BY(?![\s\S]*?WHERE)[\s\S]*?$/','',$q_rows);
		}
	}

	$rows=db_fetch_field($q_rows);
	
	if(option('list/start')>$rows || $rows==0){
		//已越界或空列表时，列表起点归零
		option('list/start',0);

	}elseif(option('list/start')+option('list/item')>=$rows && $rows>option('list/items')){
		//末页且非唯一页时，列表起点定位末页起点
		option('list/start',$rows - ($rows % option('list/items')));
	}
	
	if(!is_null(option('list/start')) && option('list/items')){
		if(is_posted('previousPage')){
			option('list/start',option('list/start')-option('list/items'));
			if(option('list/start')<0){
				option('list/start',0);
			}
		}elseif(is_posted('nextPage')){
			if(option('list/start')+option('list/items')<$rows){
				option('list/start',option('list/start')+option('list/items'));
			}
		}elseif(is_posted('firstPage')){
			option('list/start',0);
		}elseif(is_posted('finalPage')){
			option('list/start',$rows - ($rows % option('list/items')));
		}
	}else{
		option('list/start',0);
		option('list/items',25);
	}
	
	$q.=" LIMIT ".option('list/start').",".option('list/items');
	
	$listLocator=($rows==0?0:option('list/start')+1)."-".
	(option('list/start')+option('list/items')<$rows?(option('list/start')+option('list/items')):$rows).'/'.$rows;
	
	$listLocator.=
		'<button type="button" class="nav" onclick="post(\'firstPage\',true)"'.(option('list/start')==0?' disabled="disabled"':'').'>&lt;&lt;</button>'.
		'<button type="button" class="nav" onclick="post(\'previousPage\',true)"'.(option('list/start')==0?' disabled="disabled"':'').'>&nbsp;&lt;&nbsp;</button>'.
		'<button type="button" class="nav" onclick="post(\'nextPage\',true)"'.(option('list/start')+option('list/items')>=$rows?' disabled="disabled"':'').'>&nbsp;&gt;&nbsp;</button>'.
		'<button type="button" class="nav" onclick="post(\'finalPage\',true)"'.(option('list/start')+option('list/items')>=$rows?' disabled="disabled"':'').'>&gt;&gt;</button>';
	return $listLocator;
}

/* 
 * $extra_action 是一个数组，接受除了返回列表/关闭窗口之外的其他提交后动作
 * $after_update为数据库更新成功后，跳转前需要的额外操作
 */
function processSubmit($submitable,$after_update=NULL,$update_table=NULL,$set_display=true,$set_time=true,$set_user=true){
	global $_G;
	if($set_display){
		post(IN_UICE.'/display',1);
	}
	
	if($set_time){
		post(IN_UICE.'/time',$_G['timestamp']);
	}
	
	if($set_user){
		post(IN_UICE.'/uid',$_SESSION['id']);
		post(IN_UICE.'/username',$_SESSION['username']);
	}
	
	post(IN_UICE.'/company',$_G['company']);
	
	if(is_null($update_table)){
		$update_table=IN_UICE;
	}

	if($submitable){
		if(db_update($update_table,post(IN_UICE),"id='".post(IN_UICE.'/id')."'")){

			if(is_a($after_update,'Closure')){
				$after_update();
			}
	
			if(is_posted('submit/'.IN_UICE)){

				if(!$_G['as_controller_default_page']){
					unset($_SESSION[IN_UICE]['post']);
				}
				
				if($_G['as_popup_window']){
					refreshParentContentFrame();
					closeWindow();
				}else{
					if($_G['as_controller_default_page']){
						showMessage('保存成功~');
					}else{
						redirect((sessioned('last_list_action')?$_SESSION['last_list_action']:IN_UICE));
					}
				}
			}
		}
	}
}

function processUidTimeInfo($affair){
	if(!post($affair)){
		post($affair,array());
	}
	post($affair,post($affair)+uidTime());
}

function uidTime(){
	global $_G;
	$array=array(
		'uid'=>$_SESSION['id'],
		'username'=>$_SESSION['username'],
		'time'=>$_G['timestamp'],
		'company'=>$_G['company']
	);
	return $array;
}


/*
 * 在每个add页面之前获得数据ID，插入新数据或者根据数据ID获得数据数组
 */
function getPostData($callback=NULL,$generate_new_id=true,$db_table=NULL){
	global $_G;
	if(got('edit')){
		unset($_SESSION[IN_UICE]['post']);
		post(IN_UICE.'/id',intval($_GET['edit']));
	
	}elseif(is_null(post(IN_UICE.'/id'))){
		unset($_SESSION[IN_UICE]['post']);
	
		processUidTimeInfo(IN_UICE);
	
		if(is_a($callback,'Closure')){
			$callback();
		}

		if($generate_new_id){
			if(is_null($db_table)){
				if(isset($_G['actual_table'])){
					$db_table=$_G['actual_table'];
				}else{
					$db_table=IN_UICE;
				}
			}
			post(IN_UICE.'/id',db_insert($db_table,post(IN_UICE)));
		}
		//如果$generate_new_id==false，那么必须在callback中获得post(IN_UICE/id)
	}

	if(!post(IN_UICE.'/id')){
		showMessage('获得信息ID失败','warning');
		exit;
	}

	post(IN_UICE,call_user_func(IN_UICE.'_fetch',post(IN_UICE.'/id')));
}
?>