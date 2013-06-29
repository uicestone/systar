<?php
class Exam_model extends Evaluation_model{
	function __construct(){
		parent::__construct();
	}
	
	function getList($args=array()){
		!isset($args['type']) && $args['type']='exam';
		return parent::getList($args);
	}
	
	function getSeatList($exam_id){
		$q="SELECT 
				view_student.num,view_student.class_name,view_student.name AS student_name,
				exam_student.room,exam_student.seat,course.name AS course_name
			FROM exam_student INNER JOIN view_student ON exam_student.student=view_student.id
				LEFT JOIN course ON exam_student.extra_course=course.id
			WHERE
				exam_student.exam='".$exam_id."'
		";
				
		$q=$this->orderby($q,'view_student.num','ASC');
		
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
	}
	
	function uploadScore($data){

		$rows=$data->sheets[0]['numRows'];
		$cols=$data->sheets[0]['numCols'];

		$exam_part_array=array();

		for($i=1;$i<$cols;$i++){
			if($data->sheets[0]['cells'][0][$i]=='' || is_numeric($data->sheets[0]['cells'][0][$i])){
				throw new Exception('某大题的名字是空的或是数字');
			}
			$exam_part_data_array[]=array('exam_paper'=>$currentExam['exam_paper'],'name'=>$data->sheets[0]['cells'][0][$i]);
		}

		for($i=1;$i<$rows;$i++){
			for($j=1;$j<$cols;$j++){
				$cell = isset($data->sheets[0]['cells'][$i][$j])?$data->sheets[0]['cells'][$i][$j]:'';
				if(!(is_numeric($cell) || $cell=='') || $cell<0){
					throw new Exception('第'.($i+1).'行 第'.($j+1).'列的数据'.$cell.'中包含错误字符，注意只能是数字或留空（缺考）');
				}
			}
			if(array_sum(array_slice($data->sheets[0]['cells'][$i],1))>150){
				throw new Exception('第'.$i.'行的小分和超过了150分！注意不用填写总分');
			}
		}

		if($rows-1<$currentExam['students']){
			//throw new Exception('本张试卷有'.$currentExam['students'].'人参考，上传的分数为'.($rows-1).'条，请检核数据重新上传');
		}

		foreach($exam_part_data_array as $exam_part_data){
			//插入大题
			$this->db->insert('exam_part',$exam_part_data);
			$exam_part_array[]=$this->db->insert_id();
		}

		//创建一张临时表
		$q_create_temp_table="CREATE TEMPORARY TABLE `t` (`id` INT NOT NULL AUTO_INCREMENT, `num` CHAR( 6 ) NOT NULL,";

		foreach($exam_part_array as $exam_part){
			$q_create_temp_table.="`".$exam_part."` DECIMAL( 10, 1 ) NULL,";
		}

		$q_create_temp_table.=" PRIMARY KEY (`id`) ,UNIQUE (`num`))";

		$this->db->query($q_create_temp_table);

		//excel表格上传到临时表
		$q_insert_t_score='INSERT INTO t (num,`'.implode('`,`',$exam_part_array).'`) VALUES';
		for($i=1; $i<$rows; $i++) {
			$q_insert_t_score.="('".$data->sheets[0]['cells'][$i][0]."'";
			for($j=1; $j<$cols; $j++) {
				$cell = isset($data->sheets[0]['cells'][$i][$j])?$data->sheets[0]['cells'][$i][$j]:'';
				$q_insert_t_score.=",".($cell==''?'NULL':"'".$cell."'")."";
			}
			$q_insert_t_score.=')';
			if($i!=$rows-1){
				$q_insert_t_score.=',';
			}
		}
		if(!$this->db->query($q_insert_t_score)){
			throw new Exception('上传错误，可能有重复学号或者错误的学号');
			
		}

		$q_search_illegal_student="
			SELECT id,num FROM t WHERE num NOT IN
			(
				SELECT view_student.num 
				FROM exam_student INNER JOIN view_student ON view_student.id=exam_student.student
				WHERE exam_student.exam='".$currentExam['exam']."'".(isset($currentExam['extra_course'])?" AND exam_student.extra_course='".$currentExam['extra_course']."'":'')."
			)
			LIMIT 1
		";

		$r_search_illegal_student=$this->db->query($q_search_illegal_student);

		if(/*db_rows($r_search_illegal_student)==*/1){
			$illegal_line=db_fetch_array($r_search_illegal_student);
			throw new Exception(($illegal_line['id']+1).'行的"'.$illegal_line['num'].'"学号不正确，可能填写错误或学生不属于本场考试');
			
		}

		foreach($exam_part_array as $exam_part){
			//插入分数
			$q_insert_score="
			REPLACE INTO score (student,exam,exam_paper,exam_part,score,is_absent,scorer,scorer_username,time)
			SELECT view_student.id,'".$currentExam['exam']."','".$currentExam['exam_paper']."','".$exam_part."',t.`".$exam_part."`,if(t.`".$exam_part."` IS NULL,1,0),{$this->user->id},'".$_SESSION['username']."','".$this->date->now."'  
			FROM t INNER JOIN view_student ON t.num=view_student.num
			";
			mysql_query($q_insert_score);
		}

		throw new Exception('文件上传成功！');

	}

	function allocate_seat(){
		set_time_limit(0);
		
		$r_active_exam=$this->db->query("SELECT id,depart,grade FROM exam WHERE is_on=1 ORDER BY depart");
		//列出激活的考试
		
		$this->db->query("CREATE TEMPORARY TABLE exam_student_temp SELECT * FROM exam_student WHERE 1=0");
		
		while($exam=db_fetch_array($r_active_exam)){
			//向exam_student表插入学生，生成随机数，教室和座位留空
			$this->db->query("
				INSERT INTO exam_student (exam,student,depart,extra_course,time,rand)
				SELECT ".$exam['id'].",id,depart,extra_course,".$this->date->now.",1000*rand() FROM view_student 
				WHERE grade = ".$exam['grade']." AND depart='".$exam['depart']."'
				ORDER BY extra_course
			");
		}
		
		//建立教室和年级的对应关系
		$q_exam_room="
			SELECT exam_room.capacity,exam_room.id AS room,
				exam.id AS exam,exam.depart
			FROM exam_room INNER JOIN exam ON ( exam_room.grade = exam.grade AND exam_room.depart = exam.depart )
			WHERE exam.is_on=1
		";
		$r_exam_room=$this->db->query($q_exam_room);
		
		while($exam_room=db_fetch_array($r_exam_room)){
			$q_update_exam_student_of_a_room="
				UPDATE 
				(
					SELECT exam_student.id,exam_room.name AS room FROM `exam_student`,exam_room 
					WHERE room IS NULL AND exam='".$exam_room['exam']."' AND exam_room.id='".$exam_room['room']."'
					ORDER BY exam_student.extra_course,exam_student.rand
					LIMIT ".$exam_room['capacity']."
				)roomed,
				exam_student
				SET exam_student.room=roomed.room 
				WHERE exam_student.id=roomed.id";
				
			$this->db->query($q_update_exam_student_of_a_room);
		}
		$q_exam_student="SELECT * FROM `exam_student` WHERE exam IN (SELECT id FROM exam WHERE is_on=1) ORDER BY room,rand";
		$r_exam_student=$this->db->query($q_exam_student);
		
		$seat=1;
		$room='';
		while($exam_student=db_fetch_array($r_exam_student)){
			if($room==$exam_student['room']){
				$seat++;
			}else{
				$seat=1;
			}
			$q_update_seat="UPDATE exam_student SET seat='".$seat."' WHERE id = '".$exam_student['id']."'";
			$this->db->query($q_update_seat);
			$room=$exam_student['room'];
		}
		
		$this->db->query("UPDATE exam SET seat_allocated=1 WHERE is_on=1");
	}
}
?>