/*
	WRITTEN BY: James Ding
	LAST EDITED: 12/2/2014
	THis script uses the hidden tables to fill out the visible table and to update the display to user interaction. It also validates that each cell has been selected before allowing
	the requests to be submitted.
*/
$(document).ready(function () {

	$('#success').delay(10000).fadeOut(1000);
	
	
	//First, we'll select every <td> EXCEPT for those with the 'na' class. We'll add a dropdown to each.	
	$("table#requests_table td").not(".na").each(function(i, e) {
		td = $(e);
		var name = td.attr("id");
		var dropdown = $("<select></select>");
		dropdown.attr("name",name+"_val");
		dropdown.append("<option value='0'>Busy</option>" +
				"<option value='1'>Prefer Not</option>" +
				"<option value='2'>Can Work</option>" +
				"<option value='3'>Perfect</option>");
		dropdown.hide();
		
		//now the dropdown is in place and has a name value of [td id]_val, as in mon09_val
		td.append(dropdown);
		dropdown.val(null);
		
		//now let's check the database to see if it's closed during this shift
		var day = name.substring(0,3);
		var hour = name.substring(3,5);
		if($("table#hours_database_result tr."+day+" td."+hour+"").html() == 'closed') {
			td.addClass('na');
			dropdown.val(0);
		}
		
		//Finally, we'll fill it with the value from the database if we aint closed
		if(!td.hasClass('na'))
			fill_table(td);
	});

	//Next we'll set up a handler for when the submit button is clicked, to check that every cell has been filled
	$('#request_form').submit(function(event) {
		//check that every select has been picked and set to a value:
		var complete = true;
		$('select').each(function(i, e) {
			if($(e).val() == null) {
				complete = false;
			}
		});
		if(!complete) {
			alert('You need to completely fill out the form!');
			event.preventDefault();
		}
	});
	
	//add a checkbox that will automatically check if we click the submit button, so the php script won't accidentally set the database values if we didn't mean to
	var checkbox = $("<input type='checkbox'>");
	checkbox.attr("name","submit_checkbox");
	checkbox.attr("id","submit_box_id");
	checkbox.hide();
	$('#request_form').append(checkbox);

	//Now, we'll set up an event handler for a click on every cell in the table EXCEPT for headers, times down the left,
	//	and N/A blocks where the center is not open
	$('td').not('.na').click(clicked);
	$('td').not('.na').mouseover(shiftover); //event handler for holding shift and mousing over a cell
});

var clicked = function(event) {
	var cell = $(this);
	var availability = $("input[name='avail']:checked").val();
	var avail_num = null;
	
	//Change the class(and hence the color) of the cell based on the availability the user has selected. We'll also
	//	translate the availability value into its equivalent number to facilitate setting the cell value later.  We'll
	//	have to remove all the other class tags (in case we're changing a cell from one class to another) and then toggle
	//	the appropriate availability so it'll turn on if it's currently off and turn off if it's currently on.
	if(availability=='busy') {
		cell.removeClass('prefer_no can perfect');
		avail_num = 0;
	}else if(availability=='prefer_no') {
		cell.removeClass('busy can perfect');
		avail_num = 1;
	}else if(availability=='can') {
		cell.removeClass('busy prefer_no perfect');
		avail_num = 2;
	}else if(availability=='perfect') {
		cell.removeClass('busy can prefer_no');
		avail_num = 3;
	}
	
	cell.toggleClass(availability);
	
	//now it's time to change the actual value contained by the cell
	if(cell.hasClass(availability))				//meaning we just turned ON the selection
		cell.find('select').val(avail_num);
	else cell.find('select').val(null);			//meaning we just deselected it
};

var shiftover = function(event) {
	if (event.shiftKey) {

        var cell = $(this);
        var availability = $("input[name='avail']:checked").val();
        var avail_num = null;

        //Change the class(and hence the color) of the cell based on the availability the user has selected. We'll also
        //      translate the availability value into its equivalent number to facilitate setting the cell value later.  We'll
        //      have to remove all the other class tags (in case we're changing a cell from one class to another) and then toggle
        //      the appropriate availability so it'll turn on if it's currently off and turn off if it's currently on.
        if(availability=='busy') {
                cell.removeClass('prefer_no can perfect');
                avail_num = 0;
        }else if(availability=='prefer_no') {
                cell.removeClass('busy can perfect');
                avail_num = 1;
        }else if(availability=='can') {
                cell.removeClass('busy prefer_no perfect');
                avail_num = 2;
        }else if(availability=='perfect') {
                cell.removeClass('busy can prefer_no');
                avail_num = 3;
        }

        cell.toggleClass(availability);

        //now it's time to change the actual value contained by the cell
        if(cell.hasClass(availability))                         //meaning we just turned ON the selection
                cell.find('select').val(avail_num);
        else cell.find('select').val(null);                     //meaning we just deselected it
	}
};

var logout = function() {
	window.location = './../common/logout.php';
};

var submit_requests = function() {
	$('#submit_box_id').prop('checked',true);
};

var clear_requests = function() {
	var r = confirm("Are you sure you want to clear the form?");
	
	if(r) {
		$("table#requests_table td").not(".na").each(function(i, td) {
			var e = $(td);
			e.removeClass();
			e.children('select').val(null);
		});
	}
};

var reset_requests = function() {
	var r = confirm("Are you sure you want to reset the form to the previous submission?");
	
	if(r) {
		$("table#requests_table td").not(".na").each(function(i, e) {
			fill_table($(e));
		});		
	}	
};

//fill_table takes in a jqueryified cell and checks the database_table to fill out the users view
var fill_table = function(td) {
	var name = td.attr("id");
	
	var day = name.substring(0,3);
	var hour = name.substring(3,5);

	td.removeClass();

	var availability = null;
	var avail_num = $("table#student_database_result tr."+day+" td."+hour+"").html();
	
	//Change the class(and hence the color) of the cell based on the availability the user has selected. We'll also
	//	translate the availability value into its equivalent number to facilitate setting the cell value later.  We'll
	//	have to remove all the other class tags (in case we're changing a cell from one class to another) and then toggle
	//	the appropriate availability so it'll turn on if it's currently off and turn off if it's currently on.
	if(avail_num==0) {
		availability = 'busy';
	}else if(avail_num==1) {
		availability = 'prefer_no';
	}else if(avail_num==2) {
		availability = 'can';
	}else if(avail_num==3) {
		availability = 'perfect';
	}
	
	td.addClass(availability);
	td.children('select').val(avail_num);
};
