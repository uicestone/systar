<?php
/**
 * 递归去除array中的空键
 */
function array_trim($array){
	$array=(array)$array;
	foreach($array as $k => $v){
		if($v=='' || $v==array()){
			unset($array[$k]);
		}elseif(is_array($v)){
			$array[$k]=array_trim($v);
		}
	}
	return $array;
}

/**
	用array_dir('/_SESSION/post/id')来代替$_SESSION['post']['id']
	**仅适用于全局变量如$_SESSION,$_POST
	用is_null(array_dir(String $arrayindex))来判断是否存在此变量
	若指定第二个参数$setto,则会改变$arrayindex的值
*/
function array_dir($arrayindex){
	preg_match('/^[^\/]*/',$arrayindex,$match);
	$arraystr=$match[0];
	
	preg_match('/\/.*$/',$arrayindex,$match);
	$indexstr=$match[0];

	$indexstr=str_replace('/',"']['",$indexstr);
	$indexstr=substr($indexstr,2).substr($indexstr,0,2);
	
	$args=func_get_args();
	if(count($args)==1){
		return @eval('return $'.$arraystr.$indexstr.';');
	}elseif(count($args)==2){
		return @eval('return $'.$arraystr.$indexstr.'=$args[1];');
	}
}

/**
 * php5.3开始已经自带
 */
if(!function_exists('array_replace_recursive')){
	function array_replace_recursive(&$array_target,$array_source){
	
		if(!isset($array_target)){
			$array_target=$array_source;
		}else{
			foreach($array_source as $k=>$v){
				if(is_array($v)){
					array_replace_recursive($array_target[$k],$v);
				}else{
					$array_target[$k]=$v;
				}
			}
		}
		return $array_target;
	}
}

/**
 * 将数组的下级数组中的某一key抽出来构成一个新数组
 * @param $array
 * @param $keyname
 * @param $keyname_forkey 母数组中用来作为子数组键名的键值的键名
 * @return array
 */
function array_sub($array,$keyname,$keyname_forkey=NULL){
	$array_new=array();
	foreach($array as $key => $sub_array){
		if(isset($sub_array[$keyname])){
			if(is_null($keyname_forkey)){
				$array_new[$key]=$sub_array[$keyname];
			}else{
				$array_new[$sub_array[$keyname_forkey]]=$sub_array[$keyname];
			}
		}
	}
	return $array_new;
}

/**
 * 根据一个包含所有合法键作为内容的$legalkeys数组，对一个数组$array进行过滤
 */
function array_filter_key($array,$legalkeys){
    foreach($array as $key => $value){
        if(!in_array($key,$legalkeys)){
            unset($array[$key]);
        }
    }
	return $array;
}

/**
 * 判断某个值是否存在与某一数组的子数组下
 * 若指定$key_specified，则要判断子数组们的$key_specified键下是否有指定$needle值
 * 
 * 这在处理db_toArray的结果数组时十分有用，db_toArray的数组其中每一行又是一个数组
 */
function in_subarray($needle,array $array,$key_specified=NULL){
	foreach($array as $key => $subarray){
		if(isset($key_specified)){
			if(is_array($subarray) && isset($subarray[$key_specified]) && $subarray[$key_specified]==$needle){
				return $key;
			}
		}else{
			if(in_array($needle,$subarray)){
				return $key;
			}
		}
	}
	return false;
}
?>
