<?php
class SS_Loader extends CI_Loader{
	
	var $view_data=array();//要传递给视图的参数
	
	var $view_path=array();
	
	var $blocks=array();
	
	var $inner_js='';
	
	function __construct(){
		parent::__construct();
	}

	function getViewData($param=NULL){
		if(isset($param)){
			return $this->view_data[$param];
		}else{
			return $this->view_data;
		}
	}
	
	/**
	 * 将数据传输给视图
	 * @param $name 视图中可以调用的变量名
	 * @param $value 数据
	 */
	function addViewData($name,$value){
		$this->view_data+=array($name=>$value);
	}
	
	/**
	 * 将数据传输给视图（数组形式）
	 * @param array $array 数据 Array(视图中可以调用的变量名=>值,..)
	 */
	function addViewArrayData(array $array){
		$this->view_data+=$array;
	}
	
	/**
	 * @param $part_name: FALSE:进入输出缓存, 否则存入Loader::part[$part_name]
	 */
	function view($view, $return=FALSE, $block_name = FALSE){
		
		if(array_key_exists($view, $this->view_path)){
			$view=$this->view_path[$view];
		}
		
		$vars=$this->getViewData();//每次载入视图时，都将当前视图数据传递给他一次
		
		if($block_name===FALSE){
			return parent::view($view, $vars, $return);
		}
		else{
			if(!array_key_exists($block_name, $this->blocks)){
				$this->blocks[$block_name]='';
			}
			
			$block=parent::view($view, $vars, TRUE);
			
			$this->blocks[$block_name].=$block;
			
			return $block;
		}
		
	}
	
	/**
	 * 在view中载入js的简写
	 * @param string $js_file_path js文件的路径文件名（不含"web/js/"和".js"）
	 */
	function javascript($js_file_path){
		$path= $js_file_path.'.js';
		
		if(!file_exists($path)){
			//找不到文件？我们看看这个文件是不是需要根据其他文件合并
			$this->config('minify');
			$sources=$this->config->item('minify_source');
			if(!array_key_exists($path, $sources)){
				//配置文件中没有发现合并列表？放弃吧
				return;
			}else{
				if(true || ENVIRONMENT==='development'){
					//开发环境下，直接根据合并列表分别载入所有文件
					$html='';
					foreach($sources[$path] as $source){
						$hash=filemtime($source);
						$html.='<script type="text/javascript" src="/'.$source.'?'.$hash.'"></script>'."\n";
					}
					return $html;
				}else{
					//测试或生产环境下，合并并保存文件
					$this->driver('minify');
					$CI=&get_instance();
					$combined = $CI->minify->combine_files($sources[$path], 'js', false);
					$CI->minify->save_file($combined, $path);
				}
			}
		}
		
		$hash=filemtime($path);
		return '<script type="text/javascript" src="/'.$path.'?'.$hash.'"></script>'."\n";
	}

	/**
	 * 在view中载入外部css链接的简写
	 */
	function stylesheet($css_file_path){
		$path=$css_file_path.'.css';
		
		if(!file_exists($path)){
			//找不到文件？我们看看这个文件是不是需要根据其他文件合并
			$this->config('minify');
			$sources=$this->config->item('minify_source');

			if(!array_key_exists($path, $sources)){
				//配置文件中没有发现合并列表？放弃吧
				return;
			}else{
				if(true || ENVIRONMENT==='development'){
					//开发环境下，直接根据合并列表分别载入所有文件
					$html='';
					foreach($sources[$path] as $source){
						$hash=filemtime($source);
						$html.="<link rel=\"stylesheet\" href=\"/$source?$hash\" type=\"text/css\" />\n";
					}
					return $html;
				}else{
					//测试或生产环境下，合并并保存文件
					$this->driver('minify');
					$CI=&get_instance();
					$combined = $CI->minify->combine_files($sources[$path], 'css', false);
					$CI->minify->save_file($combined, $path);
				}
			}
		}
		
		$hash=filemtime($path);
		return "<link rel=\"stylesheet\" href=\"/$path?$hash\" type=\"text/css\" />\n";
	}

	/**
	 * 从$_SESSION[CONTROLLER][post][对象ID]中取得相应值，取不到的话从Loader::view_data里取
	 * @param $index
	 * @return mixed
	 */
	function value($index){
		if(post($index)!==false){
			return post($index);
		}else{
			$CI=&get_instance();

			$view_data=$CI->load->view_data;

			$index_array=explode('/',$index);

			if(isset($view_data[$index_array[0]])){
				$value=$view_data[$index_array[0]];
			}else{
				return;
			}

			for($i=1;$i<count($index_array);$i++){
				if(isset($value[$index_array[$i]])){
					$value=$value[$index_array[$i]];
				}else{
					return;
				}
			}

			return $value;
		}
	}
	
}
?>