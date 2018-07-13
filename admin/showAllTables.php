<?php 
/*
include "./../common/database_validator.php";
$con=getDatabaseConnection();

// Check connection
if (mysqli_connect_errno($con)){
  echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br>";
}
else{
  echo "connected to the database <br>";
}

*/
echo "Before db connect <br>";
$con = mysqli_connect("127.10.98.130","adminR4Im6WI","V16hdDRZ_SGr","tutorScheduler");
// Check connection
if (mysqli_connect_errno($con)){
  echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br>";
}
echo "after db connect <br>";

/* Set database
$sql="use tutorScheduler";
if (!mysqli_query($con,$sql)){
  echo "Error setting database: " . mysqli_error($con) . "<br>";
}
*/
function printTable($theCon, $tableName){
  $sql = ("select * from $tableName"); // Get result of query
  $result = mysqli_query($theCon,$sql);
  $numRows = mysqli_num_rows($result);

  for($i = 0; $i < $numRows; $i++){
    // get the array from result indexed by numbers
    $row = mysqli_fetch_array($result, MYSQLI_NUM); 
    foreach($row as $value){
      printf("%s, ", $value);
    }
    echo("<br>");
  }
}

echo"<h2>hours by day: </h2>";
printTable($con,"hoursByDay");
echo"<br>";

echo"<h2>employee info: </h2>";
printTable($con,"employeeInfo");
echo"<br>";

echo"<h2>open hours: </h2>";
printTable($con,"openHours");
echo"<br>";

echo"<h2>act schedule: </h2>";
printTable($con,"actSchedule");
echo"<br>";

?>
