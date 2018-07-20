<?php
// Create connection on Openshift machine
$con=mysqli_connect("127.7.132.2","userERA","5THnhIwCoyCMtuA0");

// Check connection
if (mysqli_connect_errno($con)){
  echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br>";
}

// Create database
$sql="create database IF NOT EXISTS tutorScheduler";
if (mysqli_query($con,$sql)){
  echo "Database tutorScheduler created successfully <br>";
}
else {
  echo "Error creating database: " . mysqli_error($con) . "<br>";
}

// Set database
$sql="use tutorScheduler";
if (mysqli_query($con,$sql)){
  echo "Database set successfully <br>";
}
else {
  echo "Error setting database: " . mysqli_error($con) . "<br>";
}

// Drop employeeInfo if previously there
$sql="drop table if exists employeeInfo";
if(mysqli_query($con,$sql)){
  echo "Deleted previously existing employeeInfo table <br>";
}

// Create employee info
$sql="create table employeeInfo (
  PID INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (PID),
  Fname VARCHAR(30), Lname VARCHAR(30), type VARCHAR(5))";

if (mysqli_query($con,$sql)){
  echo "Table employeeInfo created successfully <br>";
}
else{
  echo "Error creating table: " . mysqli_error($con) . "<br>";
}

// Populate employeeInfo with initial data
$sql="load data local infile './testEmployee.txt' into table employeeInfo 
  fields terminated by ','";

if (mysqli_query($con,$sql)){
  echo "employeeInfo loaded successfully <br>";
}
else{
  echo "Error loading data: " . mysqli_error($con) . "<br>";
}

// Drop hoursByDay if previously there
$sql="drop table if exists hoursByDay";
if(mysqli_query($con,$sql)){
  echo "Deleted previously existing hoursByDay table <br>";
}

// Create hoursByDay
$sql="create table hoursByDay (
  PID INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (PID,day),
  day VARCHAR(15), 
  h00 INT, h01 INT, h02 INT, h03 INT, h04 INT, h05 INT, h06 INT, h07 INT, 
  h08 INT, h09 INT, h10 INT, h11 INT, h12 INT, h13 INT, h14 INT, h15 INT, 
  h16 INT, h17 INT, h18 INT, h19 INT, h20 INT, h21 INT, h22 INT, h23 INT)";

if (mysqli_query($con,$sql)){
  echo "Table hoursByDay created successfully.<br>";
}
else{
  echo "Error creating table: " . mysqli_error($con) . "<br>";
}

// Populate hoursByDay with initial data
$sql="load data local infile './TutorPreferences.txt' into table hoursByDay
  fields terminated by ','";

if (mysqli_query($con,$sql)){
  echo "hoursByDay loaded successfully.<br>";
}
else{
  echo "Error loading data: " . mysqli_error($con) . "<br>";
}

// Drop openHours if previously there
$sql="drop table if exists openHours";
if(mysqli_query($con,$sql)){
  echo "Deleted previously existing openHours table <br>";
}

// Create openHours
$sql="create table openHours (
  day VARCHAR(15), 
  h07 INT, h08 INT, h09 INT, h10 INT, h11 INT, h12 INT, h13 INT, h14 INT, 
  h15 INT, h16 INT, h17 INT, h18 INT, h19 INT, h20 INT, h21 INT, h22 INT, 
  h23 INT)";

if (mysqli_query($con,$sql)){
  echo "Table openHours created successfully.<br>";
}
else{
  echo "Error creating table: " . mysqli_error($con) . "<br>";
}

// Populate openHours with initial data (all open)
$sql="load data local infile './testHours.txt' into table openHours 
  fields terminated by ','";

if (mysqli_query($con,$sql)){
  echo "openHours loaded successfully.<br>";
}
else{
  echo "Error loading data: " . mysqli_error($con) . "<br>";
}

// Drop actSchedule if previously there
$sql="drop table if exists actSchedule";
if(mysqli_query($con,$sql)){
  echo "Deleted previously existing actSchedule table <br>";
}

// Create actSchedule
$sql="create table actSchedule (
  PID INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (PID,day),
  day VARCHAR(15), 
  h00 INT, h01 INT, h02 INT, h03 INT, h04 INT, h05 INT, h06 INT, h07 INT, 
  h08 INT, h09 INT, h10 INT, h11 INT, h12 INT, h13 INT, h14 INT, h15 INT, 
  h16 INT, h17 INT, h18 INT, h19 INT, h20 INT, h21 INT, h22 INT, h23 INT)";
  
if (mysqli_query($con,$sql)){
  echo "Table actSchedule created successfully.<br>";
}
else{
  echo "Error creating table: " . mysqli_error($con) . "<br>";
}

// // Populate actSchedule with initial data
// $sql="load data local infile './testMaster.txt' into table actSchedule 
//   fields terminated by ','";

// if (mysqli_query($con,$sql)){
//   echo "actSchedule loaded successfully.<br>";
// }
// else{
//   echo "Error loading data: " . mysqli_error($con) . "<br>";
// }

// Drop tutorComments if previously there
$sql="drop table if exists tutorComments";
if(mysqli_query($con,$sql)){
  echo "Deleted previously existing tutorComments table <br>";
}

// Create tutorComments
$sql="create table tutorComments (
  PID INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (PID),
  comments TEXT(2000))";
  
if (mysqli_query($con,$sql)){
  echo "Table tutorComments created successfully.<br>";
}
else{
  echo "Error creating table: " . mysqli_error($con) . "<br>";
}

// Populate tutorComments with initial data
$sql="load data local infile './testComments.txt' into table tutorComments 
  fields terminated by ','";

if (mysqli_query($con,$sql)){
  echo "tutorComments loaded successfully.<br>";
}
else{
  echo "Error loading data: " . mysqli_error($con) . "<br>";
}

// Drop uGradWeeklyHours if previously there
$sql="drop table if exists uGradWeeklyHours";
if(mysqli_query($con,$sql)){
  echo "Deleted previously existing uGradWeeklyHours table <br>";
}

// Create uGradWeeklyHours
$sql="create table uGradWeeklyHours (
  PID INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (PID),
  minHours INT, maxHours INT, idealHours INT)";

if (mysqli_query($con,$sql)){
  echo "Table uGradWeeklyHours created successfully.<br>";
}
else{
  echo "Error creating table: " . mysqli_error($con) . "<br>";
}

// Populate uGradWeeklyHours with initial data
$sql="load data local infile './testWeekHours.txt' into table uGradWeeklyHours 
  fields terminated by ','";

if (mysqli_query($con,$sql)){
  echo "uGradWeeklyHours loaded successfully.<br>";
}
else{
  echo "Error loading data: " . mysqli_error($con) . "<br>";
}

// Drop maxDayHours if previously there
$sql="drop table if exists maxDayHours";
if(mysqli_query($con,$sql)){
  echo "Deleted previously existing maxDayHours table <br>";
}

// Create maxDayHours
$sql="create table maxDayHours (
  PID INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (PID),
  dayHours INT)";

if (mysqli_query($con,$sql)){
  echo "Table maxDayHours created successfully.<br>";
}
else{
  echo "Error creating table: " . mysqli_error($con) . "<br>";
}

// Populate maxDayHours with initial data
$sql="load data local infile './testDayHours.txt' into table maxDayHours 
  fields terminated by ','";

if (mysqli_query($con,$sql)){
  echo "maxDayHours loaded successfully.<br>";
}
else{
  echo "Error loading data: " . mysqli_error($con) . "<br>";
}

mysqli_close($con);
?>

