	<div id = 'tutor_select'>
		<?php
		$result = mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `type` != 'admin' ORDER BY `Lname`, `Fname`");
		while($next_tutor = mysqli_fetch_array($result)) {
			echo "<span class='nav' data-pid='".$next_tutor['PID']."' data-fname='".$next_tutor['Fname']."' data-lname='".$next_tutor['Lname']."' data-type='".$next_tutor['type']."'>".$next_tutor['Lname'].", ".$next_tutor['Fname']."</span><br>\n";
		}
		?>	
	</div>
	<div id= 'tutor_container'>
		<div id = 'tutor_requests'></div>
		<div id = 'tutor_schedule'></div>
		<div id = 'tutor_comments'></div>
	</div>
	<?php	
	//echo all the tables full of each tutor's requests
	$result = mysqli_query($con, "SELECT `PID` FROM `employeeInfo` WHERE `type` != 'admin'");
	while($next_tutor = mysqli_fetch_array($result)) {
		$tutor_hours = mysqli_query($con, "SELECT * FROM `hoursByDay` WHERE `PID` = '".$next_tutor['PID']."'");
		if($tutor_hours)
			echo "<table class='database_requests' data-pid='".$next_tutor['PID']."' hidden><tbody>\n";
		while($row = mysqli_fetch_array($tutor_hours)) {
			echo "<tr class='".$row[1]."'>\n";
			for($i=7; $i<24; $i++) {
				if($i < 10) {
					echo "<td class='0".($i)." na'>".$row[$i+2]."</td>\n";
				}else echo "<td class='".($i)." na'>".$row[$i+2]."</td>\n";
			}
			echo "</tr>\n";
		}
		echo "</tbody></table>\n";
	}
	?>
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
		</tbody>
	</table>
</form>