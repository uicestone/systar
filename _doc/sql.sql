--删除垃圾
DELETE FROM account WHERE amount=0;
DELETE FROM `case` WHERE display=0 AND id>0;
DELETE FROM `client` WHERE display=0 AND id>0;
DELETE FROM `schedule` WHERE display=0 AND id>0;

--查重复输入同笔收费的到账
SELECT * 
FROM account a
WHERE (
	SELECT COUNT( 1 ) 
	FROM account
	WHERE case_fee = a.case_fee
) >1
ORDER BY  `a`.`case_fee` ASC;

--今年咨询费总额
SELECT SUM(amount) FROM `account` WHERE FROM_UNIXTIME(time_occur,'%Y')='2012' AND name='咨询费'

--统计
--今年收录构成
SELECT case.type,SUM(amount) AS sum
FROM account INNER JOIN `case` ON case.id=account.case
WHERE account.name IN('律师费','顾问费','咨询费') 
            AND time_occur>=UNIX_TIMESTAMP('2012-1-1')
            AND time_occur<UNIX_TIMESTAMP('2012-8-1')
GROUP BY case.type

--汇总律师贡献
SELECT staff.name,account.sum*contribute.sum/0.7 FROM 
(
	SELECT `case`,SUM(amount) as sum FROM account WHERE name<>'办案费' AND time_occur >= UNIX_TIMESTAMP('2012-1-1') AND time_occur<UNIX_TIMESTAMP('2012-12-31') GROUP BY `case`
)account
INNER JOIN 
(
	SELECT `case`,SUM(contribute) AS sum,lawyer FROM case_lawyer WHERE role<>'实际贡献' GROUP BY `case`,lawyer HAVING sum >0
)contribute ON account.case=contribute.case
INNER JOIN staff ON contribute.lawyer = staff.id

--对账
SELECT account.id,account.amount,client.name,FROM_UNIXTIME(time_occur,'%Y%m%d') AS time_occur,FROM_UNIXTIME(account.time,'%Y%m%d') AS time 
FROM account LEFT JOIN client on client.id=account.client 
WHERE FROM_UNIXTIME(time_occur,'%Y%m')<201205 and FROM_UNIXTIME(time_occur,'%Y%m')>=201201

--将starsys的新用户插入star_bak
INSERT IGNORE INTO star_bak.`pre_common_member` (username,email,groupid)
SELECT content,content,33 FROM starsys.client_contact WHERE type='电子邮件'

--案号自增触发器
CREATE TRIGGER `trig_case_num_multiautoincrease` BEFORE INSERT ON `case_num`
 FOR EACH ROW SET `new`.`number` = IF(
	(SELECT COUNT(*) FROM case_num WHERE classification_code = new.classification_code AND type_code = new.type_code AND year_code = new.year_code) = 0, 
	1,
	(SELECT MAX(number)+1 FROM case_num WHERE classification_code = new.classification_code AND type_code = new.type_code AND year_code = new.year_code)
)

--更新分班列表的分数
UPDATE student_classdiv INNER JOIN
(
	SELECT view_student.id,view_student.name,view_student.gender,SUM(unioned.score) as score
	FROM (
		select student,".$course_field."*0.3 as score from view_score where exam = 2 and ".$course_field." IS NOT NULL
		union
		select student,".$course_field."*0.3 as score from view_score where exam = 11 and ".$course_field." IS NOT NULL
		union
		select student,".$course_field."*0.4 as score from view_score where exam = 14 and ".$course_field." IS NOT NULL
	)unioned INNER JOIN view_student ON unioned.student=view_student.id
	INNER JOIN student_classdiv USING(id)
	--WHERE student_classdiv.extra_course=".$extra_course."
	--加一科目
	GROUP BY student
	HAVING count(*)=3
)a USING (id)
SET student_classdiv.".$course_field."=a.score

--更新分班列表的分数-补全未参加每次考试的学生分数
UPDATE student_classdiv INNER JOIN
(
    SELECT view_student.id,view_student.name,view_student.gender,SUM(score) AS score
    FROM (
            SELECT student,".$course_field." AS score FROM view_score where exam = ".$prior_exam." and ".$course_field." IS NOT NULL
    )unioned INNER JOIN view_student ON unioned.student=view_student.id
    --INNER JOIN student_classdiv USING(id)
    --WHERE student_classdiv.extra_course=".$extra_course."
	--加一科目
    GROUP BY student 
)b USING(id)
SET student_classdiv.extra_course_score=b.score
WHERE student_classdiv.extra_course_score IS NULL

--拉出提高班
UPDATE student_classdiv SET new_class=1201 WHERE extra_course=4 ORDER BY course_1+course_2+course_3+extra_course DESC LIMIT 44