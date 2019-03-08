<?php
header('Content-type:text/json;charset=utf-8');
include_once("data/config.php");
include_once("inc/function.php");
loginchk($userid);
$gotourl = "";
$success = "0";
$getaction = get("action");
if($getaction=="getclassify"){	
	header('Content-type:text/html;charset=utf-8');
	$classtype = get("classtype");
	$classid = get("classid");
	$sql = "select * from ".TABLE."account_class where ufid='$userid' and classtype='$classtype'";
	$results = $conn->query($sql);
    while($row = $results -> fetchArray()){
		if($row["classid"] <> $classid){
			echo "<option value='".$row["classid"]."'>".$row["classname"]."</option>";
		}
	}
}
if($getaction=="addbank"){
	$bankname = post("bankname");
	$bankaccount = post("bankaccount");
	$balancemoney = post("balancemoney","0");
	if(empty($bankname) or empty($bankaccount)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($balancemoney)){
		$error_code = "金额非法！";
	}else{
		$addtime = strtotime("now");
		$sql = "insert into ".TABLE."bank (bankname, bankaccount, balancemoney, addtime, updatetime, userid) values ('$bankname', '$bankaccount', '$balancemoney', '$addtime', '$addtime', '$userid')";
		$result = $conn->exec($sql);
		if($result){
			$success = "1";
			$error_code = "保存成功！";
			$gotourl = "bank.php";						
		}else{
			$error_code = "保存失败！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="modifybank"){
	$bankname = post("bankname");
	$bankaccount = post("bankaccount");
	$bankid = post("bankid");
	$balancemoney = post("balancemoney","0");
	if(empty($bankname) or empty($bankaccount)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($balancemoney)){
		$error_code = "金额非法！";
	}else{
		$now = strtotime("now");
		$sql = "update ".TABLE."bank set bankname='".$bankname."',bankaccount='".$bankaccount."',balancemoney='".$balancemoney."' ,updatetime='".$now."' where bankid=".$bankid;
		$result = $conn->exec($sql);
		if ($result) {
			$success = "1";
			$error_code = "保存成功！";
			$gotourl = "bank.php";
		} else {
			$error_code = "保存失败！";
		}	
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deletebank"){
	header('Content-type:text/html;charset=utf-8');
	$bankid = get("bankid");
	if(empty($bankid) || !is_numeric($bankid)){
		$error_code = "缺少参数或参数非法！";
	}else{
		$sql = "select acid from ".TABLE."account where bankid='$bankid' and jiid='$userid'";
		$results = $conn->query($sql);
		if ($row = $results -> fetchArray()) {
			$error_code = "该银行卡有关联数据，不能删除！";
		} else {
			$del_sql = "delete from ".TABLE."bank where bankid=".$bankid;
			$del_result = $conn->exec($del_sql);
			if($del_result){
				$error_code = "删除成功！";
			}else{
				$error_code = "删除失败！";
			}			
		}
	}				
	echo $error_code;
}
if($getaction=="addrecord"){
	$classid = post("classid");
	$money = post("money");
	$zhifu = post("zhifu");
	$remark = post("remark");
	$bankid = post("bankid");
	$addtime = strtotime(post("time"));
	if(empty($classid) or empty($money) or empty($zhifu)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($money)){
		$error_code = "金额非法！";
	}else{
		$sql = "insert into ".TABLE."account (acmoney, acclassid, actime, acremark, jiid, zhifu, bankid) values ('$money', '$classid', '$addtime', '$remark', '$userid', '$zhifu', '$bankid')";
		$query = $conn->exec($sql);
		if($query){
			if($bankid>0){money_int_out($bankid,$money,$zhifu);}
			$success = "1";
			$error_code = "保存成功！";
			if($zhifu=="1"){
				$gotourl = "add.php?action=income";
			}else{
				$gotourl = "add.php?action=pay";
			}			
		}else{
			$error_code = "保存失败！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="saverecord"){
	$id = post("edit-id");
	$money = post("edit-money");
	$remark = post("edit-remark");
	$old_bankid = post("old-bank-id");
	$new_bankid = post("edit-bankid");
	$zhifu = post("edit-zhifu");
	$addtime = strtotime(post("edit-time"));
	if(empty($id) or empty($money)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($money)){
		$error_code = "金额非法！";
	}else{
		$sql = "update ".TABLE."account set acmoney='".$money."',acremark='".$remark."',actime='".$addtime."',bankid='".$new_bankid."' where acid='".$id."' and jiid='".$userid."'";
		$result = $conn->exec($sql);
		if($result){
			if($zhifu==2){
				if($old_bankid<>$new_bankid && $old_bankid>0){money_int_out($old_bankid,$money,"1");}
				if($old_bankid<>$new_bankid && $new_bankid>0){money_int_out($new_bankid,$money,"2");}
			}else{
				if($old_bankid<>$new_bankid && $old_bankid>0){money_int_out($old_bankid,$money,"2");}
				if($old_bankid<>$new_bankid && $new_bankid>0){money_int_out($new_bankid,$money,"1");}
			}
			$success = "1";
			$error_code = "保存成功！";
		} else {
			$error_code = "保存失败！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deleterecord"){
	header('Content-type:text/html;charset=utf-8');
	$get_id = get("id");
	if(empty($get_id) || !is_numeric($get_id)){
		$error_code = "缺少参数或参数非法！";
	}else{
		$sql = "select bankid,zhifu,acmoney from ".TABLE."account where acid='$get_id' and jiid='$userid'";
		$results = $conn->query($sql);
		if($row = $results -> fetchArray()){
			if($row["bankid"]>0){
				$zhifu = "2";
				if($row["zhifu"]=="2"){$zhifu = "1";}
				money_int_out($row["bankid"],$row["acmoney"],$zhifu);
			}
		}
		$del_sql = "delete from ".TABLE."account where acid='$get_id' and jiid='$userid'";
		$del_result = $conn->exec($del_sql);
		if($del_result){
			$error_code = "删除成功！";
		}else{
			$error_code = "删除失败！";
		}
	}				
	echo $error_code;
}
if($getaction=="deleterecordAll"){
	if(isset($_POST["del_id"]) && $_POST["del_id"] != ""){
		$del_id = implode(",",$_POST['del_id']);
		$del_sql = "delete from ".TABLE."account where jiid='$userid' and acid in ($del_id)";
		$del_result = $conn->exec($del_sql);
		if($del_result){
			$success = "1";
			$error_code = "删除成功！";
		}else{
			$error_code = "删除失败！";
		}	
	}else{
		$error_code = "参数不正确！";
	}			
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="changeclassify"){
	$classid = post("classid");
	$newclassid = post("newclassid");
	if(empty($newclassid) or $newclassid=="0" or empty($classid)){
		$error_code = "缺少参数或者未选择目标分类！";
	}else{
		$sql = "update ".TABLE."account set acclassid='$newclassid' where acclassid='$classid' and jiid='$userid'";
		$result = $conn->exec($sql);
		if ($result) {
			$success = "1";
			$error_code = "更改分类成功！";
			$gotourl = "classify.php";
		} else {
			$error_code = "保存失败！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deleteclassify"){
	header('Content-type:text/html;charset=utf-8');
	$classid = get("classid");
	if(empty($classid)){
		$error_code = "缺少参数！";
	}else{
		$sql = "select acid from ".TABLE."account where acclassid='$classid' and jiid='$userid'";
		$results = $conn->query($sql);
		if($row = $results -> fetchArray()){
			$error_code = "在此分类下有账目，请将账目转移到其他分类";
		} else {
			$sql = "delete from ".TABLE."account_class where classid=".$classid;
			$del_result = $conn->exec($sql);
			if($del_result){
				$error_code = "分类删除成功！";
			}else{
				$error_code = "删除失败！";
			}			
		}
	}
	echo $error_code;
}
if($getaction=="addclassify"){
	$classname = post("classname");
	$classtype = post("classtype");
	if(empty($classname)){
		$error_code = "分类名称不能为空！";
	}elseif(strlen($classname)>18){
		$error_code = "分类名称不能大于6个字！";
	}else{
		$sql = "select * from ".TABLE."account_class where classname='$classname' and classtype='$classtype' and ufid='$userid'";
		$results = $conn->query($sql);
		if($row = $results -> fetchArray()){
			$error_code = "该名称的分类已经存在！";
		}
		else {
			$sql = "insert into ".TABLE."account_class (classname, classtype, ufid) values ('$classname', '$classtype',$userid)";
			$add_result = $conn->exec($sql);
			if ($add_result) {
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "classify.php";
			} else {
				$error_code = "保存失败！";
			}
		}	
		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="modifyclassify"){
	$classname = post("classname");
	if(empty($classname)){
		$error_code = "分类名称不能为空！";
	}elseif(strlen($classname)>18){
		$error_code = "分类名称不能大于6个字！";
	}else{
		$sql = "select * from ".TABLE."account_class where classname='$classname' and classid<>'$_POST[classid]' and ufid='$userid'";
		$results = $conn->query($sql);
		if($row = $results -> fetchArray()){
			$error_code = "该名称的分类已经存在！";
		}else{
			$sql = "UPDATE ".TABLE."account_class set classname='$classname' , classtype=$_POST[classtype] where ufid='$userid' and classid=".$_POST["classid"];
			$update_result = $conn->exec($sql);
			if($update_result){
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "classify.php";
			}else{
				$error_code = "保存失败！";
			}
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="changeuser"){
	header('Content-type:text/html;charset=utf-8');
	$type = get("m");
	$uid = get("uid");
	if(empty($type) or empty($uid)){
		$error_code = "参数不完整！";
	}else{
		if($type=="noallow"){
			$u_sql = "Isallow=1";
			$itlu_word = "禁用";
		}else{
			$u_sql = "Isallow=0";
			$itlu_word = "启用";
		}
		$update_sql = "update ".TABLE."user set ".$u_sql." where uid='$uid'";
		$update_result = $conn->exec($update_sql);
		if($update_result){
			$error_code = $itlu_word."成功！";
		}else{
			$error_code = "操作失败！";
		}
	}
	echo $error_code;
}
if($getaction=="updateuser"){
	$password = post_pass("password");
	$newpassword = post_pass("newpassword");
	$email = post("email");
	if(empty($password)){
		$error_code = "密码不能为空！";
	}elseif(strlen($password)<6){
		$error_code = "密码不能小于6个字符！";
	}elseif((!empty($email)) && (checkemail($email) == false)){
		$error_code = "邮箱格式错误！";
	}elseif(!empty($newpassword) && strlen($newpassword)<6){
		$error_code = "新密码不能小于6个字符！";
	}else{
		$sql = "SELECT * FROM ".TABLE."user where uid='$userid'";
		$results = $conn->query($sql);
		$row = $results -> fetchArray();
		$db_salt = $row["salt"];
		if($row["password"] == hash_md5($password,$db_salt)){			
			$update_time = strtotime("now");
			$u_sql = "utime='$update_time'";
			if(!empty($email)){
				$u_sql = $u_sql.",email='$email'";
			}
			if(!empty($newpassword)){				
				$salt = md5($row["username"].$update_time.$newpassword);
				$user_pass = hash_md5($newpassword,$salt);
				$u_sql = $u_sql.",password='$user_pass',salt='$salt'";
			}
			$update_sql = "update ".TABLE."user set ".$u_sql." where uid='$userid'";
			$update_result = $conn->exec($update_sql);
			if($update_result){
				$success = "1";
				$userinfo_update = array("userid"=>"$userid","username"=>"$row[username]","useremail"=>"$email","regtime"=>"$row[addtime]","updatetime"=>"$update_time","isadmin"=>"$row[Isadmin]");
				$userinfo = AES::encrypt($userinfo_update, $sys_key);
				setcookie("userinfo", $userinfo, time()+86400);
				$error_code = "信息修改成功！";
				$gotourl = "users.php";
			}else{
				$error_code = "出错啦，写入数据库时出错！";
			}
		}else{
			$error_code = "旧密码错误！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="export"){
	header('Content-type:text/html;charset=utf-8');
	$sql = "select a.zhifu,a.acmoney,a.actime,a.acremark,a.bankid,b.classname from ".TABLE."account as a INNER JOIN ".TABLE."account_class as b ON b.classid=a.acclassid and a.jiid='$userid'";
	$results = $conn->query($sql);
    $str = "收支,分类,账户,金额,时间,备注\n";
    $str = iconv('utf-8','gb2312',$str);
    while ($row = $results -> fetchArray()) {
        $classname = iconv('utf-8','gb2312',$row['classname']);
        if ($row['zhifu'] == 1) {
            $shouzhi = iconv('utf-8','gb2312',"收入");
        } else {
            $shouzhi = iconv('utf-8','gb2312',"支出");
        }
        $money = $row['acmoney'];
        $time = date("Y-m-d H:i",$row['actime']);		
		if($row['acremark']<>""){
			$remark = iconv('utf-8','gb2312',$row['acremark']);
		}else{
			$remark = "";
		}
		$bankname = bankname($row['bankid'],$userid,"默认账户");
		$bankname = iconv('utf-8','gb2312',$bankname);
        $str .= $shouzhi.",".$classname.",".$bankname.",".$money.",".$time.",".$remark."\n";
    }
    $filename = date('YmdHis').'.csv';
    export_csv($filename,$str);
}
if($getaction=='import') {
	header('Content-type:text/html;charset=utf-8');
    if(empty($_FILES['file']['tmp_name'])){alertgourl("请选择文件！","int_out.php");}	
    $filename = $_FILES['file']['tmp_name'];
	$filetype = (pathinfo($_FILES['file']['name']));
	if($filetype['extension'] != "csv"){
		alertgourl("2222文件格式不对，请重新选择！","int_out.php");
	}
    $handle = fopen($filename, 'r');
    $result = input_csv($handle);
    $len_result = count($result);
    if ($len_result <= 1){alertgourl("你的文件没有任何数据！","int_out.php");}
	$data_values = "";
	$insert_count = 0;
    for ($i = 1; $i < $len_result; $i++) {
		$money = $result[$i][3];
		if($money<=0){continue;}
		$bankid = 0; //涉及账户之间加减,默认仅支持0
        $shouzhi = iconv('gb2312', 'utf-8', $result[$i][0]);
        if ($shouzhi == "收入") {
            $shouzhi = "1";
        }else{
            $shouzhi = "2";
        }
		$classify = iconv('gb2312', 'utf-8', $result[$i][1]);
        $sql = "select classid from ".TABLE."account_class where classname='$classify' and ufid='$userid'";
		$results = $conn->query($sql);
		$row = $results -> fetchArray();
        if ($row["classid"]){
			$acclassid = $row["classid"];
        }else{
            $add_sql = "insert into ".TABLE."account_class (classname, classtype, ufid) values ('$classify', '$shouzhi', '$userid')";
			$add_results = $conn->query($add_sql);
			$acclassid = $add_results -> lastInsertRowID();
        }        
        $addtime = strtotime($result[$i][4]);
        $remark = iconv('gb2312', 'utf-8', $result[$i][5]);
		$insert_count++;
        $data_values .= "('$money','$acclassid','$addtime','$remark','$userid','$shouzhi','$bankid'),";
    }
    $data_values = substr($data_values,0,-1);
    fclose($handle);
	$insert_sql = "insert into ".TABLE."account (acmoney,acclassid,actime,acremark,jiid,zhifu,bankid) values $data_values";
	$insert_result = $conn->exec($insert_sql);
    if($insert_result){
		$word = "导入成功！导入".$insert_count."条";
		alertgourl($word,"int_out.php");
    }else{
		alertgourl("导入失败，请检查文件格式！","int_out.php");
    }
}
if($getaction=='updatesystem'){
	$filepath = "data/config.php";
	$info = vita_get_url_content($filepath);
	foreach($_POST as $k=>$v){
        $info=preg_replace("/define\(\"{$k}\",\".*?\"\)/","define(\"{$k}\",\"{$v}\")",$info);
    }
    file_put_contents($filepath,$info);
	$success = "1";
	$error_code = "信息修改成功！";
	$gotourl = "users.php";
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=='updatesmtp'){
	$filepath = "inc/smtp_config.php";
	$info = vita_get_url_content($filepath);
	foreach($_POST as $k=>$v){
        $info=preg_replace("/define\(\"{$k}\",\".*?\"\)/","define(\"{$k}\",\"{$v}\")",$info);
    }
    file_put_contents($filepath,$info);
	$success = "1";
	$error_code = "信息修改成功！";
	$gotourl = "users.php";
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
?>