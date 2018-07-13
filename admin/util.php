<?php

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


// Class (more like a struct) to represent a tutor.
class Tutor {
  public $pid;
  public $firstName;
  public $lastName;
  public $type; // ugrad or grad
  // Associative array of [days][hours] to preference number.
  public $availableTimes;
  public $minCanWork;
  public $maxCanWork;
  public $idealCanWork;

  function __construct($pid, $firstName, $lastName, 
                       $type, $minCanWork, $maxCanWork, $idealCanWork) {
    $this->pid = $pid;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->type = $type;
    $this->preferences = array();
    $this->availableTimes = array();
    $this->minCanWork = $minCanWork;
    $this->maxCanWork = $maxCanWork;
    $this->idealCanWork = $idealCanWork;
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
    $this->preferences = $preferences;
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

// Class (more like a struct) to represent a time slot (day and hour).
class TimeSlot {
  public $day;
  public $hour;

  function __construct($day, $hour) {
    $this->day = $day;
    $this->hour = $hour;
  }
}