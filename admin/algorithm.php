<?php
// Algorithm to schedule tutors using an anytime, optimized backtracking search
// algorithm. The algorithm utilizes heuristics to speed up the search and
// keeps track of the best solution found thus far so that the user can
// terminate the algorithm at anytime.

// ***************************************************************************
// Author: Cenk Baykal
// ***************************************************************************

// We'll be sending back data in the form of JSON.
header("Content-type: application/json");

// If the user got to algorithm.php in another way (without pressing submit),
// then the submission is not valid.
if (!($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['RunAlgorithm'])) {
    header("HTTP/1.1 400 Bad Request");
    print("Invalid Request to Run the Algorithm.");
    exit();
}

// Create connection to the database.
include "./../common/session_validator.php";
$con = getDatabaseConnection();

// Check connection
if (mysqli_connect_errno($con)) {
  header("HTTP/1.1 500 Server Error");
  print("Failed to connect to MySQL: " . mysqli_connect_error());
  exit();
}

// Validitiy Checker class that makes sure the schedule respects all 
// constraints.
class ValidityChecker {
  public $group;
  public $noWorkAfter;
  public $minTutorsPerHour;
  public $maxTutorsPerHour;
  public $graduateHours;
  public $maxHoursPerDay;
  public $weekdays;
  private $openHours;
  private $openHoursCount;

  // The constructor will be in charge of populating the contents of the 
  // constraints that were set.
  function __construct() {
    $this->weekdays = getWeekdaysArray();
    $this->group = isset($_POST['group']) ? $_POST['group'] : NULL;
    $this->minTutorsPerHour = isset($_POST['minTutorsPerHour']) ?
                              intval($_POST['minTutorsPerHour']) : NULL;
    $this->maxTutorsPerHour = isset($_POST['maxTutorsPerHour']) ?
                              intval($_POST['maxTutorsPerHour']) : NULL;
    $this->graduateHours = isset($_POST['graduateHours']) ?
                           intval($_POST['graduateHours']) : NULL;
    $this->maxHoursPerDay = isset($_POST['maxHoursPerDay']) ?
                           intval($_POST['maxHoursPerDay']) : NULL;

    if (isset($_POST['noWorkAfter'])) {
      $str = $_POST['noWorkAfter'];
      $am = strpos($str, 'AM') !== FALSE;
      $str = $am ? str_replace("AM", "", $str) : 
                   str_replace("PM", "", $str);

      // Remove :00.
      $str = str_replace(":00", "", $str);
      $val = intval($str);
      
      if ($am && $val === 12) {
        $val = 0;
      } else if (!$am) {
        if ($val != 12) {
          $val += 12;
        }
      }

      // Set the time in military time.
      $this->noWorkAfter = "h".$val;
    } else {
      $this->noWorkAfter = NULL;
    }

    $this->openHoursCount = getNumOpenHours();
    //print(json_encode($this));                         
  }

  // Reduce the number of possible slots.
  public function reduceNumberOfSlots($schedule, $slots) {
    $minHour = $this->minTutorsPerHour;
    $maxHour = $this->maxTutorsPerHour;

    $reducedSlots = array();

    foreach ($slots as $slot) {
      $numTutors = count($schedule[$slot->day][$slot->hour]);
      // WHY IS THIS THIS WAY??? if (count($numTutors) <= $maxHour) {
      if ($numTutors <= $maxHour) {
        $reducedSlots[] = $slot;
      }
    }

    return $reducedSlots;
  }

  // Checks to make sure that there is at least one hour during the weekday.l
  private function hasOneHourWeekday($combination) {
    foreach ($combination as $slot) {
      if (in_array($slot->day, $this->weekdays)) {
        // Check to make sure the hour is between 9 and 16 (16 because they would
        // start working at 4 and work till 5 pm).
        $hour = intval(str_replace("h", "", $slot->hour));
        if (9 <= $hour && $hour <= 16) 
          return true;
      }
    }

    return false;
  }

  // Checks to make sure that there is at least one hour during the weekday.l
  private function respectsMaxHoursPerDay($combination) {
    $days = array();

    foreach ($combination as $slot) {
      $days[$slot->day] = 0;
    }

    foreach ($combination as $slot) {
      $days[$slot->day]++;
      $dayCount = $days[$slot->day];
      if ($days[$slot->day] > $this->maxHoursPerDay) {
        return false;
      }
    }

    return true;
  }

  // Returns whether the given combination of time slots (schedule) is 
  // appropriate.
  public function validTutorSchedule($combination) {
    $oneHourWeekday = $this->hasOneHourWeekday($combination);
    $respectsMaxHoursPerDay = $this->respectsMaxHoursPerDay($combination);
    return $oneHourWeekday && $respectsMaxHoursPerDay;
  }

  // Returns whether the given schedule violates constraints.
  public function constraintsViolated($schedule) {
    $maxHour = $this->maxTutorsPerHour;

    foreach ($schedule as $day => $dayArray) {
      foreach ($dayArray as $hour => $tutorList) {
        if (count($tutorList) > $maxHour) {
          //$numTutors = count($tutorList);
          //echo "[$day][$hour]\n";
          return true;
        }
      }
    }

    return false;
  }

  // Returns the validitiy of the current solution depending on the contents of
  // the schedule.
  // public function validSolution($schedule) {
    $minHour = $this->minTutorsPerHour;
    $maxHour = $this->maxTutorsPerHour;

    if (count($schedule) == 0) 
      return false;

    $numValidDays = 0;
    foreach ($schedule as $dayArray) {
      foreach ($dayArray as $tutorList) {
        $n = count($tutorList);
        if ($n < $minHour || $n > $maxHour) {
          if ($n > $maxHour)
            return false;
        } else {
          $numValidDays++;
        }
      }
    }

    $ratio = floatval($numValidDays)/$this->openHoursCount;
    return $ratio > 0.96;
  }

  // Also we should initialize the schedule.
};

// Class (more like a struct) to represent a time slot (day and hour).
class TimeSlot {
  public $day;
  public $hour;

  function __construct($day, $hour) {
    $this->day = $day;
    $this->hour = $hour;
  }
}

// Class (more like a struct) to represent a tutor.
class Tutor {
  public $pid;
  public $firstName;
  public $lastName;
  public $type; // ugrad or grad
  // Associative array of [days][hours] to preference number.
  private $availableTimes;
  private $minCanWork;
  private $maxCanWork;

  function __construct($pid, $firstName, $lastName, 
                       $type, $minCanWork, $maxCanWork) {
    $this->pid = $pid;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->type = $type;
    $this->preferences = array();
    $this->minCanWork = $minCanWork;
    $this->maxCanWork = $maxCanWork;
  }

  public function setPid($newPid){
    $this->pid=$newPid;
  }
  public function getPid(){
    return $this->pid;
  }
  public function setTheType($newType){
    $this->type=$newType;
  }
  public function getTheType(){
    return $this->type;
  }
  public function setPreferences($preferences){
    //$this->preferences = $preferences;
    $this->availableTimes = $this->computeAvailableTimes($preferences);
  }
  public function getPreferences() {
    return $this->preferences;
  }

  public function getMinCanWork() {
    return $this->minCanWork;
  }

  public function getMaxCanWork() {
    return $this->maxCanWork;
  }

  public function getAvailableTimes() {
    return $this->availableTimes;
  }

  // Returns an array of available times.
  public function computeAvailableTimes($preferences) {
    $timeSlots = array();

    if (!is_null($preferences)) {
      foreach ($preferences as $day => $dayArray) {
        foreach ($dayArray as $hour => $preferenceValue) {
          if ($preferenceValue > 0) {
            $timeSlots[] = new TimeSlot($day, $hour);
          }
        }
      }
    }

    return $timeSlots;
  }
}

function getWeekdaysArray() {
  return array("mon", "tue", "wed", "thu", "fri");
}

function getDaysArray() {
  return array("mon", "tue", "wed", "thu", "fri", "sat", "sun");
}

function getHoursArray() {
  $hours = array();
  for ($x = 0; $x <= 23; $x++) {
    $str = $x < 10 ? "h0$x" : "h$x";
    $hours[] = $str;
  }

  return $hours;
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

    $result = mysqli_query($con, $sql);

    while ($row = mysqli_fetch_array($result)) {
      // Add the tutor if it is in the group.
      if ($filter === NULL || ($filter !== NULL && $filter === $row['type'])) {
        $tutor = new Tutor($pid, $row['Fname'], $row['Lname'], $row['type']);
        // Add tutor to the list of tutors.
        $tutors[$pid] = $tutor;
      }
    }
  }

  return $tutors;
}

function loadOpenHours() {
  $days = getDaysArray();
  $hours = getHoursArray();
  $openSlots = array();

  foreach($days as $theDay) {
    $sql = ("select * from openHours
      where (openHours.day = '$theDay')");
    $result = mysqli_query($con,$sql);

    if($result != NULL){
      $row = mysqli_fetch_array($result, MYSQLI_BOTH); 

      foreach ($hours as $theHour){
        $openSlots[$theDay][$theHour] = $row[$theHour];
      }
    }
  }

  return $openSlots;
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

// Generates the next permutation depending on the current permutation p.
function nextPermutation($p, $size) {
    // slide down the array looking for where we're smaller than the next guy
    for ($i = $size - 1; $p[$i] >= $p[$i+1]; --$i) { }

    // if this doesn't occur, we've finished our permutations
    // the array is reversed: (1, 2, 3, 4) => (4, 3, 2, 1)
    if ($i == -1) { return false; }

    // slide down the array looking for a bigger number than what we found before
    for ($j = $size; $p[$j] <= $p[$i]; --$j) { }

    // swap them
    $tmp = $p[$i]; $p[$i] = $p[$j]; $p[$j] = $tmp;

    // now reverse the elements in between by swapping the ends
    for (++$i, $j = $size; $i < $j; ++$i, --$j) {
         $tmp = $p[$i]; $p[$i] = $p[$j]; $p[$j] = $tmp;
    }

    return $p;
}

// Class to generate combinations.
class Combination {
  public $combination;
  public $v;
  public $list;
  public $k;

  function __construct($list, $k) {
    $this->combination = array();
    $this->v = array();
    shuffle($list);
    $this->list = $list;
    $this->k = $k;
    $this->n = count($list);


    // // Initialize $v.
    for ($x = 0; $x < $this->n; $x++) {
      if ($x >= $k) {
        $this->v[] = true;
      } else {
        $this->v[] = false;
      }
    }

    // // Initialize $v.
    // for ($x = 0; $x < $this->n; $x++) {
    //   $this->v[] = false;
    // }

    // // Generate k random numbers in the range [0, $n - 1].
    // $indices = $this->randomGen(0, $this->n - 1, $k);

    // foreach ($indices as $i) {
    //   $this->v[i] = true;
    // }
  }

  private function randomGen($min, $max, $quantity) {
    $numbers = range($min, $max);
    shuffle($numbers);
    return array_slice($numbers, 0, $quantity);
  }

  // Generates the next combination based on the current combination.
  public function nextCombination() {
    if (!$this->v)
      return false;

    $this->combination = array();

    for ($x = 0; $x < $this->n; $x++) {
      if (!$this->v[$x]) {
        $val = $this->list[$x];
        $this->combination[] = $val;
      }
    }

    $this->v = nextPermutation($this->v, $this->n - 1);
    return $this->combination;
  }
}

$scheduledTutors = array();
$unscheduledTutors = array();
$schedule = array();
$validityChecker = new ValidityChecker();

function pickTutor() {
  global $scheduledTutors;
  global $unscheduledTutors;

  $tutor = array_shift($unscheduledTutors);
  if (is_null($tutor))
    return $tutor;

  $scheduledTutors[] = $tutor;
  return $tutor;
}

function updateDatabaseWithSchedule() {
  global $schedule;
  global $con;

  mysqli_query("LOCK TABLES actSchedule WRITE");
  // // delete all previously existing rows
  $sql="delete from actSchedule";
  if(mysqli_query($con,$sql)){
  }

  $days = getDaysArray();
  $hours = getHoursArray();
  $i = 0;

  // update rows based on sasbSchedule
  foreach($days as $theDay){
    $arr = array();
    foreach($hours as $theHour) {
      if (count($schedule[$theDay][$theHour]) > 0) {
        $text = "";
        $count = count($schedule[$theDay][$theHour]);
        for($x = 0; $x < $count; $x++) {
          $text = $text.$schedule[$theDay][$theHour][$x]->firstName." ".$schedule[$theDay][$theHour][$x]->lastName;

          if ($x != $count - 1)
            $text = $text.';';
        }
        $arr[$theHour] = $text;
      } else {
        $arr[$theHour] = " ";
      }
    }

      // Add the array here.
      $sql="insert into actSchedule values ($i, '$theDay'";
      foreach ($arr as $day) {  
        $sql = $sql.', '."'".$day."'";
      }
      $sql = $sql.')';

      if(!mysqli_query($con,$sql)){
        echo "Error: " . mysqli_error($con) . "<br>";
        exit();
      } 

      $i++; 
  }

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

// Initializes the schedule based on the open hours of the writing center.
function getOpenHours() {
  global $con;
  $days = getDaysArray();
  $hours = getHoursArray();
  $ret = array();

  foreach($days as $theDay) {
    $sql = ("select * from openHours
              where (openHours.day = '$theDay')");
    $result = mysqli_query($con,$sql);

    if($result != NULL){
      $row = mysqli_fetch_array($result, MYSQLI_BOTH); 

      foreach($hours as $theHour){
          $ret[$theDay][$theHour] = intval($row[$theHour]);
      }
    }
  }

  return $ret;
}

// Main algorithm that generates the schedule.
function generateSchedule() {
  global $scheduledTutors;
  global $unscheduledTutors;
  global $schedule;
  global $validityChecker;
  $tutors = loadTutors($validityChecker->group);
  populatePreferences($tutors);
  $unscheduledTutors = $tutors;

  //initializeSchedule();
  generateScheduleHelper();
}

$numExecuted = 0;

function generateScheduleHelper() {
  global $scheduledTutors;
  global $unscheduledTutors;
  global $schedule;
  global $validityChecker;
  global $numExecuted;

  // echo 'Number of tutors scheduled: '.strval(count($scheduledTutors))."\n";
  // echo 'Number of tutors unscheduled: '.strval(count($unscheduledTutors))."\n";
  // if ($numExecuted++ > 1000) {
  //   echo 'Executed 100000 times';
  //   exit();
  // }

  // If this is a valid solution, evaluate its objective value and update the 
  // best solution thus far accordingly.
  if ($validityChecker->validSolution($schedule)) {
    // add solution to valid list
    //echo 'Valid solution found!';
    //var_dump($schedule);
    updateDatabaseWithSchedule();
    //print(json_encode(TRUE));
    //print(json_encode($schedule));
    //exit();
    return true;
  }

  if ($validityChecker->constraintsViolated($schedule)) {
    //echo 'Constraint violated, returning.';
    return false;
  }
  
  // TODOS
  // Pick a person from unscheduled list
  // Try all scheduled combinations
  // Incorporate lookahead
  $tutor = pickTutor();

  if (is_null($tutor)) {
    // No tutors left to pick means that this is not a valid solution.
    //echo 'Tutor was NULL';
    return false;
  }

  //echo $tutor->firstName."\n";

  $slots = $tutor->getAvailableTimes();
  $numSlotsOld = count($slots);

  if ($numSlotsOld == 0) {
    // If the tutor did not specify his or her preferences, skip to the next
    // tutor.
    return generateScheduleHelper();
  }

  //echo "Num available slots: $numSlotsOld\n";
  // Reduce available times here by looking at the current schedule...
  $slots = $validityChecker->reduceNumberOfSlots($schedule, $slots);
  $numSlots = count($slots);

  if ($numSlots == 0) {
    // The tutor has no available slots, the schedule is not feasible.
    //generateScheduleHelper();
    array_pop($scheduledTutors);
    $unscheduledTutors[] = $tutor;
    return false;
  }

  // Try all numbers between [minHoursCanWork, maxHoursCanWork]. These numbers
  // should be decided by the current tutor. (assuming 5 and 10 for now).
  $minCanWork = min($numSlots, 8);
  $maxCanWork = min($numSlots, 12);

  for ($x = $minCanWork; $x <= $maxCanWork; $x++) {
    // Generate all possible combinations of time slots that the tutor can work.
    $combinationObj = new Combination($slots, $x);
    
    while ($combinationObj->nextCombination()) {
      $timeSlots = $combinationObj->combination;
      if (!$validityChecker->validTutorSchedule($timeSlots))
        continue;

      // For each time slot, add the current tutor to the schedule.
      foreach ($timeSlots as $time) {
        $schedule[$time->day][$time->hour][] = $tutor;
        //$schedule['mon']['09h'][] = $tutor;
      }

      // Run the algorithm recursively...
      generateScheduleHelper();

      // Remove tutor from all time slots.
      foreach ($timeSlots as $time) {
        // $day = $time->day;
        // $hour = $time->hour;
        //echo "Day: $day hour: $hour\n";
        //$mon = count($schedule[$time->day][$time->hour]);
        //echo "Before size of mon09h: $mon\n";
        array_pop($schedule[$time->day][$time->hour]);
        //$mon = count($schedule[$time->day][$time->hour]);
        //echo "After size of mon09h: $mon\n";
      }
    }
  }

  // Add tutor back to the unscheduled tutors list so that it can be considered
  // for scheduling in other branches of the tree.
  //echo "Adding tutor back\n";
  array_pop($scheduledTutors);
  $unscheduledTutors[] = $tutor;
}

//echo "Running scheduling algorithm...";
generateSchedule();
//echo 'Done Running the algorithm';
exit();

// We're done here...

// array where key is PID and value is assoc array of days whose values are
// number of hours working that day
function initializeHoursWorkingPerDay(){
  global $pidArray;
  global $days;
  $arr = array();

  foreach($pidArray as $thePid){
    $arr[$thePid]=array();
    foreach($days as $theDay){
      $arr[$thePid][$theDay]=0;
    }
  }
  return $arr;
}

// returns array of hours that are available to be scheduled
// function getOpenHours(){
//   global $con;
//   global $days;
//   global $hours;
//   $arr = array();
//   foreach($days as $theDay) {
//     $sql = ("select * from openHours
//       where (openHours.day = '$theDay')");
//     $result = mysqli_query($con,$sql);
//     if($result != NULL){
//       $row = mysqli_fetch_array($result, MYSQLI_BOTH); 
//       foreach($hours as $theHour){
//         $arr[$theDay][$theHour]=$row[$theHour];
//       }
//     }
//   }
//   return $arr;
// }


// given a day of the week, initializes its preference array
function initializeDay($theDay){
  // array that's name is the day of the week
  ${$theDay} = initializeHours();
  return ${$theDay};
}

// initializes every hour in a day for 0 as number of tutors currently scheduled
function initializeDayByHours($theDay){
  ${$theDay} = initializeHoursInDay();
  return ${$theDay};
}

// initializes each hour in a day as number of tutors currently scheduled
function initializeHoursInDay(){
  $day = array(
    "h00" => 0,
    "h01" => 0,
    "h02" => 0,
    "h03" => 0,
    "h04" => 0,
    "h05" => 0,
    "h06" => 0,
    "h07" => 0,
    "h08" => 0,
    "h09" => 0,
    "h10" => 0,
    "h11" => 0,
    "h12" => 0,
    "h13" => 0,
    "h14" => 0,
    "h15" => 0,
    "h16" => 0,
    "h17" => 0,
    "h18" => 0,
    "h19" => 0,
    "h20" => 0,
    "h21" => 0,
    "h22" => 0,
    "h23" => 0
  );
  return $day;
}

// initializes an array of tuples for each day, for each hour. Mehtod by
// which every schedule is stored, as well as the preferences.
function initializeHours(){
  $day = array(
    "h00" => array(
      "tuples" => array()
    ),
    "h01" => array(
      "tuples" => array()
    ),
    "h02" => array(
      "tuples" => array()
    ),
    "h03" => array(
      "tuples" => array()
    ),
    "h04" => array(
      "tuples" => array()
    ),
    "h05" => array(
      "tuples" => array()
    ),
    "h06" => array(
      "tuples" => array()
    ),
    "h07" => array(
      "tuples" => array()
    ),
    "h08" => array(
      "tuples" => array()
    ),
    "h09" => array(
      "tuples" => array()
    ),
    "h10" => array(
      "tuples" => array()
    ),
    "h11" => array(
      "tuples" => array()
    ),
    "h12" => array(
      "tuples" => array()
    ),
    "h13" => array(
      "tuples" => array()
    ),
    "h14" => array(
      "tuples" => array()
    ),
    "h15" => array(
      "tuples" => array()
    ),
    "h16" => array(
      "tuples" => array()
    ),
    "h17" => array(
      "tuples" => array()
    ),
    "h18" => array(
      "tuples" => array()
    ),
    "h19" => array(
      "tuples" => array()
    ),
    "h20" => array(
      "tuples" => array()
    ),
    "h21" => array(
      "tuples" => array()
    ),
    "h22" => array(
      "tuples" => array()
    ),
    "h23" => array(
      "tuples" => array()
    )
  );
  return $day; // array of hours
}

// used to initialize tuple arrays for each day
function initializeTupleArray(){
  $arr = array(
    "sun" => initializeDay("sun"),
    "mon" => initializeDay("mon"),
    "tue" => initializeDay("tue"),
    "wed" => initializeDay("wed"),
    "thu" => initializeDay("thu"),
    "fri" => initializeDay("fri"),
    "sat" => initializeDay("sat")
  );
  return $arr;
}

// initializes array to store num tutors working each day
function initializeNumWorking(){
  $arr = array(
    "sun" => initializeDayByHours("sun"),
    "mon" => initializeDayByHours("mon"),
    "tue" => initializeDayByHours("tue"),
    "wed" => initializeDayByHours("wed"),
    "thu" => initializeDayByHours("thu"),
    "fri" => initializeDayByHours("fri"),
    "sat" => initializeDayByHours("sat"),
  );
  return $arr;
}

// tuple for an individual's preference on a given day and hour
class tuple {
  public $pid;
  public $type; // ugrad or grad
  public $pref; // preference number

  public function setPid($newPid){
    $this->pid=$newPid;
  }
  public function getPid(){
    return $this->pid;
  }
  public function setTheType($newType){
    $this->type=$newType;
  }
  public function getTheType(){
    return $this->type;
  }
  public function setPref($newPref){
    $this->pref=$newPref;
  }
  public function getPref(){
    return $this->pref;
  }
}

// loads prefernces into preferences tuple array
function loadPref(){ 
  global $con;
  global $days;
  global $hours;
  global $preferences;
  foreach($days as $theDay){
    foreach($hours as $theHour){
      $sql = ("select hoursByDay.PID,employeeInfo.type,hoursByDay.$theHour
        from hoursByDay, employeeInfo
        where (hoursByDay.day = '$theDay' and $theHour > 0 and 
        hoursByDay.PID = employeeInfo.PID)");

      $result = mysqli_query($con,$sql);
      $numRows = mysqli_num_rows($result);

      for($i = 0; $i < $numRows; $i++){ // for every row
        // get the array from result indexed by both numbers and variable name
        $row = mysqli_fetch_array($result, MYSQLI_BOTH); 
        //var_dump($row); 
        $temp= new tuple;
        $temp->setPid($row["PID"]);
        $temp->setTheType($row["type"]);
        $temp->setPref("$row[$theHour]");
        // associative array where an employee's PID points to his/her tuple
        $preferences[$theDay][$theHour]["tuples"][$row["PID"]]=$temp;
      }
    }
  }
}

// returns array of PIDs obtained from employeeInfo
function getPids(){
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

// removes a tuple from given "tuple array," which are initialized via the
// initializeTupleArray() function.
function removeTuple(&$theArray, $theDay, $theHour, $thePid){
  unset($theArray[$theDay][$theHour]["tuples"][$thePid]);
}

// adds a tuple from given "tuple array," which are initialized via the
// initializeTupleArray() function.
function addTuple(&$theArray, $theDay, $theHour, $thePid, $thePref, $theType){
  $temp= new tuple;
  $temp->setPid($thePid);
  $temp->setTheType($theType);
  $temp->setPref($thePref);
  $theArray[$theDay][$theHour]["tuples"][$thePid]=$temp;
}

// returns number of tuples in an array given day and hour
function numTuples(&$theArray, $theDay, $theHour){
  return sizeof($theArray[$theDay][$theHour]["tuples"]);
}

// returns number of people scheduled given a certain schedule
function getNumWorkingArr(&$theSchedule){
  global $days;
  global $hours;
  $toReturn = initializeNumWorking(); 
  foreach($days as $theDay){
    foreach($hours as $theHour){
      $toReturn[$theDay][$theHour] = numTuples($theSchedule,$theDay,$theHour);
    }
  }
  return $toReturn;
}

// loops through preferences array starting with highest preferences, and adds
// tuples to the given schedule until at least three people are working every
// hour.
function ensureThreeScheduled(&$theSchedule){
  scheduleAllHours($theSchedule,3,"grad",3,1);
}

// Schedules numToSchedule tutors of type theType every hour who have
// preference <= maxPref and >= minPref
function scheduleAllHours(&$theSchedule,$numToSchedule,$theType,$maxPref,$minPref){
  global $days;
  global $hours;
  global $openHours;
  global $pidArray;
  global $preferences;
  global $tutorInfo;
  global $hoursWorking;
  global $hoursWorkingPerDay;
  for($currentPref = $maxPref; $currentPref >= $minPref; $currentPref--){
    foreach($days as $theDay){
      foreach($hours as $theHour){
        // make sure writing center is open at this hour
        if($openHours[$theDay][$theHour] == 1){
          foreach($pidArray as $thePid){
            if($tutorInfo[$thePid]["type"]==$theType){
              $temp = new tuple;
              $temp = $preferences[$theDay][$theHour]["tuples"][$thePid];
              if($temp != NULL){
                $thePref = $temp->getPref();
                $theType = $temp->getTheType();
                if($thePref == $currentPref){
                  if(numTuples($theSchedule, $theDay, $theHour) < $numToSchedule){
                    addTuple($theSchedule,$theDay,$theHour,$thePid,$thePref,$theType);
                    removeTuple($preferences,$theDay,$theHour,$thePid);
                    $hoursWorking[$thePid] = $hoursWorking[$thePid] + 1;
                    $hoursWorkingPerDay[$thePid][$theDay] = $hoursWorkingPerDay[$thePid][$theDay] + 1 ;
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}

// Schedules ALL of a tutor of type=theType 3 preferences as long as they are
// scheduled for less than their max hours, 14 for grads, 10 for ugrads. If 
// not at their max, schedule all 2 preferences. Then, same for 1s.
function batchScheduling(&$theSchedule, $theType){
  global $days;
  global $hours;
  global $openHours;
  global $pidArray;
  global $preferences;
  global $tutorInfo;
  global $hoursWorking;
  global $hoursWorkingPerDay;
  global $GRAD_HOURS;
  global $UGRAD_HOURS;

  foreach($pidArray as $thePid){
    if($tutorInfo[$thePid]["type"] == "$theType"){
      if($theType == "grad")
        $minHours = $GRAD_HOURS;
      elseif($theType == "ugrad")
        $minHours = $UGRAD_HOURS;
      if($hoursWorking[$thePid] < $minHours){
        // schedule all threes
        scheduleByPref($theSchedule, 3, $thePid);

        if($hoursWorking[$thePid] < $minHours){
          // schedule all twos
          scheduleByPref($theSchedule, 2, $thePid);

          if($hoursWorking[$thePid] < $minHours){
            // schedule all ones
            scheduleByPref($theSchedule, 1, $thePid);
          }
        }
      }
    }
  }
}

// used by batchScheudling function, schedules all of a particular 
// preference for every tutor for every hour
function scheduleByPref(&$theSchedule, $requiredPref, $thePid){
  global $days;
  global $hours;
  global $openHours;
  global $pidArray;
  global $preferences;
  global $tutorInfo;
  global $hoursWorking;
  global $hoursWorkingPerDay;

  foreach($days as $theDay){
    foreach($hours as $theHour){
      // make sure writing center is open at this hour
      if($openHours[$theDay][$theHour] == 1){
        $temp = new tuple;
        $temp = $preferences[$theDay][$theHour]["tuples"][$thePid];
        if($temp != NULL){
          $thePref = $temp->getPref();
          $theType = $temp->getTheType();
          if($thePref == $requiredPref){
            addTuple($theSchedule,$theDay,$theHour,$thePid,$thePref,$theType);
            removeTuple($preferences,$theDay,$theHour,$thePid);
            $hoursWorking[$thePid] = $hoursWorking[$thePid] + 1;
            $hoursWorkingPerDay[$thePid][$theDay] = $hoursWorkingPerDay[$thePid][$theDay] + 1;
          }
        }
      }
    }
  }
}


// This heavily favors Sunday and Monday scheduling, need to spread out
// scheduling across the whole week. Going to re-do this a little differently,
// but leaving this in case it comes in handy later. See batchScheduling.
function ensureGradGe14(&$theSchedule){
  global $days;
  global $hours;
  global $openHours;
  global $pidArray;
  global $preferences;
  global $tutorInfo;
  global $hoursWorking;
  global $hoursWorkingPerDay;

  // keeps scheduling from favoring Sundays and Mondays early hours
  // doesn't work, still favors first randomly selected day
  shuffle($days);
  shuffle($hours);

  foreach($pidArray as $thePid){
    // intially skip over if tutor does not need to be scheduled
    if(($tutorInfo[$thePid]["type"] == "grad") and (intval($hoursWorking[$thePid]) < 14)){
      foreach($days as $theDay){
        foreach($hours as $theHour){
          // as long as this day is open and the grad is not at his max
          if(($openHours[$theDay][$theHour] == 1) and ($hoursWorking[$thePid] < 14)){
            $temp = new tuple;
            $temp = $preferences[$theDay][$theHour]["tuples"][$thePid];
            if($temp != NULL){
              $thePref = $temp->getPref();
              $theType = $temp->getTheType();
              addTuple($theSchedule,$theDay,$theHour,$thePid,$thePref,$theType);
              removeTuple($preferences,$theDay,$theHour,$thePid);
              $hoursWorking[$thePid] = $hoursWorking[$thePid] + 1;
              $hoursWorkingPerDay[$thePid][$theDay] = $hoursWorkingPerDay[$thePid][$theDay] + 1 ;
            }
          }
        }
      }
    }
  }
}

// Removes tuples from given schedule if a tutor is working more than five 
// hours in a shift. Places tuple back in preferences array since that tutor 
// is now available to work. Removes in order of preference.
function ensureLeFiveHoursPerDay(&$theSchedule){
  global $days;
  global $hours;
  global $openHours;
  global $pidArray;
  global $preferences;
  global $tutorInfo;
  global $hoursWorking;
  global $hoursWorkingPerDay;
  foreach($pidArray as $thePid){
    foreach($days as $theDay){
      while($hoursWorkingPerDay[$thePid][$theDay] > 5){
        $low= findLowestTuple($theSchedule,$theDay,$thePid);
        // add back to preferences since tutor can now work this hour
        addTuple($preferences,$theDay,$low["hour"],$thePid,$low["pref"],$low["type"]);
        removeTuple($theSchedule,$theDay,$low["hour"],$thePid);
        $hoursWorking[$thePid]= $hoursWorking[$thePid] - 1;
        $hoursWorkingPerDay[$thePid][$theDay] = $hoursWorkingPerDay[$thePid][$theDay] - 1 ;
      }
    }
  }
}

// given a schedule, day, and PID returns hour and pref of that person's least
// preferred tuple. (Can easily be modified to return something other than
// just the hour).
function findLowestTuple($theSchedule, $theDay, $thePid){
  global $hours;
  $lowest = 4;
  $low = array();
  //$temp = $preferences[$theDay][$theHour]["tuples"][$thePid]
  foreach($hours as $theHour){
    $currentTuple = $theSchedule[$theDay][$theHour]["tuples"][$thePid];
    if($currentTuple != NULL){
      $currentPref = $currentTuple->getPref();
      if($currentPref < $lowest){
        $lowest = $currentPref;
        $low["pref"]=$currentPref;
        $low["hour"]=$theHour;
        $low["type"]=$currentTuple->getTheType();
      }
    }
  }
  return $low;
}

// create array size 7 that has a 1 if tutor is working for corresponging
// day, and a 0 if the tutor is not.
function getDaysWorkingArr(){
  global $pidArray;
  global $tutorInfo;
  global $hoursWorkingPerDay;
  global $days;
  // assoc array whose key is PID and value is an array of size 7
  $toReturn = array();
  foreach($pidArray as $thePid){
    if($tutorInfo[$thePid]["type"] == "grad"){
      // initialize every day to 0
      $arr = array();
      $toReturn[$thePid] = $arr;

      // update daysWorkingArr
      foreach($days as $theDay){
        // going to use hoursWorkingByDay that's already been created
        if($hoursWorkingPerDay[$thePid][$theDay] > 0){ // tutor is working
          $toReturn[$thePid][] = 1;
        }
        else // tutor is not working
          $toReturn[$thePid][] = 0;
      }
    }
  }
  return $toReturn;
}

// given a tutor's pid and the day, this function finds a tutor's highest
// preference tuple for this day, and modifies the given hour, day, preference,
// and tuple for use in later adding this tuple to the schedule. Passes the 
// last variables by reference rather than returning an array containing all
// of them and then updating the variables in the other function
function gradDaysOffHelper($thePid, $theDay, &$retHour, &$retDay, &$retPref, &$retTuple, &$retBool){
  global $hours;
  global $preferences;

  foreach($hours as $theHour){
    $currentTuple =  $preferences[$theDay][$theHour]["tuples"][$thePid];
    if($tuple != NULL){ // if tutor can work this day
      $currentPref = $currentTuple->getPref();
      if($currentPref > $maxPref){
        $retBool = true; // at least one tuple to schedule is found
        $retHour = $theHour;
        $retDay = $dayToSchedule;
        $retPref = $currentPref;
        $retTuple = $currentTuple;
      }
    }
  }
}

// ensures that no graduate tutor has more than two days off. If this is
// found to be the case, an hour is scheduled for them such that this is 
// no longer the case.
function ensureGradDaysOff(&$theSchedule){
  global $days;
  global $hours;
  global $openHours;
  global $pidArray;
  global $preferences;
  global $tutorInfo;
  global $hoursWorking;
  global $hoursWorkingPerDay;
  $daysWorkingArr = getDaysWorkingArr();
  
  // now that the array has been built, re-schedule tutors so that they have
  // no more than 2 days off
  foreach($pidArray as $thePid){
    if($tutorInfo[$thePid]["type"] == "grad"){
      // If there are more than two days off from Sunday to Friday:
      for($i=0; $i<4; $i++){ // stops checking at Wednesday since not handling wraparound
        if($daysWorkingArr[$thePid][$i] == 0){ // not working that day
          // check next two days
          if(($daysWorkingArr[$thePid][$i+1] == 0) && ($daysWorkingArr[$thePid][$i+2] == 0)){
            // try to schedule 3rd day, if unable, schedule 2nd, etc.
            $dayToSchedule = numToDay($i+2); //selects 3rd day
            $found = false; // true if tuple to schedule is found

            // Find hour with highest preference (not technically necessary, just a nice thing to do)
            $maxHour = "";
            $maxDay = "";
            $maxPref = 0; 
            $maxTuple =  new tuple; // tuple with highest preference

            gradDaysOffHelper($thePid, $dayToSchedule, $maxHour, $maxDay, $maxPref, $maxTuple, $found);
            
            if(!$found){ // no tuple on 3rd day, try to schedule 2nd
              $dayToSchedule = numToDay($i+1); //selects 2nd day
              gradDaysOffHelper($thePid, $dayToSchedule, $maxHour, $maxDay, $maxPref, $maxTuple, $found);
            }
            if(!$found){ // no tuple on 2nd day, try to schedule 1st
              $dayToSchedule = numToDay($i); //selects 1st day
              gradDaysOffHelper($thePid, $dayToSchedule, $maxHour, $maxDay, $maxPref, $maxTuple, $found);
            }
            // if there's no available tuple to be scheduled, then set $maxTuple
            // to NULL and just don't worry about it. (extremely unlikely)
            if(!$found){ 
              $maxTuple = NULL;
            }

            // at this point we have a tuple, $maxTuple, to schedule for one
            // of these days to keep grads from being off for more than 2 days
            // in a row. Just going to schedule 1 hour for this day, rather
            // than worrying about scheduling a cluster of hours.
            if($maxTuple != NULL){
              $thePref = $maxTuple->getPref();
              $theType = $maxTuple->getTheType();
              addTuple($theSchedule,$maxDay,$maxHour,$thePid,$thePref,$theType);
              removeTuple($preferences,$maxDay,$maxHour,$thePid);
              $hoursWorking[$thePid] = $hoursWorking[$thePid] + 1;
              $hoursWorkingPerDay[$thePid][$theDay] = $hoursWorkingPerDay[$thePid][$theDay] + 1 ;
            }
          }
        }
      }
    }
  }
}

// returns day given numeric value, i.e. 0 = "sun", 1 = "mon", ..., 6 = "sat"
function numToDay($theNum){
  if($theNum == 0)
    return "sun";
  elseif($theNum == 1)
    return "mon";
  elseif($theNum == 2)
    return "tue";
  elseif($theNum == 3)
    return "wed";
  elseif($theNum == 4)
    return "thu";
  elseif($theNum == 5)
    return "fri";
  elseif($theNum == 6)
    return "sat";
}

// returns numeric value of day, i.e. "sun" = 0, "mon" = 1, ... , "sat" = 6
function dayToNum($theDay){
  if($theDay == "sun")
    return 0;
  else if ($theDay == "mon")
    return 1;
  else if ($theDay == "tue")
    return 2;
  else if($theDay == "wed")
    return 3;
  else if ($theDay == "thu")
    return 4;
  else if ($theDay == "fri")
    return 5;
  else if ($theDay == "sat")
    return 6;
  else // error
    return -1;
}

// Removes all previously existing rows from actSchedule, and re-populates
// them with data from sasbSchedule and glSchedule.
function populateActSchedule(){
  global $con;
  global $pidArray;
  global $days;
  global $hours;
  global $openHours;
  global $preferences;
  global $tutorInfo;
  global $hoursWorking;
  global $hoursWorkingPerDay;
  global $sasbSchedule;
  global $glSchedule;

  // delete all previously existing rows
  $sql="delete from actSchedule";
  if(mysqli_query($con,$sql)){
  }

  // initialize rows for every tutor
  foreach($pidArray as $thePid){
    // only initialize for grad and ugrad tutors
    if(($tutorInfo[$thePid]["type"] == "grad") or ($tutorInfo[$thePid]["type"] == "ugrad")){
      foreach($days as $theDay){
        $sql="insert into actSchedule (PID,day,h00,h01,h02,h03,h04,h05,h06,h07,h08,h09,h10,h11,h12,h13,h14,h15,h16,h17,h18,h19,h20,h21,h22,h23) 
          values('$thePid','$theDay',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0)";
        if(mysqli_query($con,$sql)){
        }
      }
    }
  }

  // update rows based on sasbSchedule
  foreach($days as $theDay){
    foreach($hours as $theHour){
      // only bother scheduling if the writing centers are open
      if($openHours[$theDay][$theHour] == 1){
        foreach($pidArray as $thePid){
          // only look at pids who are ugrad or grad tutors, not admins
          if(($tutorInfo[$thePid]["type"] == "grad") or ($tutorInfo[$thePid]["type"] == "ugrad")){
            $tuple=$sasbSchedule[$theDay][$theHour]["tuples"][$thePid];
            if($tuple != NULL){ // if tuple exists, tutor is scheduled this hour
              $sql="update actSchedule set $theHour=1
                where PID='$thePid' and day='$theDay'";
              if(mysqli_query($con,$sql)){
              }
              else{
                echo "Error: " . mysqli_error($con) . "<br>";
              }
            }
          }
        }
      }
    }
  }

  foreach($days as $theDay){
    foreach($hours as $theHour){
      // only bother scheduling if the writing centers are open
      if($openHours[$theDay][$theHour] == 1){
        foreach($pidArray as $thePid){
          // only look at pids who are ugrad or grad tutors, not admins
          if(($tutorInfo[$thePid]["type"] == "grad") or ($tutorInfo[$thePid]["type"] == "ugrad")){
            $tuple=$glSchedule[$theDay][$theHour]["tuples"][$thePid];
            if($tuple != NULL){ // if tuple exists, tutor is scheduled this hour
              $sql="update actSchedule set $theHour=2
                where PID='$thePid' and day='$theDay'";
              if(mysqli_query($con,$sql)){
              }
              else{
                echo "Error: " . mysqli_error($con) . "<br>";
              }
            }
          }
        }
      }
    }
  }
}

// returns assoc array size 2 with "day" and "hour" such that they have
// the greatest number of tutors working when that individual tutor is also scheduled
function getTutorMaxDay(&$theSchedule, $theNumWorkingArr, $thePid){
  global $days;
  global $hours;

  $arr = array(
    "day" => "",
    "hour" => ""
  );

  $maxNumWorking = 0;
  foreach($days as $theDay){
    foreach($hours as $theHour){
      $currentNumWorking = $theNumWorkingArr[$theDay][$theHour];
      $contains = containsTutor($theSchedule, $theDay, $theHour, $thePid);
      if(($currentNumWorking > $maxNumWorking) && $contains){
        $arr["day"] = $theDay;
        $arr["hour"] = $theHour;
        $maxNumWorking = $currentNumWorking;
      }
    }
  }
  return $arr;
}

// returns true if a given tutor is scheduled to work at a given time in a 
// given schedule
function containsTutor(&$theSchedule, $theDay, $theHour,$thePid){
  $tuples = $theSchedule[$theDay][$theHour]["tuples"];
  foreach($tuples as $theTuple){
    $currentPid = $theTuple->getPid();
    if($currentPid == $thePid){ 
      return true;
    }
  }
  return false;
}

// This function ensures that tutors are scheduled for their appropriate
// number of hours based on type (GRAD_HOURS and UGRAD_HOURS respectively)
function ensureMaxHours(&$theSchedule){
  global $days, $hours, $pidArray, $tutorInfo, $GRAD_HOURS, $UGRAD_HOURS,
    $hoursWorking;

  $numWorkingArr = array();
  $numWorkingArr = getNumWorkingArr($theSchedule);

  foreach($pidArray as $thePid){
    $theType = $tutorInfo[$thePid]["type"];
    if($theType == "grad" || $theType == "ugrad"){
      if($theType == "grad")
        $maxHours = $GRAD_HOURS;
      elseif($theType == "ugrad")
        $maxHours = $UGRAD_HOURS;
      $numWorking = $hoursWorking[$thePid];
      while($numWorking > $maxHours){
        //unschedule one hour
        $maxArr= getTutorMaxDay($theSchedule,$numWorkingArr,$thePid);
        $maxDay = $maxArr["day"];
        $maxHour = $maxArr["hour"];

        $currentTuple = $theSchedule[$maxDay][$maxHour]["tuples"][$thePid];
        $thePref = $currentTuple->getPref();

        addTuple($preferences,$maxDay,$maxHour,$thePid,$thePref,$theType);
        removeTuple($theSchedule,$maxDay,$maxHour,$thePid);
        $hoursWorking[$thePid]= $hoursWorking[$thePid] - 1;
        $hoursWorkingPerDay[$thePid][$maxDay] = $hoursWorkingPerDay[$thePid][$maxDay] - 1 ;
        $numWorking = $hoursWorking[$thePid];
      }
    }
  }
}

// This function takes the first schedule (sasbSchedule) and moves two tutors
// to GL if there are four or more currently scheduled in SASB, at least one
// of which is a graduate tutor.
function moveToGl(&$theSchedule){
  global $days, $hours, $glSchedule; 

  foreach($days as $theDay){
    foreach($hours as $theHour){
      $numWorking = numTuples($theSchedule, $theDay, $theHour);
      if($numWorking >= 4){
        // move two to GL, one has to be a grad
        $tuples = $theSchedule[$theDay][$theHour]["tuples"];
        $twoScheduled = false; // quits scheduling once two are found
        foreach($tuples as $theTuple){
          if(!$twoScheduled){
            $currentType = $theTuple->getTheType();
            if($currentType == "grad"){
              $thePref = $theTuple->getPref();
              $thePid = $theTuple->getPid();
              $theType = $theTuple->getTheType();
              // schedule this person and set gradScheduled to true
              addTuple($glSchedule,$theDay,$theHour,$thePid,$thePref,$theType);
              removeTuple($theSchedule,$theDay,$theHour,$thePid);

              $newTuples = $theSchedule[$theDay][$theHour]["tuples"];
              foreach($newTuples as $newTuple){
                if(!$twoScheduled){
                  $newPid = $newTuple->getPid();
                  $newPref = $theTuple->getPref();
                  $newType = $theTuple->getTheType();
                  addTuple($glSchedule,$theDay,$theHour,$newPid,$newPref,$newType);
                  removeTuple($theSchedule,$theDay,$theHour,$newPid);
                  $twoScheduled = true;
                }
              }
            }
          }
        }
      }
    }
  }
}


// 1. SASB covered for all open hours
loadPref();
ensureThreeScheduled($sasbSchedule);

// 2. Grad students must work at least 14 hours (at most handled later)
batchScheduling($sasbSchedule,"grad");

// 3. Ugrads work at most 10 hours, between 6 and 10
batchScheduling($sasbSchedule,"ugrad");

// 4. No more than 5 hours in a day
ensureLeFiveHoursPerDay($sasbSchedule);

// 5. Grads get no more than 2 days off in a row
ensureGradDaysOff($sasbSchedule);

// 6. Ensure grads scheduled for 14 hours exactly and ugrads for 6
ensureGradGe14($sasbSchedule);
ensureMaxHours($sasbSchedule);

// 7. Move people to GL
moveToGl($sasbSchedule);

// 8. Populate actSchedule
populateActSchedule();
