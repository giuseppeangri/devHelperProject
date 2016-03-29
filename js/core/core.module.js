(function() {
	
	'use strict';
	
	angular
		.module('devHelper.core', [
			'ngRoute',
			'ngResource',
			'ngAnimate',
			'ngMaterial',
			'ngSanitize',
		])
		.constant('WS_URL', {
			search 							: 'webservice/search.php',
			videotutorials 			: 'webservice/videotutorial.php',
			tutorials 					: 'webservice/tutorial.php',
			books 							: 'webservice/book.php',
			book_price		 			: 'webservice/book_price.php',
			events 							: 'webservice/event.php',
			groups 							: 'webservice/group.php',
			teIdes 							: 'webservice/te_ide.json',
			documentations 			: 'webservice/documentation.php',
			frameworks 					: 'webservice/framework.php'
		});
	
})();