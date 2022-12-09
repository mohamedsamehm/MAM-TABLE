<?php

	// Error Reporting
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	include 'includes/connect.php';

	// Routes

	$func	= 'includes/functions/'; // Functions Directory
	$tpl 	= 'includes/templates/'; // Template Directory
	$css 	= 'assets/css'; // Css Directory
	$js 	= 'assets/js'; // Js Directory
	// Include The Important Files

	include $func . 'functions.php';
	include $tpl . 'header.php';