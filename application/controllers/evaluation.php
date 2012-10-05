<?php
class Evaluation extends SS_controller{
	function __construct(){
		parent::__construct();
		$this->default_method='comment';
	}
	
	function staffList(){
		$q="
		SELECT staff.id,staff.name,position.ui_name AS position_name
		FROM staff
			INNER JOIN position ON position.id=staff.position
		";
		
		$this->processOrderby($q,'id');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'name'=>array('title'=>'姓名','surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'evaluation?score&staff={id}\')')),
			'position.id'=>array('title'=>'职位','content'=>'{position_name}')
		);
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);
	}
	
	function comment(){
		$evaluation_result=array(
			'_field'=>array(
				'互评分',
				'自评分',
				'主管分'
			),
			array(
				$this->evaluation->getPeer(),
				$this->evaluation->getSelf(),
				$this->evaluation->getManager()
			)
		);
		
		$q="
		SELECT evaluation_indicator.name,evaluation_indicator.weight,
			evaluation_score.comment,
			position.ui_name AS position_name,
			staff.name AS staff_name
		FROM evaluation_score 
			INNER JOIN evaluation_indicator ON evaluation_indicator.id=evaluation_score.indicator AND evaluation_score.quarter='".$this->config->item('quarter')."'
			INNER JOIN staff ON staff.id=evaluation_score.uid
			INNER JOIN position ON evaluation_indicator.critic=position.id
		WHERE comment IS NOT NULL AND staff='".$_SESSION['id']."'
		";
		
		$this->processOrderby($q,'evaluation_score.time','DESC');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'name'=>array('title'=>'评分项','content'=>'{name}({weight})'),
			'comment'=>array('title'=>'附言'),
			'staff_name'=>array('title'=>'评分人','content'=>'{staff_name}({position_name})')
		);
		
		$menu=array(
			'head'=>'<div class="right">'.
						$listLocator.
					'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);
	}
	
	function result(){
		$q="
			SELECT each_other.staff,staff.name AS staff_name,each_other.score AS each_other,each_other.critics,self.score AS self,manager.score AS manager
			FROM
			(
				SELECT staff,AVG(sum_score) AS score,COUNT(sum_score) AS critics
				FROM (
				SELECT staff,SUM(score) AS sum_score
				FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
				WHERE uid <> '6356' AND staff<>uid
				GROUP BY uid,staff
				)sum
				GROUP BY staff
			)each_other
			LEFT JOIN(
				SELECT staff,SUM(score) AS score
				FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
				WHERE uid = '6356'
				GROUP BY uid,staff
			)manager USING (staff) 
			LEFT JOIN(
				SELECT staff,SUM(score) AS score
				FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
				WHERE uid = staff
				GROUP BY uid,staff
			)self USING(staff)
			INNER JOIN staff ON staff.id=each_other.staff	
		";
		
		$this->processOrderby($q,'staff');
		
		$field=array(
			'staff_name'=>array('title'=>'姓名'),
			'each_other'=>array('title'=>'互评','content'=>'{each_other}({critics})'),
			'self'=>array('title'=>'自评'),
			'manager'=>array('title'=>'主管评分')
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);
	}
	
	function scoreWrite(){$staff=intval($_GET['staff']);
		$indicator=intval($_POST['indicator']);
		//$anonymous=intval($_POST['anonymous']);
		
		$field=$value=NULL;
		
		if(is_posted('field') && is_posted('value')){
			$field=$_POST['field'];
			$value=$_POST['value'];
		}
		
		if($evaluation_insert_score=evaluation_insert_score($indicator,$staff,$field,$value/*,$anonymous*/)){
			echo json_encode($evaluation_insert_score);
		}
	}
	
	function score(){
		$staff=intval($_GET['staff']);
		
		$position=db_fetch_field("SELECT position FROM staff WHERE id='".$staff."'");
		
		$q="
		SELECT evaluation_indicator.id,evaluation_indicator.name,evaluation_indicator.weight,
			evaluation_score.id AS score_id,evaluation_score.score,evaluation_score.comment		#,evaluation_score.anonymous
		FROM evaluation_indicator 
			LEFT JOIN evaluation_score ON (
				evaluation_indicator.id=evaluation_score.indicator 
				AND staff='".$staff."' 
				AND uid='".$_SESSION['id']."'
			)
		WHERE critic='".$_SESSION['position']."'
			AND position='".$position."'
		";
		
		$this->processOrderby($q,'id');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'name'=>array('title'=>'考核指标','td'=>'id="{id}"','content'=>'{name}({weight})','td_title'=>'width="20%"'),
			/*'anonymous'=>array('title'=>'匿名','td_title'=>'width=55px"','td'=>'style="text-align:center"','eval'=>true,'content'=>"
				return '<input type=\"checkbox\" value=\"1\" '.(!'{score_id}' || '{anonymous}'?'checked=\"checked\"':'').' />';
			"),*/
			'score'=>array('title'=>'分数','td_title'=>'width="70px"','eval'=>true,'content'=>"
				if('{score}'==0){
					return '<input type=\"text\" style=\"width:50px;\" />';
				}else{
					return '<span>{score}</span>';
				}
			"),
			'comment'=>array('title'=>'附言','eval'=>true,'content'=>"
				if(!'{comment}'){
					return '<input type=\"text\" />';
				}else{
					return '<span>{comment}</span>';
				}
			")
		);
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);
	}
}
?>