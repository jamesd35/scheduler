/*
	WRITTEN BY: Eric Jones
	LAST MODIFIED: 12/5/2014 by James Ding
	This script simply forwards the user to wherever the PHP script of the same name has decided they should go
*/

$(document).ready(function() {
	var target = $('#form_target').html();
	var form = $('form');
	var actual_url = $(location).attr('href');
	
	switch(target) {
	case 'admin':
		form.attr('action', actual_url.replace('common/onyen_validator.php', 'admin/admin.php'));
		break;
	case 'grad':
	case 'ugrad':
		form.attr('action', actual_url.replace('common/onyen_validator.php', 'tutor/tutor.php'));
		break;
	case 'no match':
		alert('Your PID was not found in the database. Please contact your adminstrator.');
		form.attr('action', actual_url.replace('common/onyen_validator.php', 'index.php'));
		break;
	case 'illegal attempt':
		alert('There was an error; the connection may have timed out. Please login again.');
		form.attr('action', actual_url.replace('common/onyen_validator.php', 'index.php'));
		break;
	}
	
	form.trigger('submit');
});
