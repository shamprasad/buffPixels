<?php
#author : Sham Prasad PS <shpa5747@colorado.edu>
#name   : index.php
#purpose: buffPixels
#date   : 2017/10/04
#version: 0.1
session_start();
session_regenerate_id();

isset($_REQUEST["s"]) ? $s=strip_tags($_REQUEST["s"]) : $s = "" ;
isset($_REQUEST["sid"]) ? $sid=strip_tags($_REQUEST["sid"]) : $sid = "" ;
isset($_REQUEST["bid"]) ? $bid=strip_tags($_REQUEST["bid"]) : $bid = "" ;
isset($_REQUEST["cid"]) ? $cid=strip_tags($_REQUEST["cid"]) : $cid = "" ;

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
include_once('hw10-lib.php');
connect($db);
if(!isset($_SESSION['authenticated'])) {
        authenticate($db,$postUser,$postPass);
}
chechAuth();
if(icheck($s) && icheck($sid) && icheck($bid) && icheck($cid)) {
	switch($s) {
	default:
			echo "<table> <tr> <td> <b> <u> All Pictures </b> </u> </td> </tr> </table>\n";
			if($stmt=mysqli_prepare($db, "select pictures.imageName, pictures.caption ,users.userName,users.userId from users,pictures where userId = picUserId")) {
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				$num_rws=mysqli_stmt_num_rows($stmt);
				if($num_rws == 0) {
					echo "
					<form method=post action=index.php?s=0>
					Empty set. Maybe invalid input.
					<input type=\"submit\" value=\"Back\" /></p>
					</form>";
		
				}
				else {
					echo '<div class="row">';
					$col_size=3;
					$col_count=0;
					mysqli_stmt_bind_result($stmt,$imageName,$caption,$userName, $userId);
					while(mysqli_stmt_fetch($stmt)) {
						$imageName=htmlspecialchars($imageName);
						$caption=htmlspecialchars($caption);
						$col_count++;
						$filePath = "temp_image/".$userName."/".$imageName.".jpg";
						echo '<div class="col-md-4">
						<div class="thumbnail">
					        <a href=';
						echo "$filePath";
						echo '><img src=';
						echo "$filePath";
						echo '  alt="Lights" style="width:90%"></a>
						<div class="caption"><p>';
						echo "$imageName by <a href=index.php?s=51&cid=$userId> $userName </a></p>";
						echo '</div>
						<div class="caption"><p>';
						echo "$caption";
						echo '</p>
						</div>
					        </div>
					  	</div>';
						if($col_count % 3 == 0) {
							echo '</div><div class="row">';
						}
					}
				}
			}
				echo "</div>";
			break;
		case 50:
			echo "<table> <tr> <td> <b> <u> List of users: </b> </u> </td> </tr> \n";
			$url="";
			if($stmt=mysqli_prepare($db, "  select userId,userName from users")) {
				mysqli_stmt_execute($stmt);
                                mysqli_stmt_store_result($stmt);
                                $num_rws=mysqli_stmt_num_rows($stmt);
                                if($num_rws == 0) {
                                        echo "
                                        <form method=post action=index.php?s=0>
                                        Empty set. Maybe invalid input.
                                        <input type=\"submit\" value=\"Back\" /></p>
                                        </form>";
                                }
                                else {

					mysqli_stmt_bind_result($stmt,$userId,$userName);
					while(mysqli_stmt_fetch($stmt)) {
						$userName=htmlspecialchars($userName);
						echo "<tr><td><a href=index.php?s=51&cid=$userId> $userName </td></tr> \n";
					}
				}
			}
				echo "</table>";
			break;
	case 51:
			echo "<table> <tr> <td> <b> <u> Pictures of the user </b> </u> </td> </tr> </table>\n";
			if($stmt=mysqli_prepare($db, "select pictures.pictureId, pictures.imageName, pictures.caption ,users.userName from users,pictures where userId = picUserId and userId = $cid")) {
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				$num_rws=mysqli_stmt_num_rows($stmt);
				if($num_rws == 0) {
					echo "
					<form method=post action=index.php?s=0>
					Empty set. Maybe invalid input.
					<input type=\"submit\" value=\"Back\" /></p>
					</form>";
		
				}
				else {
					echo '<div class="row">';
                                        $col_size=3;
                                        $col_count=0;
					mysqli_stmt_bind_result($stmt,$pictureId, $imageName,$caption,$userName);
					while(mysqli_stmt_fetch($stmt)) {
						$imageName=htmlspecialchars($imageName);
						$caption=htmlspecialchars($caption);
						$col_count++;
						$filePath = "temp_image/".$userName."/".$imageName.".jpg";
						echo '<div class="col-md-4">
                                                <div class="thumbnail">
                                                <a href=';
                                                echo "$filePath";
                                                echo '><img src=';
                                                echo "$filePath";
                                                echo '  alt="Lights" style="width:90%"></a>
                                                <div class="caption"><p>';
                                                echo "$imageName by  $userName </p>";
						if($_SESSION['userName'] == $userName)
							echo "<a href=add.php?s=6&bid=$pictureId&imageName=$imageName> Delete </a>";
						echo '</div>
                                                <div class="caption"><p>';
                                                echo "$caption";
                                                echo '</p>
                                                </div>
                                                </div>
                                                </div>';
                                                if($col_count % 3 == 0) {
                                                        echo '</div><div class="row">';
                                                }
					}
				}
			}
				echo "</div>";
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
echo '<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>';
echo "</body></html>";
?>
