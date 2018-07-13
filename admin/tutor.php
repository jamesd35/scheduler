<?php
/*
    WRITTEN BY: Ibrahim Hawari
    LAST EDITED: 12/5/2014, by James Ding
    This page allows admins to select a tutor that they want to "log in" as, so they can see the website from their view.
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

        <title>Tutor Proxy</title>

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

            <!-- Page Content -->
            <div id="page-wrapper">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">Hello,
                                <?php
                                echo $employee_info[1] . "!";
                                ?>
                            </h1>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Select a tutor to enter tutor mode
                                </div>
                                <div class="panel-body">
                                    <form id="select_employee_form" action="tutor_requests.php" method="post">
                                        <div class="row">
                                            <!-- /.col-lg-6 (nested) -->
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <select class="form-control" name='pid'>
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
                                                            if($row[0] != $_SESSION['pid'] && $type != 'Administrator') //make sure this person isn't the current user before adding them to the dropdown
                                                            echo "<option value=".$row[0].">".$row[2].", ".$row[1]." (".$type.")</option>\n";
                                                        }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- /.col-lg-6 (nested) -->
                                </div>
                                <!-- /.row (nested) -->
                                <input type="submit" class="btn btn-primary"></input>
                            </form>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
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
