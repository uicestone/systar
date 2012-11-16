<?php
class Student extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		//如果以家长或学生身份登陆，显示的是编辑查看页面，而非列表页面
		if(is_logged('parent') || is_logged('student')){

			$this->as_controller_default_page=true;
			
			if(is_logged('student')){
				post('student/id',$_SESSION['id']);
	
			}elseif(is_logged('parent')){
				post('student/id',$_SESSION['child']);
	
			}
			
			$this->edit(post('student/id'));
			
			return;
		}
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));
		
		if($this->input->get('update')){
			$this->student->update();
			showMessage('学生视图更新完成');
		}
		
		$field=array(
			'num'=>array('title'=>'学号','td'=>'id="{id}" '),
			'student.name'=>array('title'=>'姓名','content'=>'<a href="/student/edit/{id}">{name}</a>'),
			'student_num.class'=>array('title'=>'班级','content'=>'{class_name}')
		);

		if(is_logged('health')){
			$field+=array(
				'id_card'=>array('title'=>'身份证'),
				'mobile'=>array('title'=>'手机'),
				'relatives_contacts'=>array('title'=>'亲属电话'),
				'phone'=>array('title'=>'家庭电话'),
				'address'=>array('title'=>'家庭地址')
			);
		}
		
		if(is_logged('jiaowu')){
			$field['student_num.class']['td']='class="editable"';
		}
		
		$list=$this->table->setFields($field)
				->setData($this->student->getList())
				->generate();
		
		$this->load->addViewData('list', $list);
	}

	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		if($this->as_controller_default_page){
			$this->session->set_userdata('last_list_action', $this->input->server('REQUEST_URI'));
		}
		
		$this->getPostData($id,function($CI){
			post('student/name','新学生'.$CI->config->item('timestamp'));
			
			post(CONTROLLER.'/id',$CI->db->insert('user',array('group'=>'student')));
			//先创建用户，再创建学生
			
			$CI->db->insert(CONTROLLER,post(CONTROLLER));
		},false);
		
		$student_class=$this->student->fetchClassInfo(post('student/id'));
		post('student_class',array('class'=>$student_class['class'],'num_in_class'=>$student_class['num_in_class']));
		post('classes/name',$student_class['class_name']);
		isset($student_class['class_teacher_name']) && post('student_extra/class_teacher_name',$student_class['class_teacher_name']);
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if($this->input->post('submit')){
			$submitable=true;
		
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$_POST);
			
			if(is_posted('submit/student_relatives')){
				$this->student->addRelatives(post('student/id'),post('student_relatives'));
				unset($_SESSION[CONTROLLER]['post']['student_relatives']);
			}
			
			if(is_posted('submit/student_relatives_delete')){
				$this->student->deleteRelatives(post('student_relatives_check'));
			}
			
			if(is_posted('submit/student_behaviour')){
				if($this->student->addBehaviour(post('student/id'),post('student_behaviour'))){
					unset($_SESSION[CONTROLLER]['post']['student_behaviour']);
				}else{
					$submitable=false;
				}
			}
			
			if((is_posted('submit/student_comment') || is_posted('submit/student')) && 
				is_permitted('student','interactive') && 
				(post('student_comment/title')!='' || post('student_comment/content')!='')
			){
		
				if($this->student->addComment(post('student/id'),post('student_comment'))){
					unset($_SESSION[CONTROLLER]['post']['student_comment']);
				}else{
					$submitable=false;
				}
			}
		
			if(!is_posted('student/youth_league')){
				post('student/youth_league',0);
			}
			
			if(!is_posted('student/resident')){
				post('student/resident',0);
				post('student/dormitory','');
			}
			
			if(post('student/birthday')==''){
				unset($_SESSION[CONTROLLER]['post']['student']['birthday']);
			}
			
			if(is_logged('student') && is_posted('submit/student')){
				$form_check=array(
					'birthday'=>'生日',
					'id_card'=>'身份证号',
					'race'=>'民族',
					'junior_school'=>'初中',
					'mobile'=>'手机',
					'phone'=>'固定电话',
					'email'=>'电子邮箱',
					'address'=>'地址',
					'neighborhood_committees'=>'居委会',
					'bank_account'=>'银行卡号'
					
				);
				
				foreach($form_check as $item => $warning){
					if(!post(CONTROLLER.'/'.$item)){
						showMessage('请输入'.$warning,'warning');
						$submitable=false;
					}
				}
			}
			
			if(is_logged('student') && db_fetch_field("SELECT COUNT(id) FROM student_relatives WHERE student = '".$_SESSION['id']."'")<2){
				showMessage('请至少输入两位亲属，每输入一行需要点击“添加”按钮');
				$submitable=false;
			}
			
			if(empty($student_class)){
				if(!db_insert('student_class',array('student'=>post('student/id'),'class'=>post('student_class/class'),'num_in_class'=>post('student_class/num_in_class'),'term'=>$_SESSION['global']['current_term']))){
					$submitable=false;
				}
			}else{
				if(!$this->db->update('student_class',post('student_class'),array('student'=>post('student/id'),'term'=>$_SESSION['global']['current_term']))){
					$submitable=false;
				}
			}
			
			$this->processSubmit($submitable,function(){
				$username=db_fetch_field("SELECT username FROM user WHERE id = '".post(CONTROLLER.'/id')."'");
				if(!$username){
					$this->student->update();
					db_query("UPDATE user INNER JOIN view_student USING (id) SET user.username=CONCAT(view_student.name,view_student.num),user.alias=view_student.num WHERE view_student.id = '".post(CONTROLLER.'/id')."'");
				}
			});
		}
		
		$fields_student_relatives=array(
			'checkbox'=>array('title'=>'<input type="submit" name="submit[student_relatives_delete]" value="删" />','orderby'=>false,'content'=>'<input type="checkbox" name="student_relatives_check[{id}]" >','td_title'=>' width="25px"'),
			'name'=>array('title'=>'姓名','orderby'=>false),
			'relationship'=>array('title'=>'关系','orderby'=>false),
			'contact'=>array('title'=>'电话','orderby'=>false),
			'work_for'=>array('title'=>'单位','orderby'=>false)
		);
		$relatives=$this->table->setFields($fields_student_relatives)
			->generate($this->student->getRelativeList(post('student/id')));
		
		$fields_student_behaviour=array(
			'type'=>array('title'=>'类别','td_title'=>'width="10%"','orderby'=>false),
			'date'=>array('title'=>'日期','orderby'=>false),
			'name'=>array('title'=>'名称','td_title'=>'width="40%"','td'=>'title="{content}"','orderby'=>false),
			'level'=>array('title'=>'级别','orderby'=>false)
		);
		$behaviour=$this->table->setFields($fields_student_behaviour)
			->generate($this->student->getBehaviourList(post('student/id')));
		
		$fields_student_comment=array(
			'title'=>array('title'=>'标题','orderby'=>false),
			'content'=>array('title'=>'内容','td_title'=>'width="60%"','orderby'=>false),
			'username'=>array('title'=>'留言人','orderby'=>false),
			'time'=>array('title'=>'时间','orderby'=>false)
		);
		$comments=$this->table->setFields($fields_student_comment)
				->generate($this->student->getCommentList(post('student/id')));
		
		$fields_scores=array(
			'exam_name'=>array('title'=>'考试'),
			'course_1'=>array('title'=>'语文','content'=>'{course_1}<span class="rank">{rank_1}</span>'),
			'course_2'=>array('title'=>'数学','content'=>'{course_2}<span class="rank">{rank_2}</span>'),
			'course_3'=>array('title'=>'英语','content'=>'{course_3}<span class="rank">{rank_3}</span>'),
			'course_4'=>array('title'=>'物理','content'=>'{course_4}<span class="rank">{rank_4}</span>'),
			'course_5'=>array('title'=>'化学','content'=>'{course_5}<span class="rank">{rank_5}</span>'),
			'course_6'=>array('title'=>'生物','content'=>'{course_6}<span class="rank">{rank_6}</span>'),
			'course_7'=>array('title'=>'地理','content'=>'{course_7}<span class="rank">{rank_7}</span>'),
			'course_8'=>array('title'=>'历史','content'=>'{course_8}<span class="rank">{rank_8}</span>'),
			'course_9'=>array('title'=>'政治','content'=>'{course_9}<span class="rank">{rank_9}</span>'),
			'course_10'=>array('title'=>'信息','content'=>'{course_10}<span class="rank">{rank_10}</span>'),
			'course_sum_3'=>array('title'=>'3总','content'=>'{course_sum_3}<span class="rank">{rank_sum_3}</span>'),
			'course_sum_5'=>array('title'=>'4总/5总','content'=>'{course_sum_5}<span class="rank">{rank_sum_5}</span>'),
			'course_sum_8'=>array('title'=>'8总','content'=>'{course_sum_8}<span class="rank">{rank_sum_8}</span>')
		);
		$scores=$this->table->setFields($fields_scores)
				->trimColumns()
				->generate($this->student->getScores(post('student/id')));
		
		$this->load->addViewArrayData(compact('relatives','behaviour','comments','scores'));
		$this->load->view('student/edit');
		$this->load->main_view_loaded=true;
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
					extra_course_score AS `3`
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
					$this->db_update('student_classdiv',array('new_class'=>$class),'id IN ('.implode(',',$array2).')');
				}
			}
		}else{
		
			$q="SELECT * FROM student_classdiv";
			
			$list_locator=$this->processMultiPage($q);
			
			$field=array(
				'name'=>array('title'=>'姓名'),
				'gender'=>array('title'=>'性别'),
				'course_1'=>array('title'=>'语文'),
				'course_2'=>array('title'=>'数学'),
				'course_3'=>array('title'=>'英语')
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
		$this->session->set_userdata('last_list_action', $this->input->server('REQUEST_URI'));
		
		if($this->input->post('submit')){
			$submitable=true;
			
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$this->input->post());
			
			if(post('student_comment/reply_to',$this->user->check(post('student_comment_extra/reply_to_username')))<0){
				$submitable=false;
			}
			
			if(is_logged('parent')){
				$student_id=$this->student->getIdByParentUid($_SESSION['id']);
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
			'date'=>array('title'=>'日期','td_title'=>'width="100px"'),
			'username'=>array('title'=>'用户','td_title'=>'width="120px"'),
			'student_name'=>array('title'=>'学生','td_title'=>'width="60px"','wrap'=>array('mark'=>'a','href'=>'student?edit={student}')),
			'title'=>array('title'=>'标题','td_title'=>'width="120px"','td'=>'class="ellipsis" title="{title}"'),
			'content'=>array('title'=>'内容','td'=>'class="ellipsis" title="{content}"')
		);
		$list=$this->table->setFields($field)
			->setMenu(template('student/interactive_send'),'center','foot')
			->setData($this->student->getInteractiveList())
			->generate();
		$this->load->addViewData('list', $list);
	}
	
	function viewScore(){
		//TODO 图与表的sql请求合一
		if(is_logged('student')){
			$student=$_SESSION['id'];
		}elseif(is_logged('parent')){
			$student=$_SESSION['child'];
		}else{
			$student=intval($this->input->get('student'));
		}
		
		$course_array=db_toArray("SELECT id,name,chart_color FROM course",true);
		
		$score_array=db_toArray("SELECT * FROM view_score WHERE student = '".$student."' ORDER BY exam");
		
		$category=$series_raw=$series=array();
		
		foreach($score_array as $score_line_id => $score){
			$category[]=$score['exam_name'];
			foreach($course_array as $course_id => $course){
				if(!isset($series_raw[$course_id])){
					$series_raw[$course_id]=array('name'=>$course['name'],'color'=>'#'.$course['chart_color']);
				}
				if(isset($score['rank_'.$course_id])){
					$series_raw[$course_id]['data'][]=$score['rank_'.$course_id];
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
		
		$fields_scores=array(
			'exam_name'=>array('title'=>'考试'),
			'course_1'=>array('title'=>'语文','content'=>'{course_1}<span class="rank">{rank_1}</span>'),
			'course_2'=>array('title'=>'数学','content'=>'{course_2}<span class="rank">{rank_2}</span>'),
			'course_3'=>array('title'=>'英语','content'=>'{course_3}<span class="rank">{rank_3}</span>'),
			'course_4'=>array('title'=>'物理','content'=>'{course_4}<span class="rank">{rank_4}</span>'),
			'course_5'=>array('title'=>'化学','content'=>'{course_5}<span class="rank">{rank_5}</span>'),
			'course_6'=>array('title'=>'生物','content'=>'{course_6}<span class="rank">{rank_6}</span>'),
			'course_7'=>array('title'=>'地理','content'=>'{course_7}<span class="rank">{rank_7}</span>'),
			'course_8'=>array('title'=>'历史','content'=>'{course_8}<span class="rank">{rank_8}</span>'),
			'course_9'=>array('title'=>'政治','content'=>'{course_9}<span class="rank">{rank_9}</span>'),
			'course_10'=>array('title'=>'信息','content'=>'{course_10}<span class="rank">{rank_10}</span>'),
			'course_sum_3'=>array('title'=>'3总','content'=>'{course_sum_3}<span class="rank">{rank_sum_3}</span>'),
			'course_sum_5'=>array('title'=>'4总/5总','content'=>'{course_sum_5}<span class="rank">{rank_sum_5}</span>'),
			'course_sum_8'=>array('title'=>'8总','content'=>'{course_sum_8}<span class="rank">{rank_sum_8}</span>')
		);

		$scores=$this->table->setFields($fields_scores)
			->trimColumns()
			->generate($this->student->getScores($student));
		
		$this->load->addViewData('scores', $scores);
	}
}
?>