<?php
/*
	WRITTEN BY: Ibrahim Hawari
	LAST EDITED: 12/5/2014 by James Ding
	This script checks what was posted and if the user just submitted new hours, it updates the database with the new requests and prints the appropriate message. When the page loads it
	prints a table of the tutor's current requests and the center's hours which the javascript will use to fill out the visible table accordingly.
*/

	include "./../common/session_validator.php";
	$con = getDatabaseConnection();
	if (isset($_POST['pid'])) {
			session_start();
			$_SESSION['fake_pid'] = $_POST['pid'];
	}
	$employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".$_SESSION['fake_pid']."'"));
	if($employee_info[3] == 'admin') {
		echo "<script type = 'text/javascript'>location.href='http://$_SERVER[HTTP_HOST]/common/onyen_validator.php'</script>";
		exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Schedule Request</title>
	<link rel="stylesheet" type="text/css" href="./../common/stylesheet.css">

   <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">

</head>
<body>
   <div id="wrapper">
        <?php include "nav.php" ?>
        <!-- Page Content -->
        <div id="page-wrapper">
            <ul class="nav nav-tabs nav-justified">
        		        <li class = "active">
                            <a href="#!"><i class="fa fa-table fa-fw"></i> Set Hourly Preferences</a>
                        </li>
                        <li>
                            <a href="tutor_hours.php"><i class="fa fa-table fa-fw"></i> View Assigned Hours</a>
                        </li>
                        <li>
                            <a href="tutor_help.php"><i class="fa fa-fw"></i> Help</a>
                        </li>
			</ul>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                        <?php
                            echo "<p>Hello, " . $employee_info[1] . "!";
                        ?>
                        </h1>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->


<?php
//IF WE GOT TO THE PAGE VIA THE SUBMIT BUTTON, UPDATE THE DATABASE!
	if($_POST['submit_checkbox']==true) {
		$success = true;
		
		function getVal($val) {
			return $_POST[$val];
		}
		
		$day_array = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
		
		for($d=0; $d<7; $d++) {
			$day = $day_array[$d];
			for($h=7; $h<=23; $h++) {
				if($h<10) {
					mysqli_query($con, "UPDATE `hoursByDay` SET `h0".$h."`='".getVal($day.'0'.$h.'_val')."'"."where `PID` = '".$employee_info[0]."' and `day`='".$day."'");
					if(mysqli_error($con)) $success = false;
				}else {
					mysqli_query($con, "UPDATE `hoursByDay` SET `h".$h."`='".getVal($day.$h.'_val')."'"."where `PID` = '".$employee_info[0]."' and `day`='".$day."'");
					if(mysqli_error($con)) $success = false;
				}
			}
		}
        if($employee_info[3] == 'ugrad') {
		if(ctype_digit(trim($_POST['minH'])) && ctype_digit(trim($_POST['maxH'])) && ctype_digit(trim($_POST['idealH']))) {
                        $minHours = trim($_POST['minH']);
                        $maxHours = trim($_POST['maxH']);
                        $idealHours = trim($_POST['idealH']);
                        if($minHours < $idealHours && $idealHours < $maxHours) {
                                mysqli_query($con, "UPDATE `uGradWeeklyHours` SET `minHours`='".$minHours."' WHERE `PID` = '".$employee_info[0]."'");
                                mysqli_query($con, "UPDATE `uGradWeeklyHours` SET `maxHours`='".$maxHours."' WHERE `PID` = '".$employee_info[0]."'");
                                mysqli_query($con, "UPDATE `uGradWeeklyHours` SET `idealHours`='".$idealHours."' WHERE `PID` = '".$employee_info[0]."'");
                        } else echo "<div id=failure>min hours must be less than ideal, and ideal must be less than max.</div>";
                } else echo "<div id=failure>minH, maxH, and idealH must be integers.</div>";
        }
                if(ctype_digit(trim($_POST['dayH']))) {
                        $dayHours = trim($_POST['dayH']);
                        mysqli_query($con, "UPDATE `maxDayHours` SET `dayHours`='".$dayHours."' WHERE `PID` = '".$employee_info[0]."'");
                } else echo "<div id=failure>dayH must be an integer.</div>";

                if(trim($_POST['comments']) == '') {
                        $comments = 'none.';
                }else $comments = mysqli_real_escape_string($con, trim($_POST['comments']));

                mysqli_query($con, "UPDATE `tutorComments` SET `comments`='".$comments."' WHERE `PID` = '".$employee_info[0]."'");
                if(mysqli_error($con)) $success = false;
                if($success) {
                        echo "<div id=success>Successfully Submitted Requests!</div>";
                }else echo "<div id=failure>Failed to Submit Requests: The Database could not be written to!</div>";
        }

?>
	<form id="request_form" action="tutor_requests.php" method="post">
	
	<p>
	Select one of the preferences below: <br>
		<span class='busy'><input name='avail' value='busy' id='busy_radio' type='radio' checked><label for='busy_radio'>'Busy'</label></span> 
			indicates that you absolutely CANNOT work during the shift that starts at the
			hour listed. Use this option for things like classes, other jobs, etc. Cells marked 'Busy' will be black. <br>
		<span class='prefer_no'><input name='avail' value='prefer_no' id='prefer_no_radio' type='radio'><label for='prefer_no_radio'>'Prefer Not'</label></span> 
			indicates that you <em>could</em> work at this time, but would
			<em>strongly prefer</em> to not have to work if it can be avoided. These cells will be red.<br>
		<span class='can'><input name='avail' value='can' id='can_radio' type='radio'><label for='can_radio'>'Can Work'</label></span> 
			indicates that you can easily work this time and be ok, but it isn't the
			<em>ideal</em> time for you. These cell will be highlighted yellow.<br>
		<span class='perfect'><input name='avail' value='perfect' id='perfect_radio' type='radio'><label for='perfect_radio'>'Perfect'</label></span> 
			indicates that this is the <em>perfect</em> time slot for you to work, and
			you would be happy having a shift at this time. These cells will be displayed in green.
	</p>
Click on cells individually to set them to your selected preference. Clicking on a colored cell will overwrite it with your new preference, or set it to unassigned if you are still on the same preference. (If you select 'Perfect' and click on a 'Busy' cell, it will change to 'Perfect'. But if you click on a 'Perfect' cell, it will become unassigned) <br>
You can also hold shift and drag your mouse over cells to do the same as a click. <br><br>
	

		<div id="request_div">
		<table id="requests_table" class="">
			<thead>
				<tr><th></th>
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
					<td class="na"><strong>12:00AM</strong></td>
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

		<?php
        if($employee_info[3] == 'ugrad') {
                $hours = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `uGradWeeklyHours` WHERE `PID` = '".$employee_info[0]."'"));
		echo "<br>Ideal weekly hours:
                <textarea name='idealH' rows='1' cols='15' maxlength='2'>".$hours[3]."</textarea><br>";
		echo "Min weekly hours: 
                <textarea name='minH' rows='1' cols='15' maxlength='2'>".$hours[1]."</textarea><br>";
		echo "Max weekly hours:
                <textarea name='maxH' rows='1' cols='15' maxlength='2'>".$hours[2]."</textarea><br>";
		}
		$dayHour = mysqli_fetch_array(mysqli_query($con, "SELECT `dayHours` FROM `maxDayHours` WHERE `PID` = '".$employee_info[0]."'"));
        ?>

        Max hours per day: <textarea name='dayH' rows='1' cols='15' maxlength='2'><?php echo $dayHour[0]; ?></textarea><br>

		<br>Type any comments you want the admins to see below:<br>
		<?php 
			$comments_value = mysqli_fetch_array(mysqli_query($con, "SELECT `comments` FROM `tutorComments` WHERE `PID` = '".$employee_info[0]."'"));
		?>
		<textarea name='comments' rows='5' cols='100' maxlength='2000'><?php echo $comments_value[0]; ?></textarea>
		<br>
		<button id="submit" type="submit" onclick="submit_requests()">Submit</button>
		<button id="clear" type="button" onclick="clear_requests()">Clear All</button>
		<button id="reset" type="button" onclick="reset_requests()">Reset</button>
		
	</form>

<!--Insert table from student database-->
<table id="student_database_result" hidden>
<tbody>
<?php
	

	if(!$result = mysqli_query($con, "SELECT * FROM `hoursByDay` WHERE `PID` = ".$employee_info[0])){
		echo "Error ";
		echo mysqli_error($con);
	}
	
	//populate the table with the values from the database
	if($result)
	while($row = mysqli_fetch_array($result)) {
		echo "<tr class='".$row[1]."'>";
		for($i=7; $i<24; $i++) {
			if($i < 10) {
				echo "<td class='0".($i)." na'>".$row[$i+2]."</td>";
			}else echo "<td class='".($i)." na'>".$row[$i+2]."</td>";
		}
		echo "</tr>\n";
	}

?>
</tbody>
</table>

<!--Insert table from open hours database-->
<table id="hours_database_result" hidden>
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
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/plugins/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/sb-admin-2.js"></script>

    <script type="text/javascript" src="tutor_requests.js"></script>

</body>

</html>
