<?php
/*
	WRITTEN BY: Eric Jones
	LAST EDITED: 12/8/2013
	This script is called via AJAX and returns the comments for whatever tutor was indicated.
*/

include "./../common/session_validator.php";
$con = getDatabaseConnection();

$employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".mysqli_real_escape_string($con, $_SESSION['pid'])."'"));

if($employee_info[3] != 'admin') {
	header('HTTP/1.1 401 Unauthorized');
	exit();
}


header("Content-type: application/json");

$result = mysqli_query($con, "SELECT `comments` FROM `tutorComments` WHERE `PID` = '".mysqli_real_escape_string($con, $_GET['tutor'])."'");
if(mysqli_num_rows($result) == 0) {
	header('HTTP/1.1 412 Tutor Not Found!');
	exit();
}
$response = mysqli_fetch_array($result);
print(json_encode($response[0]));
exit();
?>