<?php
class School_model extends SS_Model{
	
	var $current_term;
	var $highest_grade;
	
	function __construct() {
		parent::__construct();
		
/*
		$this->current_term=$this->session->userdata('current_term');
		$this->highest_grade=$this->session->userdata('highest_grade');
 */

		$this->load->library('Lunar');
		$year=date('y',$this->config->item('timestamp'));//两位数年份
		$term_begin_this_year_timestamp=strtotime($year.'-8-1');//今年8/1
		$lunar_this_new_year_timestamp=$this->lunar->L2S($year.'-1-1');//今年农历新年的公历
		if($this->config->item('timestamp')>=$term_begin_this_year_timestamp){
			$term=1;$term_year=$year;
		}elseif($this->config->item('timestamp')<$lunar_this_new_year_timestamp){
			$term=1;$term_year=$year-1;
		}else{
			$term=2;$term_year=$year-1;
		}

		$this->current_term=$term_year.'-'.$term;
		$this->highest_grade=$term_year-2;
/*		
		$this->session->set_userdata('current_term',$term_year.'-'.$term);//计算当前学期
		$this->session->set_userdata('highest_grade',$term_year-2);//计算当前在校最高年级
 */
	}

/*
	function student_setSession($user_id){
		$user_id=intval($user_id);
		
		$query="SELECT * FROM `view_student` WHERE `id` = $user_id";
		$student=$this->db->result($query)->row_array();
	
		$this->session->set_userdata('user/class', $student['class']);
		$this->session->set_userdata('user/class_name', $student['class_name']);
		$this->session->set_userdata('user/grade', $student['grade']);
		$this->session->set_userdata('user/grade_name', $student['grade_name']);
	}
*/

	function teacher_setSession($user_id){
		$user_id=intval($user_id);
		
		//获得教师所在备课组
		$query="
			SELECT team.id 
			FROM team 
				INNER JOIN team_people ON team.id=team_people.team 
			WHERE team.type='teacher_group' AND team_people.people = $user_id
		";
		$teacher_groups=array_sub($this->db->query($query)->row_array(),'id');
		$this->session->set_userdata('user/teacher_group', $teacher_groups);
		
		//获得教师所管辖的班级，及其所在年级
		$query="
			SELECT team_relationship.relative AS id, team_relationship.team AS grade 
			FROM team_relationship INNER JOIN team ON team_relationship.relative=team.id
			WHERE team.leader=$user_id
		";
		if($class=$this->db->query($query)->row_array()){
			$this->session->set_userdata('user/manage_class', $class);
		}
	}
	
	function parent_setSession($user_id){
		$user_id=intval($user_id);

		$query="SELECT people FROM people_relationship WHERE type='parent' AND relative=$user_id";
		$this->session->set_userdata('user/child', $this->db->query($query)->row()->people);
	}
}

?>
