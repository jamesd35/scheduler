<?php


include "./../common/session_validator.php";
$con = getDatabaseConnection();

$employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".mysqli_real_escape_string($con, $_SESSION['pid'])."'"));
	
if($employee_info[3] != 'admin') {
	header('HTTP/1.1 401 Unauthorized');
	exit();
}

header("Content-type: application/json");

require_once("util.php");

function updateDatabase($schedule) {
  global $con;

  $days = getDaysArray();
  // delete all previously existing rows
  $sql="delete from actSchedule";
  if (!mysqli_query($con,$sql)) {
  	echo 'Unable to delete previous schedule';
  }

  // Hashmap from pid to tutor.
  $pidArray = array();
  foreach ($schedule as $day => $dayArray) {
  	foreach ($dayArray as $hour => $tutorList) {
  		foreach ($tutorList as $tutor) {
  			$pidArray[$tutor['pid']] = $tutor;
  		}
  	}
  }

  // initialize rows for every tutor
  foreach ($pidArray as $thePid => $tutor) {
    // only initialize for grad and ugrad tutors
    $type = $tutor['type'];
    if ($type == "grad" || $type == "ugrad") {

      foreach ($days as $theDay){
        $sql="insert into actSchedule (PID,day,h00,h01,h02,h03,h04,h05,h06,h07,h08,h09,h10,h11,h12,h13,h14,h15,h16,h17,h18,h19,h20,h21,h22,h23) 
          values('$thePid','$theDay',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0)";
        if(!mysqli_query($con,$sql)){
        	echo "Unable to add to database".mysqli_error($con)."\n";
        }
      }
    }
  }

  foreach($schedule as $theDay => $dayArray){
    foreach($dayArray as $theHour => $tutorList) {
        foreach ($tutorList as $tutor) {
          // only look at pids who are ugrad or grad tutors, not admins
          $type = $tutor['type'];
          $thePid = $tutor['pid'];
          if ($type == "grad" || $type == "ugrad") {
              $sql="update actSchedule set $theHour=1
                where PID='$thePid' and day='$theDay'";
              if(!mysqli_query($con,$sql)){
              	echo "Error: " . mysqli_error($con) . "<br>";
              }
          }
    
      }
    }
  }
}

if (isset($_POST['schedule'])) {
	updateDatabase($_POST['schedule']);
	print(json_encode(true));
}
