$(document).ready(function () {
/*
	WRITTEN BY: Eric Jones
	LAST EDITED: 12/8/2013
	This script validates whatever the user enters before allowing the php script to run, and adds the appropiate message (success or error) if the user just updated something
*/


	//fade the banner if we placed one in php
	$('#success').delay(5000).fadeOut(1000);
	$('#failure').delay(5000).fadeOut(1000);

	//handler for when they submit an edit
	$('#edit_employee_form').submit(function(event) {
		if(!validateForm('edit_'))
			event.preventDefault();
	});
	
	//handler for if they try to delete an employee
	$('#delete_employee_form').submit(function(event) {
		var r = confirm('Are you sure you want to delete '+$("input[name='edit_Fname']").val()+" "
				+$("input[name='edit_Lname']").val()+" from the system?");
		if(!r)
			event.preventDefault();
	});
	
	//handler for when they hit add
	$('#new_employee_form').submit(function(event) {
		if(!validateForm('new_')) {
			event.preventDefault();
		}
	});
	
	//make the edit div appear if they're editing an employee
	if($('#edit_employee').html().trim() != "<h2>Edit Employee:</h2>") {
		$('#edit_employee').show();
	}
	
});

//location will be either "new_" or "edit_"
var validateForm = function(location) {
	//first, remove any invalid markings from the last attempt
	$("#"+location+"employee_form").children().each(function(i,e) {
		e = $(e);
		e.removeClass("invalid");
		if(e.hasClass("error_span"))
			this.remove();
	});
	
	//check all the values for validity
	var complete = true;
	
	if($("input[name='"+location+"Fname']").val().trim() == '') {
		complete = false;
		box = $("input[name='"+location+"Fname']");
		box.addClass("invalid");
		box.after("<span class='error_span'>Invalid Name</span>");
	}
	if($("input[name='"+location+"Lname']").val().trim() == '') {
		complete = false;
		box = $("input[name='"+location+"Lname']");
		box.addClass("invalid");
		box.after("<span class='error_span'>Invalid Name</span>");
	}
	
	//Check that PID is a positive number length 9
	new_pid = +$("input[name='"+location+"pid']").val()+0;

	if(isNaN(new_pid) || new_pid < 100000000) {
		complete = false;
		box = $("input[name='"+location+"pid']");
		box.addClass("invalid");
		box.after("<span class='error_span'>Invalid PID</span>");
	}
	
	//if they selected administrator, warn them and make sure they're positive
	if(complete && $("select[name='"+location+"type']").val() == 'admin') {
		var r = confirm('Are you sure you want to add '+$("input[name='"+location+"Fname']").val()+" "
				+$("input[name='"+location+"Lname']").val()+" as an administrator?");
		if(!r)
			complete = false;
	}
	return complete;
};

var goBack = function() {
	window.location = './admin.php';
};

var logout = function() {
	window.location = './../common/logout.php';
};

