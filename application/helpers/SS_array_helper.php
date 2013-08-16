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
	
	$CI=&get_instance();
	
	$args=func_get_args();
	
	if(count($args)==1){
		return $CI->session->userdata($arrayindex);
	}elseif(count($args)==2){
		return $CI->session->set_userdata($arrayindex,$args[1]);
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
 * php 5.5 开始自带此函数
 */
if(!function_exists('array_column')){
	function array_column($array,$keyname,$keyname_forkey=NULL,$fill_null=false){
		$array_new=array();
		foreach($array as $key => $sub_array){

			if(isset($sub_array[$keyname])){
				if($keyname_forkey===false){
					$array_new[]=$sub_array[$keyname];
				}
				elseif(is_null($keyname_forkey)){
					$array_new[$key]=$sub_array[$keyname];
				}
				else{
					if(isset($sub_array[$keyname_forkey])){
						$array_new[$sub_array[$keyname_forkey]]=$sub_array[$keyname];
					}
					else{
						$array_new[$key]=$sub_array[$keyname];
					}
				}
			}
			elseif($fill_null){
				if($keyname_forkey===false){
					$array_new[]=NULL;
				}
				elseif(is_null($keyname_forkey)){
					$array_new[$key]=NULL;
				}
				else{
					if(isset($sub_array[$keyname_forkey])){
						$array_new[$sub_array[$keyname_forkey]]=NULL;
					}
					else{
						$array_new[$key]=NULL;
					}
				}
			}
		}
		return $array_new;
	}
}

function array_picksub($array,$keys){
	$array_new=array();
	foreach($array as $sub_array){
		if(array_intersect($keys,array_keys($sub_array))===$keys){
			$picked=array();
			foreach($keys as $key_to_pick){
				$picked[]=$sub_array[$key_to_pick];
			}
			$array_new[]=$picked;
		}
	}
	return $array_new;
}

/**
 * 
 * @param array $arrays
 * array(
 *	'签约'=>array(
 *		array(
 *			people=>1
 *			sum=>3
 *		),
 *		array(
 *			people=>2
 *			sum=>3
 *		)
 *	)
 *	'创收'=>array(
 *		array(
 *			people=>1
 *			sum=>3
 *		)
 *	)
 * )
 * @param type $key 'sum'
 * @param type $using 'people'
 * @return array(
 *	1=>array(
 *		签约=>3
 *		创收=>3
 *	)
 * )
 */
function array_join(array $arrays,$key,$using){
	$joined=array();
	foreach($arrays as $key => $array){
		foreach($array as $row){
			
		}
	}
}

/**
 * 判断某个值是否存在与某一数组的子数组下
 * 若指定$key_specified，则要判断子数组们的$key_specified键下是否有指定$needle值
 * 
 * 这在处理DB::result_array的结果数组时十分有用，其结果数组其中每一行又是一个数组
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

/**
 * 将数组的键作为路径，返回指定路径的子数组
 * 例如输入$array=array('a/b'=>1,'a/c'=>2,'b/a'=>3), $index='a'
 * 将返回array('b'=>1,'c'=>2);
 * @param $array
 * @param $prefix 路径
 * @param $prefix_end_with_slash 是否为prefix末尾加上'/' (default:true)
 * @param $preg 将prefix作为正则表达式匹配，由于匹配到的键名可能不唯一，因此将输出多个子数组形成的新数组
 * @return $subarray
 */
function array_prefix(array $array,$prefix,$preg=false,$prefix_end_with_slash=true){
	
	//数组中恰好存在与prefix一致的键名，则返回该键值
	if(!$preg && array_key_exists($prefix, $array)){
		return $array[$prefix];
	}
	
	if($prefix===''){
		return $array;
	}
	
	if(!$preg){
		$prefix=preg_quote($prefix,'/');
	}
	
	if($prefix_end_with_slash){
		$prefix.='\/';
	}

	$prefixed_array=array();

	foreach($array as $key => $value){
		$matches=array();
		preg_match("/^$prefix/",$key,$matches);
		if($matches){
			if($prefix_end_with_slash){
				$matches[0]=substr($matches[0],0,strlen($matches[0])-1);
			}
			$prefixed_array[$matches[0]][preg_replace("/^$prefix/", '', $key)]=$value;
		}
	}
	
	if($preg){
		return $prefixed_array;
	}else{
		return $prefixed_array?array_pop($prefixed_array):array();
	}
}

/**
 * 判断一个字符串是否为有效的json序列
 * @param type $string
 * @return type
 */
function is_json($string) {
	json_decode($string);
	return (json_last_error() === JSON_ERROR_NONE);
}

/**
 * 清除数组尾部的二级空数组
 * @param array $array
 * @return array
 */
function array_trim_rear(array $array){
	
	$return=$array;
	
	while(true){
		$tail=array_pop($array);
		if($tail===array()){
			$return=$array;
			continue;
		}else{
			break;
		}
	}

	return $return;
}

function array_remove_value(array &$array,$remove,$like=false){
	foreach($array as $key => $value){
		if(
			($like===false && $value==$remove)
			|| ($like===true && strpos($value,$remove)!==false)
			|| (is_callable($like) && $like($value,$remove))
		){
			unset($array[$key]);
		}
	}
}
?>
