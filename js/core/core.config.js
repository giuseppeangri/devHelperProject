(function() {
	
	'use strict';
	
	angular
		.module('devHelper.core')
		.config(config)
		.run(run);
	
	config.$inject = ['$httpProvider', '$routeProvider', '$locationProvider', '$mdThemingProvider'];	
	
	function config($httpProvider, $routeProvider, $locationProvider, $mdThemingProvider) {
		
		var baseUrl = 'views/';
		
		$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
		
		$routeProvider
			.when('/home', {
				templateUrl 	: baseUrl + 'home.html',
				controller 		: 'HomeController',
				controllerAs 	: 'HomeCtrl'
			})
			.when('/search', {
				templateUrl 	: baseUrl + 'search.html',
				controller 		: 'SearchController',
				controllerAs 	: 'SearchCtrl'
			})
			.otherwise({
	      redirectTo		: '/home'
	    });
	    
		$locationProvider.html5Mode(true);	  
		
		$mdThemingProvider.theme('default')
	    .backgroundPalette('blue-grey' ,{
		    'default' : '50',
	      'hue-1': '50',
	    })
	    .primaryPalette('cyan', {
	      'default': '900',
	      'hue-1': '50',
	    })
	    .accentPalette('blue-grey', {
	      'hue-1': '50',
	      'hue-2': '100',
	    });  
		
	}
	
	run.$inject = ['$rootScope', '$http', 'WS_URL'];
	
	function run($rootScope, $http, WS_URL) {
		
		$http.get(
			WS_URL.search
		).then(function(res) {
					
			$rootScope.search = res.data.data;
			
		});
		
	}
	
})();