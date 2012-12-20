<?php
define('ROWS',7);define('CELLS',5);

define('MIN_KILL_CONNECTION',4);

define('DEEPTH',2);

$function_score_run=0;

$moves[0]=array(
	array(
		'puzzle'=>array(
			array(
				1,3,2,1,3
			),
			array(
				2,3,1,2,2
			),
			array(
				2,2,3,4,3
			),
			array(
				1,3,5,4,3
			),
			array(
				5,2,5,5,4
			),
			array(
				5,4,4,3,2
			),
			array(
				5,4,3,2,1
			)
		),
		'score'=>0,
		'steps'=>array()
	)
);

$timestart=microtime(true);

for($deepth=1;$deepth<=DEEPTH;$deepth++){
	$best_move=array('score'=>0);
	foreach($moves[$deepth-1] as $move){
		if($deepth>1){
			$last_move=$move['steps'][count($move['steps'])-1];
		}
		for($row=0;$row<ROWS;$row++){
			for($cell=0;$cell<CELLS;$cell++){
				for($move_type=0;$move_type<3;$move_type++){
					if($deepth>1 && $last_move[0]==$row && $last_move[1]==$cell && $last_move[2]==$move_type){
						break;
					}
					if(!isset($move['puzzle'])){
						$puzzle=$moves[0][0]['puzzle'];
						foreach($move['steps'] as $step){
							$move['puzzle']=exchange($puzzle,$step[0],$step[1],$step[2]);
						}
					}
					
					$puzzle=exchange($move['puzzle'],$row,$cell,$move_type);
					
					if($puzzle===false){
						break;
					}
					$new_move=score($puzzle);
					$new_move['score']+=$move['score'];
					$new_move['steps']=array_merge($move['steps'],array(array($row,$cell,$move_type)));
					if($new_move['score']>$best_move['score']){
						$best_move=$new_move;
					}
					$moves[$deepth][]=$new_move;
				}
			}
		}
	}
	print_r($best_move);
}
echo $function_score_run."\n";
echo microtime(true)-$timestart;

function exchange($puzzle,$row,$cell,$type){
	$temp=$puzzle[$row][$cell];
	if($type==0){
		if(!isset($puzzle[$row][$cell+1])){
			return false;
		}
		$puzzle[$row][$cell]=$puzzle[$row][$cell+1];
		$puzzle[$row][$cell+1]=$temp;
	}
	elseif($type==1 && isset($puzzle[$row+1])){
		if(!isset($puzzle[$row+1][$cell])){
			return false;
		}
		$puzzle[$row][$cell]=$puzzle[$row+1][$cell];
		$puzzle[$row+1][$cell]=$temp;
	}
	elseif($type==2 && isset($puzzle[$row+1][$cell+1])){
		if(!isset($puzzle[$row+1][$cell+1])){
			return false;
		}
		$puzzle[$row][$cell]=$puzzle[$row+1][$cell+1];
		$puzzle[$row+1][$cell+1]=$temp;
	}
	return $puzzle;
}
function score($puzzle, $score=0, $combo=1){
	global $function_score_run;
	$function_score_run++;
	
	$connections=array();
	//$ignore=fillTable(ROWS, CELLS, array(false,false,false));
	
	for($row=0;$row<ROWS;$row++){
		for($cell=0;$cell<CELLS;$cell++){
			$connection_length=1;$connection_type=0;
			if(!isset($ignore[$row][$cell][$connection_type])){
				for($x_delta=1;$cell+$x_delta<CELLS && isset($puzzle[$row][$cell]) && $puzzle[$row][$cell]===$puzzle[$row][$cell+$x_delta];$x_delta++){
					$connection_length++;
					$ignore[$row][$cell+$x_delta][$connection_type]=true;
				}
				if($connection_length>=MIN_KILL_CONNECTION){
					$connections[]=array('start_point_row'=>$row,'start_point_cell'=>$cell,'type'=>$connection_type,'length'=>$connection_length);
				}
			}
			
			$connection_length=1;$connection_type=1;
			if(!isset($ignore[$row][$cell][$connection_type])){
				for($y_delta=1;$row+$y_delta<ROWS && isset($puzzle[$row][$cell]) && $puzzle[$row][$cell]===$puzzle[$row+$y_delta][$cell];$y_delta++){
					$connection_length++;
					$ignore[$row+$y_delta][$cell][$connection_type]=true;
				}
				if($connection_length>=MIN_KILL_CONNECTION){
					$connections[]=array('start_point_row'=>$row,'start_point_cell'=>$cell,'type'=>$connection_type,'length'=>$connection_length);
				}
			}
			
			$connection_length=1;$connection_type=2;
			if(!isset($ignore[$row][$cell][$connection_type])){
				for($x_delta=1,$y_delta=1;$row+$y_delta<ROWS && $cell+$x_delta<CELLS && isset($puzzle[$row][$cell]) && $puzzle[$row][$cell]===$puzzle[$row+$y_delta][$cell+$x_delta];$x_delta++,$y_delta++){
					$connection_length++;
					$ignore[$row+$y_delta][$cell+$x_delta][$connection_type]=true;
				}
				if($connection_length>=MIN_KILL_CONNECTION){
					$connections[]=array('start_point_row'=>$row,'start_point_cell'=>$cell,'type'=>$connection_type,'length'=>$connection_length);
				}
			}
		}
	}
	
	$num_of_connections=count($connections);
	$score_of_this_action=0;
	
	foreach($connections as $connection){
		$score_of_this_action+=($connection['length']-MIN_KILL_CONNECTION+1)*100;
	}

	$score_of_this_action=$score_of_this_action*$num_of_connections;
	
	$score_of_this_action=$score_of_this_action*$combo;
	
	$score+=$score_of_this_action;
	
	!empty($connections) && $puzzle=kill($puzzle,$connections);
	
	if($num_of_connections>0){
		return score($puzzle,$score,$combo+1);
	}
	else{
		if($score>0){
			return array('puzzle'=>$puzzle,'score'=>$score);
		}else{
			return array('score'=>$score);
		}
		
	}
	
}

function kill($puzzle,$connections){
	foreach($connections as $connection){
		if($connection['type']==0){
			for($i=0;$i<$connection['length'];$i++){
				$puzzle[$connection['start_point_row']][$connection['start_point_cell']+$i]=NULL;
			}
		}
		elseif($connection['type']==1){
			for($i=0;$i<$connection['length'];$i++){
				$puzzle[$connection['start_point_row']+$i][$connection['start_point_cell']]=NULL;
			}
		}
		elseif($connection['type']==2){
			for($i=0;$i<$connection['length'];$i++){
				$puzzle[$connection['start_point_row']+$i][$connection['start_point_cell']+$i]=NULL;
			}
		}
	}
	
	for($row=ROWS-1;$row>0;$row--){
		for($cell=CELLS-1;$cell>=0;$cell--){
			if(is_null($puzzle[$row][$cell])){
				$puzzle[$row][$cell]=$puzzle[$row-1][$cell];
				$puzzle[$row-1][$cell]=NULL;
			}
		}
	}
	
	return $puzzle;
}

function fillTable($rows,$cells,$content=NULL){
	$table=array();
	for($row=0;$row<ROWS;$row++){
		for($cell=0;$cell<CELLS;$cell++){
			$table[$row][$cell]=$content;
		}
	}
	
	return $table;
}
?>