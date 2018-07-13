<!DOCTYPE html>

<?php
error_log("testing", 3, "/var/log/php_errors.log");
	$actual_url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$target_url = "https://$_SERVER[HTTP_HOST]/common/onyen_validator.php";
?>

<html>
<head>
	<meta charset="UTF-8">
	<title>Writing Center</title>
	<link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="./common/stylesheet.css">
	<script type="text/javascript" src="./common/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="index.js"></script>
</head>
<body>
<form id='login_form' role="form" action="https://onyen.unc.edu/cgi-bin/unc_id/authenticator.pl" method='post'>
	<input type="hidden" name="targetpass" class="form-control" value="<?php echo $target_url; ?>">
	<input type="hidden" name="targetfail" value="<?php echo $actual_url; ?>">
	<input type="hidden" name="title" class="form-control" value="Writing Center Login">
	<input type="hidden" name="textpass" class="form-control" value="Successfully logged in. Click to continue.">
	<input type="hidden" name="textfail" value="Failed to log in. Click to return.">
	<input type="hidden" name="getpid" value="pid">
</form>

    <script src="./bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
