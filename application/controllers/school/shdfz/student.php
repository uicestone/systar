<?php
class Student extends People{
	
	var $score_list_args=array();
	
	function __construct(){
		parent::__construct();
		$this->load->model('student_model','student');
		$this->load->model('classes_model','classes');
		$this->people=$this->student;
		
		$this->score_list_args=array(
			'exam_name'=>array('heading'=>'考试'),
			'语文'=>array('heading'=>'语文','cell'=>'{语文}<span class="rank">{rank_语文}</span>'),
			'数学'=>array('heading'=>'数学','cell'=>'{数学}<span class="rank">{rank_数学}</span>'),
			'英语'=>array('heading'=>'英语','cell'=>'{英语}<span class="rank">{rank_英语}</span>'),
			'物理'=>array('heading'=>'物理','cell'=>'{物理}<span class="rank">{rank_物理}</span>'),
			'化学'=>array('heading'=>'化学','cell'=>'{化学}<span class="rank">{rank_化学}</span>'),
			'生物'=>array('heading'=>'生物','cell'=>'{生物}<span class="rank">{rank_生物}</span>'),
			'地理'=>array('heading'=>'地理','cell'=>'{地理}<span class="rank">{rank_地理}</span>'),
			'历史'=>array('heading'=>'历史','cell'=>'{历史}<span class="rank">{rank_历史}</span>'),
			'政治'=>array('heading'=>'政治','cell'=>'{政治}<span class="rank">{rank_政治}</span>'),
			'信息'=>array('heading'=>'信息','cell'=>'{信息}<span class="rank">{rank_信息}</span>'),
			'3总'=>array('heading'=>'3总','cell'=>'{3总}<span class="rank">{rank_3总}</span>'),
			'4总/5总'=>array('heading'=>'4总/5总','cell'=>'{5总}<span class="rank">{rank_5总}</span>'),
			'8总'=>array('heading'=>'8总','cell'=>'{8总}<span class="rank">{rank_8总}</span>')
		);
		
		$this->group_list_args['name']=array(
			'heading'=>'名称','parser'=>array('function'=>function($name,$type,$accepted){
				$out=$name;
				if($type=='society'){
					if(is_null($accepted)){
						$out.=' 待批准加入';
					}elseif($accepted){
						$out.=' 已批准加入';
					}else{
						$out.=' 已拒绝加入';
					}
				}
				return $out;
			},'args'=>array('name','type','accepted'))
		);
		
		$this->load->view_path['edit']='student/edit';

	}
	
	function index(){
		
		$this->list_args=array(
			'num'=>array('heading'=>'学号'),
			'name'=>array('heading'=>'姓名'),
			'class_name'=>array('heading'=>'班级'),
			'tags'=>array('heading'=>'标签','parser'=>array('function'=>array($this->student,'getCompiledTags'),'args'=>array('id')))
		);
		
		parent::index();
		
	}

	function add(){
		//$this->edit();
	}
	
	function edit($id){
		$this->student->id=$id;
		
		try{
			$people=array_merge($this->student->fetch($id),$this->input->sessionPost('people'));
			$tags=$this->student->getTags($this->student->id);
			$meta=array_column($this->student->getMeta($this->student->id),'content','name');

			if(!$people['name'] && !$people['abbreviation']){
				$this->output->title='未命名'.lang(CONTROLLER);
			}else{
				$this->output->title=$people['abbreviation']?$people['abbreviation']:$people['name'];
			}

			$available_options=$this->student->getAllTags();
			$profile_name_options=$this->student->getMetaNames();
			
			$this->load->addViewData('class', $this->classes->fetchByStudent($this->student->id));
			$this->load->addViewData('score_list', $this->scoreList());
			$this->load->addViewData('status_list', $this->statusList());
			$this->load->addViewData('profile_list', $this->profileList());
			$this->load->addViewData('relative_list', $this->relativeList());
			$this->load->addViewData('team_list', $this->groupList());
			$this->load->addViewArrayData(compact('people','tags','meta','available_options','profile_name_options'));

		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
		
		parent::edit($id);
	}
	
	function mychild(){
		$this->config->set_user_item('search/everyone',false,false);
		$this->config->set_user_item('search/has_relative_like',$this->user->id,false);
		$this->index();
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
		
		$score_list=$this->table->setFields($this->score_list_args)
			->setData($this->student->getScores($this->student->id,array('limit'=>3,'orderby'=>'exam desc')))
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
				'语文'=>array('heading'=>'语文'),
				'数学'=>array('heading'=>'数学'),
				'英语'=>array('heading'=>'英语')
			);
			
			$menu=array('head'=>'<div class="right">'.$list_locator.'</div>');
			
			$table=$this->fetchTableArray($q, $field);
			
			$this->view_data+=compact('table','menu');
			
			$this->load->view('lists',$this->view_data);
		}
	}

	function viewscore($student){
		
		$this->output->title='成绩 - '.$this->student->fetch($student,'name');
		
		$exams_scores=$this->student->getscores($student,array('limit'=>'pagination','orderby'=>'exam asc'));
		
		$category=array_column($exams_scores,'exam_name');
		
		$courses=$this->tag->getList(array('type'=>'course'));
		
		$series=array();
		
		foreach($courses as $course){
			
			$scores=array_column($exams_scores,$course['name']);
			
			$has_score=false;
			
			foreach($scores as $score){
				if(!is_null($score)){
					$has_score=true;
					break;
				}
			}
			
			if($has_score){
				
				$series[]=array(
					'name'=>$course['name'],
					'color'=>'#'.$course['color'],
					'data'=>array_column($exams_scores,'rank_'.$course['name'],false,true)
				);
			}
		}
		
		$this->load->addViewData('series', json_encode($series,JSON_NUMERIC_CHECK));
		$this->load->addViewData('category', json_encode($category));
		
		$scores=$this->table->setFields($this->score_list_args)
			->setData($this->student->getscores($student,array('limit'=>'pagination','orderby'=>'exam desc')))
			->trimColumns()
			->generate();
		
		$this->load->addViewData('scores', $scores);
		
		$this->load->view('student/viewscore');
	}
}
?>