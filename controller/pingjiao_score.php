<?php 
//评分界面
if(!sessioned('timeStart'))
	$_SESSION['timeStart'] = time();//打开评价窗口时的时间

$teacher_id = $_GET['teacher'];
$teacher_name = $_GET['teacher_name'];

for ($i=0;$i<count($_SESSION['stu_teachers_Uggew7ac']);$i++){
	if($_SESSION['stu_teachers_Uggew7ac'][$i]==$teacher_id){$comment_right=1;}
}

if(!isset($comment_right)){
	echo "<center><font size=4 color=red>你无权对这个老师评价！";
	echo "<a href='javascript:window.close()'>关闭</a>!</font></center>";
	exit;
}

$query="SELECT * FROM `result` WHERE `teacher`='".$teacher_id."' AND `student`='".$_SESSION['id']."' AND `term`='".$_SESSION['global']['current_term']."'";
$result=mysql_query($query,$link);
if(db_rows($result)==1){
	echo "<center><font size=4 color=red>您已经对 ";
	echo "<font color=green>".$teacher_name."</font>";
	echo "老师 进行过评分，<p>不能再次评价</p><br>";
	echo "<a href='javascript:window.close()'>关闭</a>!</font></center>";
	exit;
}//预先判断是否已经评价
echo "<b>对".$teacher_name."的评价</b>";//显示本教师名字

if(isset($_POST["scoreSubmit"])){
	$_SESSION['post']=$_POST;

	$_SESSION['post']['score']['sum']=array_sum($_SESSION['post']['score'])/count($_SESSION['post']['score']);

	if($_SESSION['post']['score']['sum']>10 || $_SESSION['post']['score']['sum'] <0){
		echo "you've broken rules";
		exit;
	}//防止跨域form超限
	
	$_SESSION['post']['score']['suggest']=$_SESSION['post']['suggest'];
	$_SESSION['post']['score']['teacher']=$teacher_id;
	$_SESSION['post']['score']['ip']=getIP();
	$_SESSION['post']['score']['time']=time();
	$_SESSION['post']['score']['student']=$_SESSION['id'];
	$_SESSION['post']['score']['term']=$_SESSION['global']['current_term'];
	
	if(time()-$_SESSION['timeStart']<7){
		unset($_SESSION['timeStart']);
		echo "<center><font size=4 color=red>你不该草率评价你的老师<br><br>";
		echo "<a href=\"$_SERVER[REQUEST_URI]\">重来</a>!</font></center>";
		exit;

	}elseif(db_insert('result',$_SESSION['post']['score'])){
		unset($_SESSION['timeStart']);
		echo "<center><font size=4 color=red>谢谢您的参与<br><br>";
		echo "<a href='javascript:window.close()'>关闭</a>!</font></center>";
		exit;
	}
}
?>
<form method="post" action="<?php $PHP_SELF ?>">
<input type=hidden name=teacher_name value=<?php echo $teacher_name; ?>>
	<!--评分项相关-->
  <table width="100%"  border="0.0" align="center" cellspacing="1" class=bgcolor1>
    <tr align=center class=bgcolor2>
      <td width="15%">序号</td>
      <td width="70%">评分标准</td>
      <td width="15%">得分</td>
    </tr>
    <tr>
      <td colspan="3" class=bgcolor2>一、师德</td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>1</td>
      <td>你认为该教师责任心强吗？</td>
      <td><select name="score[1]">
        <option value="0.0">无</option>
        <option value="2.5">不够强</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">比较强</option>
        <option value="10">很强</option>
 		</select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>2</td>
      <td>你认为该教师尊重学生吗？</td>
      <td><select name="score[2]">
         <option value="0.0">很不尊重</option>
        <option value="2.5">不够尊重</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">比较尊重</option>
        <option value="10.0">很尊重</option>
      </select></td>
    </tr>
    <tr class=bgcolor2>
      <td colspan="3">二、技能</td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>3</td>
      <td>你认为该教师课堂管理有序吗？</td>
      <td><select name="score[3]">
        <option value="0.0">很乱</option>
        <option value="2.5">有些乱</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">比较有序</option>
        <option value="10.0">有序</option>
		</select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>4</td>
      <td>该教师的教学语言能做到准确、规范、简练吗？</td>
      <td><select name="score[4]">
        <option value="0.0">完全不能</option>
        <option value="2.5">不太能</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">基本能</option>
        <option value="10.0">能</option>
      </select></td>
    </tr>

    <tr class=bgcolor3>
      <td align=center>5</td>
      <td>该教师能合理运用多媒体等信息技术教学手段吗？</td>
      <td><select name="score[5]">
        <option value="0.0">不能</option>
        <option value="2.5">不太能</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">基本能</option>
        <option value="10.0">能</option>
      </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>6</td>
      <td>该教师的板书设计能做到清晰合理吗？</td>
      <td><select name="score[6]">
        <option value="0.0">不能</option>
        <option value="2.5">不太能</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">基本能</option>
        <option value="10.0">能</option>
      </select></td>
    </tr>
    <tr class=bgcolor2>
      <td colspan="3">三、能力</td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>7</td>
      <td>你认为该教师上课准备工作如何？</td>
      <td><select name="score[7]">
        <option value="0.0">很马虎</option>
        <option value="2.5">有些随意</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">比较充分</option>
        <option value="10.0">精心备课</option>
     </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>8</td>
      <td>你认为该教师教学的深度、广度、进度设计得适当吗？</td>
      <td><select name="score[8]">
        <option value="0.0">不适当</option>
        <option value="2.5">不太适当</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">比较适当</option>
        <option value="10.0">很适当</option>
     </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>9</td>
      <td>该教师教学的重点难点讲解得透彻吗？</td>
      <td><select name="score[9]">
        <option value="0.0">很模糊</option>
        <option value="2.5">有些模糊</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">比较透彻</option>
        <option value="10.0">很透彻</option>
     </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>10</td>
      <td>该教师的课堂教学方法能做到灵活多样吗？</td>
      <td><select name="score[10]">
        <option value="0.0">不能</option>
        <option value="2.5">不太能</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">基本能</option>
        <option value="10.0">能</option>
     </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>11</td>
      <td>该教师能调动学生积极性，激发学生的学习兴趣吗？</td>
      <td><select name="score[11]">
        <option value="0.0">不能</option>
        <option value="2.5">不太能</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">基本能</option>
        <option value="10.0">能</option>
     </select></td>
    </tr>
	<tr class=bgcolor2>
      <td colspan="3">四、规范</td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>12</td>
      <td>该教师有迟到、早退、无故缺课等情况吗？</td>
      <td><select name="score[12]">
        <option value="0.0">总是</option>
        <option value="2.5">经常</option>
        <option value="5.0" selected>有时</option>
        <option value="7.5">极个别情况</option>
        <option value="10.0">从无</option>
     </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>13</td>
      <td>该教师的作业布置适量吗？</td>
      <td><select name="score[13]">
        <option value="0.0">太多或太少</option>
        <option value="2.5">有些多或有些少</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">比较适量</option>
        <option value="10.0">适量</option>
     </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>14</td>
      <td>该教师能做到试卷、作业批改认真并及时反馈吗？</td>
      <td><select name="score[14]">
        <option value="0.0">不能</option>
        <option value="2.5">不太能</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">基本能</option>
        <option value="10.0">能</option>
      </select></td>
    </tr>

    <tr class=bgcolor3>
      <td align=center>15</td>
      <td>该教师课后会对学生进行辅导吗？</td>
      <td><select name="score[15]">
        <option value="0.0">从不</option>
        <option value="2.5">不太会</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">有时会</option>
        <option value="10.0">经常会</option>
      </select></td>
    </tr>
	<tr class=bgcolor2>
      <td colspan="3">五、效果</td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>16</td>
      <td>上该老师的课，你对本课程的学习热情增加了吗？</td>
      <td><select name="score[16]">
        <option value="0.0">没有增加</option>
        <option value="2.5">较少</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">有些增加</option>
        <option value="10.0">增加很多</option>
      </select></td>
    </tr>
    <tr class=bgcolor3>
      <td align=center>17</td>
      <td>上该教师的课，你的学业水平提高了吗？</td>
      <td><select name="score[17]">
        <option value="0.0">没有提高</option>
        <option value="2.5">提高较慢</option>
        <option value="5.0" selected>一般</option>
        <option value="7.5">有些提高</option>
        <option value="10.0">提高很快</option>
     </select></td>
    </tr>
    <tr>
      <td colspan="3" class=bgcolor2>大胆写下你的评语</td>
    </tr>
	<tr class=bgcolor3>
      <td colspan="3"><textarea name="suggest" cols="60" rows="5" id="suggest"></textarea></td>
    </tr>
    <tr class=bgcolor2>
      <td colspan="3"><div align="center">
        <input type="submit" name="scoreSubmit" value="提交">
      </div></td>
    </tr>
  </table>
</form>