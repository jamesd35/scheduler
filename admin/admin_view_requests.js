/*
	WRITTEN BY: Eric Jones
	LAST EDITED: 12/8/2013
	This is the only AJAX script in the app. When a user clicks on a tutor, it fires off a request to get their information and fills out everything when it returns. Each function should
	be self-explanatory, and the variable names are explicit.
*/
$(document).ready(function() {	
	//create the table for viewing current schedule
	var current_schedule_div = $('div#tutor_schedule');
	
	var table = $("<table id='schedule'></table>");
	var body = $("<tbody></tbody>");
	table.append(body);
	
	var new_row = $("<tr></tr>");
	var days = new Array("","sun","mon","tue","wed","thu","fri","sat");
	new_row.append("<td class='na'></td><td class='na'>Sunday</td><td class='na'>Monday</td><td class='na'>Tuesday</td>" +
			"<td class='na'>Wednesday</td><td class='na'>Thursday</td><td class='na'>Friday</td><td class='na'>Saturday</td>");
	body.append(new_row);
	current_schedule_div.append(table);
	
	for(var row = 7; row < 24; row++) {
		var new_row = $("<tr></tr>");
		for(var column = 0; column <= 7; column++) {
			if(column == 0) {
				var time = row;
				if(time > 12) {
					time -= 12;
					time += ":00PM";
				}else time+= ":00AM";
				new_row.append("<td class = 'na'><strong>"+time+"</strong></td>");
			}else {
				var td = $("<td></td>");
				var new_class = days[column];
				
				if(row < 10) {
					new_class += "0"+row;
				}else new_class += ""+row;
				
				//check if the center is closed
				var day = new_class.substring(0,3);
				var hour = new_class.substring(3,5);
				
				if($("table#hours_database_result tr."+day+" td."+hour+"").html() == 'closed') {
					td.addClass('na');
				}else {
					td.attr('title', '');
					td.attr('data-daytime', new_class);
					td.click(change_schedule);
				}
				new_row.append(td);
			}
		}
		body.append(new_row);
	}
	
	var radio = $("<input name='location' value='0' id='unschedule_radio' type='radio'><label for='unschedule_radio' checked>Unschedule</label>");
	radio.attr('checked',true);
	current_schedule_div.append(radio);
	var radio = $("<span class = 'class_1'><input name='location' value='1' id='sasb_radio' type='radio'><label for='sasb_radio'>SASB</label></span>");
	current_schedule_div.append(radio);
	var radio = $("<span class = 'class_2'><input name='location' value='2' id='green_radio' type='radio'><label for='green_radio'>Greenlaw</label></span>");
	current_schedule_div.append(radio);

	current_schedule_div.append(table);
	
	//fill out all the numbers for each location
	$.ajax('getSasbNumbers.php',
	       {type: 'GET',
		    cache: false,
			success: fillTableSasb,
			error: function () {
				alert('failure');}
			});
	
	
	//set up the nav spans to hide/show the appropriate tutors when clicked
	$("span.nav").click(switch_tutor);
	
});

var change_schedule = function() {
	var pid = $("#tutor_select span.selected_span").attr('data-pid');
	//if they haven't selected a tutor yet, we'll just return immediately
	if(pid == undefined) return;
	
	var daytime = $(this).attr('data-daytime');
	var location = $("input[name='location']:checked").val();
	
	var data_sent = {};
	data_sent.pid = pid;
	data_sent.daytime = daytime;
	data_sent.location = location;

	$.ajax('tutorHours.php',
			{type: 'POST',
			 cache: false,
			 data: data_sent,
			 success: updateScheduleSuccess,
			 error: function (jqxhr, status, error) {
				alert(error);}
			});
};
var updateScheduleSuccess = function(data, status, jqxhr) {
	var cell = $("#schedule td[data-daytime='"+data.daytime+"']");
	
	cell.removeClass('class_-1');
	cell.removeClass('class_0');
	cell.removeClass('class_1');
	cell.removeClass('class_2');
	
	cell.addClass(data.location);
	
	//update the numbers listed in this cell
	cell.empty();
	cell.append("<span class='sasb'>" + data.num_sasb + "</span> : <span class='greenlaw'>" + data.num_green + "</span>");
	
	if(data['tutors_sasb'] == '') data['tutors_sasb'] = 'No SASB Tutors';
	if(data['tutors_green'] == '') data['tutors_green'] = 'No Greenlaw Tutors';
	
	cell.attr('title', data['tutors_sasb'] + "\n" + data['tutors_green']);
};

var switch_tutor = function(event) {
	var tutor_requests_div = $("#tutor_requests");
	tutor_requests_div.empty();
	$('div#tutor_select').children().removeClass('selected_span');
	
	e = $(this);
	e.addClass('selected_span');
	new_pid = e.attr('data-pid');
	var requests_table = $("table.database_requests[data-pid='"+new_pid+"']");
	
	var table = $("<table class='requests' data-pid='"+requests_table.attr('data-pid')+"'></table>");
	var body = $("<tbody></tbody>");
	table.append(body);
	
	var new_row = $("<tr></tr>");
	var days = new Array("","sun","mon","tue","wed","thu","fri","sat");
	new_row.append("<td class='na'></td><td class='na'>Sunday</td><td class='na'>Monday</td><td class='na'>Tuesday</td>" +
			"<td class='na'>Wednesday</td><td class='na'>Thursday</td><td class='na'>Friday</td><td class='na'>Saturday</td>");
	body.append(new_row);
	
	for(var row = 7; row < 24; row++) {
		var new_row = $("<tr></tr>");
		for(var column = 0; column <= 7; column++) {
			if(column == 0) {
				var time = row;
				if(time > 12) {
					time -= 12;
					time += ":00PM";
				}else time+= ":00AM";
				new_row.append("<td class = 'na'><strong>"+time+"</strong></td>");
			}else {
				var td = $("<td></td>");
				var new_class = days[column];
				if(row < 10) {
					new_class += "0"+row;
				}else new_class += ""+row;
				
				//check if the center is closed
				var day = new_class.substring(0,3);
				var hour = new_class.substring(3,5);
				
				if($("table#hours_database_result tr."+day+" td."+hour+"").html() == 'closed') {
					td.addClass('na');
				}else {
					var class_string = num_to_class(requests_table.find("tr."+day+" td."+hour).html());
					td.addClass(class_string);
				}
				
				new_row.append(td);
			}
		}
		body.append(new_row);
	}
	
	var type;
	switch(e.attr('data-type').trim()) {
		case 'ugrad':
			type = 'Undergraduate';
			break;
		case 'grad':
			type = 'Graduate';
			break;
	}
	tutor_requests_div.append("<strong>Requests from: "+e.attr('data-fname')+" "+e.attr('data-lname')+": "+type+"</strong>");
	tutor_requests_div.append(table);
	
	//now we'll fill out the current schedule table with their hours
	var sent_data = {};
	sent_data.tutor = new_pid;
	$.ajax('tutorHours.php',
	       {type: 'GET',
		    data: sent_data,
		    cache: false,
			success: printHours,
			error: function (jqxhr, status, error) {
				$("#schedule td").each(function(i, e) {
					e = $(e);
					e.removeClass('class_-1');
					e.removeClass('class_0');
					e.removeClass('class_1');
					e.removeClass('class_2');
				});
				
				alert(error);}
			});
};

var num_to_class = function(num) {
	switch(num) {
	case '0':
		return 'busy';
	case '1':
		return 'prefer_no';
	case '2':
		return 'can';
	case '3':
		return 'perfect';
	}
	
	//else we hit some error
	return 'error';
};

var fillTableSasb = function(data, status, jqxhr) {
	$("#schedule td").not('.na').each(function(i, e) {
		e = $(e);
		var daytime = e.attr('data-daytime');
		
		var num = data[daytime];
		if(num == undefined) num = 0;
		e.append("<span class='sasb'>"+num+"</span>");
		
		if(data['tutors'][daytime] == undefined) data['tutors'][daytime] = 'No SASB Tutors';
		e.attr('title', 'SASB:');
		e.attr('title', e.attr('title') + data['tutors'][daytime] + "\n");
	});
	
	//now call the greenlaw ones (we call it here so we know it'll always come second)
	$.ajax('getGreenlawNumbers.php',
	       {type: 'GET',
		    cache: false,
			success: fillTableGreenlaw,
			error: function () {
				alert('failure');}
			});
};
var fillTableGreenlaw = function(data, status, jqxhr) {
	$("#schedule td").not('.na').each(function(i, e) {
		e = $(e);
		var daytime = e.attr('data-daytime');
		
		var num = data[daytime];
		if(num == undefined) num = 0;
		e.append(" : <span class='greenlaw'>"+num+"</span>");

		if(data['tutors'][daytime] == undefined) data['tutors'][daytime] = 'No Greenlaw Tutors';
		e.attr('title', e.attr('title') + 'GREENLAW:' + data['tutors'][daytime]);
	});
};

var printHours = function(data, status, jqxhr) {
	//clear out all the previous hours
	$("#schedule td").each(function(i, e) {
		e = $(e);
		e.removeClass('class_-1');
		e.removeClass('class_0');
		e.removeClass('class_1');
		e.removeClass('class_2');
	});
	
	$.each(data, function(i, e) {
		if(i != 'pid') {
			var cell = $("#schedule td[data-daytime='"+i+"']");
			
			if(!cell.hasClass('na')) {
				cell.addClass(e);
			}
		}
	});
	
	//and insert their comments
	var sent_data = {};
	sent_data.tutor = data['pid'];
	$.ajax('tutorComments.php',
			{type: 'GET',
			 data: sent_data,
			 cache: false,
			 success: function(data) {
				var comment_div = $("#tutor_comments");
				comment_div.empty();
				comment_div.append("<strong>Total hours: </strong>");
				comment_div.append(getNumHoursWorking);
				comment_div.append("<strong> Comments: </strong>");
				comment_div.append(data);
			 },
			 error: function(jqxhr, status, error) {alert(error);}
			});

};
var getNumHoursWorking = function() {
	var total = 0;
	$("table#schedule td").each(function(i,e) {
		if($(e).hasClass('class_1') || $(e).hasClass('class_2'))
			total++;
	});
	
	return total;
};

var goBack = function() {
	window.location = './admin.php';
};

var logout = function() {
	window.location = './../common/logout.php';
};
