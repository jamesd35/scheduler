<?php
/*
    WRITTEN BY: Ibrahim Hawari
    LAST EDITED: 12/5/2014, by James Ding
    This page allows administrators to set the writing center's open and closed hours by clicking on cells of a calendar table.
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

        <title>Set Writing Center Hours</title>

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
                        <?php
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
                        ?>
                        <form id="set_hours_form" action="edit_writing_center_hours.php" method="POST">
                                    <br>
                                    <div class="row">
                                     <div class="col-lg-2">
                                     </div>
                                     <div class="col-lg-1">
                                     </div>
                                    <div class="col-lg-2">
                                        <button id="submit" type="submit" class="btn btn-primary" onclick="submit_admin()">Submit</button>
                                    </div>
                                    <div class="col-lg-2">
                                        <button id="clear" type="button" class="btn btn-danger" onclick="clear_admin()">Clear All Hours</button>
                                    </div>
                                    <div class="col-lg-1">
                                     </div>
                                    <div class="col-lg-2">
                                        <button id="reset" type="button" class="btn btn-danger" onclick="reset_admin()">Reset</button>
                                    </div>
                                </div>
                                <br>
                            <div id="admin_div">
                                <table id="admin_table" class="table table-hover">
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
                                <!--/div-->
                                <br>
                            </form>
                            <!--Insert table from database-->
                            <table id="hours_database_result" class="table table-hover" hidden=true>
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
            <script src="edit_writing_center_hours.js"></script>

        </body>

        </html>
