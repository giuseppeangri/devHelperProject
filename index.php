<?php
	$baseUrl = 'http://';
	$origin = str_replace('index.php', '', $_SERVER['PHP_SELF']);
	$baseUrl = $baseUrl.$_SERVER['SERVER_NAME'].$origin;
?>

<!DOCTYPE html>
<html ng-app="devHelper">

	<head>
		
		<title>DevHelper</title>
		<base href="<?php echo $baseUrl ?>" />
		
		<meta name="author" content="Giuseppe Angri, Giovanni De Costanzo">
		
		<meta charset="utf-8">
		<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no" />
		
		<!-- Angular Material -->
    <link rel="stylesheet" href="bower_components/angular-material/angular-material.css">
    
		<!-- Material icons -->
    <link rel="stylesheet" href="media/iconfont/material-icons.css">
    
		<!-- Custom CSS -->
		<link href="css/main.css" rel="stylesheet">
				
	</head>

	<body>
		
		<md-content ng-view></md-content>
		
		<script src="bower_components/angular/angular.min.js"></script>
		<script src="bower_components/angular-route/angular-route.min.js"></script>
		<script src="bower_components/angular-resource/angular-resource.min.js"></script>
    <script src="bower_components/angular-aria/angular-aria.js"></script>
		<script src="bower_components/angular-animate/angular-animate.min.js"></script>
		<script src="bower_components/angular-sanitize/angular-sanitize.min.js"></script>
    <script src="bower_components/angular-material/angular-material.js"></script>
    <script src="bower_components/angular-i18n/angular-locale_it-it.js"></script>
		
		<script src="js/app.module.js"></script>
		
		<script src="js/directives/fullscreen.directive.js"></script>
		<script src="js/directives/onEnter.directive.js"></script>
		
		<script src="js/core/core.module.js"></script>
		<script src="js/core/core.config.js"></script>
		
		<script src="js/home/home.module.js"></script>
		<script src="js/home/home.controller.js"></script>
		
		<script src="js/search/search.module.js"></script>
		<script src="js/search/search.controller.js"></script>
				
	</body>

</html>