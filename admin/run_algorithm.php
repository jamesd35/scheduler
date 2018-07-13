<?php
/*
    Authors: Ibrahim Hawari, Cenk Baykal
    Interface to specify the constraints and run the algorithm.
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

        <title>Run Algorithm</title>

        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- MetisMenu CSS -->
        <link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="css/sb-admin-2.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="./../common/stylesheet.css">

        <!-- Custom Fonts -->
        <link href="font-awesome-4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">

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

            <div id="page-wrapper">
                <div class="panel panel-default" id="algorithm-control">
                    <div class="panel-heading">
                        Control Algorithm Execution
                    </div>
                    <div class="panel-body">
                        <!-- /.row (nested) -->
                        <!-- <button type="button" class="btn btn-primary" id="best-schedule">Current Best Schedule</button> -->
                        <button type="button" class="btn btn-primary" id="save-schedule">Save Selected Schedule To Database</button>
                        <button type="button" class="btn btn-danger" id="stop">Stop Algorithm</button>
                        <ul class="nav nav-tabs nav-stacked" id="schedules">
                        </ul>

                    </div>
                <!-- /.panel-body -->
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-12">
                        <form role="form" id="constraints-form">
                            <br>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Run algorithm for which group of tutors?
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="disabledSelect">Select group</label>
                                                <select id="studentGroup" class="form-control">
                                                    <option>All</option>
                                                    <option>Undergraduates</option>
                                                    <option>Graduate Students</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- /.col-lg-6 (nested) -->
                                    </div>
                                    <!-- /.row (nested) -->
                                    <button type="button" class="btn btn-danger" id="b0">Disable</button>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Minimum number of tutors per hour
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="disabledSelect">Enter minimum</label>
                                                <input class="form-control" id="minNum" type="text" value="2">
                                            </div>
                                        </div>
                                        <!-- /.col-lg-6 (nested) -->
                                    </div>
                                    <!-- /.row (nested) -->
                                    <button type="button" class="btn btn-danger" id="b1">Disable</button>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Maximum number of tutors per hour
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="disabledSelect">Enter maximum</label>
                                                <input class="form-control" id="maxNum" type="text" value="10">
                                            </div>
                                        </div>
                                        <!-- /.col-lg-6 (nested) -->
                                    </div>
                                    <!-- /.row (nested) -->
                                    <button type="button" class="btn btn-danger" id="b2">Disable</button>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                            <div class="panel panel-default" id="addConstraints">
                                <div class="panel-heading">
                                    Timeslot properties
                                </div>
                                <div class="panel-body">
                                    <div class="row titles">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="disabledSelect">Select day</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="disabledSelect">Start time</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="disabledSelect">End time</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="disabledSelect">Enter minimum tutors</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="disabledSelect">Enter maximum tutors</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row timeRow">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <select id="daySlot" class="form-control">
                                                    <option>Sunday</option>
                                                    <option>Monday</option>
                                                    <option>Tuesday</option>
                                                    <option>Wednesday</option>
                                                    <option>Thursday</option>
                                                    <option>Friday</option>
                                                    <option>Saturday</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <select id="timeSlot" class="form-control">
                                                    <option>7:00 AM</option>
                                                    <option>8:00 AM</option>
                                                    <option>9:00 AM</option>
                                                    <option>10:00 AM</option>
                                                    <option>11:00 AM</option>
                                                    <option>12:00 PM</option>
                                                    <option>1:00 PM</option>
                                                    <option>2:00 PM</option>
                                                    <option>3:00 PM</option>
                                                    <option>4:00 PM</option>
                                                    <option>5:00 PM</option>
                                                    <option>6:00 PM</option>
                                                    <option>7:00 PM</option>
                                                    <option>8:00 PM</option>
                                                    <option>9:00 PM</option>
                                                    <option>10:00 PM</option>
                                                    <option>11:00 PM</option>
                                                    <option>12:00 AM</option>
                                                    <option>1:00 AM</option>
                                                    <option>2:00 AM</option>
                                                    <option>3:00 AM</option>
                                                    <option>4:00 AM</option>
                                                    <option>5:00 AM</option>
                                                    <option>6:00 AM</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <select id="timeSlotEnd" class="form-control">
                                                    <option>7:00 AM</option>
                                                    <option>8:00 AM</option>
                                                    <option>9:00 AM</option>
                                                    <option>10:00 AM</option>
                                                    <option>11:00 AM</option>
                                                    <option>12:00 PM</option>
                                                    <option>1:00 PM</option>
                                                    <option>2:00 PM</option>
                                                    <option>3:00 PM</option>
                                                    <option>4:00 PM</option>
                                                    <option>5:00 PM</option>
                                                    <option>6:00 PM</option>
                                                    <option>7:00 PM</option>
                                                    <option>8:00 PM</option>
                                                    <option>9:00 PM</option>
                                                    <option>10:00 PM</option>
                                                    <option>11:00 PM</option>
                                                    <option>12:00 AM</option>
                                                    <option>1:00 AM</option>
                                                    <option>2:00 AM</option>
                                                    <option>3:00 AM</option>
                                                    <option>4:00 AM</option>
                                                    <option>5:00 AM</option>
                                                    <option>6:00 AM</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <input class="form-control" id="minTutors" type="text" placeholder="Enter min">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <input class="form-control" id="maxTutors" type="text" placeholder="Enter max">
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <!--button type="button" class="btn btn-danger" id="b-delete">Delete</button-->
                                            </div>
                                        </div>
                                        <!-- /.col-lg-6 (nested) -->
                                    </div>
                                    <!-- /.row (nested) -->
                                    <button type="button" class="btn btn-primary" id="b3">Add</button>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Graduate weekly hours
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="disabledSelect">Enter hours</label>
                                                <input class="form-control" id="gradHours" type="text" value="14">
                                            </div>
                                        </div>
                                        <!-- /.col-lg-6 (nested) -->
                                    </div>
                                    <!-- /.row (nested) -->
                                    <button type="button" class="btn btn-danger" id="b4">Disable</button>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Maximum hours per day
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="disabledSelect">Enter hours</label>
                                                <input class="form-control" id="maxHours" type="text" value="5">
                                            </div>
                                        </div>
                                        <!-- /.col-lg-6 (nested) -->
                                    </div>
                                    <!-- /.row (nested) -->
                                    <button type="button" class="btn btn-danger" id="b5">Disable</button>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Other constraints
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <!-- /.col-lg-6 (nested) -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="disabledSelect">Nobody works only after</label>
                                                <select id="workAfter" class="form-control">
                                                    <option>5:00 PM</option>
                                                    <option>6:00 PM</option>
                                                    <option>7:00 PM</option>
                                                    <option>8:00 PM</option>
                                                    <option>9:00 PM</option>
                                                    <option>10:00 PM</option>
                                                    <option>11:00 PM</option>
                                                    <option>12:00 AM</option>
                                                    <option>1:00 AM</option>
                                                    <option>2:00 AM</option>
                                                    <option>3:00 AM</option>
                                                    <option>4:00 AM</option>
                                                    <option>5:00 AM</option>
                                                    <option>6:00 AM</option>
                                                    <option>7:00 AM</option>
                                                    <option>8:00 AM</option>
                                                    <option>9:00 AM</option>
                                                    <option>10:00 AM</option>
                                                    <option>11:00 AM</option>
                                                    <option>12:00 PM</option>
                                                    <option>1:00 PM</option>
                                                    <option>2:00 PM</option>
                                                    <option>3:00 PM</option>
                                                    <option>4:00 PM</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- /.col-lg-6 (nested) -->
                                    </div>
                                    <!-- /.row (nested) -->
                                    <button type="button" class="btn btn-danger" id="b6">Disable</button>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="run-algorithm">Run Algorithm</button><br>
                        </form>
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

        <!-- Page-specific JavaScript -->
        <script src="run_algorithm.js"></script>

    </body>

    </html>
