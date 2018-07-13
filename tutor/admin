/*
	WRITTEN BY: Eric JOnes
	Last Edited: 12/8/2013
	This script updates the display, but no user interactio nis available here so nothing else needs to happen
*/
$(document).ready(function() {

	//create the table for viewing current hours
	var table = $("<table></table>");
	var tbody = $("<tbody></tbody>");
	table.append(tbody);
	
	var new_row = $("<tr></tr>");
	var days = new Array("","sun","mon","tue","wed","thu","fri","sat");
	new_row.append("<td class='na'></td><td class='na'>Sunday</td><td class='na'>Monday</td><td class='na'>Tuesday</td>" +
			"<td class='na'>Wednesday</td><td class='na'>Thursday</td><td class='na'>Friday</td><td class='na'>Saturday</td>");
	tbody.append(new_row);
	
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
				
				var val = $("table#tutor_schedule_result td."+new_class).html();
				td.addClass("class_"+val);
				
				//check if the center is closed
				var day = new_class.substring(0,3);
				var hour = new_class.substring(3,5);

				if($("table#hours_database_result tr."+day+" td."+hour+"").html() == 'closed') {
					td.addClass('na');
				}

				new_row.append(td);
			}
		}
		tbody.append(new_row);
	}
	
	$("#view_schedule_div").append(table);
});

var logout = function() {
	window.location = './../common/logout.php';
};
