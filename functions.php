<?php

	namespace StudyPlanner;

	function load_template( $template ) {
		require 'templates/' . $template . '.php';
	}

	function get_template_path( $template ) {
		return __DIR__ . '/templates/' . $template . '.php';
	}

