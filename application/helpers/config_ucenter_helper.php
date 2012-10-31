<?php
/*Ucenter Config*/
define('UC_CONNECT', 'mysql');				// 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen()
							// mysql 是直接连接的数据库, 为了效率, 建议采用 mysql

//数据库相关 (mysql 连接时, 并且没有设置 UC_DBLINK 时, 需要配置以下变量)
define('UC_DBHOST', 'localhost');			// UCenter 数据库主机
define('UC_DBUSER', 'ucenter');				// UCenter 数据库用户名
define('UC_DBPW', '!@!*xinghan');					// UCenter 数据库密码
define('UC_DBNAME', 'ucenter');				// UCenter 数据库名称
define('UC_DBCHARSET', 'utf8');				// UCenter 数据库字符集
define('UC_DBTABLEPRE', 'ucenter.');			// UCenter 数据库表前缀
define('UC_DBCONNECT','');

//通信相关
define('UC_KEY', 'G03287Fclajapdk9ZfS1B06c2br5JdO6S2I9T9ffEbL0g4BcC6d4R0Ocn3v9t3Td');				// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', 'http://ucenter.lawyerstars.com');	// UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'utf-8');				// UCenter 的字符集
define('UC_IP', '');					// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', 5);					// 当前应用的 ID

$ucenter_db_name='ucenter';
$ucenter_db_pconnect=0;

//同步登录 Cookie 设置
$cookiedomain = ''; 			// cookie 作用域
$cookiepath = '/';			// cookie 作用路径
?>