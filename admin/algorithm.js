// Worker thread that runs the algorithm itself (scheduler.js).
// Authors: Cenk Baykal
//importScripts('../common/jquery-1.10.2.min.js');
importScripts('scheduler.js');
// Include the algorithm itself.
self.addEventListener('message', function(e) {
	// self.postMessage('I see the message');
	var problemInstance = JSON.parse(e.data);
	var scheduler = new Scheduler(problemInstance);
	scheduler.generateSchedule();
}, false);