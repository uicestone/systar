<?php
class Test extends SS_controller{
	function __construct() {
		$this->permission=true;
		parent::__construct();
		$this->load->library('unit_test');
		$this->output->enable_profiler(TRUE);
	}
	
	function index(){
		print_r($this->session->all_userdata());
		print_r($this->user);
	}
	
	function object(){
		
		$this->db->truncate('object_meta');
		$this->db->truncate('object_mod');
		$this->db->truncate('object_relationship');
		$this->db->truncate('object_status');
		$this->db->truncate('object_tag');
		$this->db->delete('object',array('type'=>'test'));
		
		$object = new Object_model();
		
		$data_insert=array(
			'name'=>'测试对象1',
			'num'=>'01',
			'type'=>'test',
			'display'=>true
		);
		
		$data_insert2=array(
			'name'=>'测试对象2',
			'num'=>'002',
			'type'=>'test',
			'display'=>true
		);
		
		$data_insert3=array(
			'name'=>'测试对象3',
			'num'=>'003',
			'type'=>'test',
			'display'=>true
		);
		
		$object1=$object->add($data_insert);
		$object2=$object->add($data_insert2);
		$object3=$object->add($data_insert3);
		
		$object->id=$object1;
		$object->update(array('num'=>'001'));
		$data = $object->fetch($object1);
		$this->unit->run(array_intersect_key($data,$data_insert),$data_insert,'对象添加');
		
		$object->addTag('个人案源');
		$object->addTag('房产','领域');
		$object->addTags(array('再成案','阶段'=>'一审'));
		$object->updateTags(array('阶段'=>'二审'));
		$object->removeTag('个人案源');
		$this->unit->run($object->getTags(),array('领域'=>'房产','再成案','阶段'=>'二审'),'标签增删');
		
		$object->addMeta('电话', '51096488');
		$meta_phone=$object->addMeta('电话', '52567816');
		$meta_address=$object->addMeta('地址', '上海市常德路1211号');
		$object->updateMetas(array('电话'=>'52567816'));
		$object->updateMeta($meta_address, array('content'=>'上海市常德路1211号1204-1207'));
		$object->removeMeta($meta_phone);
		$this->unit->run(array_column($object->getMeta(),'content','name'),array('电话'=>'52567816','地址'=>'上海市常德路1211号1204-1207'),'资料项增删');
		
		$object->addRelative($object2, array('relation'=>'后续对象'));
		$relationship_former=$object->addRelative($object3, array('relation'=>'相关对象'));
		$object->addRelative($object3);
		$object->updateRelative($relationship_former,array('relation'=>'前导对象'));
		$object->removeRelative($object3,NULL);
		$this->unit->run(array_column($object->getRelative(),'name','relation'),array('后续对象'=>'测试对象2','前导对象'=>'测试对象3'),'关系增删');

		$object->relative_mod_list['self']=array(
			'deleted'=>1,
			'read'=>2,
			'stared'=>4
		);
		$object->addRelativeMod($relationship_former, 'stared');
		$object->updateRelativeMod($relationship_former, array('deleted'=>true,'read'=>true,'stared'=>false));
		$object->removeRelativeMod($relationship_former, 'read');
		$this->unit->run(array_column($object->getRelative(NULL,array('self'=>array('read'=>false,'deleted'=>true,'stared'=>false))),'name','relation'),array('前导对象'=>'测试对象3'),'关系开关量增删');
		
		$object->addStatus('立案','2013-7-27');
		$object->addStatus('电话咨询',strtotime('2013-07-22'));
		$status_unknown=$object->addStatus('未知状态',strtotime('-5 days'));
		$object->removeStatus($status_unknown);
		$this->unit->run(array_column($object->getStatus(),'date','name'),array('立案'=>'2013-07-27','电话咨询'=>'2013-07-22'),'状态增删');
		
		$this->unit->run(array_column($object->match('测试对象'),'name','num'),array('001'=>'测试对象1','002'=>'测试对象2','003'=>'测试对象3'),'match');
		
		$object->id=$object2;
		$object->addTag('房产','领域');
		$this->unit->run(array_column($object->getList(array('type'=>'test','tags'=>array('领域'=>'房产'))),'name','num'),array('001'=>'测试对象1','002'=>'测试对象2'),'对象列表-标签','包含类别的搜索匹配');
		$this->unit->run($object->getList(array('type'=>'test','tags'=>array('类别'=>'房产'))),array(),'对象列表-标签','包含类别的搜索不匹配');
		$this->unit->run(array_column($object->getList(array('type'=>'test','without_tags'=>array('类别'=>'房产'))),'num'),array('001','002','003'),'对象列表-标签','包含类别的否定搜索匹配');
		$this->unit->run(array_column($object->getList(array('type'=>'test','without_tags'=>array('房产'))),'num'),array('003'),'对象列表-标签','包含类别的否定搜索不匹配');
		
		$object->addMeta('电话', '13641926334', '手机');
		$object->addMeta('电话', '56756616', '家庭');
		$object->addMeta('地址', '韶山路348弄28号');
		$this->unit->run(array_column($object->getList(array('type'=>'test','has_meta'=>array('电话'))),'num'),array('001','002'),'对象列表-资料项');
		
		$this->output->set_output($this->unit->report());
		
	}
	
	function account(){
		$this->load->model('account_model','account');
	}
	
	function user(){
		
	}
	
	function ar(){
		$this->db->from('object')
			->where('id IN (SELECT id FROM people)');
		echo $this->db->last_query();
	}
	
	/**
	 * CDS示例代码，id为4的用户在case栏目里搜索"一审"、"诉讼"、"法律"
	 */
	function cds(){
		$keywords=array('一审','诉讼','法律');
		$hasError=false;
		$sql='call init_CDS();';
		$isOK=$this->db->simple_query($sql); //先调用init_CDS()来初始化CDS
		if($isOK){
			for($i=0,$isContinue=true;$i<count($keywords)&&$isContinue;$i++){
				$keyword=$keywords[$i];
				$sql='insert into keywords_table values(\''.$keyword.'\');'; //顺序插入搜索关键字
				$this->db->query($sql);
				$affectedRows=$this->db->affected_rows();
				if($affectedRows==0){
					$this->errorSQLMessage($sql);
					$isContinue=false;
					$hasError=true;
				}
			}
			if(!$hasError){
				$onlyThis=1;
				$userId=2;
				$sql='call case_CDS('.$userId.','.$onlyThis.');';
				$isOK=$this->db->simple_query($sql);
				if($isOK){
					//从CD_table里去取得结果
					$sql='select ct.id,ct.name,ct.column_name from CD_table ct order by ct.degree desc,ct.matches desc;';
					$results=$this->db->query($sql);
					$rows=$results->result_array();
					foreach($rows as $row){
						$rowDisplay='';
						foreach($row as $field=>$value){
							$rowDisplay.=$field.' : '.$value.'&nbsp;&nbsp;&nbsp;';
						}
						echo $rowDisplay.'<br/>';
					}
					$results->free_result();					
				}
				else{
					$this->errorMessage($sql);
				}
			}
		}
		$sql='call finalize_CDS();'; //释放CDS资源
		$isOK=$this->db->simple_query($sql);
		if(!$isOK){
			$this->errorSQLMessage($sql);
		}
		
		
		$this->load->sidebar_loaded=true;
	}
	
	function pscws(){
		$pscws_path=APPPATH.'third_party/pscws4/';
		require_once($pscws_path.'pscws4.class.php');
		$pscws=new PSCWS4('utf8');
		$pscws->set_dict($pscws_path.'dict.utf8.xdb');
		$pscws->set_rule($pscws_path.'etc/rules.utf8.ini');
		$pscws->set_ignore(true);
		$text='我是华东政法大学的学生';
		echo $text;
		$pscws->send_text($text);
		$words=array();
		while($some=$pscws->get_result()){
			foreach($some as $one){
				array_push($words,$one['word']);
			}
		}
		var_dump($words);
		$display_text=implode(' ',$words);
		echo $display_text;
		$pscws->close();
		
		
		$this->load->sidebar_loaded=true;
	}
	
	function tagSearch(){
		$tag_string="一审婚姻法律";
		$this->load->model('Tag_model','tag_model');
		$sorted_results=$this->tag_model->search($tag_string);
		var_dump($sorted_results);
	}
}

?>
