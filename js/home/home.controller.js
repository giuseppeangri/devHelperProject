(function() {
	
	'use strict';
	
	angular
		.module('devHelper.home')
		.controller('HomeController', HomeController);
		
	HomeController.$inject = ['$scope', '$rootScope', '$location'];
	
	function HomeController($scope, $rootScope, $location) {
		
		var hc = this;
		
		hc.inizialize  = inizialize;
		hc.search      = search;
		hc.querySearch = querySearch;
				
		hc.inizialize();
		
		return hc;
		
		function inizialize() {
			
			$scope.search = $rootScope.search;
			
			$scope.types = {
				videotutorials : {
					title : 'VideoTutorials'
				},
				tutorials : {
					title : 'Tutorials'
				},
				books : {
					title : 'Books'
				},
				events : {
					title : 'Events'
				},
				groups : {
					title : 'Groups'
				},
				documentations : {
					title : 'Documentations'
				},
				frameworks : {
					title : 'Frameworks'
				}
			}
			
			$scope.selectedType = 'videotutorials';
			
			$rootScope.inputKeyword = '';
											
		}
		
		function search() {
			
			if($scope.inputKeyword) {
				
				$rootScope.inputKeyword = $scope.inputKeyword;
				$rootScope.selectedType = $scope.selectedType;		
				$rootScope.types        = $scope.types;
												
				$location.path("/search");
				
			}
			
		}
		
		function querySearch (query) {
      
      var results = query ? $scope.search.filter( createFilterFor(query) ) : $scope.search, deferred;
      return results;
      
    }
		
		function createFilterFor(query) {
      var lowercaseQuery = angular.lowercase(query);
      return function filterFn(state) {
	      var lowercaseState = angular.lowercase(state);
        return (lowercaseState.indexOf(lowercaseQuery) === 0);
      };
    }
		
	}
	
})();