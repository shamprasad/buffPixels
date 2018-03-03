<?php
function connect(&$db){
	$mycnf="/etc/buffPixel.conf";
	if (!file_exists($mycnf)) { 
		echo "ERROR: DB Config file not found: $mycnf";
		exit;
	}
	$mysql_ini_array=parse_ini_file($mycnf);
	$db_host=$mysql_ini_array["host"];
	$db_user=$mysql_ini_array["user"];
	$db_pass=$mysql_ini_array["pass"];
	$db_port=$mysql_ini_array["port"];
	$db_name=$mysql_ini_array["dbName"];
	$db=mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port); 
	if (!$db) { 
		print "Error connecting to DB: " .mysqli_connect_error();
		exit; 
	}
}
isset($_REQUEST["s"]) ? $s=strip_tags($_REQUEST["s"]) : $s = "" ;
isset($_REQUEST["sid"]) ? $sid=strip_tags($_REQUEST["sid"]) : $sid = "" ;
isset($_REQUEST["bid"]) ? $bid=strip_tags($_REQUEST["bid"]) : $bid = "" ;
isset($_REQUEST["cid"]) ? $cid=strip_tags($_REQUEST["cid"]) : $cid = "" ;
isset($_REQUEST["imageName"]) ? $imageName=strip_tags($_REQUEST["imageName"]) : $imageName = "" ;
isset($_REQUEST["caption"]) ? $caption=strip_tags($_REQUEST["caption"]) : $caption = "" ;
isset($_REQUEST["characterSide"]) ? $characterSide=strip_tags($_REQUEST["characterSide"]) : $characterSide = "" ;
isset($_REQUEST["characterPicture"]) ? $characterPicture=strip_tags($_REQUEST["characterPicture"]) : $characterPicture = "" ;
isset($_REQUEST["postUser"]) ? $postUser=strip_tags($_REQUEST["postUser"]) : $postUser = "" ;
isset($_REQUEST["postPass"]) ? $postPass=strip_tags($_REQUEST["postPass"]) : $postPass = "" ;
isset($_REQUEST["newUserName"]) ? $newUserName=strip_tags($_REQUEST["newUserName"]) : $newUserName = "" ;
isset($_REQUEST["newUserPass"]) ? $newUserPass=strip_tags($_REQUEST["newUserPass"]) : $newUserPass = "" ;
isset($_REQUEST["newUserEmail"]) ? $newUserEmail=strip_tags($_REQUEST["newUserEmail"]) : $newUserEmail = "" ;
isset($_REQUEST["myimage"]) ? $myimage=$_REQUEST["myimage"] : $myimage = "" ;
function icheck($input) {
        $input=htmlspecialchars($input);
        if($input!= "" && !is_numeric($input)) {
                return false;
        }
        else {
                return true;
        }
}
function authenticate($db,$postUser,$postPass) {
        $remote_ip = $_SERVER['REMOTE_ADDR'];
	$failed_count = 0;
	if($stmt=mysqli_prepare($db, "select  loginid from login where ip = ? and action=\"fail\" and  date between DATE_SUB(NOW(), INTERVAL 1 HOUR) and NOW()")) {
		mysqli_stmt_bind_param($stmt, "s", $remote_ip);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);
		$failed_count=mysqli_stmt_num_rows($stmt);
	}
	if ($failed_count >= 5)
	{
		echo "Error: More than 5 incorrect logins. Try after an hour";
		exit;
	}
        if ($stmt = mysqli_prepare($db, " select userid, salt, password from users where username = ?")) {
                mysqli_stmt_bind_param($stmt, "s", $postUser);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $num_rws=mysqli_stmt_num_rows($stmt);
                if($num_rws == 1) {
                        mysqli_stmt_bind_result($stmt,$userId, $storedSalt, $storedPass);
                        mysqli_stmt_fetch($stmt);
                        $userId = htmlspecialchars($userId);
                        $storedSalt = htmlspecialchars($storedSalt);
                        $storedPass = htmlspecialchars($storedPass);
                        if($storedPass == hash('sha256', $postPass. $storedSalt)) {
				session_regenerate_id();
                                $_SESSION['authenticated'] = "yes";
                                $_SESSION['userName'] = $postUser;
                                $_SESSION['userid'] = $userId;
				$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['created'] = time();
				$_SESSION['HTTP_USER_AGENT']=md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
			if ($stmt2 = mysqli_prepare($db, "insert into login set loginId='', ip = ?, user = ?,date = now(), action=\"pass\" ")) {
					mysqli_stmt_bind_param($stmt2, "ss", $remote_ip, $postUser );	
					mysqli_stmt_execute($stmt2);
	                                mysqli_stmt_close($stmt2);
				}
					
                        }
                        else {
				 if ($stmt2 = mysqli_prepare($db, "insert into login set loginId='', ip = ?, user = ?,date = now(), action=\"fail\" ")) {
                                        mysqli_stmt_bind_param($stmt2, "ss", $remote_ip, $postUser );
                                        mysqli_stmt_execute($stmt2);
                                        mysqli_stmt_close($stmt2);
					error_log("**ERROR**: Tolkien app has failed login from: ".$_SERVER['REMOTE_ADDR'],0);
                                }
                                header("Location: login.php");
                                exit;
                        }
                } else {
				if ($stmt2 = mysqli_prepare($db, "insert into login set loginId='', ip = ?, user = ?,date = now(), action=\"fail\" ")) {
                                        mysqli_stmt_bind_param($stmt2, "ss", $remote_ip, $postUser );
                                        mysqli_stmt_execute($stmt2);
                                        mysqli_stmt_close($stmt2);
					error_log("**ERROR**: Tolkien app has failed login from: ".$_SERVER['REMOTE_ADDR'],0);
                                }
                        header("Location: login.php");
                        exit;
                }
        }
}

function logout() {
	session_unset();
	session_destroy();
	header("Location: login.php");
	exit;
}
function chechAuth() {
	if(isset($_SESSION['HTTP_USER_AGENT'])) {
		if($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['SERVER_ADDR'] . $_SERVER['HTTP_USER_AGENT'])) {
			logout();
		}
	} else {
		logout();
	}

	if(isset($_SESSION['ip'])) {
		if($_SESSION['ip'] != $_SERVER['REMOTE_ADDR']) {
			logout();
		}
	} else {
		logout();
	}

	if(isset($_SESSION['created'])) {
		if((time() - $_SESSION['created']) > 1800) {
			logout();
		}
	} else {
		logout();
	}

	if("POST" == $_SERVER['REQUEST_METHOD']) {
		if(isset($_SERVER['HTTP_ORIGIN'])) {
			if($_SERVER['HTTP_ORIGIN'] !=  "https://100.66.1.9") {
				logout();
			}
		} else {
			logout();
		}
	}

}
?>
