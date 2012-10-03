<?php
class Teach extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="SELECT 
				teach.id AS id,
				staff.name AS teacher,
				course.name AS course,
				class.id AS class,
				class.name AS class_name,
				teach.term AS term 
			FROM teach,staff,class,course 
			WHERE
				staff.company='".$_G['company']."'
				AND staff.id = teach.teacher 
				AND class.id = teach.class 
				AND course.id = staff.course 
				AND class.grade >=".$_SESSION['global']['highest_grade'];
		
		addCondition($q,array('class'=>'class.id','grade'=>'class.grade','term'=>'teach.term'));
		
		$search_bar=processSearch($q,array('class.name'=>'班级','course.name'=>'学科','staff.name'=>'教师'));
		
		$q.=" ORDER BY term DESC,class.id,course.id ASC";
		
		$listLocator=processMultiPage($q);
		
		$field=array(
			'id'=>array('title'=>'','content'=>'<input type="checkbox" name="teach[{id}]">','td_title'=>'width="30px"'),
			'class_name'=>array('title'=>'班级','orderby'=>false),
			'course'=>array('title'=>'学科','orderby'=>false),
			'staff'=>array('title'=>'教师','orderby'=>false),
			'term'=>array('title'=>'学期','orderby'=>false),
			'changeTo'=>array('title'=>'本学期','orderby'=>false,'content'=>"<input type='text' name='changeTo[{class}][{id}]' size='5'>",'td_title'=>'width="82px"','td_body'=>'align="center"'),
			'unchanged'=>array('title'=>'未变','orderby'=>false,'content'=>'<input type="checkbox" name="unchanged[{id}]">','td_title'=>'width="60px"','td_body'=>'align="center"'),
		);
		
		$submitBar=array(
			'head'=>'<div class="left">'.
						'<input type="submit" name="delete" value="删除" />'.
					'</div>'.
					'<div class="right">'.
						$listLocator.
						'<input type="submit" value="保存" name="teachListSubmit" />'.
					'</div>',
		);
		
		exportTable($q,$field,$submitBar,true);
	}
}
?>