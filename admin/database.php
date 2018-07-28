<?php

// Retrieves the contents of the current schedule and outputs the schedule in
// JSON format.
// ***************************************************************************
// Author: Cenk Baykal
// ***************************************************************************

include "./../common/session_validator.php";
$con = getDatabaseConnection();

$employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".mysqli_real_escape_string($con, $_SESSION['pid'])."'"));
	
if($employee_info[3] != 'admin') {
	header('HTTP/1.1 401 Unauthorized');
	exit();
}

require_once("problem_instance.php");
require_once("util.php");

// Returns an array of PIDs obtained from employeeInfo.
function getPids() {
  global $con;
  $arr = array();
  $sql = ("select PID from employeeInfo");
  $result = mysqli_query($con,$sql);
  $numRows = mysqli_num_rows($result);
  for($i = 0; $i<$numRows; $i++){
    $row = mysqli_fetch_array($result, MYSQLI_BOTH);
    $arr[]=$row["PID"]; // adds PID to array
  }
  return $arr;
}

// Returns an associative array of day and hour keys with either 0 (closed) or
// 1 (open) for each time slot.
function getOpenHours() {
  global $con;
  $days = getDaysArray();
  $hours = getHoursArray();
  $openHours = array();

  foreach($days as $theDay) {
    $sql = ("select * from openHours
              where (openHours.day = '$theDay')");
    $result = mysqli_query($con,$sql);

    if($result != NULL){
      $row = mysqli_fetch_array($result, MYSQLI_BOTH); 

      foreach($hours as $theHour){
          $openHours[$theDay][$theHour] = intval($row[$theHour]);
      }
    }
  }

  return $openHours;
}

// Populates the preferences of each tutor in the tutors list.
function populatePreferences(&$tutors) {
  global $con;
  $days = getDaysArray();
  $hours = getHoursArray();
  $openHours = getOpenHours();

  foreach ($tutors as &$tutor) {
    $preferences = array();
    $pid = $tutor->pid;

    foreach ($days as $theDay) {
      //$preferences[$theDay] = array();
      foreach ($hours as $theHour) {
        $sql = "select hoursByDay.$theHour from hoursByDay ".
               "where (hoursByDay.day = '$theDay' and hoursByDay.PID = '$pid')";

        $result = mysqli_query($con,$sql);
        while ($row = mysqli_fetch_array($result)) {
          if ($openHours[$theDay][$theHour] != 0)
            $preferences[$theDay][$theHour] = $row[$theHour];
        }
      }
    }

    // Set the populated preferences of the tutor.
    $tutor->setPreferences($preferences);
  }
}

// Returns a list of tutors from the given group. The default group is 'all'.
function loadTutors($group = 'All') {
  global $con;
  $tutors = array();
  $pids = getPids();

  $filter = NULL;
  if ($group === 'Undergraduates') {
    $filter = 'ugrad';
  } else if ($group === 'Graduate Students') {
    $filter = 'grad';
  }

  foreach ($pids as $pid) {
    $sql = "select employeeInfo.type, employeeInfo.Fname, employeeInfo.Lname ".
           "from employeeInfo where PID = '$pid'";
    // $sql = "select uGradWeeklyHours.minHours,uGradWeeklyHours.maxHours,".
    //         "uGradWeeklyHours.idealHours, employeeInfo.type,employeeInfo.Fname, employeeInfo.Lname ".
    //        "from employeeInfo, uGradWeeklyHours where employeeInfo.PID = '$pid' and uGradWeeklyHours.PID = '$pid'";

    $result = mysqli_query($con, $sql);

    while ($row = mysqli_fetch_array($result)) {
      // Add the tutor if it is in the group.
      if ($filter === NULL || ($filter !== NULL && $filter === $row['type'])) {
        $tutor = NULL;

        // For undergrads, there is also min,max, and ideal hours.
        if ($row['type'] == 'ugrad') {
          $sqlTwo = "select uGradWeeklyHours.minHours,uGradWeeklyHours.maxHours,".
            "uGradWeeklyHours.idealHours from uGradWeeklyHours where uGradWeeklyHours.PID = '$pid'";

          $resultTwo = mysqli_query($con, $sqlTwo);
          
          if (($rowTwo = mysqli_fetch_array($resultTwo)) != NULL) {
            $tutor = new Tutor($pid, $row['Fname'], $row['Lname'], $row['type'],
                    $rowTwo['minHours'], $rowTwo['maxHours'], $rowTwo['idealHours']);
          } else {
            $tutor = new Tutor($pid, $row['Fname'], $row['Lname'], $row['type']);
          }
        } else if ($row['type'] == 'grad') {
          $tutor = new Tutor($pid, $row['Fname'], $row['Lname'], $row['type']);
        }
        // Add tutor to the list of tutors.
        $tutors[$pid] = $tutor;
      }
    }
  }

  return $tutors;
}

// Maybe include the group here?
function getTutors() {
	$tutors = loadTutors();
	populatePreferences($tutors);
	return $tutors;
}

// Initializes the schedule based on the open hours of the writing center.
function getNumOpenHours() {
  global $con;
  $days = getDaysArray();
  $hours = getHoursArray();

  $numOpenHours = 0;
  foreach($days as $theDay) {
    $sql = ("select * from openHours
              where (openHours.day = '$theDay')");
    $result = mysqli_query($con,$sql);

    if($result != NULL){
      $row = mysqli_fetch_array($result, MYSQLI_BOTH); 

      foreach($hours as $theHour){
        if (intval($row[$theHour]) > 0) {
          $numOpenHours++;
        }
      }
    }
  }

  return $numOpenHours;
}

header("Content-type: application/json");

// Does the user want the schedule?
if (isset($_GET['schedule'])) {
	$schedule = array();

	$result = mysqli_query($con, "SELECT * FROM actSchedule");
	while($row = mysqli_fetch_array($result)) {
		$day = $row['day'];

		for($i=7; $i<24; $i++) {
			if($i<10) {
				$hour = 'h0'.$i;
			}else {
				$hour = 'h'.$i;
			}
			$schedule[$day][$hour] = explode(";",$row[$hour]);
		}
	}

	print(json_encode($schedule));
} else if (isset($_GET['problemInstance'])) {
  // Problem instance loads the tutors, open hours, and other information
  // required to solve this problem instance (hence ProblemInstance) of the 
  // scheduling problem.
	$openHours = getOpenHours();
	$tutors = getTutors();
	$numOpenHours = getNumOpenHours();
	$problemInstance = new ProblemInstance($tutors, $openHours, $numOpenHours);
	print(json_encode($problemInstance));
}

exit();
