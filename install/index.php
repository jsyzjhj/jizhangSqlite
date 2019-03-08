<?php
error_reporting(E_ALL & ~E_NOTICE);  //显示全部错误
define('ROOT_PATH', dirname(dirname(__FILE__)));  //定义根目录
define('DBCHARSET','UTF8');   //设置数据库默认编码
if(function_exists('date_default_timezone_set')){
	date_default_timezone_set('Asia/Shanghai');
}
input($_GET);
input($_POST);
function input(&$data){
	foreach ((array)$data as $key => $value) {
		if(is_string($value)){
			if(!get_magic_quotes_gpc()){
				$value = htmlentities($value, ENT_NOQUOTES);
                $value = addslashes(trim($value));
			}
		}else{
			$data[$key] = input($value);
		}
	}
}
//判断是否安装过程序
if(is_file('lock') && $_GET['step'] != 5){
	@header("Content-type: text/html; charset=UTF-8");
    echo "系统已经安装！！！如果要重新安装，请删除install目录下的lock文件并删除数据库文件！";
    exit;
}

$html_title = '程序安装向导';
$html_header = <<<EOF
<div class="header">
  <div class="layout">
    <div class="title">
      <h5>PHP多用户记账系统 SQLite版</h5>
      <h2>系统安装向导</h2>
    </div>
    <div class="version">版本: 2019.01.04</div>
  </div>
</div>
EOF;

$html_footer = <<<EOF
<div class="footer">
  <h5>Powered by <font class="blue">ITLU</font><font class="orange"></font></h5>
  <h6>版权所有 2018-2019 &copy; <a href="https://itlu.org" target="_blank">ITLU</a></h6>
</div>
EOF;
require('./include/function.php');
if(!in_array($_GET['step'], array(1,2,3,4,5))){
	$_GET['step'] = 0;
}
switch ($_GET['step']) {
	case 1:
		require('./include/var.php');
		env_check($env_items);
        dirfile_check($dirfile_items);
        function_check($func_items);
		break;
	case 3:
		require('./sqlite_class.php');
		$install_error = '';
        $install_recover = '';
        step3($install_error,$install_recover);
        break;
	case 4:
		
		break;
	case 5:
		$sitepath = strtolower(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
        $sitepath = str_replace('install',"",$sitepath);
        $auto_site_url = strtolower('http://'.$_SERVER['HTTP_HOST'].$sitepath);
		break;
	default:
		# code...
		break;
}

include ("step_{$_GET['step']}.php");

function step3(&$install_error,&$install_recover){
    global $html_title,$html_header,$html_footer;
    if ($_POST['submitform'] != 'submit') return;
    $db_name = $_POST['db_name'];
	$sitename = $_POST['site_name'];
    $admin = $_POST['admin'];
	$email = $_POST['email'];
    $password = $_POST['password'];
    if (!$db_name || !$sitename || !$admin || !$email|| !$password){
        $install_error = '输入不完整，请检查';
    }
    if(strlen($admin) > 15 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^游客|^Guest/is", $admin)) {
        $install_error .= '非法用户名，用户名长度不应当超过 15 个英文字符，且不能包含特殊字符，一般是中文，字母或者数字';
    }
    if ($install_error != '') reutrn;

    require ('step_4.php');
    $sitepath = strtolower(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
    $sitepath = str_replace('install',"",$sitepath);
    $auto_site_url = strtolower('http://'.$_SERVER['HTTP_HOST'].$sitepath);
    write_config($auto_site_url);
    
	$sqlite = new CreateSqlite($db_name);
    $sql = file_get_contents("data/jizhang.sql");
    $sql = str_replace("\r\n", "\n", $sql);
    runquery($sql,$sqlite);
    showjsmessage('初始化数据 ... 成功');

    /**
     * 转码
     */
    $sitename = $_POST['site_name'];
    $username = $_POST['admin'];
    $password = $_POST['password'];
	$email = $_POST['email'];

	function hash_md5($password,$salt){
		$password=md5($password).$salt;
		$password=md5($password);
		return $password;
	}
	$addtime = strtotime("now");
	$salt = md5($username.$addtime.$password);
	$user_pass = hash_md5($password,$salt);
	
    //管理员账号密码
	$user_sql = "INSERT INTO jizhang_user (`uid`,`username`,`password`,`email`,`Isadmin`,`addtime`,`utime`,`salt`) VALUES ('1','$username','$user_pass','$email','1','$addtime','$addtime','$salt')";
	$ret = $sqlite->query($user_sql);
	
	//创建默认分类
	$class_sql = "INSERT INTO jizhang_account_class (`classid`, `classname`, `classtype`, `ufid`) VALUES (1, '默认收入', 1, 1),(2, '默认支出', 2, 1)";
	$ret = $sqlite->query($class_sql);

    //新增一个标识文件，用来屏蔽重新安装
    $fp = @fopen('lock','wb+');
    @fclose($fp);
    exit("<script type=\"text/javascript\">document.getElementById('install_process').innerHTML = '安装完成，下一步...';document.getElementById('install_process').href='index.php?step=5&sitename={$sitename}&username={$username}&password={$password}';</script>");
    exit();
}
//execute sql
function runquery($sql, $sqlite) {
    if(!isset($sql) || empty($sql)) return;
    $ret = array();
    $num = 0;
    foreach(explode(";\n", trim($sql)) as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        foreach($queries as $query) {
            $ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
        }
        $num++;
    }
    unset($sql);
    foreach($ret as $query) {
        $query = trim($query);
        if($query) {
            if(substr($query, 0, 12) == 'CREATE TABLE') {
                $line = explode('`',$query);
                $data_name = $line[1];
                showjsmessage('数据表  '.$data_name.' ... 创建成功');
				$b = $sqlite -> createTable($query);
                unset($line,$data_name);
            } else {
				$b = $sqlite -> createTable($query);
            }
        }
    }
}
//抛出JS信息
function showjsmessage($message) {
    echo '<script type="text/javascript">showmessage(\''.addslashes($message).' \');</script>'."\r\n";
    flush();
    ob_flush();
}
//写入config文件
function write_config($url) {
    extract($GLOBALS, EXTR_SKIP);
    $config = 'data/config.php';
    $configfile = @file_get_contents($config);
    $configfile = trim($configfile);
    $configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
    $db_name = $_POST['db_name'];
    //$db_prefix = $_POST['db_prefix'];
	$sitename = $_POST['site_name'];
    $admin = $_POST['admin'];
    $password = $_POST['password'];
    $configfile = str_replace("===url===",          $url, $configfile);
	$configfile = str_replace("===sitename===",     $sitename, $configfile);
    //$configfile = str_replace("===db_prefix===",    $db_prefix, $configfile);
    $configfile = str_replace("===db_name===",      $db_name, $configfile);
    @file_put_contents('../data/config.php', $configfile);
}