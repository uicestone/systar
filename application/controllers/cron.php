<?php
class Cron extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		model('document');
		forceExport();
		
		$this->db->update('document',array('path'=>$this->config->item('document_root')),'id = 1');
		
		//从实体数据库取出内存表
		db_query("
			CREATE TEMPORARY TABLE `document_temp` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				`name` VARCHAR( 255 ) NOT NULL DEFAULT  '',
				`parent` INT( 11 ) DEFAULT NULL ,
				`path` TEXT,
				`type` CHAR( 15 ) DEFAULT NULL,
				`size` INT( 11 )  DEFAULT NULL,
				`uid` INT( 11 ) NULL,
				`username` VARCHAR( 255 ) NOT NULL DEFAULT  '',
				`time` INT( 11 ) NOT NULL DEFAULT  '0',
				`comment` TEXT,
				PRIMARY KEY (  `id` ),
				KEY  `parent` (  `parent` )
			) ENGINE = INNODB DEFAULT CHARSET = utf8"
		);
		
		db_query("
			INSERT INTO `document_temp` 
			SELECT * 
			FROM  `document`"
		);
		
		$folder_array=array();
		$subfolder_array=array(1);
		
		while(!empty($subfolder_array)){
			//在需要列的目录还有的时候继续循环
			//每次循环是一层
		
			$folder_array=$subfolder_array;
			$subfolder_array=array();
		
			foreach($folder_array as $folder){
				//将当前层级的目录列出
				$current_dir=db_fetch_field("SELECT path FROM document_temp WHERE id = '".$folder."'");
		
				//列出数据库中的项
				$db_file_array=db_toArray("SELECT id,name,parent,path,type,size FROM document_temp WHERE parent='".$folder."'");
		
				foreach($db_file_array as $db_file){
					//将数据库中的目录列为下层待展开目录
					if(!isset($db_file['type'])){
						$subfolder_array[]=$db_file['id'];
						//本级的目录是下级待展开目录
					}
				}
				
				//列出实体文件中的项
				$file_array=array();
				if(is_dir(mb_convert_encoding($current_dir,'gbk','utf-8'))){
					$handle = opendir(mb_convert_encoding($current_dir,'gbk','utf-8'));
				}else{
					echo "\n".'cannot open '.$folder.': '.$current_dir.", deleted<br>";flush();
					db_delete('document_temp',"id='".$folder."'");
					continue;
				}
				while($filename = readdir($handle)){
					if($filename!='.' && $filename!='..' && !preg_match('/^~\$.*\.doc$/',$filename)){
						$filename=mb_convert_encoding($filename,'utf-8','gbk');
						$file_array[]=array(
							'name'=>$filename,
							'path'=>$current_dir.'/'.$filename,
							'parent'=>$folder,
						);
						if (is_file(mb_convert_encoding($current_dir.'/'.$filename,'gbk','utf-8'))){
							$file_array[count($file_array)-1]['size']=(int)filesize(mb_convert_encoding($current_dir.'/'.$filename,'gbk','utf-8'));
							$file_array[count($file_array)-1]['type']=document_getExtension($filename);
						}elseif(!is_dir(mb_convert_encoding($current_dir.'/'.$filename,'gbk','utf-8'))){
							$file_array[count($file_array)-1]['type']='e';
						}
					}
				}
				closedir($handle);
				
				//找出数据库中不存在的实体文件，插入数据库
				foreach($file_array as $file){
					$in_array=in_subarray($file['name'],$db_file_array,'name');
					if($in_array===false){
						//此实体文件在数据库中不存在
						$insert_id=db_insert('document_temp',$file,true,false,false);
						//echo $file['name'].' inserted<br>';flush();
						if(mysql_error()){
							flush();
						}
						if(!isset($file['type'])){
							$subfolder_array[]=$insert_id;
						}
					}
				}
				
				//找出实体文件不存在的数据库文件，从数据库删除
				foreach($db_file_array as $db_file){
					$in_array=in_subarray($db_file['name'],$file_array,'name');
					if($in_array===false){
						//此数据库文件已经不实际存在
						db_delete('document_temp',"id='".$db_file['id']."'");
						echo $db_file['name'].' deleted<br>';flush();
					}
				}
			}
		
		/*
			echo $current_dir."<br>";
			flush();
		*/
		}
		
		//将内存表存回实体数据库
		db_query("DROP TABLE IF EXISTS `document`");
		
		db_query("
			CREATE TABLE `document` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				`name` VARCHAR( 255 ) NOT NULL DEFAULT  '',
				`parent` INT( 11 ) DEFAULT NULL ,
				`path` TEXT,
				`type` CHAR( 15 ) DEFAULT NULL,
				`size` INT( 11 ) DEFAULT NULL,
				`uid` INT( 11 ) NULL,
				`username` VARCHAR( 255 ) NOT NULL DEFAULT  '',
				`time` INT( 11 ) NOT NULL DEFAULT 0,
				`comment` TEXT,
				PRIMARY KEY (  `id` ) ,
				UNIQUE KEY  `name` (  `name` ,  `parent` ) ,
				KEY  `uid` (  `uid` ) ,
				KEY  `time` (  `time` ) ,
				KEY  `parent` (  `parent` ) ,
				KEY  `size` (  `size` ) ,
				KEY  `type` (  `type` )
			) ENGINE = INNODB DEFAULT CHARSET = utf8"
		);
		
		db_query("
			INSERT INTO `document` 
			SELECT * 
			FROM  `document_temp`
		"
		);	
	}
}
?>