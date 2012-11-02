<?php
class Property extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="SELECT 
				property.id AS property,
				property.name AS name,
				property.admin AS admin,
				property_status.status AS status,
				if(property_status.is_out=1,'是','') AS is_out,
				FROM_UNIXTIME(property_status.time,'%Y-%m-%d') as time,
				property_status.usingPerson AS usingPerson,
				property.comment
			FROM property,property_status
			WHERE property.id=property_status.property 
				AND property_status.id IN 
				(SELECT max(id) FROM property_status GROUP BY property)";
		
		$searchBar=$this->processSearch($q,array('name'=>'物品','admin'=>'管理人'));
		
		$this->processOrderby($q,'time','DESC',array('property'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=Array(
			'property'=>'序号',
			'name'=>array('title'=>'物品','surround'=>array('mark'=>'a','href'=>'/property?view={property}','target'=>'blank')),
			'time'=>'更新时间','admin'=>'管理人',
				'status'=>array('title'=>'目前状态','content'=>'{status} <a href="/property?addStatus={property}" style="font-size:10px;">更新</a>'),
			'usingPerson'=>'经手人',
			'comment'=>'备注');
		
		
		$menu=array(
		'head'=>'<div style="float:left;">'.
					'<input type="submit" name="delete" value="删除" />'.
					(option('in_search_mod')?'<button type="button" value="searchCancel" onclick="redirectPara(this)">取消搜索</button>':'').
				'</div>'.
				'<div style="float:right;">'.
					$listLocator.
				'</div>',
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table','menu');
		
		$this->load->view('lists',$this->view_data);
	}

	function add(){
		$action='addProperty';
		
		if(is_posted('propertySubmit')){//获取表单数据并校验
			unset($_POST['propertySubmit']);
			$action='insertProperty';
		
			$_SESSION['property']['post']=$_POST;
			
			foreach($_POST as $k => $v){
				if(!in_array($k,Array('comment'))){//可以不填项
					if ($v==''){
						showMessage('表格未填写完整','warning');$action='addProperty';break;//不满足插入条件，改变为填表动作
					}
				}
			}
			if($action=='insertProperty'){
				$property=db_insert('property',$_SESSION['property']['post']);
				unset($_SESSION['property']['post']);
				
				$_SESSION['property']['post']['property']=$property;
				$_SESSION['property']['post']['time']=time();
				$_SESSION['property']['post']['status']='新添加';
				$_SESSION['property']['post']['is_out']=0;
				db_insert('property_status',$_SESSION['property']['post']);
				unset($_SESSION['property']['post']);
		
				$action='addProperty';
				showMessage('添加成功，可以继续添加','notice');
			}
		}
		if($action=='addProperty'){
			require 'view/property_add.php';
		}
	}
	
	function addStatus(){
		$action='addPropertyStatus';
		
		if(is_posted('propertyStatusSubmit')){//获取表单数据并校验
			unset($_POST['propertyStatusSubmit']);
			$action='insertPropertyStatus';
		
			$_SESSION['property']['post']=$_POST;
			
			foreach($_POST as $k => $v){
				if(!in_array($k,Array())){//可以不填项
					if ($v==''){
						showMessage('表格未填写完整','warning');$action='addPropertyStatus';break;//不满足插入条件，改变为填表动作
					}
				}
			}
		
			if($action=='insertPropertyStatus'){
				$_SESSION['property']['post']['property']=$_GET['addStatus'];
				$_SESSION['property']['post']['time']=time();
				db_insert('property_status',$_SESSION['property']['post']);
				unset($_SESSION['property']['post']);
				
				redirect('property');
			}
		}
		
		if($action=='addPropertyStatus'){
			require 'view/property_addStatus.php';
		}
	}
	
	function view(){
		$q="SELECT * 
			FROM `property`,`property_status` 
			WHERE property.id=property_status.property 
				AND property.id='".$_GET['view']."'";
		
		$this->processOrderby($q,'time','DESC');
		
		$field=Array('property'=>'序号','num'=>'编号','name'=>'物品','status'=>'目前状态','time'=>'时间','usingPerson'=>'经手人','comment'=>'备注');
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table');
		
		$this->load->view('lists',$this->view_data);
	}
}
?>