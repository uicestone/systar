<?php
class File extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		model('case');
			
		$q="
		SELECT
			case.id,case.name AS case_name,case.stage,case.time_contract,case.time_end,case.num,
			case.is_reviewed,case.apply_file,case.is_query,
			case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
			case.finance_review,case.info_review,case.manager_review,case.filed,
			lawyers.lawyers,
			file_status_grouped.status,file_status_grouped.staff AS staff,FROM_UNIXTIME(file_status_grouped.time,'%Y-%m-%d %H:%i:%s') AS status_time,
			contribute_allocate.contribute_sum,
			uncollected.uncollected,
			staff.name AS staff_name
		FROM 
			`case` INNER JOIN case_num ON `case`.id=case_num.`case`
		
			LEFT JOIN
			(
				SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
				FROM case_lawyer,staff 
				WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
				GROUP BY case_lawyer.`case`
			)lawyers
			ON `case`.id=lawyers.`case`
			
			LEFT JOIN (
				SELECT * FROM (
					SELECT `case`,status,staff,time FROM file_status ORDER BY time DESC
				)file_status_ordered
				GROUP BY `case`
			)file_status_grouped 
			ON case.id=file_status_grouped.case
			
			LEFT JOIN staff ON file_status_grouped.staff=staff.id
			
			LEFT JOIN 
			(
				SELECT `case`,SUM(contribute) AS contribute_sum
				FROM case_lawyer
				GROUP BY `case`
			)contribute_allocate
			ON `case`.id=contribute_allocate.case
			
			LEFT JOIN
			(
				SELECT `case`,IF(amount_sum IS NULL,fee_sum,fee_sum-amount_sum) AS uncollected FROM
				(
					SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' AND reviewed=0 GROUP BY `case`
				)case_fee_grouped
				LEFT JOIN
				(
					SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE reviewed=0 GROUP BY `case`
				)account_grouped
				USING (`case`)
			)uncollected
			ON case.id=uncollected.case
			
		WHERE case.display=1 AND case.id>=20 AND case.filed=1
		";
		
		$search_bar=$this->processSearch($q,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));
		
		$this->processOrderby($q,'time_contract','DESC',array('case.name','lawyers'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'num'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="case?edit={id}">{num}</a>'),
			'case_name'=>array('title'=>'案名'),
			'time_contract'=>array('title'=>'收案时间'),
			'time_end'=>array('title'=>'结案时间'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
			'status'=>array('title'=>'状态','td'=>'title="{status_time}"','eval'=>true,'content'=>"
				return case_getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			"),
			'staff_name'=>array('title'=>'人员')
		);
		
		$submitBar=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$submitBar);
	}

	function addStatus(){
		$action='addFileStatus';
		
		if(is_posted('fileStatusSubmit')){//获取表单数据并校验
			unset($_POST['fileStatusSubmit']);
			$action='insertFileStatus';
		
			$_SESSION['file']['post']=$_POST;
			
			foreach($_POST as $k => $v){
				if(!in_array($k,Array())){//可以不填项
					if ($v==''){
						showMessage('表格未填写完整','warning');$action='addFileStatus';break;//不满足插入条件，改变为填表动作
					}
				}
			}
		
			if($action=='insertFileStatus'){
				$_SESSION['file']['post']['file']=$_GET['addStatus'];
				$_SESSION['file']['post']['time']=time();
				db_insert('file_status',$_SESSION['file']['post']);
				unset($_SESSION['file']['post']);
				
				//redirect('/file');
			}
		}
		
		if($action=='addFileStatus'){
			require 'view/file_addStatus.php';
		}
	}
	
	function history(){
		$q="SELECT 
				file.id AS file,
				file.`case` AS `case`,
				file.lawyer AS lawyer,
				file.client AS client,
				file_status.status AS status,
				file.date_start AS date_start,
				file_status.time AS time,
				file_status.person AS person,
				file.comment AS comment
			FROM file,file_status
			WHERE file.id=file_status.file 
				AND file_status.id IN 
				(SELECT max(id) FROM file_status GROUP BY file)";
		
		$search_bar=$this->processSearch($q,array('case'=>'案件','lawyer'=>'律师','client'=>'客户'));
		
		$this->processOrderby($q,'time','DESC',array('case','client','lawyer','status'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=Array(
			'file'=>array('title'=>'序号','td_title'=>'width=50px'),
			'case'=>array('title'=>'案件','surround'=>array('mark'=>'a','href'=>'/file?view={file}','target'=>'blank')),
			'client'=>'客户','lawyer'=>'承办律师',
			'date_start'=>array('title'=>'收案日期','eval'=>true,'content'=>"
				return date('Y年m月d日',{date_start});
			"),
			'status'=>array('title'=>'状态','surround'=>array('mark'=>'a','href'=>"/file?addStatus={file}")),
			'time'=>array('title'=>'更新时间','eval'=>true,'content'=>"
				return date('Y年m月d日',{time});
			"),
			'comment'=>'备注'
		);
		
		$submitBar=array(
		'head'=>'<div style="float:left;">'.
					(option('in_search_mod')?'<button type="button" value="searchCancel" onclick="redirectPara(this)">取消搜索</button>':'').
				'</div>'.
				'<div style="float:right;">'.
					$listLocator.
				'</div>',
		);
		
		exportTable($q,$field,$submitBar,true);
		
		require 'view/file_list_sidebar.htm';
	}

	function tobe(){
		model('case');
		
		$q="
		SELECT
			case.id,case.name,case.num,case.stage,case.time_contract,case.time_end,
			case.is_reviewed,case.apply_file,case.is_query,
			case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
			case.finance_review,case.info_review,case.manager_review,case.filed,
			contribute_allocate.contribute_sum,
			uncollected.uncollected,
			lawyers.lawyers
		
		FROM 
			`case` LEFT JOIN
			(
				SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
				FROM case_lawyer,staff 
				WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
				GROUP BY case_lawyer.`case`
			)lawyers
			ON `case`.id=lawyers.`case`
		
			LEFT JOIN 
			(
				SELECT `case`,SUM(contribute) AS contribute_sum
				FROM case_lawyer
				GROUP BY `case`
			)contribute_allocate
			ON `case`.id=contribute_allocate.case
			
			LEFT JOIN
			(
				SELECT `case`,IF(amount_sum IS NULL,fee_sum,fee_sum-amount_sum) AS uncollected FROM
				(
					SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' AND reviewed=0 GROUP BY `case`
				)case_fee_grouped
				LEFT JOIN
				(
					SELECT `case`, SUM(amount) AS amount_sum FROM account WHERE reviewed=0 GROUP BY `case`
				)account_grouped
				USING (`case`)
			)uncollected
			ON case.id=uncollected.case
			
		WHERE case.display=1 AND case.id>=20 AND case.apply_file=1 AND filed=0
		";
		
		$search_bar=$this->processSearch($q,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));
		
		$this->processOrderby($q,'case.time_contract','ASC',array('case.name','lawyers'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'num'=>array('title'=>'案号','content'=>'<a href="case?edit={id}">{num}</a>','td_title'=>'width="180px"'),
			'name'=>array('title'=>'案名','content'=>'{name}'),
			'time_contract'=>array('title'=>'收案时间'),
			'time_end'=>array('title'=>'结案时间'),
			'lawyers'=>array('title'=>'主办律师'),
			'status'=>array('title'=>'状态','td_title'=>'width="75px"','td'=>'title="{status_time}"','eval'=>true,'content'=>"
				return case_getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			")
		);
		
		$submitBar=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$submitBar);
	}
	
	function view(){
		$q="SELECT *,FROM_UNIXTIME(time,'%Y-%m-%d') AS time 
			FROM `file`,`file_status` 
			WHERE file.id=file_status.file 
				AND file.id='".$_GET['view']."'";
		
		$this->processOrderby($q,'time','DESC');
		
		$field=Array('file'=>'序号','client'=>'客户','case'=>'案件','lawyer'=>'承办律师','status'=>'状态','time'=>'时间','person'=>'借阅人','comment'=>'备注');
		
		exportTable($q,$field);
	}
}
?>