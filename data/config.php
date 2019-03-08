<?php
ob_start();
session_start();
//基本设置
date_default_timezone_set("Asia/Shanghai");

// 检查PHP版本
if(PHP_VERSION<5.3){
	die("PHP版本小于5.3，请升级！");
}
define("siteName","多用户记账系统");
define("SiteURL","http://www.test.com/");
define("Multiuser","0");/*是否开启多用户，1为开启，0为禁用*/
// database
define("DB_NAME","jizhang_RA16D7d77RvfgplP.s3db");
define("TABLE","jizhang_");