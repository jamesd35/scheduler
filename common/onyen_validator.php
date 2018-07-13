<?php
error_log("entering onyen_validator.php", 3, "/var/log/php_errors.log");
/*
	WRITTEN BY: Eric Jones
	LAST MODIFIED: 12/5/2014 by James Ding
	This file is called when the UNC onyen authenticator returns.  It compares the pid sent to the server via POST with the pid given by the UNC authenticator.  If they match it checks
	if the pid is listed in the employee database, and if so it starts the session and generates a security cookie for the user before forwarding them to the appropriate landing page.
*/

	$con=mysqli_connect("127.7.132.2","adminBsuSUg4","MPmBwRCaX5h2","tutorScheduler");
?>


<!DOCTYPE html>

<html>
<head>
	<script type="text/javascript" src="jquery-1.10.2.min.js"></script>
	<script type='text/javascript' src='onyen_validator.js'></script>
</head>
<body>
<form method = 'POST'>

<?php
	$employee_info = array();
	
	
	//This will be a link to the validation text file provided by the authenticator.
	$vfyvalidate = "https://onyen.unc.edu/cgi-bin/unc_id/authenticator.pl/" . $_POST['vfykey'];

	$file = file_get_contents($vfyvalidate);
	$vfypid = 1;
	
	$lines = explode("\n", $file);
	foreach($lines as $row => $data) {
		if(substr($data, 0, 4) == 'pid:') {
			$vfypid = substr($data, 5, 9);
		}
	}
	
	if($vfypid == $_POST['pid']) {	//meaning the validation file matches the PID given by post, thus the login is valid
		$result = mysqli_query($con, "SELECT * FROM employeeInfo WHERE `PID` = ".mysqli_real_escape_string($con, $_POST['pid']));
		if($result) {
			$employee_info = mysqli_fetch_array($result);
		}
		if($employee_info[0] != null) {
			//the employee was found in the database
			session_start();
			
			//generate an authorization cookie and setup the session data
			$_SESSION['pid'] = $vfypid;
			$_SESSION['authsalt'] = substr(md5(rand()), 0, 32);
			$_SESSION['remote_address'] = $_SERVER['REMOTE_ADDR'];
			
			$expire = time() + 3600; //idle time is 1 hour
			$cookie_data = md5($_SESSION['pid'] . $_SESSION['remote_address'] . $_SERVER['REMOTE_ADDR'] . $_SESSION['authsalt']);
			
			setcookie('TutorSchedulerAuth', $cookie_data, $expire, '/', $_SERVER['SERVER_NAME'], 1);
			
			echo "<span id = 'form_target' hidden>".trim($employee_info[3])."</span>\n";
			
		}else {
			//meaning they aren't listed in the info database, and we'll redirect them back to login
			echo "<span id = 'form_target' hidden>no match</span>\n";
		}
	}else {
		//else the pid they posted didn't match the pid from unc, so it's a bunk attempt. Possibly expired.
		
		echo "<span id = 'form_target' hidden>illegal attempt</span>\n";

	}

?>

</form>
</body>
</html>

