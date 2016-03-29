(function() {
	
	'use strict';
	
	angular
		.module('devHelper')
		.directive('fullscreen', fullscreen);
	
	fullscreen.$inject = ['$window'];
	
	function fullscreen($window) {
		
		var directive = {
			link : link
		};
		
		return directive;
		
		function link(scope, element, attrs) {
	      
      var setDimensions = function() {
				var height 	= $window.innerHeight +'px';

				if(attrs.fullscreen) {
					height = ($window.innerHeight - attrs.fullscreen) +'px';
				}
		    
	      element[0].style.height    = height;
	      element[0].style.minHeight = height;
	      element[0].style.width     = '100%';
      }
      
	    setDimensions();
	    
	    angular.element($window).on('resize', function() {
        scope.$apply(function(){
	        
	        setDimensions();
	        
        });
      });
			
		}
		
	}
	
})();