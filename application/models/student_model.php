<?php
class Student_model extends People_model{
	
	var $profile=array(
		'youth_league'=>'团员',
		'junior_school'=>'初中',
		'source_type'=>'生源类型',
		'resident'=>'住校',
		'dormitory'=>'宿舍',
		'mobile'=>'手机',
		'email'=>'电子邮件',
		'phone'=>'家庭电话',
		'address'=>'家庭地址',
		'community'=>'居委会',
		'bank_account'=>'银行帐号',
		'diseases_history'=>'疾病史'
	);
	
	function __construct(){
		parent::__construct();
		$this->fields['type']='student';
	}
	
	function match($part_of_name) {
		$this->db->where('people.type','student');
		return parent::match($part_of_name);
	}
	
	/**
	 * 
	 * @param array $args
	 *	in_class bool 只显示属于某个班级的学生
	 * @return type
	 */
	function getList($args=array()){
		
		!isset($args['type']) && $args['type']='student';
		
		$this->db->select('
			people.*,
			people_team.id AS class,people_team.name AS class_name
		',false)
			->join('people_relationship class_student',"class_student.relative = people.id AND (class_student.till>=CURDATE() OR class_student.till IS NULL)",isset($args['in_class']) && $args['in_class']?'inner':'left')
			->join('people people_team',"people_team.id = class_student.people",isset($args['in_class']) && $args['in_class']?'inner':'left')
			->where('people_team.type','classes');
		
		if(isset($args['team'])){
			$this->db->where_in('class_student.people',$args['team']);
			unset($args['team']);
		}
			
		$args['orderby']='num';
		
		return parent::getList($args);
	}
	
	function getScores($student,array $args=array()){
		$student=intval($student);
		
		$this->db->from('school_view_score')
			->where('student',$student);

		//复制一个DB对象用来计算行数，因为计算行数需要运行sql，将清空DB对象中属性
		$db_num_rows=clone $this->db;
		
		if(isset($args['orderby'])){
			if(is_array($args['orderby'])){
				foreach($args['orderby'] as $orderby){
					$this->db->order_by($orderby[0],$orderby[1]);
				}
			}elseif($args['orderby']){
				$this->db->order_by($args['orderby']);
			}
		}
		
		if(isset($args['limit'])){
			if($args['limit']==='pagination'){
				$args['limit']=$this->pagination($db_num_rows);
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			elseif(is_array($args['limit'])){
				call_user_func_array(array($this->db,'limit'), $args['limit']);
			}
			else{
				call_user_func(array($this->db,'limit'), $args['limit']);
			}
		}
		
		return $this->db->get()->result_array();
	}
	
	function testClassDiv($div,$data,$classes,$gender,$showResult=false){
		global $tests,$students,$subjects;
	
		$tests++;
		
		$score=array();
		/*$score:array(
			1(性别)=>array(
				1(班号)=>array(
					1(科目号)=>array(
						学号=>本科分数
					)
				)
			)
		)
		*/
	
		//将div分班方案分解为score分数表
		for($subject=0;$subject<$subjects;$subject++){
			foreach($div as $gender_in_array1 => $array1){
				foreach($array1 as $class=>$array2){
					foreach($array2 as $student){
						$score[$gender_in_array1][$class][$subject][$student]=$data[$student][$subject];
					}
				}
			}
		}
		
		//$_SESSION['score']=$score;
		//print_r($score);
		
		$result=array();
	
		for($subject=0;$subject<$subjects;$subject++){
			for($class=0;$class<$classes;$class++){
				$result[$class][$subject]['num']=count($score[$gender][$class][$subject]);//得到每班每学科的人数
				$result[$class][$subject]['sum']=array_sum($score[$gender][$class][$subject]);//得到每班每学科的和
				$result[$class][$subject]['aver']=$result[$class][$subject]['sum']/$result[$class][$subject]['num'];//得到每班每学科的平均值
				//$result[$class][$subject]['std']=std($score[$gender][$class][$subject],$result[$class][$subject]['aver']);//得到每班每学科的标准差
			}
		}
		
		if($showResult){
			echo "\n<br>result".$gender.": "; print_r($result);
		}
		
		/*for($subject=0;$subject<$subjects;$subject++){
			for($class=0;$class<$classes;$class++){
		
				$std[]=$result[$class][$subject]['std'];
		
			}
		}
		
		$std_sum=array_sum($std);//各班各学科的标准差的和*/
		
		$aver_std=array();
	
		for($subject=0;$subject<$subjects;$subject++){
	
			$aver=array();
	
			for($class=0;$class<$classes;$class++){
				$aver[]=$result[$class][$subject]['aver'];
			}
			$aver_std[]=std($aver);
		}
		
		$aver_std_sum=array_sum($aver_std);//各班每学科总分的标差的和
		
		return $aver_std_sum;
	}
	
}