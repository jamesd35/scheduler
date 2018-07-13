
// ***********************************
// Miscellaneous functions
// ***********************************
// Shuffle the array so that it has the elements in a randomly sorted.
function Shuffle(o) {
	for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
}

// Simple struct in order to represent a time slot in the calendar.
function TimeSlot(day, hour) {
	this.day = day;
	this.hour = hour;
}

// Helper class that is in charge of checking the validity of schedules, 
// solutions, and tutor arrangements. Enforces hour constraints such as min and
// max bounds on the number of tutors per time slot, one hour on the evening,
// etc.
function ValidityChecker(constraints, numOpenHours, openHours) {
	// Initializaiton of class variables.
	// Essentially, the initializaiton entails converting the DISABLED constraints
	// into numeric values. For instance, if there is no maximum imposed on a
	// number, then we can take that number to be POSTIVE_INFINITY. If there is
	// no minimum imposed, then the minimum is 0.
	this.constraints = constraints;
	this.group = constraints.hasOwnProperty('group') ? constraints.group : null;
	this.minTutorsPerHour = constraints.hasOwnProperty('minTutorsPerHour') ? 
							parseInt(constraints.minTutorsPerHour) : 0;
	this.maxTutorsPerHour = constraints.hasOwnProperty('maxTutorsPerHour') ? 
							parseInt(constraints.maxTutorsPerHour) : Number.POSITIVE_INFINITY;
	this.graduateHours = constraints.hasOwnProperty('graduateHours') ? 
							parseInt(constraints.graduateHours) : Number.POSITIVE_INFINITY;
	this.maxHoursPerDay = constraints.hasOwnProperty('maxHoursPerDay') ? 
							parseInt(constraints.maxHoursPerDay) : Number.POSITIVE_INFINITY;	

	if (constraints.hasOwnProperty('noWorkAfter')) {
	  var str = constraints.noWorkAfter;
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

	  // Set the time in military time.
      this.noWorkAfter = val;
	} else {
		this.noWorkAfter = null;
	}

	this.numOpenHours = numOpenHours;
	this.weekdays = ['mon', 'tue', 'wed', 'thu', 'fri'];

	var days = {};
	// Calculate the number of days that the writing center is open.
	for (var day in openHours) {
		if (openHours.hasOwnProperty(day)) {
			for (var hour in openHours[day]) {
				if (openHours[day].hasOwnProperty(hour)) {
					if (openHours[day][hour] > 0) {
						days[day] = 1;
					}
				}
			}
		}
	}

	this.numOpenDays = (Object.keys(days)).length;
	//console.log('Num open days: ', this.numOpenDays);
}

ValidityChecker.prototype.reduceNumberOfSlots = function(schedule,
														 preferenceSetting,
														 tutor,
														 slots) {
	var addnHrs = this.constraints.addnConstraints;
	var newSlots = [];
	for (var i = 0; i < slots.length; ++i) {
		var day = slots[i].day;
		var hour = slots[i].hour;
		if (tutor.preferences && tutor.preferences[day] && tutor.preferences[day][hour])
			if (parseInt(tutor.preferences[day][hour]) >= preferenceSetting)
				newSlots.push(slots[i]);
	}

	// console.log('Previous value of slots', slots.length);
	// console.log('Length of new slots', newSlots.length);
	slots = newSlots;

	var reducedSlots = [];
	for (var i = 0; i < slots.length; ++i) {
		var slot = slots[i];
		if (schedule[slot.day] && schedule[slot.day][slot.hour]) {
			var numTutors = schedule[slot.day][slot.hour].length;

			if (addnHrs[slot.day] && addnHrs[slot.day][slot.hour]) {
				if (numTutors < addnHrs[slot.day][slot.hour].max) {
					reducedSlots.push(slot);
				}
			} else {
				if (numTutors < this.maxTutorsPerHour) {
					reducedSlots.push(slot);
				}
			}
		} else {
			reducedSlots.push(slot);
		}
	}

	return reducedSlots;
}

ValidityChecker.prototype.hasOneHourWeekday = function(combination) {
	for (var i = 0; i < combination.length; ++i) {
		if (this.weekdays.indexOf(combination[i].day)) {
			var hour = parseInt(combination[i].hour.replace('h',''));
			if (9 <= hour && hour <= 16)
				return true;
		}
	}

	return false;
}

ValidityChecker.prototype.respectsMaxHoursPerDay = function(combination) {
	var days = {};

	for (var i = 0; i < combination.length; ++i) {
		days[combination[i].day] = 0;
	}

	for (var i = 0; i < combination.length; ++i) {
		++days[combination[i].day];
		var dayCount = days[combination[i].day];
		if (dayCount > this.maxHoursPerDay) {
			return false;
		}
	}

	return true;
}

ValidityChecker.prototype.worksOneEvening = function(combination) {
	for (var i = 0; i < combination.length; ++i) {
		var hour = parseInt(combination[i].hour.replace('h',''));
		if (hour >= 16)
			return true;
	}

	return false;
}

ValidityChecker.prototype.doesNotOnlyWorkAfter = function(combination) {
	for (var i = 0; i < combination.length; ++i) {
		var hour = parseInt(combination[i].hour.replace('h',''));
		if (hour < this.noWorkAfter)
			return true;
	}

	return false;
}

ValidityChecker.prototype.doesNotHaveTwoDaysOff = function(combination) {
	var days = {};
	for (var i = 0; i < combination.length; ++i) {
		var day = combination[i].day;
		days[day] = 1;
	}

	var numDaysWorking = Object.keys(days).length;
	// console.log('Num days working', numDaysWorking);
	// console.log('Num open days', this.numOpenDays);
	return numDaysWorking >= this.numOpenDays - 2;
}

ValidityChecker.prototype.validTutorSchedule = function(combination, tutor) {
	var hasOneHourWeekday = this.hasOneHourWeekday(combination);
	if (!hasOneHourWeekday)
		return false;

	var respectsMaxHoursPerDay = this.respectsMaxHoursPerDay(combination);
	if (!respectsMaxHoursPerDay)
		return false;

	var worksOneEvening = this.worksOneEvening(combination);
	if (!worksOneEvening)
		return false;

	// If the "Tutor does not work after some time slot" is set, then enforce
	// the constraint.
	if (this.noWorkAfter) {
		var doesNotOnlyWorkAfter = this.doesNotOnlyWorkAfter(combination);
		if (!doesNotOnlyWorkAfter)
			return false;
	}
	// If the tutor is a grdaduate student, he can't take more than 2 
	// consecutive days off.
	if (tutor.type == 'grad') {
		var doesNotHaveTwoDaysOff = this.doesNotHaveTwoDaysOff(combination);
		if (!doesNotHaveTwoDaysOff)
			return false;
	}

	return true;
}

ValidityChecker.prototype.constraintsViolated = function(schedule) {
	for (var day in schedule) {
		if (schedule.hasOwnProperty(day)) {
			for (var hour in schedule[day]) {
				if (schedule[day].hasOwnProperty(hour)) {
					// Fix the problem of ranges for tutors in the generation of 
					// combinations.
					if (schedule[day][hour].length > this.maxTutorsPerHour) {
						return true;
					}
				}
			}
		}
	}

	return false;
}

ValidityChecker.prototype.validSolution = function(schedule, unscheduledTutors, openHours) {
	var keys = Object.keys(schedule);
	if (keys.length == 0) 
      return false;

  	if (unscheduledTutors.length != 0) {
		return false;
  	}

  	var addnHrs = this.constraints.addnConstraints;
  	// Need to see if its in the special constraints.
	for (var day in openHours) {
		if (openHours.hasOwnProperty(day)) {
			for (var hour in openHours[day]) {
				if (openHours[day].hasOwnProperty(hour)) {
					if (parseInt(openHours[day][hour]) > 0) {
						if (!(schedule[day] && schedule[day][hour])) {
							return false;
						} else {
							var numTutors = schedule[day][hour].length;
							if (addnHrs[day] && addnHrs[day][hour]) {
								var min = addnHrs[day][hour].min;
								if (numTutors < min) 
									return false;
							} else {
								if (numTutors < this.minTutorsPerHour) {
									return false;
								}
							}

						}
					}
				}
			}
		}
	}
	return true;
}

// Class to generate combinations of schedules.
function Combination(list, k, tutor, schedule, constraints) {
  this.combination = [];
  this.v = [];
  this.initializeList(list, tutor, schedule, constraints);
  this.k = k;
  this.n = list.length;

  for (var x = 0; x < this.n; ++x) {
  	if (x >= k) {
  		this.v.push(true);
  	} else {
  		this.v.push(false);
  	}
  }
}

Combination.prototype.initializeList = function(list, tutor, schedule, constraints) {
  this.shuffle(list);
	var numSlots = 0;
	var numTutors = 0;
	for (var day in schedule) {
		if (schedule.hasOwnProperty(day)) {
			for (var hour in schedule[day]) {
				if (schedule[day].hasOwnProperty(hour)) {
					numTutors += schedule[day][hour].length;
					++numSlots;
				}
			}
		}
	}

	var avgTutors = numTutors / numSlots;
  // Now rearrange the list so that the highest preferences come first.
  var lists = [[],[],[], []];

  var prefValue = Math.random() >= 0.5 ? 2 : 3;
  for (var i = 0; i < list.length; ++i) {
  	var day = list[i].day;
  	var hour = list[i].hour;
  	var pref = parseInt(tutor.preferences[day][hour]);
  	//lists[pref - 1].push(list[i]);
  	if (( !(schedule[day] && schedule[day][hour]) || schedule[day][hour].length < avgTutors) &&
  		pref >= prefValue) {
  		lists[3].push(list[i]);
  	} else {
		lists[pref - 1].push(list[i]);
  	}
  }

  var rearrangedList = [];
  for (var i = lists.length - 1; i >= 0; --i) {
  //for (var i = 0; i < lists.length; ++i) {
  	for (var j = 0; j < lists[i].length; ++j) {
  		rearrangedList.push(lists[i][j]);
  	}
  }

  // console.log(rearrangedList);
  // console.log('rearrangedList length:', rearrangedList.length);
  // console.log('list length:', list.length);

  this.list = rearrangedList;
}

Combination.prototype.shuffle = function(o) {
	for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
}

Combination.prototype.nextCombination = function() {
	if (!this.v)
		return false;

	this.combination = [];
	for (var x = 0; x < this.n; ++x) {
		if (!this.v[x]) {
		//if (this.v[x]) {
			var val = this.list[x];
			this.combination.push(val);
		}
	}

	this.v = this.nextPermutation(this.v, this.n - 1);
	return this.combination;
}

Combination.prototype.nextPermutation = function(p, size) {
	var i;
    // slide down the array looking for where we're smaller than the next guy
    for (i = size - 1; p[i] >= p[i+1]; --i) { }

    // if this doesn't occur, we've finished our permutations
    // the array is reversed: (1, 2, 3, 4) => (4, 3, 2, 1)
    if (i == -1) { return false; }

    var j;
    // slide down the array looking for a bigger number than what we found before
    for (j = size; p[j] <= p[i]; --j) { }

    // swap them
    var tmp = p[i]; p[i] = p[j]; p[j] = tmp;

    // now reverse the elements in between by swapping the ends
    for (++i, j = size; i < j; ++i, --j) {
         tmp = p[i]; p[i] = p[j]; p[j] = tmp;
    }

    return p;
}

// Main class that contains the actual scheduling algorithm.
function Scheduler(problemInstance) {
	this.problemInstance = problemInstance;
	this.validityChecker = new ValidityChecker(problemInstance.constraints,
											   problemInstance.numOpenHours,
											   problemInstance.openHours);
	//console.log(this.validityChecker);
	this.n = 0;
	this.stop = false;
	// Lowest preference by the tutor that will be included when trying to 
	// find a valid schedule.
	this.preferenceSetting = problemInstance.preferenceSetting;
	//console.log('Preference setting:', problemInstance.preferenceSetting);
}

// Pick next tutor to schedule.
Scheduler.prototype.pickTutor = function() {
	var tutor;
	// We are relying on randomness here; we had back luck with fail-first
	// approaches to picking the tutor. Since we're multithreading things 
	// anyway, we want randomness. To this end, shuffle the list of tutors and 
	// return a random one.
	Shuffle(this.unscheduledTutors);
	tutor = this.unscheduledTutors.shift();

	if (!tutor)
		return tutor;

	this.scheduledTutors.push(tutor);
	return tutor;
}

// Core algorithm that generates the schedule.
Scheduler.prototype.generateSchedule = function() {
	this.schedule = {};
	this.scheduledTutors = [];
	this.unscheduledTutors = [];
	this.bestSchedule = {};
	this.bestScheduleQuality = Number.NEGATIVE_INFINITY;

	var group = this.validityChecker.group;

	// Copy the relevant tutors to the unscheduled list.
	var keys = Object.keys(this.problemInstance.tutors);
	for (var i = 0; i < keys.length; ++i) {
		var tutor = this.problemInstance.tutors[keys[i]];
		// Don't add tutor with no available times.
		if (tutor.availableTimes.length == 0)
			continue;
		// Only schedule tutors based on the constraints.
		var type = tutor.type;

		if (group == 'All') {
			if (type == 'grad' || type == 'ugrad') {
				this.unscheduledTutors.push(tutor);
			}
		} else if (group == 'Undergraduates') {
			if (type == 'ugrad') {
				this.unscheduledTutors.push(tutor);
			}
		} else if (group == 'Graduate Students') {
			if (type == 'grad')
				this.unscheduledTutors.push(tutor);	
		}
	}

	// Meat of the algorithm, call the recursive function.
	this.generateScheduleHelper();
	console.log('Done with Backtracking search.');
}

// Returns the ratio of the time slots filled with tutors.
Scheduler.prototype.getRatioOfSlotsFilled = function(schedule) {
    var numValidDays = 0;
    for (var day in schedule) {
    	if (schedule.hasOwnProperty(day)) {
    		for (var hour in schedule[day]) {
    			if (schedule[day].hasOwnProperty(hour)) {
    				var n = schedule[day][hour].length;
    				if (n < this.minTutorsPerHour || n > this.maxTutorsPerHour) {
    					if (n > this.maxTutorsPerHour)
    						return false;
    				} else {
    					++numValidDays;
    				}
    			}
    		}
    	}
    }

    var ratio = numValidDays/this.validityChecker.numOpenHours;
    //console.log(ratio);
    return ratio;
}

// For evaluation purposes, calculates the average preference that the schedule
// meets for a tutor. The range is [1,3] with 1 being the worst and 3 being
// the best.
Scheduler.prototype.calculateAveragePreferenceMet = function(schedule) {
	// Preference values divided by number of slots.
	var sumPreferences = 0;
	var sumCount = 0;

	for (var day in schedule) {
		if (schedule.hasOwnProperty(day)) {
			for (var hour in schedule[day]) {
				if (schedule[day].hasOwnProperty(hour)) {
					// Fix the problem of ranges for tutors in the generation of 
					// combinations.
					for (var i = 0; i < schedule[day][hour].length; ++i) {
						var tutor = schedule[day][hour][i];
						sumPreferences += parseInt(tutor.preferences[day][hour]);
						++sumCount;
					}
				}
			}
		}
	}
	var avgPreference = sumPreferences / sumCount;
	return avgPreference;
}

// Calculates the uniformity of the current schedule. The uniformity basically
// gives us a quantitative way to measure "balance" of work across time slots.
// Mathematically, this is calculated by taking the mean number of tutors per
// time slot, then using this average to calculate the variance of the 
// number of tutors in each time slot. We return a negative value of the
// variance. This is because we are trying to MAXIMIZE the objective function,
// hence high variance should be punished by subtracting a high number from the
// objective value.
Scheduler.prototype.calculateUniformity = function(schedule) {
	var numSlots = 0;
	var numTutors = 0;
	for (var day in schedule) {
		if (schedule.hasOwnProperty(day)) {
			for (var hour in schedule[day]) {
				if (schedule[day].hasOwnProperty(hour)) {
					// Fix the problem of ranges for tutors in the generation of 
					// combinations.
					numTutors += schedule[day][hour].length;
					++numSlots;
				}
			}
		}
	}

	var avgTutors = numTutors / numSlots;
	var deviation = 0;
	for (var day in schedule) {
		if (schedule.hasOwnProperty(day)) {
			for (var hour in schedule[day]) {
				if (schedule[day].hasOwnProperty(hour)) {
					// Fix the problem of ranges for tutors in the generation of 
					// combinations.
					deviation += Math.pow(schedule[day][hour].length - avgTutors, 2);
				}
			}
		}
	}

	deviation /= numTutors;
	// Return the NEGATIVE of the deviation (since we are trying to maximize value).
	return -deviation;
}

Scheduler.prototype.calculateIdealHoursMet = function(schedule) {

	var undergradTutors = [];
	for (var i = 0; i < this.scheduledTutors.length; ++i) {
		var tutor = this.scheduledTutors[i];
		var pid = tutor.pid;

		if (tutor.type == 'ugrad') 
			tutors[pid] = 0;
	}



}

// Calculates the contiguity of the tutor's time slots. The more contiguous the
// time slots, the better.
Scheduler.prototype.calculateContiguity = function(schedule) {
	var maxContiguity = {};
	var tutors = {};
	for (var i = 0; i < this.scheduledTutors.length; ++i) {
		var tutor = this.scheduledTutors[i];
		var pid = tutor.pid;
		tutors[pid] = 0;
		maxContiguity[pid] = 0;
	}

	for (var day in schedule) {
		for (var i = 0; i < this.scheduledTutors.length; ++i) {
			var tutor = this.scheduledTutors[i];
			var pid = tutor.pid;
			tutors[pid] = 0;
		}
		if (schedule.hasOwnProperty(day)) {
			for (var hour in schedule[day]) {
				if (schedule[day].hasOwnProperty(hour)) {
					var list = schedule[day][hour];
					var addedList = [];
					for (var i = 0; i < list.length; ++i) {
						var tutor = list[i];
						addedList.push(parseInt(tutor.pid));
						tutors[tutor.pid]++;
						if (tutors[tutor.pid] > maxContiguity[tutor.pid]) {
							maxContiguity[tutor.pid] = tutors[tutor.pid];
						}
					}

					for (var pid in tutors) {
						if (tutors.hasOwnProperty(pid)) {
							if (addedList.indexOf(parseInt(pid)) == -1) {
								tutors[pid] = 0;
							} 
						}
					}

				}
			}
		}
	}

	var numContiguousSpots = 0;
	for (var pid in maxContiguity) {
		if (maxContiguity.hasOwnProperty(pid))
			numContiguousSpots += maxContiguity[pid];
	}
	return {value:numContiguousSpots/1000, maxContiguity:maxContiguity};
}

Scheduler.prototype.evaluateSchedule = function(schedule) {
	// TODO: Constants...
	var uniformityMultiplier = 2;
	// Want to maximize the preference values of each tutor, so add up all
	// the preference values.
	var preferenceValues = this.calculateAveragePreferenceMet(schedule);
	//var ratio = this.getRatioOfSlotsFilled(schedule);
	var uniformity = this.calculateUniformity(schedule);
	var contiguity = this.calculateContiguity(schedule);	
	var quality = preferenceValues + uniformityMultiplier*uniformity + 
				  contiguity.value;
	// if (quality > this.bestScheduleQuality) {
	// 	// console.log('Preference values:', preferenceValues);
	// 	// console.log('Uniformity:', uniformity);
	// 	// console.log('Contiguity:', contiguity.value);
	// }
	return {quality:quality/4.0, avgPreference:preferenceValues,
			 contiguity:contiguity.value*10, uniformity:uniformity};
}

Scheduler.prototype.generateScheduleHelper = function() {
  // If this is a valid solution, evaluate its objective value and update the 
  // best solution thus far accordingly.
  if (this.validityChecker.validSolution(this.schedule, this.unscheduledTutors, this.problemInstance.openHours)) {
  	var currentQuality = this.evaluateSchedule(this.schedule);
  	if (currentQuality.quality > this.bestScheduleQuality) {
  		//console.log('Valid solution!');
  		// console.log('Average preference value:', avgPreference);
  		// console.log('Found new best schedule with quality:', currentQuality);
  		// Found a new best schedule, send it to the main thread (UI thread).
  		this.bestScheduleQuality = currentQuality.quality;
  		var msg = {schedule:this.schedule, quality: currentQuality};
  		self.postMessage(JSON.stringify(msg));
  	}
  	// Return the currentQuality.
    return currentQuality.quality;
  }

  // Pick a tutor to try.
  var tutor = this.pickTutor();
  //console.log(tutor);
  if (!tutor) {
    // No tutors left to pick means that this is not a valid solution.
    return false;
  }

  var slots = tutor.availableTimes;

  if (slots.length === 0) {
    // If the tutor did not specify his or her preferences, skip to the next
    // tutor.
    return this.generateScheduleHelper();
  }

  // Reduce available times here by looking at the current schedule and 
  // incorporating look ahead to see if some slots are simply infeasible to be
  // filled by the current tutor (e.g. slots already full).
  slots = this.validityChecker.reduceNumberOfSlots(this.schedule, 
  												   this.preferenceSetting,
  												   tutor,
  												   slots);

  var minCanWork;
  var maxCanWork;
  var idealCanWork = null; // This value is only set for undergrads.
  var type = tutor.type;

  // All graduate students must work a set amount of hours, determined by the
  // user. However, undergraduates have more flexibility, so for undergrads,
  // retrieve the min max and ideal hours that they would like to work.
  if (type == 'grad') {
  	minCanWork = this.problemInstance.constraints.graduateHours;
  	maxCanWork = this.problemInstance.constraints.graduateHours;
  } else {
  	minCanWork = parseInt(tutor.minCanWork);
  	maxCanWork = parseInt(tutor.maxCanWork);
  	idealCanWork = parseInt(tutor.idealCanWork);
  }

  if (slots.length < minCanWork) {
    // There are not enough remaining time slots for the tutor to occupy,
    // this implies that the solution is not valid. Return false.
    this.scheduledTutors.pop();
    this.unscheduledTutors.push(tutor);
    return false;
  }

  var numSlots = slots.length;
  // TODO: QUERY TUTOR PREFERENCES OR POPULATE THEM IN THE FIRST PLACE.
  // Try all numbers between [minHoursCanWork, maxHoursCanWork]. These numbers
  // should be decided by the current tutor.

  // Need a smarter choice of K here.
  var nums = [];
  for (var x = maxCanWork; x >= minCanWork; --x) {
  	//if (!idealCanWork || (idealCanWork && x != idealCanWork))
  		nums.push(x);
  }

  // If the tutor is an undergrad, then try his or her most ideal first. This is
  // the "succeed-first" heuristic.
  if (idealCanWork) {
  	// Add the ideal number of hours as the first number of hours to try (k).
  	//nums.unshift(idealCanWork);
  }
  // Heuristic: An attempt to speed things up...
  // If we have this many low quality results (lower quality than best), then
  // skip to the next combination of k.
  var kNumLowQualityResultThreshold = 10;
  var numLowQualityResults = 0;
  var bestFoundQuality = false;

  for (var i = 0; i < nums.length; i++) {
  	var x = nums[i];
  	//console.log('Trying the value of k:', x);
    // Generate all possible combinations of time slots that the tutor can work.
    var combinationObj = new Combination(slots, x, tutor, 
    									this.schedule,
    									this.problemInstance.constraints);
    var combCount = 0;
    while (combinationObj.nextCombination()) {
		// if (++combCount % 100000 == 0) {
		// 	console.log(combCount);
		// }
      var timeSlots = combinationObj.combination;
      // Implement tutor time constraints.
      if (!this.validityChecker.validTutorSchedule(timeSlots, tutor)) {
     	continue;
      }

      // For each time slot, add the current tutor to the schedule.
      for (var i = 0; i < timeSlots.length; ++i) {
      	var time = timeSlots[i];
      	// If undefined, initialize array
      	if (!this.schedule[time.day]) {
      		this.schedule[time.day] = {};
      	}

      	if (!this.schedule[time.day][time.hour]) 
      		this.schedule[time.day][time.hour] = [];

        this.schedule[time.day][time.hour].push(tutor);
      }

      // Run the algorithm recursively...
      var result = this.generateScheduleHelper();

      // Remove tutor from all time slots.
      for (var i = 0; i < timeSlots.length; ++i) {
      	var time = timeSlots[i];
        this.schedule[time.day][time.hour].pop();
      }

      // // An attempt to speed things up (didn't work well)
      if (result) {
      	if (bestFoundQuality === false) {
      		bestFoundQuality = result;
      	} else if (bestFoundQuality < result) {
      		bestFoundQuality = result;
      	}

      	// This means that the schedule succeeded.
      	if (result < this.bestScheduleQuality) {
      		if (++numLowQualityResults >= kNumLowQualityResultThreshold) {
      			//console.log('Too many low quality results, skipping this k combinations');
      			numLowQualityResults = 0;
      			// Try another combination.
      			break;
      		}
      	} else {
      		numLowQualityResults = 0;
      		//kNumLowQualityResultThreshold *= 10;
      	}
      }

    }
  }
  // Add tutor back to the unscheduled tutors list so that it can be considered
  // for scheduling in other branches of the tree.
  this.scheduledTutors.pop();
  this.unscheduledTutors.push(tutor);

  return bestFoundQuality;
}