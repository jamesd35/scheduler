<?php
/*
    WRITTEN BY: James Ding
    LAST EDITED: 12/5/2014, by James Ding
    This page displays 'prefer not' tutor preferences.
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

        <title>View Preferences</title>

        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- MetisMenu CSS -->
        <link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="css/sb-admin-2.css" rel="stylesheet">

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
                                <ul class="nav nav-tabs nav-justified">
                    <li>
                        <a href="view_tutors.php">All Preferences</a>
                    </li>
                    <li>
                        <a href="view_tutors3.php">View 'Perfect'</a>
                    </li>
                    <li>
                        <a href="view_tutors2.php">View 'Can Work'</a>
                    </li>
                    <li class="active">
                        <a href="#!">View 'Prefer Not'</a>
                    </li>
                    <li>
                        <a href="view_tutors0.php">View 'Busy'</a>
                    </li>
                </ul>
                <br>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-12">
                        <?php
                        include("../common/DBShowPref1.php");
                        ?>
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
