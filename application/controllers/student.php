<?php
class Student extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		if(got('update')){
			student_update();
			showMessage('学生视图更新完成');
		}
		
		$q=
		"SELECT 
			student.id,student.name AS name,student.id_card,student.phone,student.mobile,student.address,
			student_num.num,
			class.name AS class_name,
			relatives.contacts AS relatives_contacts
		FROM 
			student
			INNER JOIN (
				SELECT student,class,
					right((1000000 + concat(student_class.class,right((100 + student_class.num_in_class),2))),6) AS num
				FROM student_class
				WHERE student_class.term = '".$_SESSION['global']['current_term']."'
			)student_num ON student_num.student=student.id
			INNER JOIN class ON class.id=student_num.class
			LEFT JOIN (
				SELECT student.id AS student,GROUP_CONCAT(student_relatives.contact) AS contacts
				FROM student INNER JOIN student_relatives ON student_relatives.student=student.id
				WHERE student_relatives.contact<>''
				GROUP BY student.id
			)relatives
			ON relatives.student=student.id
		WHERE student.display=1
			AND (class.id=(SELECT id FROM class WHERE class_teacher='".$_SESSION['id']."')
				OR '".(is_logged('jiaowu') || is_logged('zhengjiao') || is_logged('health'))."'='1')
		";
		//班主任可以看到自己班级的学生，教务和政教可以看到其他班级的学生
		
		//将班主任的视图定位到自己班级
		if(!option('class') && !option('grade') && isset($_SESSION['manage_class'])){
			option('class',$_SESSION['manage_class']['id']);
			option('grade',$_SESSION['manage_class']['grade']);
		}
		addCondition($q,array('class'=>'class.id','grade'=>'class.grade'),array('grade'=>'class'));
				
		$search_bar=$this->processSearch($q,array('num'=>'学号','student.name'=>'姓名'));
		
		$this->processOrderby($q,'num','ASC',array('num','student.name'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'num'=>array('title'=>'学号','td'=>'id="{id}" '),
			'student.name'=>array('title'=>'姓名','content'=>'<a href="student?edit={id}">{name}</a>'),
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

	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		$this->getPostData($id,function($CI){
			global $_G;
			post('student/name','新学生'.$CI->config->item('timestamp'));
			
			post(IN_UICE.'/id',db_insert('user',array('group'=>'student')));
			//先创建用户，再创建学生
			
			db_insert(IN_UICE,post(IN_UICE));
		},false);
		
		$q_student_class="
			SELECT student_class.num_in_class AS num_in_class,
				CONCAT(RIGHT(10000+class.id,4),num_in_class) AS num,
				class.id AS class,class.name AS class_name,
				staff.name AS class_teacher_name
			FROM student_class
				INNER JOIN class ON student_class.class=class.id
				LEFT JOIN staff ON class.class_teacher=staff.id AND staff.company='".$this->config->item('company')."'
			WHERE student='".post('student/id')."' 
				AND term='".$_SESSION['global']['current_term']."'
		";
		$student_class=db_fetch_first($q_student_class);
		post('student_class',array('class'=>$student_class['class'],'num_in_class'=>$student_class['num_in_class']));
		post('class/name',$student_class['class_name']);
		isset($student_class['class_teacher_name']) && post('student_extra/class_teacher_name',$student_class['class_teacher_name']);
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if(is_posted('submit')){
			$submitable=true;
		
			$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
			
			if(is_posted('submit/student_relatives')){
				student_addRelatives(post('student/id'),post('student_relatives'));
				unset($_SESSION[IN_UICE]['post']['student_relatives']);
			}
			
			if(is_posted('submit/student_relatives_delete')){
				student_deleteRelatives(post('student_relatives_check'));
			}
			
			if(is_posted('submit/student_behaviour')){
				if(student_addBehaviour(post('student/id'),post('student_behaviour'))){
					unset($_SESSION[IN_UICE]['post']['student_behaviour']);
				}else{
					$submitable=false;
				}
			}
			
			if((is_posted('submit/student_comment') || is_posted('submit/student')) && 
				is_permitted('student','interactive') && 
				(post('student_comment/title')!='' || post('student_comment/content')!='')
			){
		
				if(student_addComment(post('student/id'),post('student_comment'))){
					unset($_SESSION[IN_UICE]['post']['student_comment']);
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
				unset($_SESSION[IN_UICE]['post']['student']['birthday']);
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
					if(!post(IN_UICE.'/'.$item)){
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
				if(!db_update('student_class',post('student_class'),"student='".post('student/id')."' AND term='".$_SESSION['global']['current_term']."'")){
					$submitable=false;
				}
			}
			
			$this->processSubmit($submitable,function(){
				$username=db_fetch_field("SELECT username FROM user WHERE id = '".post(IN_UICE.'/id')."'");
				if(!$username){
					student_update();
					db_query("UPDATE user INNER JOIN view_student USING (id) SET user.username=CONCAT(view_student.name,view_student.num),user.alias=view_student.num WHERE view_student.id = '".post(IN_UICE.'/id')."'");
				}
			});
		}
		
		$q_student_relatives="
			SELECT 
				id,name,relationship,work_for,contact
			FROM 
				student_relatives
			WHERE company='".$this->config->item('company')."'
				AND `student`='".post('student/id')."'
		";
		
		$field_student_relatives=array(
			'checkbox'=>array('title'=>'<input type="submit" name="submit[student_relatives_delete]" value="删" />','orderby'=>false,'content'=>'<input type="checkbox" name="student_relatives_check[{id}]" >','td_title'=>' width=60px'),
			'name'=>array('title'=>'姓名','orderby'=>false),
			'relationship'=>array('title'=>'关系','orderby'=>false),
			'contact'=>array('title'=>'电话','orderby'=>false),
			'work_for'=>array('title'=>'单位','orderby'=>false)
		);
		
		$q_student_behaviour="
			SELECT name,type,date,level,content FROM student_behaviour WHERE student = '".post('student/id')."'
			LIMIT 5
		";
		$field_student_behaviour=array(
			'type'=>array('title'=>'类别','td_title'=>'width="10%"','orderby'=>false),
			'date'=>array('title'=>'日期','orderby'=>false),
			'name'=>array('title'=>'名称','td_title'=>'width="40%"','td'=>'title="{content}"','orderby'=>false),
			'level'=>array('title'=>'级别','orderby'=>false)
		);
		
		$q_student_comment="
			SELECT student_comment.title,student_comment.content,
				FROM_UNIXTIME(time,'%Y-%m-%d') AS time,IF(staff.name IS NULL,student_comment.username,staff.name) AS username 
			FROM student_comment LEFT JOIN staff ON staff.id=student_comment.uid 
			WHERE student = '".post('student/id')."' AND (reply_to IS NULL OR reply_to = '".$_SESSION['id']."' OR uid = '".$_SESSION['id']."')
			ORDER BY student_comment.time DESC
			LIMIT 5
		";
		$field_student_comment=array(
			'title'=>array('title'=>'标题','orderby'=>false),
			'content'=>array('title'=>'内容','td_title'=>'width="60%"','orderby'=>false),
			'username'=>array('title'=>'留言人','orderby'=>false),
			'time'=>array('title'=>'时间','orderby'=>false)
		);
		
		if($this->as_controller_default_page){
			$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		}
		
		$scores=student_get_scores(post('student/id'));
	}

	function classDiv(){
		$classes=2;
		$subjects=4;
		
		if(got('run')){
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
					db_update('student_classdiv',array('new_class'=>$class),'id IN ('.implode(',',$array2).')');
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
			
			$this->data+=compact('table','menu');
			
			$this->load->view('lists',$this->data);
		}
	}

	function interactive(){
		model('user');
		
		if(is_posted('submit')){
			$submitable=true;
			
			$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
			
			if(post('student_comment/reply_to',user_check(post('student_comment_extra/reply_to_username')))<0){
				$submitable=false;
			}
			
			if(is_logged('parent')){
				$student_id=student_getIdByParentUid($_SESSION['id']);
			}else{
				$student_id=student_getIdByParentUid(post('student_comment/reply_to'));
			}
			
			if($submitable){
				student_addComment($student_id,post('student_comment'));
				unset($_SESSION[IN_UICE]['post']['student_comment']);
				unset($_SESSION[IN_UICE]['post']['student_comment_extra']);
			}
		}
		
		$q="
		SELECT student_comment.title,student_comment.content,FROM_UNIXTIME(student_comment.time,'%Y-%m-%d') AS date,student_comment.username,student_comment.student,
			view_student.name AS student_name
		FROM student_comment INNER JOIN view_student ON student_comment.student=view_student.id
		WHERE student_comment.reply_to='".$_SESSION['id']."' 
			OR student_comment.uid='".$_SESSION['id']."' 
			OR (
				'".isset($_SESSION['manage_class'])."' 
				AND view_student.class='".$_SESSION['manage_class']['id']."'
			)
		ORDER BY time DESC
		";
		
		$list_locator=$this->processMultiPage($q);
		
		$field=array(
			'date'=>array('title'=>'日期','td_title'=>'width="100px"'),
			'username'=>array('title'=>'用户','td_title'=>'width="120px"'),
			'student_name'=>array('title'=>'学生','td_title'=>'width="60px"','surround'=>array('mark'=>'a','href'=>'student?edit={student}')),
			'title'=>array('title'=>'标题','td_title'=>'width="120px"','td'=>'class="ellipsis" title="{title}"'),
			'content'=>array('title'=>'内容','td'=>'class="ellipsis" title="{content}"')
		);
		
		$menu=array(
			'head'=>'<div class="right">'.$list_locator.'</div>',
			'foot'=>template('student_interactive_send')
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists',$this->data);
	}
	
	function viewScore(){
		//TODO 图与表的sql请求合一
		if(is_logged('student')){
			$student=$_SESSION['id'];
		}elseif(is_logged('parent')){
			$student=$_SESSION['child'];
		}else{
			$student=intval($_GET['student']);
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
		
		$scores=student_get_scores($student);
	}
}
?>