<?php
#author : Sham Prasad PS <shpa5747@colorado.edu>
#name   : add.php
#purpose: A script to display tolkien database
#date   : 2017/10/04
#version: 0.1
session_start();
session_regenerate_id();

include_once('hw10-lib.php');
echo "

        <html>
        <head><title>buffPixels</title>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">
        <link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css\" integrity=\"sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb\" crossorigin=\"anonymous\">
        </head>
        <body>
        <center> ";
        echo '<nav class="navbar navbar-expand-lg navbar-light bg-light">
          <a class="navbar-brand" href=index.php>buffPixels</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
              <li class="nav-item active">
                <a class="nav-link" href=index.php?s=50>Browse photos by users <span class="sr-only">(current)</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href=add.php>Upload a picture</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href=add.php?s=99>Logout</a>
              </li>
            </ul>
          </div>
        </nav>';
echo "<hr>
";
connect($db);
if(!isset($_SESSION['authenticated'])) {
	authenticate($db,$postUser,$postPass);
} 
chechAuth();
if(icheck($s) && icheck($sid) && icheck($bid) && icheck($cid)) {
	switch($s) {
		case 0:
		default: 
			echo "<form method=post action=add.php enctype=\"multipart/form-data\"> 
				<table> <tr> <td colspan=2> Upload a new image </td> </tr>
				<tr> <td> Image Name </td> <td> <input type=text name=imageName value=\"\"> </td> </tr>
				<tr> <td> Caption </td> <td> <input type=text name=caption value=\"\"> </td> </tr>
				<tr> <td><input type=\"file\" name=\"myimage\"> </td></tr>
				<tr> <td colspan=2> <input type=hidden name=s value=5> <input type=submit name=submit value=submit> </td></tr>
				</table> 
				</form>";
		break;
		case 5:
			$imageName = mysqli_real_escape_string($db,$imageName);
			$target = 'temp_image/'.$_SESSION['userName']."/".$imageName.".jpg";
			move_uploaded_file( $_FILES['myimage']['tmp_name'], $target);
			$caption = mysqli_real_escape_string($db,$caption);	
			if ($stmt = mysqli_prepare($db, " insert into pictures set pictureId='',imageName= ?, caption= ?,  picUserId= ?"))	{
				mysqli_stmt_bind_param($stmt, "ssi", $imageName,$caption,$_SESSION['userid']);//get user id
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
			}
			
			echo "<form method=post action=add.php> 
			<table> <tr> <td colspan=2> You have added image successfully!! </td> </tr>
			<tr> <td colspan=2>
			 <input type=hidden name=s value=0>
			 <input type=submit name=submit value=\"Go Back\"> </td></tr>
			</table> 
			</form>";
			break;
		case 6:
			$bid = htmlspecialchars($bid);
			$imageName=htmlspecialchars($imageName);
			error_log($bid,0);
			if ($stmt = mysqli_prepare($db, "delete from pictures where pictureId=?"))	{
				mysqli_stmt_bind_param($stmt, "i",$bid);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				$filePath = "temp_image/".$_SESSION['userName']."/".$imageName.".jpg";
				unlink($filePath);
			}
			break;
		case 90:
			if($_SESSION['userid'] == 1){
				$newUserName = htmlspecialchars($newUserName);
				$newUserPass = htmlspecialchars($newUserPass);
				$newUserEmail = htmlspecialchars($newUserEmail);
				if($newUserName == "" || $newUserPass == "" || $newUserEmail == "" ) {
					echo "<form method=post action=add.php> 
					<table> <tr> <td colspan=2> Add Users to Tolkein app </td> </tr>
					<tr> <td> User Name </td> <td> <input type=text name=newUserName value=\"\"> </td> </tr>
					<tr> <td> Password </td> <td> <input type=password name=newUserPass value=\"\"> </td> </tr>
					<tr> <td> Email </td> <td> <input type=text name=newUserEmail value=\"\"> </td> </tr>
					<tr> <td colspan=2> <input type=hidden name=s value=90> <input type=submit name=submit value=submit> </td></tr>
					</table> 
					</form>";
				
				} elseif($newUserName != "" && $newUserPass != "" && $newUserEmail != "" ) {
					$newUserName = mysqli_real_escape_string($db,$newUserName);
					$newUserPass = mysqli_real_escape_string($db,$newUserPass);
					$newUserEmail = mysqli_real_escape_string($db,$newUserEmail);
					$saltHash = hash('sha256', $newUserName);
					$newUserPass = hash('sha256', $newUserPass.$saltHash);
					if($stmt=mysqli_prepare($db, "insert into users set userid = '', username = ? , password = ?, salt = ?, email = ?")) {
						mysqli_stmt_bind_param($stmt, "ssss",$newUserName, $newUserPass, $saltHash, $newUserEmail);
						mysqli_stmt_execute($stmt);
						mysqli_stmt_close($stmt);
					}
					mkdir("temp_image/".$newUserName);
					header("Location: add.php?s=0");
				}
			} else {
				echo " Error: you are not the admin user";
			}
			break;
		case 91:
			if($_SESSION['userid'] == 1){
				echo "<table> <tr> <td>  <b> List of users: </b> </td> </tr> \n";
				if($stmt=mysqli_prepare($db, "select username from users" )) {
					mysqli_stmt_bind_result($stmt, $userName);
					mysqli_stmt_execute($stmt);
					while(mysqli_stmt_fetch($stmt)) {
						$userName = htmlspecialchars($userName);
						echo " <tr> <td> $userName </td> <td> <a href=add.php?s=92&newUserName=$userName> Update password </td></tr>\n";
					}
				}
				echo "</table>";
			} else {
				echo " Error: you are not the admin user";
			}
			break;
		case 92:
			if($_SESSION['userid'] == 1){
				$newUserName = htmlspecialchars($newUserName);
				$newUserPass = htmlspecialchars($newUserPass);
				if($newUserPass == "") {
					echo "<form method=post action=add.php> 
					<table> <tr> <td colspan=2> Update password for $newUserName </td> </tr>
					<tr> <td> Password </td> <td> <input type=password name=newUserPass value=\"\"> </td> </tr>
					<tr> <td colspan=2>  <input type=hidden name=newUserName value=$newUserName> <input type=hidden name=s value=92> <input type=submit name=submit value=submit> </td></tr>
					</table> 
					</form>";
				} else {
					$newUserName = mysqli_real_escape_string($db,$newUserName);
                                        $newUserPass = mysqli_real_escape_string($db,$newUserPass);
					$saltHash = hash('sha256', $newUserName);
	                                $newUserPass = hash('sha256', $newUserPass.$saltHash);
					if($stmt=mysqli_prepare($db, " update users set password= ?,salt =? where username=? " )) {	
						mysqli_stmt_bind_param($stmt, "sss", $newUserPass, $saltHash, $newUserName);
                                                mysqli_stmt_execute($stmt);
						mysqli_stmt_close($stmt);	
					}
					header("Location: add.php?s=91");			
				}
			} else {
				echo " Error: you are not the admin user";
			}
			break;
		case 93:
			if($_SESSION['userid'] == 1){
                                echo "<table> <tr> <td>  <b> List of failed logins: </b> </td> </tr> \n";
                                if($stmt=mysqli_prepare($db, "select ip, user , date from login where action=\"fail\"" )) {
                                        mysqli_stmt_bind_result($stmt, $ip, $username,$date);
                                        mysqli_stmt_execute($stmt);
                                        while(mysqli_stmt_fetch($stmt)) {
                                                $ip = htmlspecialchars($ip);
                                                $username = htmlspecialchars($username);
                                                $date = htmlspecialchars($date);
                                                echo " <tr> <td> $ip </td> <td> $username </td><td> $date </td> </tr>\n";
                                        }
                                }
                                echo "</table>";
                        } else {
                                echo " Error: you are not the admin user";
                        }				
			break;
			
		case 99:
			logout();
			break;
	}
}
else {
	echo "
	<form method=post action=index.php?s=0>
	Invalid input
	<input type=\"submit\" value=\"Back\" /></p>
	</form>";
}
if($_SESSION['userid'] == 1) {
	echo "	<br> <a href=add.php?s=91> List users </a>  ";
	echo "  <br> <a href=add.php?s=90> Add user </a> ";
	echo "  <br> <a href=add.php?s=93> View failed logins </a> ";
}
echo " <br> <a href=add.php?s=99> Logout </a> ";
echo '<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>';
	echo "</body></html>";
?>
