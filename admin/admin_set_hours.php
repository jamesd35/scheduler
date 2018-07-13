<?php
/*
	WRITTEN BY: Eric Jones
	LAST EDITED: 11/8/2014, by Ibrahim Hawari
	This page is used to set the hours for which the center is open. It works by first checking if the value 'submit_checkbox' was sent to the server, and if so then it assumes you got here
	by submitting new hours. It updates teh database accordingly then loads the page as normal. The current hours are loaded into a hidden table at the bottom of the page, and the associated
	javascript file reads from it and changes the display so that the display matches the database each time the page is loaded.
*/

	include "./../common/session_validator.php";
	$con = getDatabaseConnection();

	$employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".mysqli_real_escape_string($con, $_SESSION['pid'])."'"));

	if($employee_info[3] != 'admin') {
		echo "<script type = 'text/javascript'>location.href='http://$_SERVER[HTTP_HOST]/common/onyen_validator.php'</script>";
		exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Admin Form</title>
	<link rel="stylesheet" type="text/css" href="./../common/stylesheet.css">
	<script type="text/javascript" src="./../common/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="admin_set_hours.js"></script>
	<link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="./../bootstrap/css/simple-sidebar.css" rel="stylesheet">
</head>
<body>


<?php

//IF WE GOT TO THE PAGE VIA THE SUBMIT BUTTON, UPDATE THE DATABASE!
	if($_POST['submit_checkbox']==true) {
		$success = true;

		function getClass($val) {
			if($_POST[$val]) {
				$val = 1;
			}else $val = 0;
	
			return $val;
		}
	
		$day_array = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
	
		for($d=0; $d<7; $d++) {
			$day = $day_array[$d];
			for($i=7; $i<=23; $i++) {
				if($i < 10) {
					mysqli_query($con, "UPDATE `openHours` SET `h0".$i."` = ".getClass($day.'0'.$i.'_val')." WHERE `day`='".mysqli_real_escape_string($con, $day)."'");
					if(mysqli_error($con)) $success = false;
				}else {
					mysqli_query($con, "UPDATE `openHours` SET `h".$i."` = ".getClass($day.$i.'_val')." WHERE `day`='".mysqli_real_escape_string($con, $day)."'");
					if(mysqli_error($con)) $success = false;
				}
			}
		}
		if($success) {
			echo "<div id=success>Successfully Submitted Hours!</div>";
		}else echo "<div id=failure>Failed to Submit Hours! There was a query error.</div>";
	}

	//the go back/logout bar
	echo "<div><strong class='login'>Currently logged in as " . $employee_info[1] . " " . $employee_info[2] . ". <button type='button' onclick='logout()'>Log Out</button></strong>" .
		"<button onclick='goBack()'>Back to Administrator Overview Page</button></div>";

	echo "
	<div id='wrapper'>
		<div id='sidebar-wrapper'>
			<ul class='sidebar-nav'>
				<li class='sidebar-brand'>
					<a href='algorithm.php'>Run Algorithm</a>";
	echo "
				</li>
				<li>
					<a href='admin_set_hours.php'>Set Writing Center Hours</a>
				</li>
				<li>
					<a href='./admin_add_edit_employees.php'>Add/Edit/Remove Employees</a>
				</li>
				<li>
					<a href='./admin_view_requests.php'>Set Schedules</a>
				</li>";
	echo "
				<li>
					<a href='./../common/DBShowPref.php'>View All Preferences</a>
				</li>
				<li>
					<a href='./../common/DBShowActual.php'>View Master Schedule</a><a href='admin_help.html'>Help</a>
				</li>
			</ul>
		</div>";
	echo "
	</div>";

?>

	<form id="set_hours_form" action="admin_set_hours.php" method="POST">
		<div id="admin_div">
		<table id="admin_table" class="table table-bordered">
			<thead>
				<tr>
					<th class="na"></th>
					<th class="na">Sunday</th>
					<th class="na">Monday</th>
					<th class="na">Tuesday</th>
					<th class="na">Wednesday</th>
					<th class="na">Thursday</th>
					<th class="na">Friday</th>
					<th class="na">Saturday</th></tr>
			</thead>
			<tbody>
<!-- 7:00AM HOUR BEGINS HERE -->
				<tr id="07">
					<td class="na"><strong>7:00AM</strong></td>
					<td id="sun07"></td>
					<td id="mon07"></td>
					<td id="tue07"></td>
					<td id="wed07"></td>
					<td id="thu07"></td>
					<td id="fri07"></td>
					<td id="sat07"></td>
				</tr>
<!-- 8:00AM HOUR BEGINS HERE -->
				<tr id="08">
					<td class="na"><strong>8:00AM</strong></td>
					<td id="sun08"></td>
					<td id="mon08"></td>
					<td id="tue08"></td>
					<td id="wed08"></td>
					<td id="thu08"></td>
					<td id="fri08"></td>
					<td id="sat08"></td>
				</tr>
<!-- 9:00AM HOUR BEGINS HERE -->
				<tr id="09">
					<td class="na"><strong>9:00AM</strong></td>
					<td id="sun09"></td>
					<td id="mon09"></td>
					<td id="tue09"></td>
					<td id="wed09"></td>
					<td id="thu09"></td>
					<td id="fri09"></td>
					<td id="sat09"></td>
				</tr>
<!-- 10:00AM HOUR BEGINS HERE -->
				<tr id="10">
					<td class="na"><strong>10:00AM</strong></td>
					<td id="sun10"></td>
					<td id="mon10"></td>
					<td id="tue10"></td>
					<td id="wed10"></td>
					<td id="thu10"></td>
					<td id="fri10"></td>
					<td id="sat10"></td>
				</tr>
<!-- 11:00AM HOUR BEGINS HERE -->
				<tr id="11">
					<td class="na"><strong>11:00AM</strong></td>
					<td id="sun11"></td>
					<td id="mon11"></td>
					<td id="tue11"></td>
					<td id="wed11"></td>
					<td id="thu11"></td>
					<td id="fri11"></td>
					<td id="sat11"></td>
				</tr>
<!-- 12:00PM HOUR BEGINS HERE -->
				<tr id="12">
					<td class="na"><strong>12:00PM</strong></td>
					<td id="sun12"></td>
					<td id="mon12"></td>
					<td id="tue12"></td>
					<td id="wed12"></td>
					<td id="thu12"></td>
					<td id="fri12"></td>
					<td id="sat12"></td>
				</tr>
<!-- 1:00PM HOUR BEGINS HERE -->
				<tr id="13">
					<td class="na"><strong>1:00PM</strong></td>
					<td id="sun13"></td>
					<td id="mon13"></td>
					<td id="tue13"></td>
					<td id="wed13"></td>
					<td id="thu13"></td>
					<td id="fri13"></td>
					<td id="sat13"></td>
				</tr>
<!-- 2:00PM HOUR BEGINS HERE -->
				<tr id="14">
					<td class="na"><strong>2:00PM</strong></td>
					<td id="sun14"></td>
					<td id="mon14"></td>
					<td id="tue14"></td>
					<td id="wed14"></td>
					<td id="thu14"></td>
					<td id="fri14"></td>
					<td id="sat14"></td>
				</tr>
<!-- 3:00PM HOUR BEGINS HERE -->
				<tr id="15">
					<td class="na"><strong>3:00PM</strong></td>
					<td id="sun15"></td>
					<td id="mon15"></td>
					<td id="tue15"></td>
					<td id="wed15"></td>
					<td id="thu15"></td>
					<td id="fri15"></td>
					<td id="sat15"></td>
				</tr>
<!-- 4:00PM HOUR BEGINS HERE -->
				<tr id="16">
					<td class="na"><strong>4:00PM</strong></td>
					<td id="sun16"></td>
					<td id="mon16"></td>
					<td id="tue16"></td>
					<td id="wed16"></td>
					<td id="thu16"></td>
					<td id="fri16"></td>
					<td id="sat16"></td>
				</tr>
<!-- 5:00PM HOUR BEGINS HERE -->
				<tr id="17">
					<td class="na"><strong>5:00PM</strong></td>
					<td id="sun17"></td>
					<td id="mon17"></td>
					<td id="tue17"></td>
					<td id="wed17"></td>
					<td id="thu17"></td>
					<td id="fri17"></td>
					<td id="sat17"></td>
				</tr>
<!-- 6:00PM HOUR BEGINS HERE -->
				<tr id="18">
					<td class="na"><strong>6:00PM</strong></td>
					<td id="sun18"></td>
					<td id="mon18"></td>
					<td id="tue18"></td>
					<td id="wed18"></td>
					<td id="thu18"></td>
					<td id="fri18"></td>
					<td id="sat18"></td>
				</tr>
<!-- 7:00PM HOUR BEGINS HERE -->
				<tr id="19">
					<td class="na"><strong>7:00PM</strong></td>
					<td id="sun19"></td>
					<td id="mon19"></td>
					<td id="tue19"></td>
					<td id="wed19"></td>
					<td id="thu19"></td>
					<td id="fri19"></td>
					<td id="sat19"></td>
				</tr>
<!-- 8:00PM HOUR BEGINS HERE -->
				<tr id="20">
					<td class="na"><strong>8:00PM</strong></td>
					<td id="sun20"></td>
					<td id="mon20"></td>
					<td id="tue20"></td>
					<td id="wed20"></td>
					<td id="thu20"></td>
					<td id="fri20"></td>
					<td id="sat20"></td>
				</tr>
<!-- 9:00PM HOUR BEGINS HERE -->
				<tr id="21">
					<td class="na"><strong>9:00PM</strong></td>
					<td id="sun21"></td>
					<td id="mon21"></td>
					<td id="tue21"></td>
					<td id="wed21"></td>
					<td id="thu21"></td>
					<td id="fri21"></td>
					<td id="sat21"></td>
				</tr>
<!-- 10:00PM HOUR BEGINS HERE -->
				<tr id="22">
					<td class="na"><strong>10:00PM</strong></td>
					<td id="sun22"></td>
					<td id="mon22"></td>
					<td id="tue22"></td>
					<td id="wed22"></td>
					<td id="thu22"></td>
					<td id="fri22"></td>
					<td id="sat22"></td>
				</tr>
<!-- 11:00PM HOUR BEGINS HERE -->
				<tr id="23">
					<td class="na"><strong>11:00PM</strong></td>
					<td id="sun23"></td>
					<td id="mon23"></td>
					<td id="tue23"></td>
					<td id="wed23"></td>
					<td id="thu23"></td>
					<td id="fri23"></td>
					<td id="sat23"></td>
				</tr>
			</tbody>
		</table>
		</div>
		<br>
		<button id="submit" type="submit" onclick="submit_admin()">Submit</button>
		<button id="clear" type="button" onclick="clear_admin()">Clear All Hours</button>
		<button id="reset" type="button" onclick="reset_admin()">Reset</button>

	</form>

<!--Insert table from database-->
<table id="hours_database_result" hidden=true>
<tbody>
<?php
	
	function numToClass($val) {
		if($val==1) { 
			return 'open';
		}else return 'closed';
	}

	if(!$result = mysqli_query($con, "SELECT * FROM `openHours`")){
		echo "Error ";
		echo mysqli_error($con);
	}
	
	//populate the table with the values from the database
	while($row = mysqli_fetch_array($result)) {
		echo "<tr class='".$row[0]."'>";
		for($i=1; $i<18; $i++) {
			if($i < 4) {
				echo "<td class='0".($i+6)."'>".numToClass($row[$i])."</td>";
			}else echo "<td class='".($i+6)."'>".numToClass($row[$i])."</td>";
		}
		echo "</tr>\n";
	}

?>
</tbody>
</table>

</body>
</html>