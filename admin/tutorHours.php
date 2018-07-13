<?php
/*
	WRITTEN BY: Eric Jones
	LAST EDITED: 12/8/2013
	This script is called via AJAX and either returns the indicated tutor's schedule, or updates the tutor's schedule depending on whther it comes via POST or GET.
*/

include "./../common/session_validator.php";
$con = getDatabaseConnection();

$employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".mysqli_real_escape_string($con, $_SESSION['pid'])."'"));
	
if($employee_info[3] != 'admin') {
	header('HTTP/1.1 401 Unauthorized');
	exit();
}


header("Content-type: application/json");


if($_POST['pid'] == null) {
//if the request didn't come by post, they want us to return the users schedule rather than update it
	$response = array();
	$response['pid'] = $_GET['tutor'];
	
	$result = mysqli_query($con, "SELECT * FROM `actSchedule` WHERE `PID` = '".mysqli_real_escape_string($con, $_GET['tutor'])."'");
	if(mysqli_num_rows($result) == 0) {
		header('HTTP/1.1 412 Tutor Not Found!');
		exit();
	}
	
	while($row = mysqli_fetch_array($result)){
		for($i=7; $i<24; $i++) {
			if($i<10) {
				$next_shift = $row['day'] . '0' . $i;
				$next_number = $row['h0'.$i];
			}else {
				$next_shift = $row['day'] . $i;
				$next_number = $row['h'.$i];
			}
			$response[$next_shift] = 'class_'.$next_number;
		}
	}

	print(json_encode($response));
	exit();
}else {
//else they used post, so we are meant to update the database
	$response = array();
	
	$pid = mysqli_real_escape_string($con, $_POST['pid']);
	$location = mysqli_real_escape_string($con, $_POST['location']);
	$daytime = mysqli_real_escape_string($con, $_POST['daytime']);
	$day = substr($daytime, 0, 3);
	$hour = substr($daytime, 3, 2);
	//set it to 5 so that mysqli_affected_rows will return 1 even if we're not changing the value (and thus will only return false if the tutor doesn't exist)
	mysqli_query($con, "UPDATE `actSchedule` SET `h".$hour."` = '5' WHERE `PID` = '".$pid."' AND `day` = '".$day."'");
	mysqli_query($con, "UPDATE `actSchedule` SET `h".$hour."` = ".$location." WHERE `PID` = '".$pid."' AND `day` = '".$day."'");
	if(mysqli_affected_rows($con) == 0) {
		header('HTTP/1.1 412 Tutor Not Found!');
		exit();
	}
	
	$response['daytime'] = $day.$hour;
	$response['pid'] = $pid;
	$response['location'] = "class_".$location;
	
	//get the new tutors working at this shift
	$result = mysqli_query($con, "SELECT `h".$hour."`, `PID` FROM `actSchedule` WHERE `day` = '".$day."'");
	$response['num_sasb'] = 0;
	$response['num_green'] = 0;
	$response['tutors_sasb'] = array();
	$response['tutors_green'] = array();
	
	while($row = mysqli_fetch_array($result)) {
		if($row["h$hour"] == 1) {
			$response['num_sasb'] += 1;
			
			$temp_result = mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".$row['PID']."'");
			$temp_row = mysqli_fetch_array($temp_result);
			$response['tutors_sasb'][] = $temp_row['Fname'] . " " . $temp_row['Lname'];						
		}else if($row["h$hour"] == 2) {
			$response['num_green'] += 1;
			
			$temp_result = mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".$row['PID']."'");
			$temp_row = mysqli_fetch_array($temp_result);
			$response['tutors_green'][] = $temp_row['Fname'] . " " . $temp_row['Lname'];						
		}
	}
	
	print(json_encode($response));
	exit();
}

?>