<?php
/*
    WRITTEN BY: Ibrahim Hawari
    LAST EDITED: 12/5/2014 by James Ding
    Help Page for Tutors
*/

    include "./../common/session_validator.php";
    $con = getDatabaseConnection();
    $employee_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `employeeInfo` WHERE `PID` = '".$_SESSION['fake_pid']."'"));
    if($employee_info[3] == 'admin') {
        echo "<script type = 'text/javascript'>location.href='http://$_SERVER[HTTP_HOST]/common/onyen_validator.php'</script>";
        exit();
    }
?>  

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Tutor Help Page</title>

    <link rel="stylesheet" type="text/css" href="../common/stylesheet.css">

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">


<body>
    <div id="wrapper">
        <?php include "nav.php" ?>
        <!-- Page Content -->
        <div id="page-wrapper">
            <ul class="nav nav-tabs nav-justified">
                        <li>
                            <a href="tutor_requests.php"><i class="fa fa-table fa-fw"></i> Set Hourly Preferences</a>
                        </li>
                        <li>
                            <a href="tutor_hours.php"><i class="fa fa-table fa-fw"></i> View Assigned Hours</a>
                        </li>
                        <li class = "active">
                            <a href="#!"><i class="fa fa-fw"></i> Help</a>
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
       <strong>We strongly recommend that all users use the most recent version of Chrome when using this application.  The most recent versions of Firefox and Internet Explorer should
                        also work, but there are known incompatibilities with older versions and some other browsers.</strong>
        <br>
        <br>
        <a href='#setHours'>Set Hourly Preferences</a>
        <br>
        <a href='#viewHours'>View Assigned Hours</a>
        <br>
        <br>
        <h2 id='setHours'>Set Hourly Preferences</h2>
        <p>
		This page is used to submit your available hours so that the scheduling algorithm and administrators can set your schedule.  Select the availability you wish to apply by clicking
                the radio buttons highlighted in yellow below, then click on a cell in the table to change your availability for that shift.
You can also hold shift and drag your mouse over cells for the same functionality.
The changes you make will not be saved until you click
                'Submit' at the bottom of the page. Clicking 'Clear All' will clear the form so that no shift has any preference associated with it.  Clicking 'Reset' will reset the form to whatever
                your requests were when the page loaded.
        Undergraduates will need to submit their desired ideal, min, and max weekly hours as integers. Both undergrads and grads must submit the max hours they are willing to work in a day (also as an integer).
The comment box can be used to submit any comments you wish the administrators to see when they are making your schedule.  You do not need
                to submit anything, but it may be useful for you.

        </p>
        <p>
                While the scheduling software does its best to give everybody the optimum schedule, please note that there are times
                at which point it will be <em>impossible</em> for us to keep everybody happy. The schedule you are about to submit
                is a <strong>request,</strong> and in no way guarantees that your assigned shifts will perfectly reflect it.
        </p>
        <img src='../tutor/pics/setHours.png' alt='Set Hours Image' width='550' height='400'>
        <br>
        <a href=''>Top</a>
        <br>
        <br>
        <br>
	<h2 id='viewHours'>View Assigned Hours</h2>
	<p>
		This page is used to view the hours that you have been assigned by the scheduling algorithm and the administrators.
	</p>
	<img src='../tutor/pics/viewHours.png' alt='Set Hours Image' width='550' height='400'>
	<br>
	<a href=''>Top</a>
	<br>
	<br>
	<br>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

</body>

    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/plugins/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/sb-admin-2.js"></script>

</html>
