
// Authors: Cenk Baykal, Ibrahim Hawari

// For retrievel of the time now.
Date.prototype.timeNow = function () {
     return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ 
     ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ 
     ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
}


$(document).ready(function() {
	// studentGroup
	$("#b0").click(function() {
		if ($(this).hasClass("btn-danger")) {
			$("#studentGroup").prop("disabled", true);
			$(this).attr("class", "btn btn-success");
			$(this).html("Enable");
		} else {
			$("#studentGroup").prop("disabled", false);
			$(this).attr("class", "btn btn-danger");		
			$(this).html("Disable");
		}
	});
	// minNum
	$("#b1").click(function() {
		if ($(this).hasClass("btn-danger")) {
			$("#minNum").prop("disabled", true);
			$(this).attr("class", "btn btn-success");
			$(this).html("Enable");
		} else {
			$("#minNum").prop("disabled", false);
			$(this).attr("class", "btn btn-danger");		
			$(this).html("Disable");
		}
	});
	// maxNum
	$("#b2").click(function() {
		if ($(this).hasClass("btn-danger")) {
			$("#maxNum").prop("disabled", true);
			$(this).attr("class", "btn btn-success");
			$(this).html("Enable");
		} else {
			$("#maxNum").prop("disabled", false);
			$(this).attr("class", "btn btn-danger");		
			$(this).html("Disable");
		}
	});
	// daySlot, timeSlot, minTutors, maxTutors
    $("#b3").on('click', function (e) {
    	e.preventDefault();
    	var $self = $(this);
    	$self.before($self.prev('div').clone());
    	//$self.remove();
	});

	// gradHours
	$("#b4").click(function() {
		if ($(this).hasClass("btn-danger")) {
			$("#gradHours").prop("disabled", true);
			$(this).attr("class", "btn btn-success");
			$(this).html("Enable");
		} else {
			$("#gradHours").prop("disabled", false);
			$(this).attr("class", "btn btn-danger");		
			$(this).html("Disable");
		}
	});
	// maxHours
	$("#b5").click(function() {
		if ($(this).hasClass("btn-danger")) {
			$("#maxHours").prop("disabled", true);
			$(this).attr("class", "btn btn-success");
			$(this).html("Enable");
		} else {
			$("#maxHours").prop("disabled", false);
			$(this).attr("class", "btn btn-danger");		
			$(this).html("Disable");
		}
	});
	// workAfter
	$("#b6").click(function() {
		if ($(this).hasClass("btn-danger")) {
			$("#workAfter").prop("disabled", true);
			$(this).attr("class", "btn btn-success");
			$(this).html("Enable");
		} else {
			$("#workAfter").prop("disabled", false);
			$(this).attr("class", "btn btn-danger");		
			$(this).html("Disable");
		}
	});

	var algorithmManager;

	// The essential Run Algorithm component when the submit button is pressed.
	$('#run-algorithm').click(function() {
		// console.log('Clicked run algorithm!');
		algorithmManager = new AlgorithmManager();
		algorithmManager.RunAlgorithm();
		$('div.row').slideUp(1000, function() {
			$('#algorithm-control').fadeIn(1000);
		});
		return false;
	});

	$('#schedules').on('click', 'li', function() {
		$('#schedules li.active').removeClass('active');
		$(this).addClass('active');
		var schedule = JSON.parse($(this).data('schedule'));
		console.log(schedule);
		algorithmManager.DisplaySchedule(schedule);
	});


	$('#save-schedule').click(function() {
		var schedule = JSON.parse($('#schedules li.active').data('schedule'));
		console.log('In save schedule');
		$('#save-schedule').append(" <i class='fa fa-spinner fa-spin' id = 'spinner'></i>");
		algorithmManager.UpdateSchedule(schedule);
	});

	$('#stop').click(function() {
		algorithmManager.StopAlgorithm();
		alert('Stopped algorithm. Please click on Run Algorithm to restart the algorithm.');
	});
});

// Helper class (struct) to store constraints.
function Constraint(min, max) {
	this.min = min;
	this.max = max;
}

function populateConstraintProperty(obj, element, name) {
	if (!element.is(":disabled")) {
		obj[name] = element.val();
	}
}

function toMilitaryTime(str) {
  var am = str.indexOf('AM') > -1;
  str = am ? str.replace("AM", "") : str.replace("PM", "");

  // Remove :00.
  str = str.replace(":00", "");
  var val = parseInt(str);
  
  if (am && val === 12) {
    val = 0;
  } else if (!am) {
    if (val != 12) {
      val += 12;
    }
  }

  // Return the time in military time.
  return val;
}

function populateAdditionalConstraints(obj) {
	obj.addnConstraints = {};
	var days = {
		'Monday': 'mon',
		'Tuesday': 'tue',
		'Wednesday': 'wed',
		'Thursday': 'thu',
		'Friday': 'fri',
		'Saturday': 'sat',
		'Sunday': 'sun'
	};
	
	$('#addConstraints .timeRow').each(function() {
		var day = days[$(this).find('#daySlot option:selected').text()];
		var timeStart = 
			toMilitaryTime($(this).find('#timeSlot option:selected').text());
		var timeEnd = 
			toMilitaryTime($(this).find('#timeSlotEnd option:selected').text());
		var min = parseInt($(this).find('#minTutors').val());
		var max = parseInt($(this).find('#maxTutors').val());

		if (!isNaN(min) && !isNaN(max)) {
			if (!obj.addnConstraints[day])
				obj.addnConstraints[day] = {};

			for (var hour = timeStart; hour <= timeEnd; ++hour) {
				var h = hour < 10 ? 'h0' + hour : 'h' + hour;
				console.log(h);
				obj.addnConstraints[day][h] = new Constraint(min, max);
			}
		}

		// console.log(days[day]);
		// console.log(timeStart);
		// console.log(timeEnd);
		// console.log(toMilitaryTime(timeStart));
		// console.log(toMilitaryTime(timeEnd));
		// console.log(min);
		// console.log(max);
	});

	return true;
}

function ConstraintsToJSON() {
	var object = {};
	if (!populateAdditionalConstraints(object))
		return false;

	if (!$('#studentGroup').is(":disabled")) {
		object['group'] = $('#studentGroup option:selected').text();
	}

	if (!$('#workAfter').is(":disabled")) {
		object['noWorkAfter'] = $('#workAfter option:selected').text();
	}

	populateConstraintProperty(object, $('#minNum'), 'minTutorsPerHour');
    populateConstraintProperty(object, $('#maxNum'), 'maxTutorsPerHour');
	populateConstraintProperty(object, $('#gradHours'), 'graduateHours');
	populateConstraintProperty(object, $('#maxHours'), 'maxHoursPerDay');
	console.log(object);
	return object;
}

function PopulateProblemInstance(problemInstance, callback) {
	problemInstance.constraints = ConstraintsToJSON();

	if (!problemInstance.constraints)
		return false;
	// Make the AJAX call here.
	$.ajax({
		url: "database.php",
		type: "GET",
		data: 'problemInstance',
		success: function(data) {
			console.log(data);
			problemInstance.tutors = data.tutors;
			problemInstance.openHours = data.openHours;
			problemInstance.numOpenHours = data.numOpenHours;
			// Now run the algorithm.
			callback();
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert('Sorry, there was an error in running the algorithm. \n' + 
				  xhr.responseText);
		}
	});
}

// Main class to handle the running of the scheduling algorithm.
function AlgorithmManager() {
	this.schedules = [];
	this.currentSchedule = {};
	this.bestSchedule = {};
	this.workerPool = [];
	this.bestScheduleQuality = Number.NEGATIVE_INFINITY;
	this.preferenceSetting = 2;
	this.bestScheduleWorkerId = 0;
	this.interval = null;
	this.timeOut = null;
}

// Function that invokes as soon as the algorithm finds a new schedule. The function
// adds the new schedule to the user interface and displays it to the user.
AlgorithmManager.prototype.OnNewScheduleFound = function() {
	// Deep copy.
	this.currentSchedule = jQuery.extend(true, {}, this.bestSchedule);
	this.bestSchedule = { };
	this.schedules.push(this.currentSchedule);
	var scheduleNum = this.schedules.length;

	var currentDate = new Date();
	var $li = $('<li class="active"></li>');
	var time = currentDate.timeNow();
	var $a = $('<a>' + 'Schedule #' + scheduleNum + ' ' + time + 
				' (Schedule Quality: ' + this.bestScheduleQuality.toFixed(3) + ')</a>');
	$li.data('schedule', JSON.stringify(this.currentSchedule));

	$('#schedules li.active').removeClass('active');
	$('#schedules').append($li.append($a));

	this.DisplaySchedule(this.currentSchedule);
}

// Returns a worker that will handle the execution of the scheduling algorithm
// on a separate thread.
AlgorithmManager.prototype.InitializeWorker = function(problemInstance, id) {
	// Initialize the worker (new thread).
	var newWorker = new Worker('algorithm.js');
	var that = this;
	newWorker.identification = id;
	problemInstance.preferenceSetting = this.preferenceSetting;
	// Attach the handler of what happens when a new best schedule is found.
	newWorker.addEventListener('message', function(e) {
		// Update the current best schedule.
		var msg = JSON.parse(e.data);
		if (msg.quality.quality > that.bestScheduleQuality) {
			console.log('New best schedule found with quality: ', msg.quality.quality);
			console.log(msg.quality);
			that.bestScheduleQuality = msg.quality.quality;
			var schedule = msg.schedule;
			that.bestSchedule = schedule;
			that.bestScheduleWorkerId = this.identification;	
			that.OnNewScheduleFound();
		}
	}, false);

	return newWorker;
}

// Main function that is invoked when the user clicks the run algorithm
// button.
AlgorithmManager.prototype.RunAlgorithm = function() {
	// Number of threads to use (multithreaded optimization).
	var kNumThreads = 3;

	// Number of seconds interval until a random restart occurs.
	var kNumSeconds = 10;

	// Number of seconds until the algorithm tries to decrement the preference
	// level to achieve the result.
	var kNumSecondsBeforeDecrement = 60;

	// Variable for the problem instance.
	var problemInstance = {};
	var that = this;
	PopulateProblemInstance(problemInstance, function() {
		// Once the problem instance is populated, initialize a thread pool
		// and launch the algorithm on separate threads in a multithreaded
		// fashion.
		for (var i = 0; i < that.workerPool.length; ++i) {
			that.workerPool[i].terminate;
		}

		that.workerPool = [];
		for (var i = 0; i < kNumThreads; ++i) {
			var newWorker = that.InitializeWorker(problemInstance, i);
			that.workerPool.push(newWorker);
			
			// Start the worker.
			newWorker.postMessage(JSON.stringify(problemInstance));
		}

		// Random restarts every 20 seconds depending on performance of algorithm.
		var randNumber = Math.random()*kNumSeconds*1000;

		that.interval = setInterval(function() {
			//console.log('Random restart');
			var randWorker = that.bestScheduleWorkerId;
			while (randWorker == that.bestScheduleWorkerId) {
				randWorker = Math.floor(Math.random()*that.workerPool.length);
			}

			that.workerPool[randWorker].terminate();

			var newWorker = that.InitializeWorker(problemInstance, randWorker);
			that.workerPool[randWorker] = newWorker;

			// Start the worker.
			newWorker.postMessage(JSON.stringify(problemInstance));
			//that.RunAlgorithm();
		}, randNumber);

		// If a schedule was still not found after some time, then decrement
		// the preference level of the algorithm.
		that.timeOut = setTimeout(function() { 
			if (that.bestScheduleQuality <= Number.NEGATIVE_INFINITY) {
				console.log('No schedule found still, downgrading preference level');
				for (var i = 0; i < that.workerPool.length; ++i) {
					that.workerPool[i].terminate();
				}
				that.workerPool = [];
				that.preferenceSetting = Math.max(that.preferenceSetting - 1, 1);
			}
		}, kNumSecondsBeforeDecrement*1000);
	});
}

AlgorithmManager.prototype.StopAlgorithm = function() {
	console.log('Stopping algorithm');
	if (this.interval) {
		clearInterval(this.interval);
	}

	if (this.timeout) {
		clearTimeout(this.timeout);
	}

	for (var i = 0; i < this.workerPool; ++i) {
		this.workerPool[i].terminate();
	}

	this.workerPool = [];
}

AlgorithmManager.prototype.UpdateSchedule = function(schedule) {
	$.ajax({
		url: "update_database.php",
		type: "POST",
		data: {schedule: schedule},
		success: function(data) {
			alert('Sucessfully saved the schedule!');
			//DisplaySchedule(data);
			$('#spinner').remove();
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert('Sorry, there was an error in saving the schedule. \n' + 
				  xhr.responseText);
			$('#spinner').remove();
		}
	});
}

AlgorithmManager.prototype.DisplaySchedule = function(schedule) {
	var days = ['', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
	var hours = [];
  	for (var i = 0; i <= 23; ++i) {
    	var str = i < 10 ? "h0" : "h";
    	str += i;
    	hours.push(str);
  	}

  		//create the table for viewing current hours
	var table = $("<table></table>");
	var tbody = $("<tbody></tbody>");
	table.append(tbody);
	
	var new_row = $("<tr></tr>");
	new_row.append("<td class='na'></td><td class='na'>Monday</td><td class='na'>Tuesday</td>" +
			"<td class='na'>Wednesday</td><td class='na'>Thursday</td><td class='na'>Friday</td><td class='na'>Saturday</td><td class='na'>Sunday</td>");
	tbody.append(new_row);
	
	var addedOne = false;
	for(var row = 7; row < 24; row++) {
		var hour = row < 10 ? 'h0' + row : 'h' + row;
		var new_row = $("<tr></tr>");
		for(var column = 0; column <= 7; column++) {
			if(column == 0) {
				var time = row;
				if(time > 12) {
					time -= 12;
					time += ":00PM";
				} else if (time == 12) {
					time += ":00PM";
				} else time+= ":00AM";
				new_row.append("<td class = 'na'><strong>"+time+"</strong></td>");
			} else {
				var td = "<td>Writing Center Closed</td>";
				if (schedule.hasOwnProperty(days[column]) &&
					schedule[days[column]].hasOwnProperty(hour)) {
					addedOne = true;
					var txt = "";

					// Rearrange the tutors so that the graduate tutors come first.
					// Graduate students are in parenthesis (FirstName LastName).
					for (var i = 0; i < schedule[days[column]][hour].length; ++i) {
						var tutor = schedule[days[column]][hour][i];
						if (tutor.type == 'grad')
							txt += '<p style="margin:2px;">(' + tutor.firstName +' ' + tutor.lastName + ')</p>';
					}

					// Then add the undergrads to the list.
					for (var i = 0; i < schedule[days[column]][hour].length; ++i) {
						var tutor = schedule[days[column]][hour][i];
						if (tutor.type == 'ugrad')
							txt += '<p style="margin:2px;">' + tutor.firstName +' ' + tutor.lastName + '</p>';
					}

					txt = '<td>' + txt + '</td>';
					td = $(txt);
				}

				new_row.append(td);
			}
		}
		tbody.append(new_row);
	}
	
	if (addedOne) {
		var div = $("<div id='view_schedule_div' style='width:auto; border:10px;'></div>");
		div.append(table);
		$('#page-wrapper #view_schedule_div').remove();
		$('#page-wrapper').append(div);
	}
}
