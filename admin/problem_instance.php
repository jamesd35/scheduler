<?php

class ProblemInstance {
	public $tutors;
	public $openHours;
	public $numOpenHours;

	function __construct($tutors, $openHours, $numOpenHours) {
		$this->tutors = $tutors;
		$this->openHours = $openHours;
		$this->numOpenHours = $numOpenHours;
	}
};