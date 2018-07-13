	<!DOCTYPE html>
	<html>
	<body>
		<?php

//IF WE GOT TO THE PAGE VIA THE ADD BUTTON, UPDATE THE DATABASE!
		if($_POST['new_pid'] != null) {
			$success = true;
			$unique = true;

			$new_Fname = mysqli_real_escape_string($con, $_POST['new_Fname']);
			$new_Lname = mysqli_real_escape_string($con, $_POST['new_Lname']);
			$new_pid = mysqli_real_escape_string($con, $_POST['new_pid']);
			$new_type = mysqli_real_escape_string($con, $_POST['new_type']);

		//figure out which type they selected so we can set it as the default if the page comes back failed
			switch($new_type) {
				case 'grad':
				$grad_selected = 'selected';
				break;
				case 'ugrad':
				$ugrad_selected = 'selected';
				break;
				case 'admin':
				$admin_selected = 'selected';
				break;
			}

		//check if the given employee is already in the table
			$temp = mysqli_fetch_array(mysqli_query($con, "SELECT `PID` FROM `employeeInfo` WHERE `PID` = '".$new_pid."'"));
			if($temp[0] == $new_pid) {
				$unique = false;
			}
			$temp = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `Lname` = '".$new_Lname."'"));
			if($temp != null && strcasecmp($temp[1], $new_Fname) == 0) {
				$unique = false;
			}

			if(!$unique) {
				echo "<div id=failure>Failed to add ".$new_Fname." ".$new_Lname." to database; duplicate employee!</div>";
			}else {
			//add them to employeeInfo
				mysqli_query($con, "INSERT INTO `employeeInfo` (`PID`, `Fname`, `Lname`, `type`) VALUES ('".$new_pid."','".$new_Fname."','".$new_Lname."','".$new_type."');");
			//add them to hoursByDay if not an admin
				if($new_type != 'admin') {
					mysqli_query($con, "INSERT INTO `hoursByDay` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','sun','-1','-1','-1','-1','-1','-1','-1','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `hoursByDay` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','mon','-1','-1','-1','-1','-1','-1','-1','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `hoursByDay` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','tue','-1','-1','-1','-1','-1','-1','-1','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `hoursByDay` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','wed','-1','-1','-1','-1','-1','-1','-1','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `hoursByDay` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','thu','-1','-1','-1','-1','-1','-1','-1','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `hoursByDay` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','fri','-1','-1','-1','-1','-1','-1','-1','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `hoursByDay` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','sat','-1','-1','-1','-1','-1','-1','-1','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3','3');");
					if(mysqli_error($con)) $success = false;

				//add em into actSchedule
					mysqli_query($con, "INSERT INTO `actSchedule` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','sun','-1','-1','-1','-1','-1','-1','-1','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `actSchedule` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','mon','-1','-1','-1','-1','-1','-1','-1','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `actSchedule` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','tue','-1','-1','-1','-1','-1','-1','-1','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `actSchedule` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','wed','-1','-1','-1','-1','-1','-1','-1','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `actSchedule` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','thu','-1','-1','-1','-1','-1','-1','-1','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `actSchedule` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','fri','-1','-1','-1','-1','-1','-1','-1','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');");
					if(mysqli_error($con)) $success = false;
					mysqli_query($con, "INSERT INTO `actSchedule` (`PID`, `day`, `h00`, `h01`, `h02`, `h03`, `h04`, `h05`, `h06`, `h07`, `h08`, `h09`, `h10`, `h11`, `h12`, 
						`h13`, `h14`, `h15`, `h16`, `h17`, `h18`, `h19`, `h20`, `h21`, `h22`, `h23`) 
					VALUES ('".$new_pid."','sat','-1','-1','-1','-1','-1','-1','-1','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');");
					if(mysqli_error($con)) $success = false;

				//add em into tutorComments
					mysqli_query($con, "INSERT INTO `tutorComments` (`PID`, `comments`) VALUES ('".$new_pid."', 'none.');");
					if(mysqli_error($con)) $success = false;

				//add into uGradWeeklyHours if undergrad
					if($ugrad_selected = 'selected') {
					mysqli_query($con, "INSERT INTO `uGradWeeklyHours` (`PID`, `minHours`, `maxHours`, `idealHours`) VALUES ('".$new_pid."','0','20','10');");
					}

					if(mysqli_error($con)) $success = false;
				//add into maxDayHours
					mysqli_query($con, "INSERT INTO `maxDayHours` (`PID`, `dayHours`) VALUES ('".$new_pid."', '10');");
                                        if(mysqli_error($con)) $success = false;

					if($success) {
						echo "<div id=success>Successfully added ".$new_Fname." ".$new_Lname." to database!</div>";
					}else echo "<div id=failure>Failed to add ".$new_Fname." ".$new_Lname." to database! A query error occured.</div>";
				}

			//reset all the values so the fields are blank again
				$new_Fname = '';
				$new_Lname = '';
				$new_pid = '';
				$grad_selected = '';
				$ugrad_selected = '';
				$admin_selected = '';
			}
		}
//IF WE GOT HERE VIA THE REMOVE BUTTON, REMOVE THE EMPLOYEE!
		if($_POST['delete_pid'] != null) {
			$success = true;

			$delete_pid = mysqli_real_escape_string($con, $_POST['delete_pid']);
			$temp = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".$delete_pid."'"));
			if(mysqli_error($con)) $success = false;

		//remove from employeeInfo
			mysqli_query($con, "DELETE FROM `employeeInfo` WHERE `PID` = '".$delete_pid."'");
			if(mysqli_error($con)) $success = false;
		//remove from hoursByDay
			mysqli_query($con, "DELETE FROM `hoursByDay` WHERE `PID` = '".$delete_pid."'");
			if(mysqli_error($con)) $success = false;
		//remove from actSchedule
			mysqli_query($con, "DELETE FROM `actSchedule` WHERE `PID` = '".$delete_pid."'");
			if(mysqli_error($con)) $success = false;
		//remove from tutorComments
			mysqli_query($con, "DELETE FROM `tutorComments` WHERE `PID` = '".$delete_pid."'");
			if(mysqli_error($con)) $success = false;
		//remove from uGradWeeklyHours
			mysqli_query($con, "DELETE FROM `uGradWeeklyHours` WHERE `PID` = '".$delete_pid."'");
		//remove from maxDayHours
			mysqli_query($con, "DELETE FROM `maxDayHours` WHERE `PID` = '".$delete_pid."'");
                        if(mysqli_error($con)) $success = false;

			if($success) {
				echo "<div id='success'>Successfully removed ".$temp[1]." ".$temp[2]." from the database!</div>";
			}else echo "<div id='failure'>Failed to successfully remove ".$temp[1]." ".$temp[2]." from all tables! An error occured.</div>";
		}


//IF WE GOT HERE VIA SUBMITTING AN EDIT
		if($_POST['edit_pid'] != null) {
			$success = true;

			$edit_pid = mysqli_real_escape_string($con, $_POST['edit_pid']);
			$old_pid = mysqli_real_escape_string($con, $_POST['old_pid']);
			$edit_Lname = mysqli_real_escape_string($con, $_POST['edit_Lname']);
			$edit_Fname = mysqli_real_escape_string($con, $_POST['edit_Fname']);
			$unique = true;

		//check if the given employee is already in the table
			$temp = mysqli_fetch_array(mysqli_query($con, "SELECT `PID` FROM `employeeInfo` WHERE `PID` = '".$edit_pid."' AND `PID` != '".$old_pid."'"));
			if($temp != null) {
				$unique = false;
			}
			$temp = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `Lname` = '".$edit_Lname."' AND `Fname` = '".$edit_Fname.
				"' AND `PID` != '".$old_pid."'"));
			if($temp != null) {
				$unique = false;
			}

			if(!$unique) {
				echo "<div id=failure>Failed to edit ".$edit_Fname." ".$edit_Lname." in database; would create duplicate employee!</div>";
			}else {
			//update employeeInfo
				mysqli_query($con, "UPDATE `employeeInfo` SET `Fname` = '".$edit_Fname."' WHERE `PID` = '".$old_pid."'");
				if(mysqli_error($con)) $success = false;
				mysqli_query($con, "UPDATE `employeeInfo` SET `Lname` = '".$edit_Lname."' WHERE `PID` = '".$old_pid."'");
				if(mysqli_error($con)) $success = false;
				if($_POST['edit_type'] != null) {
					$edit_type = mysqli_real_escape_string($con, $_POST['edit_type']);
					mysqli_query($con, "UPDATE `employeeInfo` SET `type` = '".$edit_type."' WHERE `PID` = '".$old_pid."'");
					if(mysqli_error($con)) $success = false;
				}
				mysqli_query($con, "UPDATE `employeeInfo` SET `PID` = '".$edit_pid."' WHERE `PID` = '".$old_pid."'");
				if(mysqli_error($con)) $success = false;
			//update hoursByDay
				mysqli_query($con, "UPDATE `hoursByDay` SET `PID` = '".$edit_pid."' WHERE `PID` = '".$old_pid."'");
				if(mysqli_error($con)) $success = false;
			//update actSchedule
				mysqli_query($con, "UPDATE `actSchedule` SET `PID` = '".$edit_pid."' WHERE `PID` = '".$old_pid."'");
				if(mysqli_error($con)) $success = false;
			//update tutorComments
				mysqli_query($con, "UPDATE `tutorComments` SET `PID` = '".$edit_pid."' WHERE `PID` = '".$old_pid."'");
				if(mysqli_error($con)) $success = false;

				if($success) {
					echo "<div id=success>Successfully edited ".$edit_Fname." ".$edit_Lname." in database!</div>";
				}else echo "<div id=failure>Failed to edit ".$edit_Fname." ".$edit_Lname." in all tables! There was an error.</div>";
			}
		}
		?>

<br>
		<div id="add_employee">
			<h3>Add Employee</h3>
			<form id="new_employee_form" action="edit_employees.php" method="post">
				<span class="input_span">First Name:</span><input type="text" name="new_Fname" value="<?php echo $new_Fname; ?>">
				<br>
				<span class="input_span">Last Name:</span><input type="text" name="new_Lname" value="<?php echo $new_Lname; ?>">
				<br>
				<span class="input_span">PID:</span><input maxlength="9" type="text" name="new_pid" value="<?php echo $new_pid; ?>">
				<br>
				<span class="input_span">Employee Type:</span><select name="new_type">
				<option <?php echo $grad_selected; ?> value='grad'>Graduate Tutor</option>
				<option <?php echo $ugrad_selected; ?> value='ugrad'>Undergraduate Tutor</option>
				<option <?php echo $admin_selected; ?> value='admin'>Adminstrator</option>
			</select>
			<br>
			<button id="submit" type="submit" class="btn btn-primary">Add</button>
		</form>
	</div>

	<div id="select_employee">
		<h3>Edit Employee</h3>
		<form id="select_employee_form" action="edit_employees.php" method="post">
			<strong>Please note that you cannot edit your own information</strong><br>
			<select name='employee_pid'>
				<?php
				if(!$result = mysqli_query($con, "SELECT * FROM `employeeInfo` ORDER BY `Lname`, `Fname`")) {
					echo mysqli_error($con);
				}
				
				while($row = mysqli_fetch_array($result)) {
					$type;
					switch($row[3]) {
						case 'grad':
						$type = 'Graduate Tutor';
						break;
						case 'ugrad':
						$type = 'Undergraduate Tutor';
						break;
						case 'admin':
						$type = 'Administrator';
						break;
					}
					if($row[0] != $_SESSION['pid'])	//make sure this person isn't the current user before adding them to the dropdown
					echo "<option value=".$row[0].">".$row[2].", ".$row[1]." (".$type.")</option>\n";
				}
				?>
			</select>
			<button submit='submit' class="btn btn-primary">Go</button>
			<br>
		</form>
	</div>
	
	<br>
	<div id="edit_employee" hidden>
		<h2>Edit Employee:</h2>
		<?php
//this stuff only appears when they start editing somebody
		if($_POST['employee_pid'] != null) {

			$employee = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".mysqli_real_escape_string($con, $_POST['employee_pid'])."'"));

			echo "<form id='delete_employee_form' action='edit_employees.php' method='POST'><strong>".$employee[1]." ".$employee[2]."</strong>";
			echo "<input type='hidden' name='delete_pid' value='".$employee[0]."'> <button type='submit' class='btn btn-danger'>Delete Employee</button></form>";

			switch($employee[3]) {
				case 'grad':
				$grad_selected = 'selected';
				break;
				case 'ugrad':
				$ugrad_selected = 'selected';
				break;
			}

			?>
			<br>
			<form id="edit_employee_form" action="edit_employees.php" method="post">
				<input type='hidden' name='old_pid' value='<?php echo $employee[0] ?>'>

				<span class="input_span">First Name:</span><input type="text" name="edit_Fname" value="<?php echo $employee[1]; ?>">

				<span class="input_span">Last Name:</span><input type="text" name="edit_Lname" value="<?php echo $employee[2]; ?>">

				<span class="input_span">PID:</span><input maxlength="9" type="text" name="edit_pid" value="<?php echo $employee[0]; ?>">
				<?php if($employee[3] != 'admin') { ?>
				<span class="input_span">Employee Type:</span><select name="edit_type">
				<option <?php echo $grad_selected; ?> value='grad'>Graduate Tutor</option>
				<option <?php echo $ugrad_selected; ?> value='ugrad'>Undergraduate Tutor</option>
			</select>
			<?php } ?>
			<button id="submit" type="submit" class="btn btn-primary">Submit Information</button>
		</form>
		<br>
		<?php 
	}
	?>
</div>
</body>
</html>
