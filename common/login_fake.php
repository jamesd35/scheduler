<?php

	$dbhost = getenv("MYSQL_SERVICE_HOST");
	$dbport = getenv("MYSQL_SERVICE_PORT");
	$dbuser = getenv("databaseuser");
	$dbpwd = getenv("databasepassword");
	$dbname = getenv("databasename");
	$con = new mysqli($dbhost, $dbuser, $dbpwd, $dbname);

//	$con=mysqli_connect("127.0.0.1","userERA","5THnhIwCoyCMtuA0","tutorScheduler");

	$result = mysqli_query($con, "SELECT * FROM `employeeInfo`");
	$admin_url = "http://$_SERVER[HTTP_HOST]/admin/admin.php";
	$tutor_url = "http://$_SERVER[HTTP_HOST]/tutor/tutor.php";
	
	if($_POST['pid'] != null) {
			session_start();
			
			//generate an authorization cookie and setup the session data
			$_SESSION['pid'] = $_POST['pid'];
			$_SESSION['authsalt'] = substr(md5(rand()), 0, 32);
			$_SESSION['remote_address'] = $_SERVER['REMOTE_ADDR'];
			
			$expire = time() + 450;
			$cookie_data = md5($_SESSION['pid'] . $_SESSION['remote_address'] . $_SERVER['REMOTE_ADDR'] . $_SESSION['authsalt']);
			
			setcookie('TutorSchedulerAuth', $cookie_data, $expire, '/');
			
			$result = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".$_POST['pid']."'"));
						
			switch(trim($result['type'])) {
				case 'admin':
					echo "<script>location.href='$admin_url'</script>";
					exit();
				case 'grad':
				case 'ugrad':
					echo "<script>location.href='$tutor_url'</script>";
					exit();
			}
	}
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Login(fake)</title>
</head>
<body>
	Select an employee to log in as:
	<form id='selection' method="post" action="login_fake.php">
		<select name="pid">
			<?php
				while($row = mysqli_fetch_array($result)) {
					echo "<option value=".$row[0].">".$row[1]." ".$row[2].", ".$row[3]."</option><br>";
				}
			?>
		</select>
		<br>
		<button type='submit'>Submit</button>
	</form>

</body>
</html>
