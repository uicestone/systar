<?php
class Student extends People{
	
	var $section_title='学生';
	
	function __construct(){
		parent::__construct();
		$this->people=$this->student;
		$this->load->model('classes_model','classes');
	}
	
	function index(){
		
		$this->list_args=array(
			'num'=>array('heading'=>'学号'),
			'name'=>array('heading'=>'姓名'),
			'class_name'=>array('heading'=>'班级'),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->people,'getCompiledLabels'),'args'=>array('{id}')))
		);
		
		option('search/type','学生');
		
		if($this->input->get('update')){
			$this->student->updateView();
			$this->output->message('学生视图更新完成');
		}
		
		parent::index();
		
	}

	function add(){
		//$this->edit();
	}
	
	function edit($id){
		$this->people->id=$id;
		
		try{
			$people=array_merge($this->people->fetch($id),$this->input->sessionPost('people'));
			$labels=$this->people->getLabels($this->people->id);
			$profiles=array_sub($this->people->getProfiles($this->people->id),'content','name');

			if(!$people['name'] && !$people['abbreviation']){
				$this->section_title='未命名'.$this->section_title;
			}else{
				$this->section_title=$people['abbreviation']?$people['abbreviation']:$people['name'];
			}

			$available_options=$this->people->getAllLabels();
			$profile_name_options=$this->people->getProfileNames();

			$this->load->addViewData('score_list', $this->scoreList());
			$this->load->addViewData('profile_list', $this->profileList());
			$this->load->addViewData('relative_list', $this->relativeList());
			$this->load->addViewArrayData(compact('controller','people','labels','profiles','available_options','profile_name_options'));

			$this->load->view('student/edit');
			$this->load->view('people/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
	}
	
	function relativeList($id=NULL,$list_args=NULL){
		if(is_null($list_args)){
			$list_args=array(
				'name'=>array('heading'=>'名称','cell'=>'{name}'), 
				'phone'=>array('heading'=>'电话'), 
				'email'=>array('heading'=>'电邮'), 
				'relation'=>array('heading'=>'关系')
			);
		}
		return parent::relativeList($id, $list_args);
	}
	
	function scoreList(){
		
		$list_args=array(
			'exam_name'=>array('heading'=>'考试'),
			'course_1'=>array('heading'=>'语文','cell'=>'{course_1}<span class="rank">{rank_1}</span>'),
			'course_2'=>array('heading'=>'数学','cell'=>'{course_2}<span class="rank">{rank_2}</span>'),
			'course_3'=>array('heading'=>'英语','cell'=>'{course_3}<span class="rank">{rank_3}</span>'),
			'course_4'=>array('heading'=>'物理','cell'=>'{course_4}<span class="rank">{rank_4}</span>'),
			'course_5'=>array('heading'=>'化学','cell'=>'{course_5}<span class="rank">{rank_5}</span>'),
			'course_6'=>array('heading'=>'生物','cell'=>'{course_6}<span class="rank">{rank_6}</span>'),
			'course_7'=>array('heading'=>'地理','cell'=>'{course_7}<span class="rank">{rank_7}</span>'),
			'course_8'=>array('heading'=>'历史','cell'=>'{course_8}<span class="rank">{rank_8}</span>'),
			'course_9'=>array('heading'=>'政治','cell'=>'{course_9}<span class="rank">{rank_9}</span>'),
			'course_10'=>array('heading'=>'信息','cell'=>'{course_10}<span class="rank">{rank_10}</span>'),
			'course_sum_3'=>array('heading'=>'3总','cell'=>'{course_sum_3}<span class="rank">{rank_sum_3}</span>'),
			'course_sum_5'=>array('heading'=>'4总/5总','cell'=>'{course_sum_5}<span class="rank">{rank_sum_5}</span>'),
			'course_sum_8'=>array('heading'=>'8总','cell'=>'{course_sum_8}<span class="rank">{rank_sum_8}</span>')
		);
		
		$score_list=$this->table->setFields($list_args)
			->setData($this->student->getScores($this->student->id))
			->trimColumns()
			->generate();
		
		return $score_list;
	}
	
	function classDiv(){
		$classes=2;
		$subjects=4;
		
		if($this->input->get('run')){
			set_time_limit(0);
		
			$data=db_toArray("
				SELECT id,gender,
					course_1 AS `0`,
					course_2 AS `1`,
					course_3 AS `2`,
					extra_course_view_score AS `3`
				FROM student_classdiv 
				WHERE type<>'借读'
					AND new_class IS NULL
					AND extra_course=5
			");
			
			//将数组key改为id
			$data_tmp=array();
			
			foreach($data as $key => $value){
				$data_tmp[$value['id']]=$value;
			}
			
			$data=$data_tmp;
			
			$students=count($data);
			
			//print_r($data);
			
			/*$data:array(
				学号=>$student:array(
					id=>学号
					gender=>性别
					1=>学科成绩
					2=>学科成绩
					...
				)
			)*/
			
			$exchanges=$tests=0;
			
			//每个性别一个数组
			$student_list_div_by_gender=array();
			foreach($data as $id => $line_data){
				$student_list_div_by_gender[$line_data['gender']][]=$line_data;
			}
			//print_r($student_list_div_by_gender);
			
			//生成第一种分班方案
			$div=array();
			for($gender=0;$gender<2;$gender++){
				for($i=0;$student=array_pop($student_list_div_by_gender[$gender]);$i++){
					$div[$gender][$i % $classes][]=$student['id'];
				}
				unset($student);
			}
			
			//print_r($div);
			
			/*分班方案$div:array(
				1(性别)=>array(
					1(班号)=>array(
						序号=>学号
					)
				)
				2=>array(
					...
				)
			)*/
			
			forceExport();
			
			while(!isset($foundBest[1]) || !isset($foundBest[0])){
				for($gender=0;$gender<2;$gender++){
					if(isset($foundBest[$gender])){
						continue;
					}
					$former_s=$s=student_testClassDiv($div,$data,$classes,$gender);
					$exchange=array();
					for($class_l=0;$class_l<$classes;$class_l++){
						for($class_r=$class_l+1;$class_r<$classes;$class_r++){
							for($student_l=0;$student_l<count($div[$gender][$class_l]);$student_l++){
								for($student_r=0;$student_r<count($div[$gender][$class_r]);$student_r++){
									$new_div[$gender]=$div[$gender];
									$tmp_student=$new_div[$gender][$class_r][$student_r];
									$new_div[$gender][$class_r][$student_r]=$new_div[$gender][$class_l][$student_l];
									$new_div[$gender][$class_l][$student_l]=$tmp_student;
									$t=student_testClassDiv($new_div,$data,$classes,$gender);
									$exchange[]=array('class_l'=>$class_l,'class_r'=>$class_r,'student_l'=>$student_l,'student_r'=>$student_r,'differ'=>$s-$t);
								}
							}
						}
					}
					$max_differ=0;
					foreach($exchange as $id=>$exchange_plan){
						if($exchange_plan['differ']>$max_differ){
							$max_differ=$exchange_plan['differ'];
							$max_id=$id;
						}
					}
					if($max_differ>0){
						$tmp_student=$div[$gender][$exchange[$max_id]['class_r']][$exchange[$max_id]['student_r']];
						$div[$gender][$exchange[$max_id]['class_r']][$exchange[$max_id]['student_r']]=$div[$gender][$exchange[$max_id]['class_l']][$exchange[$max_id]['student_l']];
						$div[$gender][$exchange[$max_id]['class_l']][$exchange[$max_id]['student_l']]=$tmp_student;
						
						$s=student_testClassDiv($div,$data,$classes,$gender);
						
						echo 'gender='.$gender.', s='.$s."<br>\n";flush();
			
						$exchanges++;
					}
			
					
					if($s==$former_s){
						$foundBest[$gender]=true;
					}
				}
				
				echo "\n".$tests.'tests, '.$exchanges.'exchanges'."<br><br>\n\n";flush();
			}
			echo 'completed';
			
			//student_testClassDiv($div,$data,$classes,0,true);
			//student_testClassDiv($div,$data,$classes,1,true);
			//print_r($div);
			
			foreach($div as $gender_in_array1 => $array1){
				foreach($array1 as $class=>$array2){
					$this->db->update('student_classdiv',array('new_class'=>$class),'id IN ('.implode(',',$array2).')');
				}
			}
		}else{
		
			$q="SELECT * FROM student_classdiv";
			
			$list_locator=$this->processMultiPage($q);
			
			$field=array(
				'name'=>array('heading'=>'姓名'),
				'gender'=>array('heading'=>'性别'),
				'course_1'=>array('heading'=>'语文'),
				'course_2'=>array('heading'=>'数学'),
				'course_3'=>array('heading'=>'英语')
			);
			
			$menu=array('head'=>'<div class="right">'.$list_locator.'</div>');
			
			$table=$this->fetchTableArray($q, $field);
			
			$this->view_data+=compact('table','menu');
			
			$this->load->view('lists',$this->view_data);
		}
	}

	/**
	 * 家校互动
	 */
	function interactive(){
		
		if($this->input->post('submit')){
			$submitable=true;
			
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$this->input->post());
			
			if(post('student_comment/reply_to',$this->user->check(post('student_comment_extra/reply_to_username')))<0){
				$submitable=false;
			}
			
			if($this->user->isLogged('parent')){
				$student_id=$this->student->getIdByParentUid($this->user->id);
			}else{
				$student_id=$this->student->getIdByParentUid(post('student_comment/reply_to'));
			}
			
			if($submitable){
				$this->student->addComment($student_id,post('student_comment'));
				unset($_SESSION[CONTROLLER]['post']['student_comment']);
				unset($_SESSION[CONTROLLER]['post']['student_comment_extra']);
			}
		}
		
		$field=array(
			'date'=>array('heading'=>array('data'=>'日期','width'=>'100px')),
			'username'=>array('heading'=>array('data'=>'用户','width'=>'120px')),
			'student_name'=>array('heading'=>array('data'=>'学生','width'=>'60px'),'wrap'=>array('mark'=>'a','href'=>'student?edit={student}')),
			'title'=>array('heading'=>array('data'=>'标题','width'=>'120px'),'cell'=>array('class'=>'ellipsis','title'=>'{title}')),
			'content'=>array('heading'=>'内容','cell'=>array('class'=>'ellipsis','title'=>'{content}'))
		);
		$list=$this->table->setFields($field)
			->setData($this->student->getInteractiveList())
			->generate();
		$this->load->addViewData('list', $list);
	}
	
	function view_score(){
		//TODO 图与表的sql请求合一
		if($this->user->isLogged('student')){
			$student=$this->user->id;
		}elseif($this->user->isLogged('parent')){
			$student=$_SESSION['child'];
		}else{
			$student=intval($this->input->get('student'));
		}
		
		$course_array=db_toArray("SELECT id,name,chart_color FROM course",true);
		
		$view_score_array=db_toArray("SELECT * FROM school_view_view_score WHERE student = '".$student."' ORDER BY exam");
		
		$category=$series_raw=$series=array();
		
		foreach($view_score_array as $view_score_line_id => $view_score){
			$category[]=$view_score['exam_name'];
			foreach($course_array as $course_id => $course){
				if(!isset($series_raw[$course_id])){
					$series_raw[$course_id]=array('name'=>$course['name'],'color'=>'#'.$course['chart_color']);
				}
				if(isset($view_score['rank_'.$course_id])){
					$series_raw[$course_id]['data'][]=$view_score['rank_'.$course_id];
				}else{
					$series_raw[$course_id]['data'][]=NULL;
				}
			}
		}
		
		foreach($series_raw as $series_id => $series_single){
			//将$series_raw中有分数的系列取出
			if(isset($series_single['data']) && array_sum($series_single['data'])>0){//data不都为NULL
				$series[]=$series_single;
			}
		}
		
		$series=json_encode($series,JSON_NUMERIC_CHECK);
		$category=json_encode($category);
		$this->load->addViewArrayData(compact('series','category'));
		
		$fields_view_scores=array(
			'exam_name'=>array('heading'=>'考试'),
			'course_1'=>array('heading'=>'语文','cell'=>'{course_1}<span class="rank">{rank_1}</span>'),
			'course_2'=>array('heading'=>'数学','cell'=>'{course_2}<span class="rank">{rank_2}</span>'),
			'course_3'=>array('heading'=>'英语','cell'=>'{course_3}<span class="rank">{rank_3}</span>'),
			'course_4'=>array('heading'=>'物理','cell'=>'{course_4}<span class="rank">{rank_4}</span>'),
			'course_5'=>array('heading'=>'化学','cell'=>'{course_5}<span class="rank">{rank_5}</span>'),
			'course_6'=>array('heading'=>'生物','cell'=>'{course_6}<span class="rank">{rank_6}</span>'),
			'course_7'=>array('heading'=>'地理','cell'=>'{course_7}<span class="rank">{rank_7}</span>'),
			'course_8'=>array('heading'=>'历史','cell'=>'{course_8}<span class="rank">{rank_8}</span>'),
			'course_9'=>array('heading'=>'政治','cell'=>'{course_9}<span class="rank">{rank_9}</span>'),
			'course_10'=>array('heading'=>'信息','cell'=>'{course_10}<span class="rank">{rank_10}</span>'),
			'course_sum_3'=>array('heading'=>'3总','cell'=>'{course_sum_3}<span class="rank">{rank_sum_3}</span>'),
			'course_sum_5'=>array('heading'=>'4总/5总','cell'=>'{course_sum_5}<span class="rank">{rank_sum_5}</span>'),
			'course_sum_8'=>array('heading'=>'8总','cell'=>'{course_sum_8}<span class="rank">{rank_sum_8}</span>')
		);

		$view_scores=$this->table->setFields($fields_view_scores)
			->trimColumns()
			->generate($this->student->getview_scores($student));
		
		$this->load->addViewData('view_scores', $view_scores);
	}
}
?>