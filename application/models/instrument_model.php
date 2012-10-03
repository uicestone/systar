<?php
class Instrument_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function num_to_chn($num,$type='lower'){
	/*
		将一个阿拉伯数字(正实数)转换为汉语
		$integer	整数部分
		$demical	小数部分
		$digit		当前位的数字
		$beside_zero 当前位的低一位是否已经是零
		$is_tail	当前位是否为最后的不需要表示的0
		$num		数字金额
		$output		要输出的大写字符串
		$pos		当前数位的倒次序
	*/
		if($type=='upper'){
			$char=array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖');
			$ten=array('拾','佰','仟','万','亿');
		}elseif($type=='lower'){
			$char=array('零','一','二','三','四','五','六','七','八','九');
			$ten=array('十','百','千','万','亿');
		}
	
		if(!is_numeric($num) || $num<0 || $num>=1E12){
			return false;
		}
		
		$num=sprintf('%s',$num);//将数字格式化为字符串
		
		$num_part=explode('\.',$num);
		
		$integer=$num_part[0];
		$decimal=isset($num_part[1])?$num_part[1]:'';
		
		//处理小数部分
		$output_decimal='';
		if($decimal!=''){
			$output_decimal='点';
			for($pos=0;$pos<strlen($decimal);$pos++){
				$output_decimal.=$char[substr($decimal,$pos,1)];
			}
		}
	
		//处理整数部分
		$output_integer='';$beside_zero=false;$is_tail=true;
		
		if($integer==0){
			$output_integer=$char[0];
		}else{
			for($pos=1;$pos<=4 && $pos<=strlen($integer);$pos++){
				$digit=substr($integer,-$pos,1);
				if($digit!=0){
					$is_tail=false;
					switch($pos){
						case 2:$output_integer=$ten[0].$output_integer;break;
						case 3:$output_integer=$ten[1].$output_integer;break;
						case 4:$output_integer=$ten[2].$output_integer;break;
					}
				}
				$output_integer=(!$is_tail && !($beside_zero && $digit==0))?($char[$digit].$output_integer):$output_integer;
				if($digit==0){
					$beside_zero=true;
				}else{
					$beside_zero=false;
				}
			}
		}
		
		//处理亿万部分
		$ten_thousands=substr($integer,strlen($integer)>8?-8:0,strlen($integer)>8?4:strlen($integer)-4);
		if(strlen($integer)>4 && $ten_thousands>0){
			$output_integer=num_to_chn($ten_thousands).$ten[3].$output_integer;
		}
		
		$hundred_millions=substr($integer,strlen($integer)>12?-12:0,strlen($integer)>12?8:strlen($integer)-8);
		if(strlen($integer)>8 && $hundred_millions>0){
			$output_integer=num_to_chn($hundred_millions).$ten[4].$output_integer;
		}
		
		return $output_integer.$output_decimal;
	
	}
	
	function formalRMB($sum){
	
		if(!is_numeric($sum) || $sum<0){
			return false;
		}
	
		$integer=floor($sum);
		$decimal=substr(100+round(100*($sum-$integer)),-2);//保留整数前面的'0'
		
		//处理小数部分
		$is_tail=true;
		if($decimal==0){
			$output='整';
		}else{
			$output='';
			for($pos=1;$pos<=strlen($decimal);$pos++){
				$digit=substr($decimal,-$pos,1);
				if($digit!=0){
					$is_tail=false;
					switch($pos){
						case 1:$output='分'.$output;break;
						case 2:$output='角'.$output;
					}
				}
				$output=!$is_tail?num_to_chn($digit,'upper').$output:$output;
			}
		}
		
		//处理整数部分
		$output=num_to_chn($integer,'upper').'元'.$output;
		
		return $output;
	}
	
	function instrument($type,$condition){
	
		$paper=$item=array();$line='';
		
		$item[]=$condition['client_name'].
			'（ 以下简称"委托人"）因与'.
			$condition['opposite_side'].$condition['opposite'].
			'纠纷一案，聘请上海星瀚律师事务所（以下简称"律师事务所"）的律师处理相关法律事宜，经双方协商一致，订立下列条款，共同遵照履行：';
			
		$paper[]=$item;
		$item=array();
		//添加一级段落
		
		$item[]='委托人委托律师事务所处理的事务内容为：';
		
		$line='诉讼阶段：';
		
		$glue='';
		foreach($condition['stages'] as $stage){
			if($stage!='total'){
				$line.=$glue;
				$line.=$stage;
				$glue=',';
			}
		}
		
		$line.='（权限详见授权委托书）。';
		
		$item[]=$line;
		$paper[]=$item;
		$item=array();
		//添加一级段落
		
		$line='律师事务所指派';
		
		$glue='';
		foreach($condition['lawyer'] as $lawyer => $hourlyFee){
			$line.=$glue;
			$line.=$lawyer;
			$glue=',';
		}
		
		$line.='作为本合同项下案件承办律师（以下简称"律师"）。';
		
		$item[]=$line;
		$paper[]=$item;
		$item=array();
		//添加一级段落
		
		$item[]='根据双方协商一致，本合同项下律师费按以下方式收取：';
		
		foreach($condition['fee'] as $stage => $feeArray){
			if($stage!='total')
				$item[]=$stage.'：';
		
			if(array_key_exists('固定',$feeArray) && !array_key_exists('风险',$feeArray)){
				//固定收费
				$item[]='固定收费：';
				$item[]='律师费为'.formalRMB($feeArray['固定']).',由委托人于签订本合同二日内向律师事务所支付。';
				
			}else{
				//含风险收费
				$item[]='风险代理的基本收费：';
				$item[]='签订本合同后二日内，委托人应当支付律师事务所'.formalRMB($feeArray['固定']).'作为律师风险代理的基本律师费。';
				
				$item[]='风险代理的风险收费：';
				
				foreach($feeArray['风险'] as $riskFeeItem){
					$line='若通过律师工作，委托人能与第三人通过协商、调解、案外和解等方式或通过法院判决、法院调解等方式从而';
					$line.=$riskFeeItem['condition'];
					$line.='则委托人同意另行按';
					if(isset($riskFeeItem['percent'])){
						$line.='获得上述款项的'.$riskFeeItem['percent'].'%';
					}
					if(isset($riskFeeItem['percent']) && isset($riskFeeItem['fee'])){
						$line.=',以及';
					}
					if(isset($riskFeeItem['fee'])){
						$line.=formalRMB($riskFeeItem['fee']);
					}
					$line.='的标准向律师事务所支付律师风险代理费。';
				}
				$item[]=$line;
				$item[]='本条款项下律师费用应在'.$condition['fee_pay_time'].'支付';
			}
		}
		
		$item[]=$line;
		$paper[]=$item;
		$item=array();
		//添加一级段落
		
		if(isset($condition['fee_misc'])){
			$item[]='根据双方协商一致，与办理委托事项密切关联的各项支出（以下可简称"办案费用"）中，交通费、邮寄费、调查费、复印费、诉讼费、保全费、外地餐饮费、外地住宿费、查询费等由委托人承担。本合同签订后的二日内，委托人应向预付办案费用'.formalRMB($condition['fee_misc']).'，结案时据实结算。';
		}
		
		$item[]=$line;
		$paper[]=$item;
		$item=array();
		//添加一级段落
		
		$q_items="SELECT * FROM instrument WHERE 1=1 ORDER BY `order`";
		$items=db_toArray($q_items);
		$items=array_sub($items,'content');
		
		for($i=0;$i<count($items);$i++){
			$items[$i]=explode("\n",$items[$i]);
		}
		
		$paper=array_merge($paper,$items);
		
	}
}

?>