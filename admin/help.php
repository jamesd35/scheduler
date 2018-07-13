<?php
/*
    WRITTEN BY: James Ding
    LAST EDITED: 12/5/2014, by James Ding
    This is the help page for the administrator's interface.
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
    <html lang="en">

    <head>

    	<meta charset="utf-8">
    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<meta name="description" content="">
    	<meta name="author" content="">

    	<title>Help Page</title>

    	<!-- Bootstrap Core CSS -->
    	<link href="css/bootstrap.min.css" rel="stylesheet">

    	<!-- MetisMenu CSS -->
    	<link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">

    	<!-- Custom CSS -->
    	<link href="css/sb-admin-2.css" rel="stylesheet">

    	<!-- Custom Fonts -->
    	<link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    	<!-- Page specific JavaScript -->
    	<link href="../common/stylesheet.css" rel="stylesheet" type="text/css">

    	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>

    <body>

    	<div id="wrapper">

    		<?php
    		include("nav.php");
    		?>

    		<!-- Page Content -->
    		<div id="page-wrapper">
    			<div class="row">
    				<div class="col-lg-12">
                          <h1><center>UNC Writing Center Scheduler User Guide</center></h1>
                          <br>
                          <br>
                          <strong> Contents: </strong>
                          <br>
                          <strong>&nbsp;&nbsp;&nbsp;&nbsp;1. </strong><a href='#runAlg'>Run Algorithm</a>
                          <br>
                          <strong>&nbsp;&nbsp;&nbsp;&nbsp;2. </strong><a href='#mastSched'>Master Schedule</a>
                          <br>
                          <strong>&nbsp;&nbsp;&nbsp;&nbsp;3. </strong><a href='#manOp'>Manage Open Hours</a>
                          <br>
                          <strong>&nbsp;&nbsp;&nbsp;&nbsp;4. </strong><a href='#manEmp'>Manage Employees</a>
                          <br>
                          <strong>&nbsp;&nbsp;&nbsp;&nbsp;5. </strong><a href='#tutorPref'>Tutor Preferences</a>
                          <br>
                          <strong>&nbsp;&nbsp;&nbsp;&nbsp;6. </strong><a href='#tutorView'>Tutor View</a>
                          <br>
                          <br>
                          <h2 id='runAlg'>Run Algorithm</h2>
                          <p>
                            Running the scheduling algorithm is done in two parts. First, you are to select which constraints to disable and which constraints to enable. You are then given the option of modifying the enabled constraints. These constraints include parameters such as the minimum number of tutors to be assigned to a particular time slot, or the subset of tutors the scheduling algorithm is to be run on (undergraduates, graduate students, etc.). In short, your course of action as an administrator should be as follows:  
                            <br>
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;1. Enable/Disable and modify the constraints in accordance the Writing Center's real-life scheduling constraints.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;2. Click "Run Algorithm".
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;3. The algorithm will produce a list of schedules according to the constraints given. With time, the quality of schedules generated will improve, such that it may be in your best interest to wait a while before choosing to terminate schedule generation.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;4. From the list of schedules generated, select the schedule you desire.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;5. Click "Save Selected Schedule To Database". An indicator will appear to confirm that the schedule is being saved.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;6. Congratulations! The schedule you chose is now the master schedule for the Writing Center.
                            <br>
                            <br>
                            <img src='pics/01.png' alt='01' width='750'>
                        </p>
                        <a href=''>Top</a>
                        <br>
                        <br>
                                                  <h2 id='mastSched'>Master Schedule</h2>
                          <p>
                            Simply put, this page displays a calendar view in which each timeslot is populated by a list tutors assigned by the scheduling algorithm.
                            <br>
                            <br>
                                                        <img src='pics/02.png' alt='02' width='750'>

                        </p>
                        <a href=''>Top</a>
                        <br>
                        <br>
                                                  <h2 id='manOp'>Manage Open Hours</h2>
                          <p>
                            The open hours managemen tool allows you to set which hours the writing center is open. Simply click a cell within the table to toggle the setting for that hour.
                            Click on a white cell to turn it black, or click on a black cell to turn it white.
                            White cells represent open hours, black cells are closed hours.
                                Changes made on this page <strong>WILL NOT BE SAVED</strong> unless you click 'Submit' before leaving the page.
                                The 'Clear All Hours' button will set clear the form completely so that every hour is set to 'Open' (white). The 'Reset' button will reset the form to whatever the hours were when this
                                page was loaded. <strong>Note</strong> that neither of these buttons will make permanent changes, and if you wish to save the changes you still must click 'submit' before leaving
                                the page.
                            <br>
                            <br>
                                                        <img src='pics/03.png' alt='03' width='750'>

                        </p>
                        <a href=''>Top</a>
                        <br>
                        <br>
                                                  <h2 id='manEmp'>Manage Employees</h2>
                          <p>
                            The employee management tool allows you to to add, edit, and remove employees.  The section to the left of the page is used to add an employee. Note that a new employee may share either a first OR last
                                name with any other employee, but <strong>must</strong> have a unique first name and last name combination, ie you may not have two employees named 'John Smith', though you may have
                                multiple named 'John' and multiple named 'Smith'.  'PID' must be a unique 9 digit integer, and must be the employee's UNC PID associated with the employee's ONYEN.  Once you click
                                'add', the employee will be added to the system if no conflicts are found.  The section to the right of the page is used to edit existing employees. First, select an employee from the
                                dropdown list, and press 'Go'.  Note that you cannot edit your own information. This is to prevent accidentally locking yourself out of the system.
                            <br>
                            <br>
                                                        <img src='pics/04.png' alt='04' width='750'>

                        </p>
                        <a href=''>Top</a>
                        <br>
                        <br>
                                                  <h2 id='tutorPref'>Tutor Preferences</h2>
                          <p>
                            The tutor preferences tool allows you to view a calendar populated with tutor preferences at each time slot. 
                            Several options are provided which allow you to filter the calendar view by preference type.
                            <br>
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;1. All Preferences - Each timeslot is populated with the list of all tutors and their preferences.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;2. View 'Perfect' - Each timeslot is populated with a list of only those tutors who designated that timeslot as 'Perfect'.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;3. View 'Can Work' - Each timeslot is populated with a list of only those tutors who designated that timeslot as 'Can Work'.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;4. View 'Prefer Not' - Each timeslot is populated with a list of only those tutors who designated that timeslot as 'Prefer Not'.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;5. View 'Busy' - Each timeslot is populated with a list of only those tutors who designated that timeslot as 'Busy'.
                            <br>
                            <br>
                                                        <img src='pics/05.png' alt='05' width='750'>

                        </p>
                        <a href=''>Top</a>
                        <br>
                        <br>
                                                  <h2 id='tutorView'>Tutor View</h2>
                          <p>
                            The tutor view allows you to manually edit the preferences of each tutor.
                            <br>
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;1. Select an employee from the drop down menu to log in as.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;2. You are now able to modify the chosen tutor's preferences and view their assigned schedule.
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;3. For more help, see the Help page associated with the tutor view.
                            <br>
                            <br>
                                                        <img src='pics/06.png' alt='06' width='750'>
                        </p>
                        <a href=''>Top</a>
                        <br>
                        <br>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->
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

    </body>

    </html>
