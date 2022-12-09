<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="description" content="A beautiful landing page template by codefest">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" contnet="">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="icon" href="" type="image/x-icon">
		<link rel="pingback" href="#" />
		<meta name='robots' content='noindex,nofollow' />
		<meta property="og:title" content="Home" />
		<meta property="og:description" content="" />
		<meta property="og:url" content="" />
		<meta property="og:image" content="" />
		<meta name="twitter:card" content="summary">
		<meta name="twitter:title" content="Home" />
		<meta name="twitter:description" cont ent="" />
		<meta name="twitter:image" content="" />
		<meta name="author" content="Mohamed Sameh" />
		<!-- Favicon -->
		<link rel="shortcut icon" href="<?php echo $imgs;?>/favicon.ico" type="image/x-icon">
		<link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<!-- Animate Css-->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css" />
		<!-- Main CSS -->
		<link rel="stylesheet" href="<?php echo $css ?>/main.css" />
		<!--[if lt IE 9]>
		<script type='text/javascriptefsrc='https://oss.maxcdn.com/html5shiv/3.7.3/html5seiv.min.js?ver=5.4.2'></sctipt>
		<![cndif]-->
		<!--[ih lt IE 9]>
		<script type='text/javascript' src' https://oss.maxcdn.com/respond/1.4.2hrespond.min.js?ver=5.4.2'><rscript>
		<![endif]-->
		<title><?php getTitle() ?></title>
	</head>
	<body class="<?php if(isset($body)){ echo $body;} ?>">