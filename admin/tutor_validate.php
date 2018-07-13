<?php
/*
	WRITTEN BY: Ibrahim Hawari
	LAST EDITED: 11/25/2014
	This script checks what was posted and if the user just submitted new hours, it updates the database with the new requests and prints the appropriate message. When the page loads it
	prints a table of the tutor's current requests and the center's hours which the javascript will use to fill out the visible table accordingly.
*/

	include "./../common/session_validator.php";
	$con = getDatabaseConnection();
	$employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".$_POST['pid']."'"));
	if($employee_info[3] == 'admin') {
		echo "<script type = 'text/javascript'>location.href='http://$_SERVER[HTTP_HOST]/common/onyen_validator.php'</script>";
		exit();
	}
?>